<?php

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for specific validations
    | used in your application. The convention is to place each custom message
    | for a given validation rule in this array.
    |
    */

    'alpha_spaces' => 'El campo :attribute solo puede contener letras y espacios.',
    'numeric_with_dash' => 'El campo :attribute solo puede contener números y guiones.',
    'numeric_with_symbols' => 'El campo :attribute solo puede contener números, espacios y símbolos +().-',
    'strong_password' => 'La contraseña debe tener al menos 8 caracteres e incluir mayúsculas, minúsculas y números.',

];
