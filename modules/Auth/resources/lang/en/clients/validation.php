<?php

return [
    'required' => 'The :attribute field is required.',
    'string'   => 'The :attribute must be a string.',
    'select'   => 'The :attribute field must be selected.',
    'email'    => 'The :attribute must be a valid email address.',
    'unique'   => 'The :attribute has already been taken.',
    'same'     => 'The confirmation password does not match.',
    'min'      => 'The :attribute must be at least :min characters.',

    'attributes' => [
        'email'            => 'Email',
        'password'         => 'Password',
        'phone'            => 'Phone number',
        'confirm_password' => 'Confirm password',
        'name'             => 'Full name',
    ],
];
