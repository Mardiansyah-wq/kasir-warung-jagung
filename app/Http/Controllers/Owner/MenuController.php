<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\StoreMenuVariantRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Requests\UpdateMenuVariantRequest;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Services\MenuImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');

        if (! in_array($category, Menu::CATEGORIES, true)) {
            $category = null;
        }

        $menus = Menu::query()
            ->with('variants')
            ->when($category, fn ($query) => $query->where('category', $category))
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('owner.menus.index', [
            'menus' => $menus,
            'category' => $category,
        ]);
    }

    public function create(): View
    {
        return view('owner.menus.create');
    }

    public function store(StoreMenuRequest $request, MenuImageService $images): RedirectResponse
    {
        $data = $request->validated();
        $paths = null;

        if ($request->hasFile('image')) {
            try {
                $paths = $images->store($request->file('image'));
            } catch (\Throwable $exception) {
                report($exception);

                return back()
                    ->withInput()
                    ->withErrors(['image' => 'Gambar gagal diproses. Coba file lain.']);
            }
        }

        try {
            $menu = DB::transaction(function () use ($data, $paths) {
                $menu = Menu::create([
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'category' => $data['category'],
                    'is_active' => (bool) ($data['is_active'] ?? false),
                    'image_path' => $paths['image_path'] ?? null,
                    'thumb_path' => $paths['thumb_path'] ?? null,
                ]);

                $menu->variants()->create([
                    'variant_name' => $data['variant_name'],
                    'selling_price' => $data['selling_price'],
                    'is_active' => true,
                ]);

                return $menu;
            });
        } catch (\Throwable $exception) {
            if ($paths) {
                $images->delete($paths['image_path'], $paths['thumb_path']);
            }

            throw $exception;
        }

        return redirect()
            ->route('owner.menus.edit', $menu)
            ->with('status', 'Menu berhasil ditambahkan. Silakan tambah varian lain jika perlu.');
    }

    public function edit(Menu $menu): View
    {
        $menu->load(['variants' => fn ($query) => $query->orderBy('selling_price')]);

        return view('owner.menus.edit', [
            'menu' => $menu,
        ]);
    }

    public function update(UpdateMenuRequest $request, Menu $menu, MenuImageService $images): RedirectResponse
    {
        $data = $request->validated();
        $oldImage = $menu->image_path;
        $oldThumb = $menu->thumb_path;
        $paths = null;

        if ($request->hasFile('image')) {
            try {
                $paths = $images->store($request->file('image'));
            } catch (\Throwable $exception) {
                report($exception);

                return back()
                    ->withInput()
                    ->withErrors(['image' => 'Gambar gagal diproses. Coba file lain.']);
            }
        }

        try {
            $menu->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'],
                'is_active' => (bool) ($data['is_active'] ?? false),
                'image_path' => $paths['image_path'] ?? $oldImage,
                'thumb_path' => $paths['thumb_path'] ?? $oldThumb,
            ]);
        } catch (\Throwable $exception) {
            if ($paths) {
                $images->delete($paths['image_path'], $paths['thumb_path']);
            }

            throw $exception;
        }

        if ($paths) {
            $images->delete($oldImage, $oldThumb);
        }

        return redirect()
            ->route('owner.menus.edit', $menu)
            ->with('status', 'Menu berhasil diperbarui.');
    }

    public function toggle(Menu $menu): RedirectResponse
    {
        $menu->update(['is_active' => ! $menu->is_active]);

        return back()->with('status', $menu->is_active
            ? 'Menu diaktifkan kembali.'
            : 'Menu dinonaktifkan.');
    }

    public function storeVariant(StoreMenuVariantRequest $request, Menu $menu): RedirectResponse
    {
        $menu->variants()->create([
            'variant_name' => $request->validated('variant_name'),
            'selling_price' => $request->validated('selling_price'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Varian berhasil ditambahkan.');
    }

    public function updateVariant(UpdateMenuVariantRequest $request, Menu $menu, MenuVariant $variant): RedirectResponse
    {
        abort_unless((int) $variant->menu_id === (int) $menu->id, 404);

        $variant->update([
            'variant_name' => $request->validated('variant_name'),
            'selling_price' => $request->validated('selling_price'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Varian berhasil diperbarui.');
    }

    public function toggleVariant(Menu $menu, MenuVariant $variant): RedirectResponse
    {
        abort_unless((int) $variant->menu_id === (int) $menu->id, 404);

        $variant->update(['is_active' => ! $variant->is_active]);

        return back()->with('status', $variant->is_active
            ? 'Varian diaktifkan kembali.'
            : 'Varian dinonaktifkan.');
    }
}
