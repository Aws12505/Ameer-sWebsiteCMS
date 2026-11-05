<?php

use App\Http\Controllers\Admin\ContactPageController;
use App\Http\Controllers\Admin\ContactRequestController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HeroSettingsController;
use App\Http\Controllers\Admin\HomePageController;
use App\Http\Controllers\Admin\PageTextController;
use App\Http\Controllers\Admin\ProjectCategoryController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::post('uploads', UploadController::class)->middleware('throttle:60,1')->name('uploads.store');

        Route::get('site-settings', [SiteSettingsController::class, 'edit'])->name('site-settings.edit');
        Route::put('site-settings', [SiteSettingsController::class, 'update'])->name('site-settings.update');

        Route::get('hero-settings', [HeroSettingsController::class, 'edit'])->name('hero-settings.edit');
        Route::put('hero-settings', [HeroSettingsController::class, 'update'])->name('hero-settings.update');

        Route::get('pages/home', [HomePageController::class, 'edit'])->name('pages.home.edit');
        Route::put('pages/home', [HomePageController::class, 'update'])->name('pages.home.update');
        Route::get('pages/contact', [ContactPageController::class, 'edit'])->name('pages.contact.edit');
        Route::put('pages/contact', [ContactPageController::class, 'update'])->name('pages.contact.update');
        Route::get('pages/{page}', [PageTextController::class, 'edit'])->whereIn('page', ['services', 'projects'])->name('pages.text.edit');
        Route::put('pages/{page}', [PageTextController::class, 'update'])->whereIn('page', ['services', 'projects'])->name('pages.text.update');

        $sortable = [
            'service-categories' => [ServiceCategoryController::class, 'serviceCategory'],
            'services' => [ServiceController::class, 'service'],
            'project-categories' => [ProjectCategoryController::class, 'projectCategory'],
            'projects' => [ProjectController::class, 'project'],
            'reviews' => [ReviewController::class, 'review'],
        ];

        foreach ($sortable as $uri => [$controller, $parameter]) {
            Route::put("{$uri}/reorder", [$controller, 'reorder'])->name("{$uri}.reorder");
            Route::patch("{$uri}/{{$parameter}}/publish", [$controller, 'publish'])->name("{$uri}.publish");
            Route::resource($uri, $controller)->except('show')->parameters([$uri => $parameter]);
        }

        Route::get('contact-requests/export', [ContactRequestController::class, 'export'])->name('contact-requests.export');
        Route::resource('contact-requests', ContactRequestController::class)
            ->only(['index', 'show', 'update'])
            ->parameters(['contact-requests' => 'contactRequest']);
        Route::delete('contact-requests/{contactRequest}', [ContactRequestController::class, 'destroy'])
            ->middleware('can:admin')
            ->name('contact-requests.destroy');

        Route::get('trash', [TrashController::class, 'index'])->name('trash.index');
        Route::post('trash/{type}/{id}/restore', [TrashController::class, 'restore'])->whereNumber('id')->name('trash.restore');
        Route::delete('trash/{type}/{id}', [TrashController::class, 'destroy'])->whereNumber('id')->middleware('can:admin')->name('trash.destroy');

        Route::resource('users', UserController::class)->except('show')->middleware('can:admin');
    });
});

require __DIR__.'/settings.php';
