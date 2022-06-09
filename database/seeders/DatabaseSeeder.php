<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->populateUsers();
        $this->populateTransactions();
    }

    private function populateUsers()
    {
        $generator = \Nubs\RandomNameGenerator\All::create();

        $i = 0;
        while($i < 500) {
            $name = $generator->getName();
            $timesampMinBirthday = \DateTime::createFromFormat('Y-m-d', '1975-01-01')->getTimestamp();
            $timestampMaxbirthday = \DateTime::createFromFormat('Y-m-d', '2004-01-01')->getTimestamp();

            $timesampMinCreated = \DateTime::createFromFormat('Y-m-d', '2020-01-01')->getTimestamp();
            $timestampMaxCreated = \DateTime::createFromFormat('Y-m-d', '2022-06-09')->getTimestamp();

            //Pode gerar emails e nomes iguais, por isso o try/catch
            try {
                \App\Models\User::create([
                    'name' => $name,
                    'email' => strtolower(str_replace(' ', '.', $name)) . '@mail.com',
                    'birthday' => date('Y-m-d', rand($timesampMinBirthday, $timestampMaxbirthday)),
                    'opening_balance' => rand(0, 99999),
                    'created_at' => date('Y-m-d', rand($timesampMinCreated, $timestampMaxCreated)),
                ]);
            } catch (\Exception $e) {

            }

             $i++;
        }
    }

    private function populateTransactions()
    {
        $generator = new \Nubs\RandomNameGenerator\Vgng();

        $timestampMaxCreated = \DateTime::createFromFormat('Y-m-d', '2022-06-09')->getTimestamp();

        $i = 0;
        while($i < 10000) {
            $type = array('debit', 'credit');
            $user = User::inRandomOrder()->first();
            $timesampMinCreated = $user->created_at->getTimestamp();

            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'name' => $generator->getName(),
                'type' => $type[array_rand($type)],
                'value' => rand(1, 99999),
                'created_at' => date('Y-m-d', rand($timesampMinCreated, $timestampMaxCreated)),
            ]);
            $i++;
        }

        $i = 0;
        while($i < 1000) {
            $transaction = \App\Models\Transaction::inRandomOrder()->where('type', '!=', 'chargeback')->doesnthave('transactions')->first();
            $timesampMinCreated = $transaction->created_at->getTimestamp();
            \App\Models\Transaction::create([
                'user_id' => $transaction->user_id,
                'transaction_reference_id' => $transaction->id,
                'name' => 'chargeback reference '. $transaction->id . ' - '. $transaction->name,
                'type' => 'chargeback',
                'value' => $transaction->value,
                'created_at' => date('Y-m-d', rand($timesampMinCreated, $timestampMaxCreated)),
            ]);
            $i++;
        }
    }
}
