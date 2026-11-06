<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ConversationController as AdminConversationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\QuoteController as AdminQuoteController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\QuoteController;
use App\Models\Conversation;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home', [
        'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
        'hasDemoProducts' => Product::query()->where('is_active', true)->where('is_demo', true)->exists(),
        'aiEnabled' => filled(config('services.openai.key')),
    ]);
})->name('home');

Route::post('/api/chat', ChatController::class)->middleware('throttle:20,1')->name('chat.send');
Route::post('/api/quotes', QuoteController::class)->middleware('throttle:12,1')->name('quote.send');
Route::get('/api/chat/{conversation:public_id}', function (Conversation $conversation) {
    return response()->json([
        'messages' => $conversation->messages()->oldest('id')->get(['role', 'channel', 'content']),
        'marketing_consent' => $conversation->marketing_consent,
        'marketing_interests' => $conversation->marketing_interests ?? [],
    ]);
})->middleware('throttle:60,1')->name('chat.history');
Route::delete('/api/chat/{conversation:public_id}', function (Conversation $conversation) {
    $conversation->delete();

    return response()->noContent();
})->middleware('throttle:10,1')->name('chat.clear');

Route::get('/admin/login', [AuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('admin.login.store');
Route::post('/admin/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('admin.logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/conversations', [AdminConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/{conversation}', [AdminConversationController::class, 'show'])->name('conversations.show');
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/quotes', [AdminQuoteController::class, 'index'])->name('quotes.index');
    Route::patch('/quotes/{quote}/status', [AdminQuoteController::class, 'updateStatus'])->name('quotes.status');
});
