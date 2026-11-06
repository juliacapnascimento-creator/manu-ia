<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Conversation::query()->withCount(['messages', 'quotes'])->with('quotes.product')->latest('last_message_at');

        if ($request->query('consent') === 'yes') {
            $query->where('marketing_consent', true);
        } elseif ($request->query('consent') === 'no') {
            $query->where('marketing_consent', false);
        }

        return view('admin.conversations.index', [
            'conversations' => $query->paginate(20)->withQueryString(),
            'consentFilter' => $request->query('consent', 'all'),
        ]);
    }

    public function show(Conversation $conversation): View
    {
        return view('admin.conversations.show', [
            'conversation' => $conversation->load(['messages', 'quotes.product']),
        ]);
    }
}
