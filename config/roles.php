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

        'admin.demo-plot.index',
        'admin.demo-plot.data',
        'admin.demo-plot.detail',
        'admin.demo-plot.add',
        'admin.demo-plot.edit',
        'admin.demo-plot.save',
        'admin.demo-plot.duplicate',
        'admin.demo-plot.export',

        'admin.demo-plot-visit.index',
        'admin.demo-plot-visit.data',
        'admin.demo-plot-visit.detail',
        'admin.demo-plot-visit.add',
        'admin.demo-plot-visit.edit',
        'admin.demo-plot-visit.save',
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

        'admin.demo-plot.index',
        'admin.demo-plot.data',
        'admin.demo-plot.detail',
        'admin.demo-plot.export',

        'admin.demo-plot-visit.index',
        'admin.demo-plot-visit.data',
        'admin.demo-plot-visit.detail',

        'admin.activity-type.index',
        'admin.activity-type.data',
    ],
    User::Role_ASM => [
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

        'admin.activity-type.index',
        'admin.activity-type.data',
    ],
];
