<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        return view('promotions.index', [
            'promotions' => Promotion::withCount('transactions')->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('promotions.form', ['promotion' => new Promotion]);
    }

    public function store(Request $request): RedirectResponse
    {
        Promotion::create($this->validated($request) + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('promotions.index')->with('success', 'Promo berhasil ditambahkan.');
    }

    public function edit(Promotion $promotion): View
    {
        return view('promotions.form', ['promotion' => $promotion]);
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $promotion->update($this->validated($request) + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('promotions.index')->with('success', 'Promo berhasil diperbarui.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->delete();

        return redirect()->route('promotions.index')->with('success', 'Promo dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'banner' => ['nullable', 'string', 'max:255'],
            'promo_type' => ['required', 'in:percentage,nominal,buy_x_get_y,member_only'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'min_spend' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
    }
}
