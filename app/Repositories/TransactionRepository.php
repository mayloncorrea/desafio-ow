<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionRepository
{

    private $model;

    public function __construct()
    {
        $this->model = new Transaction();
    }

    public function getTransactionsByUserIdFiltersOrDate($userId, $filter, $date)
    {
        $transactions = $this->model::where('user_id', $userId);
        switch ($filter) {
            case 'last_thirty_days':
                $transactions->where('created_at', '>=', now()->subDays(30)->startOfDay());
                break;
            case null:
                $transactions->where(DB::raw("(DATE_FORMAT(created_at,'%m/%y'))"), $date);
                break;
        }
        return $transactions->orderBy('created_at', 'desc')->get();
    }
}
