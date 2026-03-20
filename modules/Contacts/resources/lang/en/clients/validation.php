<?php

return [
    'required' => 'The :attribute field is required.',
    'email'    => 'The :attribute must be a valid email address.',
    'max'      => 'The :attribute may not be greater than :max characters.',
    'integer'  => 'The :attribute must be an integer.',
    'select'   => 'The :attribute field must be selected.',
    'regex'    => 'The :attribute format is invalid.',
    'recaptcha' => 'Please complete the captcha verification before submitting.',

    'attributes' => [
        'name'    => 'name',
        'email'   => 'email',
        'phone'   => 'phone number',
        'message' => 'message',
        'g-recaptcha-response' => 'captcha',
    ],
];
