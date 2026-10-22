<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Services\MenuImageService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MenuImageController extends Controller
{
    public function show(Menu $menu, string $size): BinaryFileResponse
    {
        abort_unless(in_array($size, ['image', 'thumb'], true), 404);

        $path = $size === 'thumb'
            ? ($menu->thumb_path ?: $menu->image_path)
            : $menu->image_path;

        abort_if(! $path, 404);

        $disk = Storage::disk(MenuImageService::DISK);

        abort_unless($disk->exists($path), 404);

        $response = response()->file($disk->path($path), [
            'Content-Type' => 'image/webp',
        ]);

        $response->setPrivate();
        $response->setMaxAge(86400);

        return $response;
    }
}
