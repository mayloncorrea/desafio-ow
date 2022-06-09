<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository
{

    private $model;

    public function __construct()
    {
        $this->model = new User();
    }

    public function getCurrentBalanceByUser($userId)
    {
        $user = $this->model->find($userId);
        $transactionsCredit = $this->getTotalByUserIdAndTypeTransaction($userId);
        $transactionsDebit = $this->getTotalByUserIdAndTypeTransaction($userId, 'debit');

        return ($transactionsCredit->total + $user->opening_balance) - $transactionsDebit->total;
    }

    public function getTotalByUserIdAndTypeTransaction($userId, $type = 'credit')
    {
        return Transaction::select(DB::raw('SUM(value) AS total'))->where('user_id', $userId)->where('type', '=', $type)->doesntHave('transactions')->first();
    }
}
