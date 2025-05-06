<?php

declare(strict_types=1);

namespace App\Services\PaymentStrategy;

use App\Dto\Payment\CreatePaymentDto;
use App\Enums\UserServiceScheduleType;
use App\Exceptions\Abonnement\NotYourAbonnementException;
use App\Exceptions\AbonnementPresent\NotEnougVisitsException;
use App\Models\User;
use App\Models\UserAbonnementPresent;
use App\Services\Contracts\PaymentStrategy;

class AbonnementPresentPaymentStrategy implements PaymentStrategy
{
    public function handlePayment(CreatePaymentDto $args, User $user): UserServiceScheduleType
    {
        $userAbonnementPresent = UserAbonnementPresent::with(['userAbonnement'])
            ->findOrFail($args->user_abonnement_present_id);

        if ($userAbonnementPresent->userAbonnement->user_id !== $user->id) {
            throw new NotYourAbonnementException();
        }

        if ($userAbonnementPresent->visits < 1) {
            throw new NotEnougVisitsException();
        }

        $userAbonnementPresent->visits -= 1;
        $userAbonnementPresent->save();

        return UserServiceScheduleType::ABONNEMENT;
    }
}
