<?php

// use App\Http\Controllers\Admin\DashboardController;
// use App\Http\Controllers\Auth\AuthController;
// use App\Http\Controllers\Auth\ForgotPasswordController;
// use App\Http\Controllers\Auth\ResetPasswordController;
// use App\Http\Controllers\Profile\ProfileController;
// use Illuminate\Support\Facades\Route;
// use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
// use App\Http\Controllers\Contact\ContactController;
// use App\Http\Controllers\Order\AdminOrderController;
// use App\Http\Controllers\User\UserController;
// use App\Http\Controllers\Product\ProductController;
// use App\Http\Controllers\Product\ProductCategoryController;
// use App\Http\Controllers\Product\BrandController;
// use App\Http\Controllers\Product\ColourController;
// use App\Http\Controllers\Product\SizeController;
// use App\Http\Controllers\Product\SupplierController;
// use App\Http\Controllers\Order\CustomerOrderController;
// use App\Http\Controllers\Order\OrderController;
// use App\Http\Controllers\Product\CartController;
// use App\Http\Controllers\Product\CategoryController;
// use App\Http\Controllers\Product\VariantOptionController;
// use App\Http\Controllers\Product\VariantValueController;
// // use App\Http\Controllers\Address\AddressController;
// use App\Http\Controllers\Profile\EmailUpdateController;
// use App\Http\Controllers\User\AddressController;
// use App\Http\Controllers\Customer\AdminCustomerController;
// use App\Http\Controllers\Product\DiscountController;
// use App\Http\Controllers\Product\InventoryController;

// // Public routes
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
// Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
// Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);


// // Project routes

// // Blog routes





// Route::post('/contact', [ContactController::class, 'sendContactMessage']);

// Route::get('/products', [ProductController::class, 'index']);
// Route::get('/products/{slug}', [ProductController::class, 'show']);
// Route::post('/products', [ProductController::class, 'store']);
// Route::put('/products/{product}', [ProductController::class, 'update']);
// Route::delete('/products/{slug}', [ProductController::class, 'destroy']);







// // Protected routes
// Route::middleware('auth:sanctum')->group(function () {
//     // User routes
//     Route::post('/logout', [AuthController::class, 'logout']);
//     Route::post('/request-email-change', [EmailUpdateController::class, 'requestChange']);
//     Route::get('/user/profile', [ProfileController::class, 'index']);
//     Route::put('/user/update-name', [ProfileController::class, 'updateName']);
//     Route::put('/user/update-phone', [ProfileController::class, 'updatePhone']);
//     Route::post('/user/create-address', [AddressController::class, 'store']);
//     Route::get('/user/addresses', [AddressController::class, 'index']);
//     Route::get('/user/address/{id}', [AddressController::class, 'show']);
//     Route::put('/user/update-address/{id}', [AddressController::class, 'update']);
//     Route::delete('/user/addresses/{id}', [AddressController::class, 'destroy']);


//     Route::patch('/user/addresses/{address}/default', [AddressController::class, 'setDefault']);
// });



// Route::get('/variant-options', [VariantOptionController::class, 'index']);
// Route::post('/variant-options', [VariantOptionController::class, 'store']);
// Route::put('/variant-options/{variantOption}', [VariantOptionController::class, 'update']);
// Route::get('/variant-options/{id}/values', [VariantOptionController::class, 'getValues']);

// Route::post('/variant-option-values', [VariantValueController::class, 'store']);
// Route::put('/variant-option-values/{variantOptionValue}', [VariantValueController::class, 'update']);





// Route::apiResource('categories', CategoryController::class);
// Route::apiResource('brands', BrandController::class);



// // routes/api.php





// Route::apiResource('colours', ColourController::class);
// Route::post('colours/{id}/restore', [ColourController::class, 'restore']);




// Route::apiResource('sizes', SizeController::class);
// Route::post('sizes/{id}/restore', [SizeController::class, 'restore']);



// Route::apiResource('suppliers', SupplierController::class);
// Route::post('suppliers/restore/{id}', [SupplierController::class, 'restore']);


