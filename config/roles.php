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

        'admin.activity-plan.index',
        'admin.activity-plan.data',
        'admin.activity-plan.add',
        'admin.activity-plan.edit',
        'admin.activity-plan.save',
        'admin.activity-plan.delete',
        'admin.activity-plan.detail',

        'admin.activity-plan-detail.index',
        'admin.activity-plan-detail.data',
        'admin.activity-plan-detail.add',
        'admin.activity-plan-detail.edit',
        'admin.activity-plan-detail.save',
        'admin.activity-plan-detail.delete',

        'admin.activity.index',
        'admin.activity.data',
        'admin.activity.add',
        'admin.activity.edit',
        'admin.activity.save',
        'admin.activity.delete',
        'admin.activity.detail',

        'admin.customer.index',
        'admin.customer.data',
        'admin.customer.detail',
        'admin.customer.add',
        'admin.customer.edit',
        'admin.customer.save',
        'admin.customer.duplicate',
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

        'admin.activity-plan.index',
        'admin.activity-plan.data',
        'admin.activity-plan.detail',
        'admin.activity-plan.respond',
        'admin.activity-plan.export',

        'admin.activity-plan-detail.index',
        'admin.activity-plan-detail.data',
        'admin.activity-plan-detail.add',
        'admin.activity-plan-detail.edit',
        'admin.activity-plan-detail.save',
        'admin.activity-plan-detail.delete',

        'admin.activity.index',
        'admin.activity.data',
        'admin.activity.detail',
        'admin.activity.respond',
        'admin.activity.export',

        'admin.activity-target.index',
        'admin.activity-target.data',
        'admin.activity-target.detail',
        'admin.activity-target.add',
        'admin.activity-target.edit',
        'admin.activity-target.delete',
        'admin.activity-target.save',
        'admin.activity-target.export',

        'admin.report.index',
        'admin.report.demo-plot-detail',
        'admin.report.demo-plot-detail-with-photo',
        'admin.report.demo-activity-plan-detail',
        'admin.report.demo-activity-realization-detail',
        'admin.report.demo-activity-target-detail',
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
