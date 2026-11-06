<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function index(): View
    {
        return view('admin.quotes.index', [
            'quotes' => Quote::query()->with('product')->latest()->limit(100)->get(),
        ]);
    }

    public function updateStatus(Request $request, Quote $quote): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'converted', 'abandoned'])],
        ]);

        $quote->update($validated);

        return redirect()->route('admin.quotes.index')->with('status', 'Situação do orçamento atualizada. Nenhuma mensagem foi enviada.');
    }
}
