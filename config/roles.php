<?php

use App\Models\User;

return [
    User::Role_BS => [
        'admin.user.index',
        'admin.user.data',
        'admin.user.detail',
        'admin.user.export',
    ],
    User::Role_Agronomist => [
        'admin.user.index',
        'admin.user.data',
        'admin.user.detail',
        'admin.user.export',
    ],
    User::Role_ASM => [
        'admin.user.index',
        'admin.user.data',
        'admin.user.detail',
        'admin.user.export',
    ],
];
