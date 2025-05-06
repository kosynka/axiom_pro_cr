<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Dto\Payment\CreatePaymentDto;
use App\Enums\UserServiceScheduleType;
use App\Models\User;

interface PaymentStrategy
{
    public function handlePayment(CreatePaymentDto $args, User $user): UserServiceScheduleType;
}
