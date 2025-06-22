<?php

use App\Http\Controllers\Admin\ApiController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CompanyProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DemplotController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\TargetController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VarietyController;
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

        Route::prefix('varieties')->group(function () {
            Route::get('', [VarietyController::class, 'index'])->name('admin.variety.index');
            Route::get('data', [VarietyController::class, 'data'])->name('admin.variety.data');
            Route::get('add', [VarietyController::class, 'editor'])->name('admin.variety.add');
            Route::get('duplicate/{id}', [VarietyController::class, 'duplicate'])->name('admin.variety.duplicate');
            Route::get('edit/{id}', [VarietyController::class, 'editor'])->name('admin.variety.edit');
            Route::get('detail/{id}', [VarietyController::class, 'detail'])->name('admin.variety.detail');
            Route::post('save', [VarietyController::class, 'save'])->name('admin.variety.save');
            Route::post('delete/{id}', [VarietyController::class, 'delete'])->name('admin.variety.delete');
            Route::get('export', [VarietyController::class, 'export'])->name('admin.variety.export');
        });

        Route::prefix('products')->group(function () {
            Route::get('', [ProductController::class, 'index'])->name('admin.product.index');
            Route::get('data', [ProductController::class, 'data'])->name('admin.product.data');
            Route::get('add', [ProductController::class, 'editor'])->name('admin.product.add');
            Route::get('duplicate/{id}', [ProductController::class, 'duplicate'])->name('admin.product.duplicate');
            Route::get('edit/{id}', [ProductController::class, 'editor'])->name('admin.product.edit');
            Route::post('save', [ProductController::class, 'save'])->name('admin.product.save');
            Route::post('delete/{id}', [ProductController::class, 'delete'])->name('admin.product.delete');
            Route::get('detail/{id}', [ProductController::class, 'detail'])->name('admin.product.detail');
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

        Route::prefix('demplots')->group(function () {
            Route::get('', [DemplotController::class, 'index'])->name('admin.demplot.index');
            Route::get('data', [DemplotController::class, 'data'])->name('admin.demplot.data');
            Route::get('add', [DemplotController::class, 'editor'])->name('admin.demplot.add');
            Route::get('edit/{id}', [DemplotController::class, 'editor'])->name('admin.demplot.edit');
            Route::get('detail/{id}', [DemplotController::class, 'detail'])->name('admin.demplot.detail');
            Route::post('save', [DemplotController::class, 'save'])->name('admin.demplot.save');
            Route::post('delete/{id}', [DemplotController::class, 'delete'])->name('admin.demplot.delete');
            Route::get('export', [DemplotController::class, 'export'])->name('admin.demplot.export');
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
