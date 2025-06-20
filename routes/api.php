<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductCategoryController;
use App\Http\Controllers\Product\BrandController;
use App\Http\Controllers\Product\ColourController;
use App\Http\Controllers\Product\SizeController;
use App\Http\Controllers\Product\SupplierController;
use App\Http\Controllers\Order\CustomerOrderController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Product\CartController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Product\VariantOptionController;
use App\Http\Controllers\Product\VariantValueController;
// use App\Http\Controllers\Address\AddressController;
use App\Http\Controllers\Profile\EmailUpdateController;
use App\Http\Controllers\User\AddressController;
use Illuminate\Session\Middleware\StartSession;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);


// Project routes

// Blog routes





Route::post('/contact', [ContactController::class, 'sendContactMessage']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{slug}', [ProductController::class, 'destroy']);







// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/request-email-change', [EmailUpdateController::class, 'requestChange']);
    Route::get('/user/profile', [ProfileController::class, 'index']);
    Route::put('/user/update-name', [ProfileController::class, 'updateName']);
    Route::put('/user/update-phone', [ProfileController::class, 'updatePhone']);
    Route::post('/user/create-address', [AddressController::class, 'store']);
    Route::get('/user/addresses', [AddressController::class, 'index']);
    Route::get('/user/address/{id}', [AddressController::class, 'show']);
    Route::put('/user/update-address/{id}', [AddressController::class, 'update']);
    Route::delete('/user/addresses/{id}', [AddressController::class, 'destroy']);


    Route::patch('/user/addresses/{address}/default', [AddressController::class, 'setDefault']);
});



Route::get('/variant-options', [VariantOptionController::class, 'index']);
Route::post('/variant-options', [VariantOptionController::class, 'store']);
Route::put('/variant-options/{variantOption}', [VariantOptionController::class, 'update']);
Route::get('/variant-options/{id}/values', [VariantOptionController::class, 'getValues']);

Route::post('/variant-option-values', [VariantValueController::class, 'store']);
Route::put('/variant-option-values/{variantOptionValue}', [VariantValueController::class, 'update']);





Route::apiResource('categories', CategoryController::class);
Route::apiResource('brands', BrandController::class);



// routes/api.php





Route::apiResource('colours', ColourController::class);
Route::post('colours/{id}/restore', [ColourController::class, 'restore']);




Route::apiResource('sizes', SizeController::class);
Route::post('sizes/{id}/restore', [SizeController::class, 'restore']);



Route::apiResource('suppliers', SupplierController::class);
Route::post('suppliers/restore/{id}', [SupplierController::class, 'restore']);


Route::prefix('orders')->group(function () {
    Route::get('/', [CustomerOrderController::class, 'index']);
    Route::post('/', [CustomerOrderController::class, 'store']);
    Route::get('{id}', [CustomerOrderController::class, 'show']);
    Route::put('{id}', [CustomerOrderController::class, 'update']);
    Route::delete('{id}', [CustomerOrderController::class, 'destroy']);
    Route::patch('{id}/restore', [CustomerOrderController::class, 'restore']);
});

Route::middleware('auth:sanctum')->post('/checkout', [OrderController::class, 'checkout']);





// Route::middleware(['auth:sanctum'])->group(function () {
//     Route::post('/cart/add', [CartController::class, 'addToCart']);
//     Route::get('/cart', [CartController::class, 'getCart']);
//     Route::put('/cart/{cartItem}', [CartController::class, 'updateCartItem']);
//     Route::delete('/cart/{cartItem}', [CartController::class, 'removeCartItem']);
//     Route::get('/cart/item-quantity', [CartController::class, 'getCartItemQuantity']);
// });



// Don't protect cart routes with `auth:sanctum` so guests can access them
Route::post('/cart/add', [CartController::class, 'addToCart']);
Route::get('/cart', [CartController::class, 'getCart']);
Route::put('/cart/{cartItem}', [CartController::class, 'updateCartItem']);
Route::delete('/cart/{cartItem}', [CartController::class, 'removeCartItem']);
Route::get('/cart/item-quantity', [CartController::class, 'getCartItemQuantity']);



// Route::middleware('auth:admin')->group(function () {

// });

Route::get('/users', [UserController::class, 'index']);



// Route::middleware('auth:sanctum')->group(function () {



//     Route::get('/user-addresses', [AddressController::class, 'index']);
//     Route::get('/auth-user-addresses', [AddressController::class, 'authUserAddresses']);
//     Route::post('/addresses', [AddressController::class, 'store']);
//     Route::get('/addresses/{id}', [AddressController::class, 'show']);
//     Route::put('/addresses/{id}', [AddressController::class, 'update']);
//     Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
// });