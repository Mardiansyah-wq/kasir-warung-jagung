<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\StoreMenuVariantRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Requests\UpdateMenuVariantRequest;
use App\Models\ActivityLog;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Services\ActivityLogger;
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

    public function store(StoreMenuRequest $request, MenuImageService $images, ActivityLogger $logger): RedirectResponse
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

        $changes = [
            'name' => ['new' => $menu->name],
            'category' => ['new' => $menu->category],
            'is_active' => ['new' => (bool) $menu->is_active],
        ];

        if ($menu->description) {
            $changes['description'] = ['new' => $menu->description];
        }

        if ($menu->image_path) {
            $changes['image'] = ['new' => true];
        }

        $logger->log(
            ActivityLog::ACTION_MENU_CREATED,
            $menu,
            $menu->name,
            'Menambah menu '.$menu->name.' dengan varian '.$data['variant_name'].'.',
            $changes,
        );

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

    public function update(
        UpdateMenuRequest $request,
        Menu $menu,
        MenuImageService $images,
        ActivityLogger $logger,
    ): RedirectResponse {
        $data = $request->validated();
        $before = $this->menuSnapshot($menu);
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

        $changes = $this->diff($before, $this->menuSnapshot($menu));

        if ((bool) $oldImage !== (bool) $menu->image_path) {
            $changes['image'] = ['old' => (bool) $oldImage, 'new' => (bool) $menu->image_path];
        }

        if ($changes !== []) {
            $logger->log(
                ActivityLog::ACTION_MENU_UPDATED,
                $menu,
                $menu->name,
                'Mengubah menu '.$menu->name.'.',
                $changes,
            );
        }

        return redirect()
            ->route('owner.menus.edit', $menu)
            ->with('status', 'Menu berhasil diperbarui.');
    }

    public function toggle(Menu $menu, ActivityLogger $logger): RedirectResponse
    {
        $wasActive = $menu->is_active;

        $menu->update(['is_active' => ! $wasActive]);

        $logger->log(
            $menu->is_active ? ActivityLog::ACTION_MENU_ACTIVATED : ActivityLog::ACTION_MENU_DEACTIVATED,
            $menu,
            $menu->name,
            ($menu->is_active ? 'Mengaktifkan' : 'Menonaktifkan').' menu '.$menu->name.'.',
            ['is_active' => ['old' => $wasActive, 'new' => $menu->is_active]],
        );

        return back()->with('status', $menu->is_active
            ? 'Menu diaktifkan kembali.'
            : 'Menu dinonaktifkan.');
    }

    public function storeVariant(
        StoreMenuVariantRequest $request,
        Menu $menu,
        ActivityLogger $logger,
    ): RedirectResponse {
        $variant = $menu->variants()->create([
            'variant_name' => $request->validated('variant_name'),
            'selling_price' => $request->validated('selling_price'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $changes = [
            'variant_name' => ['new' => $variant->variant_name],
            'selling_price' => ['new' => (float) $variant->selling_price],
            'is_active' => ['new' => (bool) $variant->is_active],
        ];

        if ($variant->type) {
            $changes['type'] = ['new' => $variant->type];
        }

        $logger->log(
            ActivityLog::ACTION_VARIANT_CREATED,
            $variant,
            $this->variantLabel($menu, $variant),
            'Menambah varian '.$variant->displayName().' pada menu '.$menu->name.'.',
            $changes,
        );

        return back()->with('status', 'Varian berhasil ditambahkan.');
    }

    public function updateVariant(
        UpdateMenuVariantRequest $request,
        Menu $menu,
        MenuVariant $variant,
        ActivityLogger $logger,
    ): RedirectResponse {
        abort_unless((int) $variant->menu_id === (int) $menu->id, 404);

        $before = $this->variantSnapshot($variant);

        $variant->update([
            'variant_name' => $request->validated('variant_name'),
            'selling_price' => $request->validated('selling_price'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $changes = $this->diff($before, $this->variantSnapshot($variant));

        if ($changes !== []) {
            $logger->log(
                ActivityLog::ACTION_VARIANT_UPDATED,
                $variant,
                $this->variantLabel($menu, $variant),
                'Mengubah varian '.$variant->displayName().' pada menu '.$menu->name.'.',
                $changes,
            );
        }

        return back()->with('status', 'Varian berhasil diperbarui.');
    }

    public function toggleVariant(
        Menu $menu,
        MenuVariant $variant,
        ActivityLogger $logger,
    ): RedirectResponse {
        abort_unless((int) $variant->menu_id === (int) $menu->id, 404);

        $wasActive = $variant->is_active;

        $variant->update(['is_active' => ! $wasActive]);

        $logger->log(
            $variant->is_active ? ActivityLog::ACTION_VARIANT_ACTIVATED : ActivityLog::ACTION_VARIANT_DEACTIVATED,
            $variant,
            $this->variantLabel($menu, $variant),
            ($variant->is_active ? 'Mengaktifkan' : 'Menonaktifkan').' varian '.$variant->displayName()
                .' pada menu '.$menu->name.'.',
            ['is_active' => ['old' => $wasActive, 'new' => $variant->is_active]],
        );

        return back()->with('status', $variant->is_active
            ? 'Varian diaktifkan kembali.'
            : 'Varian dinonaktifkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function menuSnapshot(Menu $menu): array
    {
        return [
            'name' => $menu->name,
            'description' => $menu->description,
            'category' => $menu->category,
            'is_active' => (bool) $menu->is_active,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function variantSnapshot(MenuVariant $variant): array
    {
        return [
            'variant_name' => $variant->variant_name,
            'type' => $variant->type,
            'selling_price' => (float) $variant->selling_price,
            'is_active' => (bool) $variant->is_active,
        ];
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array<string, array{old: mixed, new: mixed}>
     */
    private function diff(array $before, array $after): array
    {
        $changes = [];

        foreach ($before as $field => $old) {
            $new = $after[$field] ?? null;

            if ($old !== $new) {
                $changes[$field] = ['old' => $old, 'new' => $new];
            }
        }

        return $changes;
    }

    private function variantLabel(Menu $menu, MenuVariant $variant): string
    {
        return $menu->name.' · '.$variant->displayName();
    }
}
