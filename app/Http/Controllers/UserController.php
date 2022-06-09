<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $paginator = User::orderBy('created_at', 'desc')->paginate(5);
        return response()->json($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request) : JsonResponse
    {
        $result = ['success' => true, 'message' => 'Usuário salvo com sucesso.', 'user_id' => null];
        try {

            $user = new \App\Models\User($request->toArray());
            $user->save();

            $result['user_id'] = $user->id;

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = 'Erro ao salvar usuário. '. $e->getMessage();
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

        $result['data'] = User::where('id', $id)->first();

        if(!$result['data']) {
            $result['success'] = false;
            $result['message'] = 'Usuário não encontrado';
        }

        return response()->json($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $result = ['success' => true, 'message' => 'Usuário atualizado com sucesso.'];
        try {

            $check = User::where('id', $id)->update($request->only(['name', 'birthday', 'opening_balance']));

            if(!$check) {
                throw new \Exception();
            }

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = 'Erro ao salvar usuário. '. $e->getMessage();
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
        $result = ['success' => true, 'message' => 'Usuário deletado com sucesso.'];
        try {

            $user = User::where('id', $id)->first();
            if(!$user) {
                throw new \Exception('Usuário não encontrado.');
            }

            if(Transaction::where('user_id', $id)->count() || $user->opening_balance > 0) {
                throw new \Exception('Não é possivel excluir um usuário com movimentações cadastradas ou saldo.');
            }

            $check = User::where('id', $id)->delete();

            if(!$check) {
                throw new \Exception();
            }

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = 'Erro ao deletar usuário. '. $e->getMessage();
        }
        return response()->json($result);
    }

    public function currentBalance(Request $request)
    {
        $result = ['success' => true];
        try {
            if(!$request->user_id) {
                throw new \Exception('O parâmetro user_id é obrigatório');
            }

            $user = User::find($request->user_id);

            if(!$user) {
                throw new \Exception('Usuário não encontrado');
            }

            $repositoryUser = new UserRepository();
            $result['current_balance'] = $repositoryUser->getCurrentBalanceByUser($request->user_id);

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = 'Erro ao buscar Saldo - '. $e->getMessage();
        }

        return response()->json($result);
    }
}
