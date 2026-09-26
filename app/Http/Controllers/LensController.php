<?php

namespace App\Http\Controllers;

use App\Models\Lens;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LensController extends Controller
{
    public function index(Request $request): View
    {
        $lenses = Lens::query()
            ->with('category')
            ->when($request->filled('brand'), fn ($q) => $q->where('brand', $request->string('brand')))
            ->when($request->filled('q'), fn ($q) => $q->where(
                fn ($w) => $w->where('sku', 'like', "%{$request->string('q')}%")
                    ->orWhere('name', 'like', "%{$request->string('q')}%"),
            ))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('lenses.index', [
            'lenses' => $lenses,
            'brands' => Lens::query()->distinct()->orderBy('brand')->pluck('brand'),
            'filters' => $request->only(['brand', 'q']),
        ]);
    }

    public function create(): View
    {
        return view('lenses.form', ['lens' => new Lens, 'categories' => $this->categories()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $lens = Lens::create($this->validated($request));

        return redirect()->route('lenses.index')->with('success', "Lensa {$lens->name} berhasil disimpan.");
    }

    public function edit(Lens $lens): View
    {
        return view('lenses.form', ['lens' => $lens, 'categories' => $this->categories()]);
    }

    public function update(Request $request, Lens $lens): RedirectResponse
    {
        $lens->update($this->validated($request));

        return redirect()->route('lenses.index')->with('success', "Lensa {$lens->name} berhasil diperbarui.");
    }

    public function destroy(Lens $lens): RedirectResponse
    {
        $name = $lens->name;
        $lens->delete();

        return redirect()->route('lenses.index')->with('success', "Lensa {$name} dihapus.");
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    private function categories()
    {
        return ProductCategory::whereIn('type', ['lens', 'accessory'])->orderBy('name')->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:lenses,sku'.($request->route('lens') ? ','.$request->route('lens')->id : '')],
            'brand' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'lens_type' => ['required', 'string', 'max:50'],
            'material' => ['nullable', 'string', 'max:50'],
            'index_val' => ['required', 'string', 'max:10'],
            'coating' => ['nullable', 'string', 'max:100'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:100'],
        ]);
    }
}
