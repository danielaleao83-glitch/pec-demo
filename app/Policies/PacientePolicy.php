<?php

namespace App\Policies;

use App\Models\Paciente\Paciente;
use App\Models\User;
use App\Services\LGPD\LGPDService;

class PacientePolicy
{
    public function view(User $user, Paciente $paciente): bool
    {
        return app(LGPDService::class)->podeAcessar($paciente);
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can_create_paciente ?? true;
    }

    public function delete(User $user, Paciente $paciente): bool
    {
        return $user->is_admin ?? false;
    }
}
