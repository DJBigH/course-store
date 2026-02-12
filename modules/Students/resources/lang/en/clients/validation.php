<?php

return [
    'required' => ':attribute is required',
    'email' => ':attribute is not a valid email address',
    'unique' => ':attribute already exists',
    'max' => ':attribute must not exceed :max characters',
    'min' => ':attribute must be at least :min characters',
    'integer' => ':attribute must be a number',
    'select' => ':attribute must be selected',
    'regex' => ':attribute format is invalid',
    'password-invalid' => 'The current password is invalid',

    'attributes' => [
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'phone' => 'Phone number',
        'confirm_password' => 'Confirm password',
        'old_password' => 'Current password',
    ],
];
