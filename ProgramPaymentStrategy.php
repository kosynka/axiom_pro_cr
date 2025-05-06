<?php

declare(strict_types=1);

namespace App\Services\PaymentStrategy;

use App\Dto\Payment\CreatePaymentDto;
use App\Enums\UserServiceScheduleType;
use App\Exceptions\Program\NotEnoughVisitsException;
use App\Exceptions\Program\NotYourProgramException;
use App\Models\User;
use App\Models\UserProgram;
use App\Services\Contracts\PaymentStrategy;

class ProgramPaymentStrategy implements PaymentStrategy
{
    public function handlePayment(CreatePaymentDto $args, User $user): UserServiceScheduleType
    {
        $userProgram = UserProgram::with(['programServices.service'])
            ->findOrFail($args->user_program_id);

        if ($userProgram->user_id !== $user->id) {
            throw new NotYourProgramException();
        }

        $programService = $userProgram->programServices()
            ->where('service_id', $args->service_id)
            ->first();

        if (!isset($programService) || $programService->visits < 1) {
            throw new NotEnoughVisitsException();
        }

        $programService->visits -= 1;
        $programService->save();

        return UserServiceScheduleType::PROGRAM;
    }
}