// Route::prefix('orders')->group(function () {
//     Route::get('/', [CustomerOrderController::class, 'index']);
//     Route::post('/', [CustomerOrderController::class, 'store']);
//     Route::get('{id}', [CustomerOrderController::class, 'show']);
//     Route::put('{id}', [CustomerOrderController::class, 'update']);
//     Route::delete('{id}', [CustomerOrderController::class, 'destroy']);
//     Route::patch('{id}/restore', [CustomerOrderController::class, 'restore']);
// });

// Route::middleware('auth:sanctum')->post('/checkout', [OrderController::class, 'checkout']);

// Route::middleware('auth:sanctum')->get('/user/orders', [OrderController::class, 'userOrders']);
// Route::middleware('auth:sanctum')->get('/user/orders/{id}', [OrderController::class, 'show']);
// Route::delete('/user/orders/{id}/cancel', [OrderController::class, 'cancel'])->middleware('auth:sanctum');





// Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
//     Route::get('/customers', [AdminCustomerController::class, 'index']);
//     Route::get('/customers/{id}', [AdminCustomerController::class, 'show']);

//     Route::get('/discounts', [DiscountController::class, 'index']);
//     Route::post('/discounts', [DiscountController::class, 'store']);

//     Route::get('/discounts/{id}', [DiscountController::class, 'show']);
//     Route::put('/discounts/{id}', [DiscountController::class, 'update']);

//     Route::delete('/discounts/{id}', [DiscountController::class, 'destroy']);
// });

// Route::middleware('auth:sanctum')->post('/apply-discount', [DiscountController::class, 'applyDiscount']);
// Route::middleware('auth:sanctum')->get('/auto-discount', [DiscountController::class, 'checkAutomaticDiscount']);






// // Don't protect cart routes with `auth:sanctum` so guests can access them
// Route::post('/cart/add', [CartController::class, 'addToCart']);
// Route::get('/cart', [CartController::class, 'getCart']);
// Route::put('/cart/{cartItem}', [CartController::class, 'updateCartItem']);
// Route::delete('/cart/{cartItem}', [CartController::class, 'removeCartItem']);
// Route::get('/cart/item-quantity', [CartController::class, 'getCartItemQuantity']);



// // Route::middleware('auth:admin')->group(function () {

// // });

// Route::get('/users', [UserController::class, 'index']);








// Route::get('admin/orders', [AdminOrderController::class, 'index']);

// Route::get('/admin/orders/{id}', [AdminOrderController::class, 'show']);
// Route::patch('admin/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);

// // User

// // Admin
// Route::delete('/admin/orders/{id}/cancel', [AdminOrderController::class, 'adminCancel'])->middleware('auth:sanctum');



// Route::get('admin/inventory', [InventoryController::class, 'index']);
// Route::patch('/admin/inventory/update-stock/{type}/{id}', [InventoryController::class, 'updateStock']);



// Route::middleware(['admin'])->group(function () {


//     Route::get('/admin/dashboard/summary', [DashboardController::class, 'summary']);
// });




























use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Profile\EmailUpdateController;

use App\Http\Controllers\Contact\ContactController;

use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\AddressController;

use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Product\BrandController;

use App\Http\Controllers\Product\VariantOptionController;
use App\Http\Controllers\Product\VariantValueController;
use App\Http\Controllers\Product\CartController;
use App\Http\Controllers\Product\DiscountController;
use App\Http\Controllers\Product\InventoryController;

use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\AdminOrderController;

use App\Http\Controllers\Customer\AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Contact\ContactMessageController;
use App\Http\Controllers\Role\AdminUserRoleController;
use App\Http\Controllers\Role\PermissionController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Setting\SettingController;

// -----------------------------
// ✅ PUBLIC ROUTES
// -----------------------------

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);
// Route::get('/settings/currency', [SettingController::class, 'currency']);


// Route::post('/contact', [ContactController::class, 'sendContactMessage']);
Route::post('/contact-message', [ContactMessageController::class, 'store']);


Route::get('/settings', [SettingController::class, 'index']);
Route::get('/settings/{slug}', [SettingController::class, 'show']);
Route::put('/settings/{slug}', [SettingController::class, 'update'])->middleware('permission:settings.update');








