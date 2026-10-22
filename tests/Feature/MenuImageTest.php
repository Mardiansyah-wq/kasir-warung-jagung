<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use App\Services\MenuImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(MenuImageService::DISK);
    }

    public function test_owner_can_upload_menu_image_and_it_is_compressed_to_webp(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->post(route('owner.menus.store'), [
                'name' => 'Jagung Bakar',
                'category' => 'makanan',
                'is_active' => '1',
                'variant_name' => 'Serut',
                'selling_price' => 10000,
                'image' => $this->imageFixture('menu.jpg'),
            ])
            ->assertRedirect();

        $menu = Menu::firstOrFail();

        $this->assertNotNull($menu->image_path);
        $this->assertNotNull($menu->thumb_path);
        $this->assertStringEndsWith('.webp', $menu->image_path);
        $this->assertStringEndsWith('-thumb.webp', $menu->thumb_path);
        $this->assertTrue(Storage::disk(MenuImageService::DISK)->exists($menu->image_path));
        $this->assertTrue(Storage::disk(MenuImageService::DISK)->exists($menu->thumb_path));

        $full = getimagesize(Storage::disk(MenuImageService::DISK)->path($menu->image_path));
        $thumb = getimagesize(Storage::disk(MenuImageService::DISK)->path($menu->thumb_path));

        $this->assertSame(1200, $full[0]);
        $this->assertSame(900, $full[1]);
        $this->assertSame('image/webp', $full['mime']);
        $this->assertSame(480, $thumb[0]);
        $this->assertSame(480, $thumb[1]);
        $this->assertSame('image/webp', $thumb['mime']);

        $this->assertLessThan(
            filesize(base_path('tests/Fixtures/images/menu.jpg')),
            Storage::disk(MenuImageService::DISK)->size($menu->image_path),
        );
    }

    public function test_portrait_image_is_resized_to_fit_within_1200_pixels(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->post(route('owner.menus.store'), [
                'name' => 'Menu Portrait',
                'category' => 'makanan',
                'is_active' => '1',
                'variant_name' => 'Reguler',
                'selling_price' => 10000,
                'image' => $this->imageFixture('portrait.jpg'),
            ])
            ->assertRedirect();

        $menu = Menu::firstOrFail();
        $full = getimagesize(Storage::disk(MenuImageService::DISK)->path($menu->image_path));

        $this->assertSame(675, $full[0]);
        $this->assertSame(1200, $full[1]);
        $this->assertSame('image/webp', $full['mime']);
    }

    public function test_exif_orientation_is_applied_exactly_once(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->post(route('owner.menus.store'), [
                'name' => 'Menu Miring',
                'category' => 'makanan',
                'is_active' => '1',
                'variant_name' => 'Reguler',
                'selling_price' => 10000,
                'image' => $this->imageFixture('oriented.jpg'),
            ])
            ->assertRedirect();

        $menu = Menu::firstOrFail();
        $full = getimagesize(Storage::disk(MenuImageService::DISK)->path($menu->image_path));
        $thumb = getimagesize(Storage::disk(MenuImageService::DISK)->path($menu->thumb_path));

        $this->assertSame(1200, $full[0]);
        $this->assertSame(675, $full[1]);
        $this->assertSame(480, $thumb[0]);
        $this->assertSame(480, $thumb[1]);
    }

    public function test_webp_image_is_accepted_and_normalized(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->post(route('owner.menus.store'), [
                'name' => 'Menu WebP',
                'category' => 'cemilan',
                'is_active' => '1',
                'variant_name' => 'Original',
                'selling_price' => 8000,
                'image' => $this->imageFixture('menu.webp'),
            ])
            ->assertRedirect();

        $menu = Menu::firstOrFail();
        $full = getimagesize(Storage::disk(MenuImageService::DISK)->path($menu->image_path));

        $this->assertSame('image/webp', $full['mime']);
        $this->assertSame(800, $full[0]);
        $this->assertSame(600, $full[1]);
    }

    public function test_guest_cannot_view_menu_image(): void
    {
        $menu = $this->menuWithImage();

        $this->get(route('media.menus.show', [$menu, 'image']))
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_private_menu_image(): void
    {
        $owner = User::factory()->owner()->create();
        $menu = $this->menuWithImage();

        $response = $this->actingAs($owner)
            ->get(route('media.menus.show', [$menu, 'thumb']))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/webp');

        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
    }

    public function test_replacing_image_deletes_the_old_files(): void
    {
        $owner = User::factory()->owner()->create();
        $menu = $this->menuWithImage();

        $oldImage = $menu->image_path;
        $oldThumb = $menu->thumb_path;

        $this->actingAs($owner)
            ->put(route('owner.menus.update', $menu), [
                'name' => $menu->name,
                'category' => $menu->category,
                'is_active' => '1',
                'image' => $this->imageFixture('menu.png'),
            ])
            ->assertRedirect();

        $menu->refresh();

        $this->assertNotSame($oldImage, $menu->image_path);
        $this->assertNotSame($oldThumb, $menu->thumb_path);
        $this->assertFalse(Storage::disk(MenuImageService::DISK)->exists($oldImage));
        $this->assertFalse(Storage::disk(MenuImageService::DISK)->exists($oldThumb));
        $this->assertTrue(Storage::disk(MenuImageService::DISK)->exists($menu->image_path));
    }

    public function test_invalid_image_is_rejected(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->post(route('owner.menus.store'), [
                'name' => 'Menu Salah',
                'category' => 'makanan',
                'is_active' => '1',
                'variant_name' => 'Reguler',
                'selling_price' => 10000,
                'image' => UploadedFile::fake()->createWithContent('bukan-gambar.txt', 'hello'),
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('menus', 0);
    }

    public function test_menu_without_image_has_no_image_path(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->post(route('owner.menus.store'), [
                'name' => 'Tanpa Foto',
                'category' => 'makanan',
                'is_active' => '1',
                'variant_name' => 'Reguler',
                'selling_price' => 10000,
            ])
            ->assertRedirect();

        $menu = Menu::firstOrFail();

        $this->assertNull($menu->image_path);
        $this->assertNull($menu->thumb_path);
    }

    public function test_missing_image_returns_not_found(): void
    {
        $owner = User::factory()->owner()->create();
        $menu = Menu::create([
            'name' => 'Tanpa Foto',
            'category' => 'makanan',
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->get(route('media.menus.show', [$menu, 'image']))
            ->assertNotFound();
    }

    private function menuWithImage(): Menu
    {
        $paths = app(MenuImageService::class)->store($this->imageFixture('menu.jpg'));

        return Menu::create([
            'name' => 'Jagung Bakar',
            'category' => 'makanan',
            'is_active' => true,
            'image_path' => $paths['image_path'],
            'thumb_path' => $paths['thumb_path'],
        ]);
    }

    private function imageFixture(string $name): UploadedFile
    {
        $path = base_path('tests/Fixtures/images/'.$name);

        return new UploadedFile(
            $path,
            $name,
            mime_content_type($path) ?: 'image/jpeg',
            null,
            true,
        );
    }
}
