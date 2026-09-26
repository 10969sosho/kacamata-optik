<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('categories.index', [
            'categories' => ProductCategory::withCount('lenses')->orderBy('type')->orderBy('name')->paginate(10),
            'category' => request()->query('edit') ? ProductCategory::findOrFail(request()->query('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:product_categories,name'],
            'type' => ['required', 'in:lens,frame,accessory'],
            'is_active' => ['nullable', 'in:1'],
        ]);

        $data['slug'] = str($data['name'])->slug()->toString();
        $data['is_active'] = $request->boolean('is_active');

        ProductCategory::create($data);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, ProductCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:product_categories,name,'.$category->id],
            'type' => ['required', 'in:lens,frame,accessory'],
            'is_active' => ['nullable', 'in:1'],
        ]);

        $data['slug'] = str($data['name'])->slug()->toString();
        $data['is_active'] = $request->boolean('is_active');

        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        if ($category->lenses()->exists()) {
            return back()->withErrors(['category' => 'Kategori masih dipakai lensa.']);
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori dihapus.');
    }
}
