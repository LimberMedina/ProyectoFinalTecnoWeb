<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConsumidorFinalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $user = DB::table('users')
                ->where('nombre', 'Consumidor Final')
                ->where('apellidos', '')
                ->first();

            if (!$user) {
                $role = DB::table('roles')->whereRaw('LOWER(nombre) = ?', ['cliente'])->first();

                DB::table('users')->insert([
                    'nombre' => 'Consumidor Final',
                    'apellidos' => '',
                    'email' => null,
                    'ci' => '99999999',
                    'telefono' => '00000000',
                    'password' => Hash::make(Str::random(32)),
                    'estado' => true,
                    'role_id' => $role ? $role->id : 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
