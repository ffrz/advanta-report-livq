<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\ActivityPlanController;
use App\Http\Controllers\Admin\ActivityPlanDetailController;
use App\Http\Controllers\Admin\ActivityTargetController;
use App\Http\Controllers\Admin\ActivityTypeController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CompanyProfileController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DatabaseController;
use App\Http\Controllers\Admin\DemoPlotController;
use App\Http\Controllers\Admin\DemoPlotVisitController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;

use App\Http\Middleware\Auth;
use App\Http\Middleware\NonAuthenticated;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('homepage-new');
})->name('home');

Route::get('/test', function () {
    return inertia('Test');
})->name('test');

Route::get('/_cmd/clear-all-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    return 'Cache cleared';
});

Route::middleware(NonAuthenticated::class)->group(function () {
    Route::redirect('/', 'admin/auth/login', 301);
    Route::prefix('/admin/auth')->group(function () {
        Route::match(['get', 'post'], 'login', [AuthController::class, 'login'])->name('admin.auth.login');
        Route::match(['get', 'post'], 'register', [AuthController::class, 'register'])->name('admin.auth.register');
        Route::match(['get', 'post'], 'forgot-password', [AuthController::class, 'forgotPassword'])->name('admin.auth.forgot-password');
    });
});

Route::middleware([Auth::class])->group(function () {
    Route::match(['get', 'post'], 'admin/auth/logout', [AuthController::class, 'logout'])->name('admin.auth.logout');

    Route::prefix('admin')->group(function () {
        Route::redirect('', 'admin/dashboard', 301);

        Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::prefix('reports')->group(function () {
            Route::get('', [ReportController::class, 'index'])->name('admin.report.index');

            Route::get('demo-plot-detail', [ReportController::class, 'demoPlotDetail'])->name('admin.report.demo-plot-detail');
            Route::get('new-demo-plot-detail', [ReportController::class, 'newDemoPlotDetail'])->name('admin.report.new-demo-plot-detail');
            Route::get('demo-plot-with-photo', [ReportController::class, 'demoPlotWithPhoto'])->name('admin.report.demo-plot-with-photo');
            Route::get('activity-plan-detail', [ReportController::class, 'activityPlanDetail'])->name('admin.report.activity-plan-detail');
            Route::get('activity-realization-detail', [ReportController::class, 'activityRealizationDetail'])->name('admin.report.activity-realization-detail');
            Route::get('activity-target-detail', [ReportController::class, 'activiyTargetDetail'])->name('admin.report.activity-target-detail');
        });

        Route::middleware('auto-permission')->group(function () {
            Route::prefix('products')->group(function () {
                Route::get('', [ProductController::class, 'index'])->name('admin.product.index');
                Route::get('data', [ProductController::class, 'data'])->name('admin.product.data');
                Route::get('add', [ProductController::class, 'editor'])->name('admin.product.add');
                Route::get('duplicate/{id}', [ProductController::class, 'duplicate'])->name('admin.product.duplicate');
                Route::get('edit/{id}', [ProductController::class, 'editor'])->name('admin.product.edit');
                Route::post('save', [ProductController::class, 'save'])->name('admin.product.save');
                Route::post('delete/{id}', [ProductController::class, 'delete'])->name('admin.product.delete');
                Route::get('detail/{id}', [ProductController::class, 'detail'])->name('admin.product.detail');
                Route::get('export', [ProductController::class, 'export'])->name('admin.product.export');
            });

            Route::prefix('product-categories')->group(function () {
                Route::get('', [ProductCategoryController::class, 'index'])->name('admin.product-category.index');
                Route::get('data', [ProductCategoryController::class, 'data'])->name('admin.product-category.data');
                Route::get('add', [ProductCategoryController::class, 'editor'])->name('admin.product-category.add');
                Route::get('duplicate/{id}', [ProductCategoryController::class, 'duplicate'])->name('admin.product-category.duplicate');
                Route::get('edit/{id}', [ProductCategoryController::class, 'editor'])->name('admin.product-category.edit');
                Route::post('save', [ProductCategoryController::class, 'save'])->name('admin.product-category.save');
                Route::post('delete/{id}', [ProductCategoryController::class, 'delete'])->name('admin.product-category.delete');
            });

            Route::prefix('customers')->group(function () {
                Route::get('', [CustomerController::class, 'index'])->name('admin.customer.index');
                Route::get('data', [CustomerController::class, 'data'])->name('admin.customer.data');
                Route::get('add', [CustomerController::class, 'editor'])->name('admin.customer.add');
                Route::get('duplicate/{id}', [CustomerController::class, 'duplicate'])->name('admin.customer.duplicate');
                Route::get('edit/{id}', [CustomerController::class, 'editor'])->name('admin.customer.edit');
                Route::post('save', [CustomerController::class, 'save'])->name('admin.customer.save');
                Route::post('delete/{id}', [CustomerController::class, 'delete'])->name('admin.customer.delete');
                Route::get('detail/{id}', [CustomerController::class, 'detail'])->name('admin.customer.detail');
                Route::get('export', [CustomerController::class, 'export'])->name('admin.customer.export');
            });

            Route::prefix('activity-types')->group(function () {
                Route::get('', [ActivityTypeController::class, 'index'])->name('admin.activity-type.index');
                Route::get('data', [ActivityTypeController::class, 'data'])->name('admin.activity-type.data');
                Route::get('add', [ActivityTypeController::class, 'editor'])->name('admin.activity-type.add');
                Route::get('duplicate/{id}', [ActivityTypeController::class, 'duplicate'])->name('admin.activity-type.duplicate');
                Route::get('edit/{id}', [ActivityTypeController::class, 'editor'])->name('admin.activity-type.edit');
                Route::post('save', [ActivityTypeController::class, 'save'])->name('admin.activity-type.save');
                Route::post('delete/{id}', [ActivityTypeController::class, 'delete'])->name('admin.activity-type.delete');
            });

            Route::prefix('activities')->group(function () {
                Route::get('', [ActivityController::class, 'index'])->name('admin.activity.index');
                Route::get('data', [ActivityController::class, 'data'])->name('admin.activity.data');
                Route::get('duplicate/{id}', [ActivityController::class, 'duplicate'])->name('admin.activity.duplicate');
                Route::get('add', [ActivityController::class, 'editor'])->name('admin.activity.add');
                Route::get('edit/{id}', [ActivityController::class, 'editor'])->name('admin.activity.edit');
                Route::get('detail/{id}', [ActivityController::class, 'detail'])->name('admin.activity.detail');
                Route::post('respond/{id}', [ActivityController::class, 'respond'])->name('admin.activity.respond');
                Route::post('save', [ActivityController::class, 'save'])->name('admin.activity.save');
                Route::post('delete/{id}', [ActivityController::class, 'delete'])->name('admin.activity.delete');
                Route::get('export', [ActivityController::class, 'export'])->name('admin.activity.export');
            });

            Route::prefix('activity-plans')->group(function () {
                Route::get('', [ActivityPlanController::class, 'index'])->name('admin.activity-plan.index');
                Route::get('data', [ActivityPlanController::class, 'data'])->name('admin.activity-plan.data');
                Route::get('duplicate/{id}', [ActivityPlanController::class, 'duplicate'])->name('admin.activity-plan.duplicate');
                Route::get('add', [ActivityPlanController::class, 'editor'])->name('admin.activity-plan.add');
                Route::get('edit/{id}', [ActivityPlanController::class, 'editor'])->name('admin.activity-plan.edit');
                Route::get('detail/{id}', [ActivityPlanController::class, 'detail'])->name('admin.activity-plan.detail');
                Route::post('respond/{id}', [ActivityPlanController::class, 'respond'])->name('admin.activity-plan.respond');
                Route::post('save', [ActivityPlanController::class, 'save'])->name('admin.activity-plan.save');
                Route::post('delete/{id}', [ActivityPlanController::class, 'delete'])->name('admin.activity-plan.delete');
                Route::get('export', [ActivityPlanController::class, 'export'])->name('admin.activity-plan.export');
            });

            Route::prefix('activity-plan-details')->group(function () {
                Route::get('', [ActivityPlanDetailController::class, 'index'])->name('admin.activity-plan-detail.index');
                Route::get('data', [ActivityPlanDetailController::class, 'data'])->name('admin.activity-plan-detail.data');
                Route::get('add', [ActivityPlanDetailController::class, 'editor'])->name('admin.activity-plan-detail.add');
                Route::get('edit/{id}', [ActivityPlanDetailController::class, 'editor'])->name('admin.activity-plan-detail.edit');
                Route::post('save', [ActivityPlanDetailController::class, 'save'])->name('admin.activity-plan-detail.save');
                Route::post('delete/{id}', [ActivityPlanDetailController::class, 'delete'])->name('admin.activity-plan-detail.delete');
            });


            Route::prefix('activity-targets')->group(function () {
                Route::get('', [ActivityTargetController::class, 'index'])->name('admin.activity-target.index');
                Route::get('data', [ActivityTargetController::class, 'data'])->name('admin.activity-target.data');
                Route::get('duplicate/{id}', [ActivityTargetController::class, 'duplicate'])->name('admin.activity-target.duplicate');
                Route::get('add', [ActivityTargetController::class, 'editor'])->name('admin.activity-target.add');
                Route::get('edit/{id}', [ActivityTargetController::class, 'editor'])->name('admin.activity-target.edit');
                Route::get('detail/{id}', [ActivityTargetController::class, 'detail'])->name('admin.activity-target.detail');
                Route::post('save', [ActivityTargetController::class, 'save'])->name('admin.activity-target.save');
                Route::post('delete/{id}', [ActivityTargetController::class, 'delete'])->name('admin.activity-target.delete');
                Route::get('export', [ActivityTargetController::class, 'export'])->name('admin.activity-target.export');
            });

            Route::prefix('demo-plots')->group(function () {
                Route::get('', [DemoPlotController::class, 'index'])->name('admin.demo-plot.index');
                Route::get('data', [DemoPlotController::class, 'data'])->name('admin.demo-plot.data');
                Route::get('export', [DemoPlotController::class, 'export'])->name('admin.demo-plot.export');
                Route::get('detail/{id}', [DemoPlotController::class, 'detail'])->name('admin.demo-plot.detail');
                Route::get('duplicate/{id}', [DemoPlotController::class, 'duplicate'])->name('admin.demo-plot.duplicate');
                Route::get('add', [DemoPlotController::class, 'editor'])->name('admin.demo-plot.add');
                Route::get('edit/{id}', [DemoPlotController::class, 'editor'])->name('admin.demo-plot.edit');
                Route::post('save', [DemoPlotController::class, 'save'])->name('admin.demo-plot.save');
                Route::post('delete/{id}', [DemoPlotController::class, 'delete'])->name('admin.demo-plot.delete');
            });

            Route::prefix('demo-plot-vistis')->group(function () {
                Route::get('', [DemoPlotVisitController::class, 'index'])->name('admin.demo-plot-visit.index');
                Route::get('data', [DemoPlotVisitController::class, 'data'])->name('admin.demo-plot-visit.data');
                Route::get('detail/{id}', [DemoPlotVisitController::class, 'detail'])->name('admin.demo-plot-visit.detail');
                Route::get('export', [DemoPlotVisitController::class, 'export'])->name('admin.demo-plot-visit.export');
                Route::get('add', [DemoPlotVisitController::class, 'editor'])->name('admin.demo-plot-visit.add');
                Route::get('edit/{id}', [DemoPlotVisitController::class, 'editor'])->name('admin.demo-plot-visit.edit');
                Route::post('save', [DemoPlotVisitController::class, 'save'])->name('admin.demo-plot-visit.save');
                Route::post('delete/{id}', [DemoPlotVisitController::class, 'delete'])->name('admin.demo-plot-visit.delete');
            });
        });

        Route::prefix('settings')->group(function () {
            Route::get('profile/edit', [ProfileController::class, 'edit'])->name('admin.profile.edit');
            Route::post('profile/update', [ProfileController::class, 'update'])->name('admin.profile.update');
            Route::post('profile/update-password', [ProfileController::class, 'updatePassword'])->name('admin.profile.update-password');

            Route::get('company-profile/edit', [CompanyProfileController::class, 'edit'])->name('admin.company-profile.edit');
            Route::post('company-profile/update', [CompanyProfileController::class, 'update'])->name('admin.company-profile.update');

            Route::prefix('users')->middleware(['auto-permission'])->group(function () {
                Route::get('', [UserController::class, 'index'])->name('admin.user.index');
                Route::get('data', [UserController::class, 'data'])->name('admin.user.data');
                Route::get('add', [UserController::class, 'editor'])->name('admin.user.add');
                Route::get('edit/{id}', [UserController::class, 'editor'])->name('admin.user.edit');
                Route::get('duplicate/{id}', [UserController::class, 'duplicate'])->name('admin.user.duplicate');
                Route::post('save', [UserController::class, 'save'])->name('admin.user.save');
                Route::post('delete/{id}', [UserController::class, 'delete'])->name('admin.user.delete');
                Route::get('detail/{id}', [UserController::class, 'detail'])->name('admin.user.detail');
                Route::get('export', [UserController::class, 'export'])->name('admin.user.export');
            });

            Route::get('database', [DatabaseController::class, 'index'])->name('admin.db.index');
            Route::get('database/backup', [DatabaseController::class, 'backup'])->name('admin.db.backup');
            Route::get('db/restore', [DatabaseController::class, 'restore'])->name('admin.db.restore');
        });
    });
});