// -----------------------------
// ✅ PRODUCT & VARIANT ROUTES
// -----------------------------

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
// Route::post('/products', [ProductController::class, 'store']);
// Route::put('/products/{product}', [ProductController::class, 'update']);
// Route::delete('/products/{slug}', [ProductController::class, 'destroy']);

Route::apiResource('categories', CategoryController::class);
Route::apiResource('brands', BrandController::class);

Route::get('/variant-options', [VariantOptionController::class, 'index']);
Route::post('/variant-options', [VariantOptionController::class, 'store']);
Route::put('/variant-options/{variantOption}', [VariantOptionController::class, 'update']);
Route::get('/variant-options/{id}/values', [VariantOptionController::class, 'getValues']);

Route::post('/variant-option-values', [VariantValueController::class, 'store']);
Route::put('/variant-option-values/{variantOptionValue}', [VariantValueController::class, 'update']);


// -----------------------------
// ✅ CART ROUTES (Guest allowed)
// -----------------------------

Route::post('/cart/add', [CartController::class, 'addToCart']);
Route::get('/cart', [CartController::class, 'getCart']);
Route::put('/cart/{cartItem}', [CartController::class, 'updateCartItem']);
Route::delete('/cart/{cartItem}', [CartController::class, 'removeCartItem']);
Route::get('/cart/item-quantity', [CartController::class, 'getCartItemQuantity']);


// -----------------------------
// ✅ AUTHENTICATED USER ROUTES
// -----------------------------

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/refresh-user', [AuthController::class, 'refreshUser']);

    // Profile
    Route::get('/user/profile', [ProfileController::class, 'index']);
    Route::put('/user/update-name', [ProfileController::class, 'updateName']);
    Route::put('/user/update-phone', [ProfileController::class, 'updatePhone']);
    Route::post('/request-email-change', [EmailUpdateController::class, 'requestChange']);

    // Addresses
    Route::post('/user/create-address', [AddressController::class, 'store']);
    Route::get('/user/addresses', [AddressController::class, 'index']);
    Route::get('/user/address/{id}', [AddressController::class, 'show']);
    Route::put('/user/update-address/{id}', [AddressController::class, 'update']);
    Route::delete('/user/addresses/{id}', [AddressController::class, 'destroy']);
    Route::patch('/user/addresses/{address}/default', [AddressController::class, 'setDefault']);

    // Orders
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/user/orders', [OrderController::class, 'userOrders']);
    Route::get('/user/orders/{id}', [OrderController::class, 'show']);
    Route::delete('/user/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // Discount (apply/check)
    Route::post('/apply-discount', [DiscountController::class, 'applyDiscount']);
    Route::get('/auto-discount', [DiscountController::class, 'checkAutomaticDiscount']);
});


