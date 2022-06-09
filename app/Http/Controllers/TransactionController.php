<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportTransactionsRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    private $repository;

    public function __construct()
    {
        $this->repository = new \App\Repositories\TransactionRepository();
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $result = ['success' => true, 'data' => array()];
        try {

            if(!$request->user_id) {
                throw new \Exception('O campo user_id é obrigatório');
            }

            $user = User::where('id', $request->user_id)->first();

            if(!$user) {
                throw new \Exception('Usuário não encontrado.');
            }

            $paginator = Transaction::where('user_id', $request->user_id)->orderBy('created_at', 'desc')->paginate(5);

            $result['data'] = [
                'user' => $user,
                'transactions' => $paginator
            ];

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = 'Erro ao buscar Movimentações - '. $e->getMessage();
        }

        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTransactionRequest $request
     * @return JsonResponse
     */
    public function store(StoreTransactionRequest $request)
    {
        $result = ['success' => true, 'message' => 'Movimentação Cadastrada com sucesso.', 'transaction_id' => null];
        try {

            if($request->transaction_reference_id && !Transaction::where('id', $request->transaction_reference_id)->first()) {
                throw new \Exception('A Movimentação de referência não existe');
            }

            $transaction = new \App\Models\Transaction($request->toArray());
            $transaction->save();

            $result['transaction_id'] = $transaction->id;

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = 'Erro ao salvar movimentação. '. $e->getMessage();
        }
        return response()->json($result);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $result = ['success' => true, 'data' => null];

        $result['data'] = Transaction::where('id', $id)->first();

        if(!$result['data']) {
            $result['success'] = false;
            $result['message'] = 'Movimentação não encontrada';
        }

        return response()->json($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTransactionRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateTransactionRequest $request, $id)
    {
        $result = ['success' => true, 'message' => 'Movimentação atualizada com sucesso.'];
        try {

            $check = Transaction::where('id', $id)->update($request->only(['name', 'description', 'value']));

            if(!$check) {
                throw new \Exception();
            }

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = 'Erro ao salvar movimentação. '. $e->getMessage();
        }
        return response()->json($result);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $result = ['success' => true, 'message' => 'Movimentação deletada com sucesso.'];
        try {

            $checkHasChargeback = Transaction::where('transaction_reference_id', $id)->first();
            if($checkHasChargeback) {
                throw new \Exception('A movimentação foi estornada, é necessário exluir o estorno primeiramente.');
            }

            $check = Transaction::where('id', $id)->delete();

            if(!$check) {
                throw new \Exception();
            }

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = 'Erro ao deletar movimentação. '. $e->getMessage();
            $result['transaction_chargeback_id'] = $checkHasChargeback ? $checkHasChargeback->id : null;
        }
        return response()->json($result);
    }

    public function exportTransactions(ExportTransactionsRequest $request)
    {
        $user = User::find($request->user_id);
        $transactions = $this->repository->getTransactionsByUserIdFiltersOrDate($request->user_id, $request->filter, $request->date);
        $repositoryUser = new UserRepository();

        $userHeader = array(
            'Nome: '. $user->name,
            'Email: '. $user->email,
            'Nascimento: '. $user->birthday->format('d/m/Y'),
            'Saldo Inicial: ' . $user->opening_balance,
            'Saldo: '. $repositoryUser->getCurrentBalanceByUser($request->user_id)
        );

        $transactionHeader = array(
            'ID', 'Descrição', 'Tipo', 'Data', 'Valor'
        );


        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=export.csv');
        echo "\xEF\xBB\xBF";

        $csv = '"' . implode('";"',$userHeader) . '"';
        $csv .= "\n".'"' . implode('";"',$transactionHeader) . '"';

        foreach($transactions as $transaction) {
            $data = [
                $transaction->id,
                $transaction->name,
                $transaction->type,
                $transaction->created_at->format('d/m/Y'),
                $transaction->value,
            ];
            $csv .= "\n".'"' . implode('";"',$data) . '"';
        }

        echo $csv;
        exit;
    }
}
