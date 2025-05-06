<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\Payment\CreatePaymentDto;
use App\Exceptions\Payment\PaymentFailedException;
use App\Models\Program;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserProgram;
use App\Repositories\UserProgramRepository;
use App\Services\PaymentStrategy\PaymentContext;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private UserProgramRepository $userProgramRepository,
    ) {
    }

    public function buyProgram(string $strategy, Program $program, User $user): UserProgram
    {
        $args = CreatePaymentDto::from([
            'price' => $program->price,
            'transaction_type' => Transaction::TYPE_PURCHASE_PROGRAM,
        ]);

        $paymentContext = app(PaymentContext::class)->setPaymentStrategy($strategy);

        try {
            DB::beginTransaction();

            $paymentContext->executeStrategy($args, $user);

            $userProgram = $this->userProgramRepository->create($program, $user);

            $transaction = $user->transactions()->latest()->first();

            $paymentContext->bindTransaction($userProgram, $transaction);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw new PaymentFailedException($e->getMessage(), 0, $e);
        }

        return $userProgram;
    }
}
