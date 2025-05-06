<?php

declare(strict_types=1);

namespace App\Services\PaymentStrategy;

use App\Dto\Payment\CreatePaymentDto;
use App\Enums\UserServiceScheduleType;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TransactionRepository;
use App\Services\Contracts\PaymentStrategy;
use Illuminate\Database\Eloquent\Model;

class PaymentContext
{
    private PaymentStrategy $paymentStrategy;

    public function __construct(
        private TransactionRepository $transactionRepository,
    ) {
    }

    public function setPaymentStrategy(string $type = 'balance'): self
    {
        switch ($type) {
            case 'balance':
                $this->paymentStrategy = new BalancePaymentStrategy();
                break;
            case 'program':
                $this->paymentStrategy = new ProgramPaymentStrategy();
                break;
            case 'abonnement':
                $this->paymentStrategy = new AbonnementPaymentStrategy();
                break;
            case 'abonnement_present':
                $this->paymentStrategy = new AbonnementPresentPaymentStrategy();
                break;
            default:
                $this->paymentStrategy = new BalancePaymentStrategy();
        }

        return $this;
    }

    public function executeStrategy(CreatePaymentDto $args, User $user): UserServiceScheduleType
    {
        if (!isset($this->paymentStrategy)) {
            $this->setPaymentStrategy();
        }

        return $this->paymentStrategy->handlePayment($args, $user);
    }

    public function bindTransaction(Model $transactionableModel, Transaction $transaction): bool
    {
        return $this->transactionRepository->bindTransaction($transactionableModel, $transaction);
    }
}
