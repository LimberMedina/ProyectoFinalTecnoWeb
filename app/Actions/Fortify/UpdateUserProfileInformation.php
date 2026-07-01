<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        $nombre = $input['nombre'] ?? $input['name'] ?? null;
        $hasDireccionColumn = Schema::hasColumn('users', 'direccion');

        $rules = [
            'nombre' => ['required_without:name', 'string', 'max:255'],
            'name' => ['required_without:nombre', 'string', 'max:255'],
            'apellidos' => ['nullable', 'string', 'max:255'],
            'ci' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'telefono' => ['nullable', 'string', 'max:15'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ];

        if ($hasDireccionColumn) {
            $rules['direccion'] = ['nullable', 'string', 'max:500'];
        }

        Validator::make($input, $rules)->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $data = [
                'nombre' => $nombre,
                'apellidos' => $input['apellidos'] ?? $user->apellidos,
                'ci' => $input['ci'] ?? $user->ci,
                'telefono' => $input['telefono'] ?? $user->telefono,
                'fecha_nacimiento' => $input['fecha_nacimiento'] ?? $user->fecha_nacimiento,
                'email' => $input['email'],
            ];

            if ($hasDireccionColumn) {
                $data['direccion'] = $input['direccion'] ?? $user->direccion;
            }

            $user->forceFill($data)->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $nombre = $input['nombre'] ?? $input['name'] ?? $user->nombre;
        $hasDireccionColumn = Schema::hasColumn('users', 'direccion');

        $data = [
            'nombre' => $nombre,
            'apellidos' => $input['apellidos'] ?? $user->apellidos,
            'ci' => $input['ci'] ?? $user->ci,
            'telefono' => $input['telefono'] ?? $user->telefono,
            'fecha_nacimiento' => $input['fecha_nacimiento'] ?? $user->fecha_nacimiento,
            'email' => $input['email'],
            'email_verified_at' => null,
        ];

        if ($hasDireccionColumn) {
            $data['direccion'] = $input['direccion'] ?? $user->direccion;
        }

        $user->forceFill($data)->save();

        $user->sendEmailVerificationNotification();
    }
}
