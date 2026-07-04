<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Cliente; // Asegúrate de importar el modelo Cliente
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        // 1. Crear el usuario en la BD de datos generales
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // 2. Asignarle el rol de 'cliente' usando Spatie Laravel Permission
        $user->assignRole('cliente');

        // 3. Crear el registro en la tabla 'clientes' de forma automática
        Cliente::create([
            'nombre'    => $user->name,
            'email'     => $user->email,
            'telefono'  => null,
            'direccion' => null,
            'user_id'   => $user->id,
        ]);

        return $user;
    }
}