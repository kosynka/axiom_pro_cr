<?php

declare(strict_types=1);

namespace App\Services\PaymentStrategy;

use App\Dto\Payment\CreatePaymentDto;
use App\Enums\UserServiceScheduleType;
use App\Exceptions\Abonnement\NotYourAbonnementException;
use App\Exceptions\Abonnement\NotEnoughMinutesException;
use App\Models\User;
use App\Models\UserAbonnement;
use App\Services\Contracts\PaymentStrategy;

class AbonnementPaymentStrategy implements PaymentStrategy
{
    public function handlePayment(CreatePaymentDto $args, User $user): UserServiceScheduleType
    {
        $userAbonnement = UserAbonnement::findOrFail($args->user_abonnement_id);

        if ($userAbonnement->user_id !== $user->id) {
            throw new NotYourAbonnementException();
        }

        if ($userAbonnement->minutes < $args->duration) {
            throw new NotEnoughMinutesException();
        }

        $userAbonnement->minutes -= $args->duration;
        $userAbonnement->save();

        return UserServiceScheduleType::ABONNEMENT;
    }
}
