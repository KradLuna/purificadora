<?php

return [
    'admin' => [
        'password' => env('ABS_PASS', '1410'),
        'password_b' => env('MOONS_PASS', '1410'),
    ],
    'liters' => [
        'allowed_range' => env('ALLOWED_RANGE', 20),
    ]
];
