<?php

namespace App\Http\Controllers;

use App\Models\Frame;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FrameController extends Controller
{
    public function index(Request $request): View
    {
        $frames = Frame::query()
            ->when($request->filled('brand'), fn ($q) => $q->where('brand', $request->string('brand')))
            ->when($request->filled('q'), fn ($q) => $q->where(
                fn ($w) => $w->where('sku', 'like', "%{$request->string('q')}%")
                    ->orWhere('name', 'like', "%{$request->string('q')}%")
                    ->orWhere('barcode', 'like', "%{$request->string('q')}%"),
            ))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('frames.index', [
            'frames' => $frames,
            'brands' => Frame::query()->distinct()->orderBy('brand')->pluck('brand'),
            'filters' => $request->only(['brand', 'q']),
        ]);
    }

    public function create(): View
    {
        return view('frames.form', ['frame' => new Frame]);
    }

    public function store(Request $request): RedirectResponse
    {
        $frame = Frame::create($this->validated($request));

        return redirect()->route('frames.index')->with('success', "Frame {$frame->name} berhasil disimpan.");
    }

    public function edit(Frame $frame): View
    {
        return view('frames.form', ['frame' => $frame]);
    }

    public function update(Request $request, Frame $frame): RedirectResponse
    {
        $frame->update($this->validated($request, $frame));

        return redirect()->route('frames.index')->with('success', "Frame {$frame->name} berhasil diperbarui.");
    }

    public function destroy(Frame $frame): RedirectResponse
    {
        $name = $frame->name;
        $frame->delete();

        return redirect()->route('frames.index')->with('success', "Frame {$name} dihapus.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Frame $frame = null): array
    {
        return $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:frames,sku'.($frame ? ','.$frame->id : '')],
            'barcode' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'brand' => ['required', 'string', 'max:80'],
            'model' => ['nullable', 'string', 'max:80'],
            'color' => ['nullable', 'string', 'max:80'],
            'material' => ['nullable', 'string', 'max:80'],
            'size' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', 'string', 'max:20'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'photo' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
