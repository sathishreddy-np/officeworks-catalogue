<?php

// These use lines import the catalogue controllers so the routes below can call them.
use App\Http\Controllers\Catalogue\CategoryController;
use App\Http\Controllers\Catalogue\EnquiryController;
use App\Http\Controllers\Catalogue\HomeController;
use App\Http\Controllers\Catalogue\ProductController;
use App\Http\Controllers\Catalogue\SearchController;
// Route is Laravel's tool for connecting a URL to a controller method.
use Illuminate\Support\Facades\Route;

// GET / shows the five top-rated products. The name "home" is used by route('home').
Route::get('/', [HomeController::class, 'index'])->name('home');
// GET /search?q=jacket runs the server-side search.
Route::get('/search', [SearchController::class, 'index'])->name('search');
// {category} is the slug, for example mens-clothing. The where() rule allows only lowercase letters, numbers, and dashes.
Route::get('/categories/{category}', [CategoryController::class, 'show'])
    ->where('category', '[a-z0-9\-]+')
    ->name('categories.show');
// {product} must be a number, for example /products/11.
Route::get('/products/{product}', [ProductController::class, 'show'])
    ->whereNumber('product')
    ->name('products.show');
// POST saves an enquiry. throttle allows 8 submits per minute from one visitor.
Route::post('/products/{product}/enquiries', [EnquiryController::class, 'store'])
    ->whereNumber('product')
    ->middleware('throttle:8,1')
    ->name('enquiries.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
