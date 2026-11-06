<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Product;
use App\Models\Quote;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'activeProducts' => Product::query()->where('is_active', true)->count(),
            'demoProducts' => Product::query()->where('is_active', true)->where('is_demo', true)->count(),
            'conversations' => Conversation::query()->count(),
            'consentedConversations' => Conversation::query()->where('marketing_consent', true)->count(),
            'totalQuotes' => Quote::query()->count(),
            'pendingQuotes' => Quote::query()->where('status', 'pending')->count(),
            'convertedQuotes' => Quote::query()->where('status', 'converted')->count(),
            'recentQuotes' => Quote::query()->with('product')->latest()->limit(5)->get(),
        ]);
    }
}
