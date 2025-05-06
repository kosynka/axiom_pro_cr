<?php

declare(strict_types=1);

namespace App\Services\PaymentStrategy;

use App\Dto\Payment\CreatePaymentDto;
use App\Enums\TransactionStatus;
use App\Enums\UserServiceScheduleType;
use App\Exceptions\Balance\NotEnougBalanceException;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Contracts\PaymentStrategy;

class BalancePaymentStrategy implements PaymentStrategy
{
    public function handlePayment(CreatePaymentDto $args, User $user): UserServiceScheduleType
    {
        if ($user->balance < $args->price) {
            throw new NotEnougBalanceException();
        }

        $user->balance -= $args->price;
        $user->save();

        Transaction::create([
            'user_id' => $user->id,
            'type' => $args->transaction_type,
            'amount' => $args->price,
            'status' => TransactionStatus::SUCCESS,
        ]);

        return UserServiceScheduleType::PRIMARY;
    }
}
