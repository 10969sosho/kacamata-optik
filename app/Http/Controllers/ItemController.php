<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\Frame;
use App\Models\Lens;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ItemController extends Controller
{
    /**
     * Halaman Master Item: seluruh item (frame, lensa, softlens, case, aksesoris)
     * ditampilkan dalam satu daftar yang dipisah per kategori.
     */
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->query('q', ''));
        $activeTab = (string) $request->query('tab', 'all');

        $categories = $this->accessoryCategories();
        $rows = $this->catalogRows($keyword);

        $tabs = $this->tabs($rows, $categories);
        $activeTab = $tabs->pluck('key')->contains($activeTab) ? $activeTab : 'all';

        $filtered = $rows
            ->filter(fn (array $row) => $activeTab === 'all' || $row['tab'] === $activeTab)
            ->sortBy('name')
            ->values();

        return view('items.index', [
            'items' => $this->paginate($filtered, $request),
            'tabs' => $tabs,
            'categories' => $categories,
            'activeTab' => $activeTab,
            'filters' => ['q' => $keyword, 'tab' => $activeTab],
            'createUrl' => $this->createUrl($activeTab, $categories),
        ]);
    }

    public function createAccessory(Request $request): View
    {
        $accessory = new Accessory;
        $accessory->category_id = $request->integer('category_id') ?: null;

        return view('items.accessory-form', [
            'accessory' => $accessory,
            'categories' => $this->accessoryCategories(),
        ]);
    }

    public function storeAccessory(Request $request): RedirectResponse
    {
        $accessory = Accessory::create($this->validatedAccessory($request));

        return redirect()
            ->route('items.index', ['tab' => 'cat-'.$accessory->category_id])
            ->with('success', "Item {$accessory->name} berhasil disimpan.");
    }

    public function editAccessory(Accessory $accessory): View
    {
        return view('items.accessory-form', [
            'accessory' => $accessory,
            'categories' => $this->accessoryCategories(),
        ]);
    }

    public function updateAccessory(Request $request, Accessory $accessory): RedirectResponse
    {
        $accessory->update($this->validatedAccessory($request, $accessory));

        return redirect()
            ->route('items.index', ['tab' => 'cat-'.$accessory->category_id])
            ->with('success', "Item {$accessory->name} berhasil diperbarui.");
    }

    public function destroyAccessory(Accessory $accessory): RedirectResponse
    {
        $name = $accessory->name;
        $accessory->delete();

        return redirect()->route('items.index')->with('success', "Item {$name} dihapus.");
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    private function accessoryCategories(): Collection
    {
        return ProductCategory::query()
            ->where('type', 'accessory')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Baris seragam untuk frame, lensa, dan aksesoris.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function catalogRows(string $keyword): Collection
    {
        $frames = Frame::query()
            ->when($keyword !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('sku', 'like', "%{$keyword}%")
                ->orWhere('name', 'like', "%{$keyword}%")
                ->orWhere('brand', 'like', "%{$keyword}%")))
            ->get()
            ->map(fn (Frame $frame) => [
                'source' => 'frame',
                'tab' => 'frame',
                'category' => 'Frame',
                'sku' => $frame->sku,
                'name' => $frame->name,
                'brand' => $frame->brand,
                'detail' => collect([$frame->brand, $frame->size])->filter()->implode(' · '),
                'price' => (float) $frame->sell_price,
                'stock' => (int) $frame->stock,
                'min_stock' => (int) $frame->min_stock,
                'status' => $frame->status,
                'edit_url' => route('frames.edit', $frame),
                'delete_url' => route('frames.destroy', $frame),
                'delete_name' => $frame->name,
            ]);

        $lenses = Lens::with('category')
            ->when($keyword !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('sku', 'like', "%{$keyword}%")
                ->orWhere('name', 'like', "%{$keyword}%")
                ->orWhere('brand', 'like', "%{$keyword}%")))
            ->get()
            ->map(fn (Lens $lens) => [
                'source' => 'lens',
                'tab' => 'lens',
                'category' => $lens->category?->name ?? 'Lensa',
                'sku' => $lens->sku,
                'name' => $lens->name,
                'brand' => $lens->brand,
                'detail' => collect([$lens->lens_type, $lens->index_val ? 'Index '.$lens->index_val : null])->filter()->implode(' · '),
                'price' => (float) $lens->sell_price,
                'stock' => (int) $lens->stock,
                'min_stock' => (int) $lens->min_stock,
                'status' => 'active',
                'edit_url' => route('lenses.edit', $lens),
                'delete_url' => route('lenses.destroy', $lens),
                'delete_name' => $lens->name,
            ]);

        $accessories = Accessory::with('category')
            ->when($keyword !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('sku', 'like', "%{$keyword}%")
                ->orWhere('name', 'like', "%{$keyword}%")
                ->orWhere('brand', 'like', "%{$keyword}%")))
            ->get()
            ->map(fn (Accessory $accessory) => [
                'source' => 'accessory',
                'tab' => $accessory->category_id ? 'cat-'.$accessory->category_id : 'uncategorized',
                'category' => $accessory->category?->name ?? 'Tanpa Kategori',
                'sku' => $accessory->sku,
                'name' => $accessory->name,
                'brand' => $accessory->brand ?? '-',
                'detail' => $accessory->brand ?: $accessory->category?->name ?: '-',
                'price' => (float) $accessory->sell_price,
                'stock' => (int) $accessory->stock,
                'min_stock' => (int) $accessory->min_stock,
                'status' => $accessory->status,
                'edit_url' => route('items.accessories.edit', $accessory),
                'delete_url' => route('items.accessories.destroy', $accessory),
                'delete_name' => $accessory->name,
            ]);

        return $frames->concat($lenses)->concat($accessories)->values();
    }

    /**
     * Tab kategori: Semua, Frame, Lensa, lalu tiap kategori aksesoris (Softlens, Case, dll).
     *
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  Collection<int, ProductCategory>  $categories
     * @return Collection<int, array{key: string, label: string, count: int}>
     */
    private function tabs($rows, $categories): Collection
    {
        $counts = $rows->countBy(fn (array $row) => $row['tab']);

        $tabs = collect([
            ['key' => 'all', 'label' => 'Semua Item', 'count' => $rows->count()],
            ['key' => 'frame', 'label' => 'Frame', 'count' => $counts->get('frame', 0)],
            ['key' => 'lens', 'label' => 'Lensa', 'count' => $counts->get('lens', 0)],
        ]);

        foreach ($categories as $category) {
            $key = 'cat-'.$category->id;

            $tabs->push([
                'key' => $key,
                'label' => $category->name,
                'count' => $counts->get($key, 0),
            ]);
        }

        if ($counts->get('uncategorized', 0) > 0) {
            $tabs->push(['key' => 'uncategorized', 'label' => 'Tanpa Kategori', 'count' => $counts->get('uncategorized')]);
        }

        return $tabs;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    private function paginate(Collection $rows, Request $request): LengthAwarePaginator
    {
        $page = max(1, $request->integer('page'));

        return new LengthAwarePaginator(
            $rows->forPage($page, 15)->values(),
            $rows->count(),
            15,
            $page,
            ['path' => $request->url(), 'query' => $request->except('page')],
        );
    }

    /**
     * Tujuan tombol "Tambah Item" mengikuti kategori yang sedang aktif.
     *
     * @param  Collection<int, ProductCategory>  $categories
     */
    private function createUrl(string $activeTab, Collection $categories): string
    {
        if ($activeTab === 'lens') {
            return route('lenses.create');
        }

        if (str_starts_with($activeTab, 'cat-')) {
            return route('items.accessories.create', ['category_id' => substr($activeTab, 4)]);
        }

        if ($activeTab === 'frame') {
            return route('frames.create');
        }

        return $categories->isNotEmpty()
            ? route('items.accessories.create', ['category_id' => $categories->first()->id])
            : route('frames.create');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedAccessory(Request $request, ?Accessory $accessory = null): array
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:accessories,sku'.($accessory ? ','.$accessory->id : '')],
            'name' => ['required', 'string', 'max:150'],
            'brand' => ['nullable', 'string', 'max:80'],
            'category_id' => ['required', 'exists:product_categories,id'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['brand'] = $data['brand'] ?? null;
        $data['description'] = $data['description'] ?? null;

        return $data;
    }
}
