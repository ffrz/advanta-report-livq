<?php

use App\Http\Controllers\Admin\ActivityTypeController;
use App\Http\Controllers\Admin\ApiController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CompanyProfileController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DemoPlotController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\TargetController;
use App\Http\Controllers\Admin\UserController;

use App\Http\Middleware\Auth;
use App\Http\Middleware\NonAuthenticated;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('homepage-new');
})->name('home');

Route::get('/test', function () {
    return inertia('Test');
})->name('test');

Route::middleware(NonAuthenticated::class)->group(function () {
    Route::redirect('/', 'admin/auth/login', 301);
    Route::prefix('/admin/auth')->group(function () {
        Route::match(['get', 'post'], 'login', [AuthController::class, 'login'])->name('admin.auth.login');
        Route::match(['get', 'post'], 'register', [AuthController::class, 'register'])->name('admin.auth.register');
        Route::match(['get', 'post'], 'forgot-password', [AuthController::class, 'forgotPassword'])->name('admin.auth.forgot-password');
    });
});

Route::middleware([Auth::class])->group(function () {
    Route::prefix('api')->group(function () {
        // Route::get('active-customers', [ApiController::class, 'activeCustomers'])->name('api.active-customers');
    });

    Route::match(['get', 'post'], 'admin/auth/logout', [AuthController::class, 'logout'])->name('admin.auth.logout');

    Route::prefix('admin')->group(function () {
        Route::redirect('', 'admin/dashboard', 301);

        Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('test', [DashboardController::class, 'test'])->name('admin.test');
        Route::get('about', function () {
            return inertia('admin/About');
        })->name('admin.about');

        Route::prefix('products')->group(function () {
            Route::get('', [ProductController::class, 'index'])->name('admin.product.index');
            Route::get('data', [ProductController::class, 'data'])->name('admin.product.data');
            Route::get('add', [ProductController::class, 'editor'])->name('admin.product.add');
            Route::get('duplicate/{id}', [ProductController::class, 'duplicate'])->name('admin.product.duplicate');
            Route::get('edit/{id}', [ProductController::class, 'editor'])->name('admin.product.edit');
            Route::post('save', [ProductController::class, 'save'])->name('admin.product.save');
            Route::post('delete/{id}', [ProductController::class, 'delete'])->name('admin.product.delete');
            Route::get('detail/{id}', [ProductController::class, 'detail'])->name('admin.product.detail');
            Route::get('export/{id}', [ProductController::class, 'export'])->name('admin.product.export');
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

        Route::prefix('targets')->group(function () {
            Route::get('', [TargetController::class, 'index'])->name('admin.target.index');
            Route::get('data', [TargetController::class, 'data'])->name('admin.target.data');
            Route::get('add', [TargetController::class, 'editor'])->name('admin.target.add');
            Route::get('duplicate/{id}', [TargetController::class, 'duplicate'])->name('admin.target.duplicate');
            Route::get('edit/{id}', [TargetController::class, 'editor'])->name('admin.target.edit');
            Route::get('detail/{id}', [TargetController::class, 'detail'])->name('admin.target.detail');
            Route::post('save', [TargetController::class, 'save'])->name('admin.target.save');
            Route::post('delete/{id}', [TargetController::class, 'delete'])->name('admin.target.delete');
            Route::get('export', [TargetController::class, 'export'])->name('admin.target.export');
        });

        Route::prefix('demo-plots')->group(function () {
            Route::get('', [DemoPlotController::class, 'index'])->name('admin.demo-plot.index');
            Route::get('data', [DemoPlotController::class, 'data'])->name('admin.demo-plot.data');
            Route::get('duplicate/{id}', [DemoPlotController::class, 'duplicate'])->name('admin.demo-plot.duplicate');
            Route::get('add', [DemoPlotController::class, 'editor'])->name('admin.demo-plot.add');
            Route::get('edit/{id}', [DemoPlotController::class, 'editor'])->name('admin.demo-plot.edit');
            Route::get('detail/{id}', [DemoPlotController::class, 'detail'])->name('admin.demo-plot.detail');
            Route::post('save', [DemoPlotController::class, 'save'])->name('admin.demo-plot.save');
            Route::post('delete/{id}', [DemoPlotController::class, 'delete'])->name('admin.demo-plot.delete');
            Route::get('export', [DemoPlotController::class, 'export'])->name('admin.demo-plot.export');
        });

        Route::prefix('settings')->group(function () {
            Route::get('profile/edit', [ProfileController::class, 'edit'])->name('admin.profile.edit');
            Route::post('profile/update', [ProfileController::class, 'update'])->name('admin.profile.update');
            Route::post('profile/update-password', [ProfileController::class, 'updatePassword'])->name('admin.profile.update-password');

            Route::get('company-profile/edit', [CompanyProfileController::class, 'edit'])->name('admin.company-profile.edit');
            Route::post('company-profile/update', [CompanyProfileController::class, 'update'])->name('admin.company-profile.update');

            Route::prefix('users')->group(function () {
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
        });
    });
});
