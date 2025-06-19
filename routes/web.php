<?php

use App\Http\Controllers\Admin\ApiController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ClosingController;
use App\Http\Controllers\Admin\CompanyProfileController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerServiceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InteractionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VisitController;
use App\Http\Middleware\Auth;
use App\Http\Middleware\NonAuthenticated;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('homepage');
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
        Route::get('active-customers', [ApiController::class, 'activeCustomers'])->name('api.active-customers');
    });

    Route::match(['get', 'post'], 'admin/auth/logout', [AuthController::class, 'logout'])->name('admin.auth.logout');

    Route::prefix('admin')->group(function () {
        Route::redirect('', 'admin/dashboard', 301);

        Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('test', [DashboardController::class, 'test'])->name('admin.test');
        Route::get('about', function () {
            return inertia('admin/About');
        })->name('admin.about');

        Route::prefix('reports')->group(function () {
            Route::get('', [ReportController::class, 'index'])->name('admin.report.index');
            Route::match(['get', 'post'], 'interaction', [ReportController::class, 'interaction'])->name('admin.report.interaction');
            Route::match(['get', 'post'], 'sales-activity', [ReportController::class, 'salesActivity'])->name('admin.report.sales-activity');

            Route::match(['get', 'post'], 'closing-detail', [ReportController::class, 'closingDetail'])->name('admin.report.closing-detail');
            Route::match(['get', 'post'], 'closing-by-sales', [ReportController::class, 'closingBySales'])->name('admin.report.closing-by-sales');
            Route::match(['get', 'post'], 'closing-by-services', [ReportController::class, 'closingByServices'])->name('admin.report.closing-by-services');
        });

        Route::prefix('services')->group(function () {
            Route::get('', [ServiceController::class, 'index'])->name('admin.service.index');
            Route::get('data', [ServiceController::class, 'data'])->name('admin.service.data');
            Route::get('add', [ServiceController::class, 'editor'])->name('admin.service.add');
            Route::get('duplicate/{id}', [ServiceController::class, 'duplicate'])->name('admin.service.duplicate');
            Route::get('edit/{id}', [ServiceController::class, 'editor'])->name('admin.service.edit');
            Route::get('detail/{id}', [ServiceController::class, 'detail'])->name('admin.service.detail');
            Route::post('save', [ServiceController::class, 'save'])->name('admin.service.save');
            Route::post('delete/{id}', [ServiceController::class, 'delete'])->name('admin.service.delete');
            Route::get('export', [ServiceController::class, 'export'])->name('admin.service.export');
        });

        Route::prefix('customers')->group(function () {
            Route::get('', [CustomerController::class, 'index'])->name('admin.customer.index');
            Route::get('data', [CustomerController::class, 'data'])->name('admin.customer.data');
            Route::get('add', [CustomerController::class, 'editor'])->name('admin.customer.add');
            Route::get('duplicate/{id}', [CustomerController::class, 'duplicate'])->name('admin.customer.duplicate');
            Route::get('edit/{id}', [CustomerController::class, 'editor'])->name('admin.customer.edit');
            Route::get('detail/{id}', [CustomerController::class, 'detail'])->name('admin.customer.detail');
            Route::post('save', [CustomerController::class, 'save'])->name('admin.customer.save');
            Route::post('delete/{id}', [CustomerController::class, 'delete'])->name('admin.customer.delete');
            Route::get('export', [CustomerController::class, 'export'])->name('admin.customer.export');
        });

        Route::prefix('interactions')->group(function () {
            Route::get('', [InteractionController::class, 'index'])->name('admin.interaction.index');
            Route::get('data', [InteractionController::class, 'data'])->name('admin.interaction.data');
            Route::get('add', [InteractionController::class, 'editor'])->name('admin.interaction.add');
            Route::get('edit/{id}', [InteractionController::class, 'editor'])->name('admin.interaction.edit');
            Route::get('detail/{id}', [InteractionController::class, 'detail'])->name('admin.interaction.detail');
            Route::post('save', [InteractionController::class, 'save'])->name('admin.interaction.save');
            Route::post('delete/{id}', [InteractionController::class, 'delete'])->name('admin.interaction.delete');
            Route::get('export', [InteractionController::class, 'export'])->name('admin.interaction.export');
        });

        Route::prefix('closings')->group(function () {
            Route::get('', [ClosingController::class, 'index'])->name('admin.closing.index');
            Route::get('data', [ClosingController::class, 'data'])->name('admin.closing.data');
            Route::get('add', [ClosingController::class, 'editor'])->name('admin.closing.add');
            Route::get('edit/{id}', [ClosingController::class, 'editor'])->name('admin.closing.edit');
            Route::get('detail/{id}', [ClosingController::class, 'detail'])->name('admin.closing.detail');
            Route::post('save', [ClosingController::class, 'save'])->name('admin.closing.save');
            Route::post('delete/{id}', [ClosingController::class, 'delete'])->name('admin.closing.delete');
            Route::get('export', [ClosingController::class, 'export'])->name('admin.closing.export');
        });

        Route::prefix('customer-services')->group(function () {
            Route::get('', [CustomerServiceController::class, 'index'])->name('admin.customer-service.index');
            Route::get('data', [CustomerServiceController::class, 'data'])->name('admin.customer-service.data');
            Route::get('add', [CustomerServiceController::class, 'editor'])->name('admin.customer-service.add');
            Route::get('edit/{id}', [CustomerServiceController::class, 'editor'])->name('admin.customer-service.edit');
            Route::get('detail/{id}', [CustomerServiceController::class, 'detail'])->name('admin.customer-service.detail');
            Route::post('save', [CustomerServiceController::class, 'save'])->name('admin.customer-service.save');
            Route::post('delete/{id}', [CustomerServiceController::class, 'delete'])->name('admin.customer-service.delete');
            Route::get('export', [CustomerServiceController::class, 'export'])->name('admin.customer-service.export');
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
