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
        $chartStart = now()->startOfMonth()->subMonths(5);
        $chartEnd = now()->startOfMonth()->addMonth();
        $monthlyQuotes = Quote::query()
            ->where('created_at', '>=', $chartStart)
            ->where('created_at', '<', $chartEnd)
            ->get(['created_at'])
            ->countBy(fn (Quote $quote): string => $quote->created_at->format('Y-m'));
        $monthlyConversations = Conversation::query()
            ->where('created_at', '>=', $chartStart)
            ->where('created_at', '<', $chartEnd)
            ->get(['created_at'])
            ->countBy(fn (Conversation $conversation): string => $conversation->created_at->format('Y-m'));
        $monthlyActivity = collect(range(5, 0))
            ->map(function (int $monthsAgo) use ($monthlyQuotes, $monthlyConversations): array {
                $month = now()->startOfMonth()->subMonths($monthsAgo);
                $key = $month->format('Y-m');

                return [
                    'key' => $key,
                    'label' => $month->locale('pt_BR')->translatedFormat('M'),
                    'quotes' => $monthlyQuotes->get($key, 0),
                    'conversations' => $monthlyConversations->get($key, 0),
                ];
            });
        $maxMonthlyActivity = max(1, (int) $monthlyActivity->max(fn (array $month): int => max($month['quotes'], $month['conversations'])));
        $topQuotedProducts = Quote::query()
            ->selectRaw('product_id, COUNT(*) as quote_count')
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderByDesc('quote_count')
            ->limit(5)
            ->get()
            ->filter(fn (Quote $quote): bool => $quote->product !== null)
            ->values();

        return view('admin.dashboard', [
            'activeProducts' => Product::query()->where('is_active', true)->count(),
            'demoProducts' => Product::query()->where('is_active', true)->where('is_demo', true)->count(),
            'conversations' => Conversation::query()->count(),
            'consentedConversations' => Conversation::query()->where('marketing_consent', true)->count(),
            'conversationsWithInterests' => Conversation::query()
                ->where('marketing_consent', true)
                ->whereJsonLength('marketing_interests', '>', 0)
                ->count(),
            'totalQuotes' => Quote::query()->count(),
            'pendingQuotes' => Quote::query()->where('status', 'pending')->count(),
            'convertedQuotes' => Quote::query()->where('status', 'converted')->count(),
            'recentQuotes' => Quote::query()->with('product')->latest()->limit(5)->get(),
            'monthlyActivity' => $monthlyActivity,
            'maxMonthlyActivity' => $maxMonthlyActivity,
            'topQuotedProducts' => $topQuotedProducts,
        ]);
    }
}
