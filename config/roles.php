<?php

use App\Models\User;

return [
    User::Role_BS => [
        'admin.user.index',
        'admin.user.data',
        'admin.user.detail',
        'admin.user.export',

        'admin.product.index',
        'admin.product.data',
        'admin.product.detail',
    ],
    User::Role_Agronomist => [
        'admin.user.index',
        'admin.user.data',
        'admin.user.detail',
        'admin.user.export',

        'admin.product.index',
        'admin.product.data',
        'admin.product.detail',

        'admin.customer.index',
        'admin.customer.data',
        'admin.customer.detail',
        'admin.customer.add',
        'admin.customer.edit',
        'admin.customer.save',
        'admin.customer.duplicate',
    ],
    User::Role_ASM => [
        'admin.user.index',
        'admin.user.data',
        'admin.user.detail',
        'admin.user.export',

        'admin.product.index',
        'admin.product.data',
        'admin.product.detail',
    ],
];
