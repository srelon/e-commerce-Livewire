<?php

namespace Database\Seeders;

use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void {
        $payments = [
            [
                'name' => 'Pay by Card Online',
                'key' => 'card',
            ],
            [
                'name' => 'Cash on Delivery',
                'key' => 'cash',
            ],
        ];

        foreach ($payments as $payment) {
            $record = Payment::withTrashed()->firstOrCreate(
                ['key' => $payment['key']],
                [
                    'name' => $payment['name'],
                    'status' => 1,
                ],
            );

            if ($record->trashed()) {
                $record->restore();
            }
        }
    }
}