// -----------------------------
// ✅ ADMIN ROUTES
// -----------------------------









Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {

    // Products
    Route::get('/products', [ProductController::class, 'index'])->middleware('permission:products.view');
    Route::post('/products', [ProductController::class, 'store'])->middleware('permission:products.create');
    // Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('permission:products.update');



    Route::put('/products/{product}', [ProductController::class, 'update'])
        ->withoutScopedBindings()
        ->middleware('permission:products.update');

    Route::delete('/products/{slug}', [ProductController::class, 'destroy'])->middleware('permission:products.delete');

    // Customers
    Route::get('/customers', [AdminCustomerController::class, 'index'])->middleware('permission:users.view');
    Route::get('/customers/{id}', [AdminCustomerController::class, 'show'])->middleware('permission:users.view');

    // Orders
    Route::get('/orders', [AdminOrderController::class, 'index'])->middleware('permission:orders.view');
    Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->middleware('permission:orders.view');
    Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->middleware('permission:orders.update');
    Route::delete('/orders/{id}/cancel', [AdminOrderController::class, 'adminCancel'])->middleware('permission:orders.cancel');

    // Discounts
    Route::get('/discounts', [DiscountController::class, 'index'])->middleware('permission:discounts.view');
    Route::post('/discounts', [DiscountController::class, 'store'])->middleware('permission:discounts.create');
    Route::get('/discounts/{id}', [DiscountController::class, 'show'])->middleware('permission:discounts.view');
    Route::put('/discounts/{id}', [DiscountController::class, 'update'])->middleware('permission:discounts.update');
    Route::delete('/discounts/{id}', [DiscountController::class, 'destroy'])->middleware('permission:discounts.delete');

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->middleware('permission:inventory.view');
    Route::patch('/inventory/update-stock/{type}/{id}', [InventoryController::class, 'updateStock'])->middleware('permission:inventory.update');

    // Dashboard
    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])
        ->middleware('permission:dashboard.view');
    // Roles & Permissions
    Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:roles.view');
    Route::get('/roles/{id}', [RoleController::class, 'show'])->middleware('permission:roles.view');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:roles.create');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->middleware('permission:roles.update');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete');

    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:roles.view');

    // Role Assignment
    Route::get('/users-with-roles', [AdminUserRoleController::class, 'index'])->middleware('permission:roles.view');
    Route::get('/users/{id}/roles', [AdminUserRoleController::class, 'showRoles'])->middleware('permission:roles.view');
    Route::post('/users/{id}/roles', [AdminUserRoleController::class, 'assignRoles'])->middleware('permission:roles.assign');
    Route::delete('/users/{id}/roles/{role}', [AdminUserRoleController::class, 'revokeRole'])->middleware('permission:roles.revoke');

    //settings

 

    Route::get('/settings', [SettingController::class, 'index'])->middleware('permission:settings.view');

    Route::get('/settings/{slug}', [SettingController::class, 'show'])->middleware('permission:settings.view');

    Route::put('/settings/{slug}', [SettingController::class, 'update'])->middleware('permission:settings.update');
});
































// Route::prefix('admin')->middleware('auth:sanctum')->group(function () {

//     //products
//     Route::post('/products', [ProductController::class, 'store']);
//     Route::put('/products/{product}', [ProductController::class, 'update']);
//     Route::delete('/products/{slug}', [ProductController::class, 'destroy']);


//     // Customers
//     Route::get('/customers', [AdminCustomerController::class, 'index']);
//     Route::get('/customers/{id}', [AdminCustomerController::class, 'show']);

//     // Orders
//     Route::get('/orders', [AdminOrderController::class, 'index']);
//     Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
//     Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
//     Route::delete('/orders/{id}/cancel', [AdminOrderController::class, 'adminCancel']);

//     // Discounts
//     Route::get('/discounts', [DiscountController::class, 'index']);
//     Route::post('/discounts', [DiscountController::class, 'store']);
//     Route::get('/discounts/{id}', [DiscountController::class, 'show']);
//     Route::put('/discounts/{id}', [DiscountController::class, 'update']);
//     Route::delete('/discounts/{id}', [DiscountController::class, 'destroy']);

//     // Inventory
//     Route::get('/inventory', [InventoryController::class, 'index']);
//     Route::patch('/inventory/update-stock/{type}/{id}', [InventoryController::class, 'updateStock']);

//     // Dashboard Summary
//     Route::get('/dashboard/summary', [DashboardController::class, 'summary']);


//     //Roles and Permissions
//     Route::get('/roles', [RoleController::class, 'index']);
//     Route::get('/roles/{id}', [RoleController::class, 'show']);
//     Route::post('/roles', [RoleController::class, 'store']);
//     Route::put('/roles/{id}', [RoleController::class, 'update']);
//     Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
//     // Route::get('/roles/{id}/permissions', [RoleController::class, 'permissions']);
//     // Route::post('/roles/{id}/permissions', [RoleController::class, 'assignPermissions']);

//     Route::get('/permissions', [PermissionController::class, 'index']);



//     Route::get('/users-with-roles', [AdminUserRoleController::class, 'index']);
//     Route::get('/users/{id}/roles', [AdminUserRoleController::class, 'showRoles']);
//     Route::post('/users/{id}/roles', [AdminUserRoleController::class, 'assignRoles']);
//     Route::delete('/users/{id}/roles/{role}', [AdminUserRoleController::class, 'revokeRole']);
// });


// -----------------------------
// ✅ test routes
// -----------------------------

Route::get('/users', [UserController::class, 'index']);