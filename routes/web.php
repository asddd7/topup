<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// ADMIN
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\ItemCategoryController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\StockController;

// USER
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\GameController as UserGameController;
use App\Http\Controllers\User\OrderController as UserOrderController;
use App\Http\Controllers\User\VoucherController;


use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    [HomeController::class,'index']
)
->name('dashboard');
/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::get(
    '/login',
    [AuthController::class,'showLogin']
)
->name('login');


Route::post(
    '/login',
    [AuthController::class,'login']
);

Route::get(
    '/register',
    [AuthController::class,'showRegister']
)
->name('register');

Route::post(
    '/register',
    [AuthController::class,'register']
);

/*
|--------------------------------------------------------------------------
| GAME PUBLIC
|--------------------------------------------------------------------------
*/

Route::get(
    '/game',
    [UserGameController::class,'index']
)
->name('game.index');

Route::get(
    '/game/{game}',
    [UserGameController::class,'show']
)
->name('game.show');

/*
|--------------------------------------------------------------------------
| PROMOTION / VOUCHER
|--------------------------------------------------------------------------
*/

Route::post(
    '/check-voucher',
    [VoucherController::class, 'check']
)->name('voucher.check');

Route::post(
    '/payment-promo',
    [VoucherController::class, 'paymentPromo']
)->name('payment.promo');

Route::post(
    '/voucher/calculate',
    [UserOrderController::class, 'calculatePromotion']
)->name('voucher.calculate');
/*
|--------------------------------------------------------------------------
| PUBLIC ORDER
|--------------------------------------------------------------------------
*/

Route::post(
    '/order',
    [UserOrderController::class,'store']
)
->name('order.store');

/*
|--------------------------------------------------------------------------
| DETAIL ORDER PUBLIC
|--------------------------------------------------------------------------
*/

Route::get(
    '/order/{invoice}',
    [UserOrderController::class,'show']
)
->name('order.show');

/*
|--------------------------------------------------------------------------
| PAYMENT
|--------------------------------------------------------------------------
*/

Route::get(
    '/order/{invoice}/payment',
    [UserOrderController::class,'payment']
)
->name('order.payment');

Route::post(
    '/order/{invoice}/upload-proof',
    [UserOrderController::class,'uploadProof']
)
->name('order.uploadProof');

/*
|--------------------------------------------------------------------------
| GUEST CHECK ORDER
|--------------------------------------------------------------------------
*/

Route::get(
    '/order/check/{invoice}',
    [UserOrderController::class,'checkOrder']
)
->name('order.check');

/*
|--------------------------------------------------------------------------
| USER LOGIN
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:2'
])
->prefix('user')
->group(function(){

Route::get(
    '/orders',
    [UserOrderController::class,'index']
)
->name('order.index');

Route::get(
    '/profile',
    [ProfileController::class,'index']
)
->name('profile.modal');

});
 
/*
|--------------------------------------------------------------------------
| ADMIN AREA
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:1'
])
->prefix('admin')
->name('admin.')
->group(function(){

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

Route::get(
    '/dashboard',
    [DashboardController::class,'index']
)
->name('dashboard');

Route::get(
    '/game/{game}/manage',
    [GameController::class,'manage']
)->name('game.manage');

/*
|--------------------------------------------------------------------------
| GAME
|--------------------------------------------------------------------------
*/

Route::resource(
    'game',
    GameController::class
);

Route::resource(
    'banner',
    BannerController::class
);

/*
|--------------------------------------------------------------------------
| ITEM MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::prefix(
    'game/{game}'
)
->group(function(){

Route::resource(
    'items',
    ItemController::class
)
->names([
    'index'=>'game.items',
    'create'=>'game.items.create',
    'store'=>'game.items.store',
    'edit'=>'game.items.edit',
    'update'=>'game.items.update',
    'destroy'=>'game.items.destroy'
]);

});

Route::get(
    '/stock',
    [StockController::class,'index']
)->name('stock.index');

Route::post(
    '/stock/{item}',
    [StockController::class,'update']
)->name('stock.update');

/*
|--------------------------------------------------------------------------
| MASTER DATA
|--------------------------------------------------------------------------
*/

Route::resource(
    'item-category',
    ItemCategoryController::class
);

Route::resource(
    'discount',
    DiscountController::class
);

Route::resource(
    'payment',
    PaymentController::class
);

Route::resource(
    'activity-log',
    ActivityLogController::class
)->only([
    'index',
    'show'
]);

/*
|--------------------------------------------------------------------------
| ORDER MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::resource(
    'order',
    OrderController::class
)
->only([
    'index',
    'show',
    'update'
]);

/*
|--------------------------------------------------------------------------
| PAYMENT CONFIRMATION
|--------------------------------------------------------------------------
*/

Route::post(
    '/order/{order}/confirm',
    [OrderController::class,'confirm']
)
->name('order.confirm');

Route::post(
    '/orders/{order}/approve',
    [OrderController::class, 'approve']
)->name('order.approve');

Route::post(
    'order/{order}/reject',
    [OrderController::class,'reject']
)
->name('order.reject');

Route::get('/setting', [SettingController::class, 'index'])->name('setting.index');
Route::put('/setting', [SettingController::class, 'update'])->name('setting.update');
});

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

Route::post(
    '/logout',
    [AuthController::class,'logout']
)
->middleware('auth')
->name('logout');

require __DIR__.'/auth.php';