<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_stock_and_it_is_logged_for_both_pages(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create();
        $variant = $this->createVariant();

        $this->actingAs($owner)->post(route('owner.stock.store'), [
            'branch_id' => $branch->id,
            'variant_id' => $variant->id,
            'mode' => 'in',
            'quantity' => 5,
            'note' => 'Belanja pagi',
        ])->assertRedirect(route('owner.stock.index', ['branch_id' => $branch->id]));

        $this->assertSame(5, (int) BranchStock::where('branch_id', $branch->id)
            ->where('menu_variant_id', $variant->id)
            ->value('quantity'));

        $this->assertDatabaseHas('stock_movements', [
            'branch_id' => $branch->id,
            'menu_variant_id' => $variant->id,
            'user_id' => $owner->id,
            'movement_type' => 'in',
            'quantity' => 5,
            'note' => 'Belanja pagi',
        ]);

        $log = ActivityLog::where('action', ActivityLog::ACTION_STOCK_ADDED)->firstOrFail();

        $this->assertSame($branch->id, $log->branch_id);
        $this->assertSame(MenuVariant::class, $log->subject_type);
        $this->assertSame($variant->id, $log->subject_id);
        $this->assertSame(['old' => 0, 'new' => 5], $log->changes['stock']);
        $this->assertSame(['new' => 5], $log->changes['quantity']);
        $this->assertStringContainsString('Pusat', $log->description);

        $this->actingAs($owner)
            ->get(route('owner.activity.index', ['action' => 'stock']))
            ->assertOk()
            ->assertSee('Tambah stok')
            ->assertSee('Pusat')
            ->assertSee('Belanja pagi');

        $this->actingAs($staff)
            ->get(route('staff.activity.index'))
            ->assertOk()
            ->assertSee('Tambah stok')
            ->assertSee('Pusat');
    }

    public function test_owner_can_reduce_and_correct_stock(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $variant = $this->createVariant();

        BranchStock::create([
            'branch_id' => $branch->id,
            'menu_variant_id' => $variant->id,
            'quantity' => 10,
        ]);

        $this->actingAs($owner)->post(route('owner.stock.store'), [
            'branch_id' => $branch->id,
            'variant_id' => $variant->id,
            'mode' => 'out',
            'quantity' => 3,
        ])->assertRedirect();

        $this->assertSame(7, (int) BranchStock::where('branch_id', $branch->id)
            ->where('menu_variant_id', $variant->id)
            ->value('quantity'));
        $this->assertDatabaseHas('stock_movements', [
            'movement_type' => 'out',
            'quantity' => 3,
        ]);

        $this->actingAs($owner)->post(route('owner.stock.store'), [
            'branch_id' => $branch->id,
            'variant_id' => $variant->id,
            'mode' => 'set',
            'quantity' => 4,
        ])->assertRedirect();

        $this->assertSame(4, (int) BranchStock::where('branch_id', $branch->id)
            ->where('menu_variant_id', $variant->id)
            ->value('quantity'));

        $this->assertDatabaseHas('stock_movements', [
            'movement_type' => 'adjustment',
            'quantity' => 3,
        ]);

        $adjusted = ActivityLog::where('action', ActivityLog::ACTION_STOCK_ADJUSTED)->firstOrFail();
        $this->assertSame(['old' => 7, 'new' => 4], $adjusted->changes['stock']);
        $this->assertStringContainsString('7 menjadi 4', $adjusted->description);
    }

    public function test_reducing_more_than_available_stock_is_rejected_without_a_log(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $variant = $this->createVariant();

        BranchStock::create([
            'branch_id' => $branch->id,
            'menu_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($owner)->post(route('owner.stock.store'), [
            'branch_id' => $branch->id,
            'variant_id' => $variant->id,
            'mode' => 'out',
            'quantity' => 5,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(2, (int) BranchStock::where('branch_id', $branch->id)
            ->where('menu_variant_id', $variant->id)
            ->value('quantity'));
        $this->assertSame(0, StockMovement::count());
        $this->assertSame(0, ActivityLog::count());
    }

    public function test_correcting_stock_to_the_same_value_does_not_write_a_log(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $variant = $this->createVariant();

        BranchStock::create([
            'branch_id' => $branch->id,
            'menu_variant_id' => $variant->id,
            'quantity' => 4,
        ]);

        $this->actingAs($owner)->post(route('owner.stock.store'), [
            'branch_id' => $branch->id,
            'variant_id' => $variant->id,
            'mode' => 'set',
            'quantity' => 4,
        ])->assertRedirect();

        $this->assertSame(0, ActivityLog::count());
        $this->assertSame(0, StockMovement::count());
    }

    public function test_staff_stock_addition_is_logged_and_visible_to_the_owner(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Cabang Dua', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create();
        $variant = $this->createVariant();

        $this->actingAs($staff)->post(route('staff.stock.store'), [
            'variant_id' => $variant->id,
            'quantity' => 6,
        ])->assertRedirect();

        $log = ActivityLog::where('action', ActivityLog::ACTION_STOCK_ADDED)->firstOrFail();
        $this->assertSame($staff->id, $log->user_id);
        $this->assertSame($branch->id, $log->branch_id);

        $this->actingAs($owner)
            ->get(route('owner.activity.index', ['action' => 'stock']))
            ->assertOk()
            ->assertSee('Cabang Dua')
            ->assertSee($staff->name);

        $this->actingAs($staff)
            ->get(route('staff.activity.index'))
            ->assertOk()
            ->assertSee('Tambah stok')
            ->assertSee('Stok: 0 → 6');
    }

    public function test_staff_can_reduce_stock_when_a_note_is_provided(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create();
        $variant = $this->createVariant();

        BranchStock::create([
            'branch_id' => $branch->id,
            'menu_variant_id' => $variant->id,
            'quantity' => 10,
        ]);

        $this->actingAs($staff)->post(route('staff.stock.store'), [
            'variant_id' => $variant->id,
            'mode' => 'out',
            'quantity' => 3,
            'note' => 'Rusak saat panggang',
        ])->assertRedirect();

        $this->assertSame(7, (int) BranchStock::where('branch_id', $branch->id)
            ->where('menu_variant_id', $variant->id)
            ->value('quantity'));

        $this->assertDatabaseHas('stock_movements', [
            'branch_id' => $branch->id,
            'user_id' => $staff->id,
            'movement_type' => 'out',
            'quantity' => 3,
            'note' => 'Rusak saat panggang',
        ]);

        $log = ActivityLog::where('action', ActivityLog::ACTION_STOCK_REDUCED)->firstOrFail();

        $this->assertSame(['new' => -3], $log->changes['quantity']);
        $this->assertSame(['new' => 'Rusak saat panggang'], $log->changes['note']);
        $this->assertStringContainsString('Catatan: Rusak saat panggang', $log->description);

        $this->actingAs($staff)
            ->get(route('staff.activity.index'))
            ->assertOk()
            ->assertSee('Kurangi stok')
            ->assertSee('Catatan: Rusak saat panggang');

        $this->actingAs($owner)
            ->get(route('owner.activity.index', ['action' => 'stock']))
            ->assertOk()
            ->assertSee('Kurangi stok')
            ->assertSee('Catatan: Rusak saat panggang')
            ->assertSee($staff->name);
    }

    public function test_staff_must_fill_a_note_when_reducing_stock(): void
    {
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create();
        $variant = $this->createVariant();

        BranchStock::create([
            'branch_id' => $branch->id,
            'menu_variant_id' => $variant->id,
            'quantity' => 10,
        ]);

        $this->actingAs($staff)->post(route('staff.stock.store'), [
            'variant_id' => $variant->id,
            'mode' => 'out',
            'quantity' => 3,
        ])->assertSessionHasErrors('note');

        $this->assertSame(10, (int) BranchStock::where('branch_id', $branch->id)
            ->where('menu_variant_id', $variant->id)
            ->value('quantity'));
        $this->assertSame(0, StockMovement::count());
        $this->assertSame(0, ActivityLog::count());
    }

    public function test_staff_can_add_stock_with_an_optional_note(): void
    {
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create();
        $variant = $this->createVariant();

        $this->actingAs($staff)->post(route('staff.stock.store'), [
            'variant_id' => $variant->id,
            'mode' => 'in',
            'quantity' => 4,
            'note' => 'Belanja pagi',
        ])->assertRedirect();

        $this->assertSame(4, (int) BranchStock::where('branch_id', $branch->id)
            ->where('menu_variant_id', $variant->id)
            ->value('quantity'));

        $log = ActivityLog::where('action', ActivityLog::ACTION_STOCK_ADDED)->firstOrFail();
        $this->assertSame(['new' => 'Belanja pagi'], $log->changes['note']);
        $this->assertStringContainsString('Catatan: Belanja pagi', $log->description);
    }

    public function test_staff_cannot_reduce_more_than_the_available_stock(): void
    {
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $staff = User::factory()->forBranch($branch)->create();
        $variant = $this->createVariant();

        BranchStock::create([
            'branch_id' => $branch->id,
            'menu_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($staff)->post(route('staff.stock.store'), [
            'variant_id' => $variant->id,
            'mode' => 'out',
            'quantity' => 5,
            'note' => 'Rusak',
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(2, (int) BranchStock::where('branch_id', $branch->id)
            ->where('menu_variant_id', $variant->id)
            ->value('quantity'));
        $this->assertSame(0, ActivityLog::count());
    }

    public function test_staff_activity_page_is_scoped_to_their_own_branch(): void
    {
        $owner = User::factory()->owner()->create();
        $first = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $second = Branch::create(['name' => 'Cabang Timur', 'is_active' => true]);
        $staff = User::factory()->forBranch($first)->create();
        $firstVariant = $this->createVariant('Jagung Manis');
        $secondVariant = $this->createVariant('Jagung Pedas');

        $this->actingAs($owner)->post(route('owner.stock.store'), [
            'branch_id' => $first->id,
            'variant_id' => $firstVariant->id,
            'mode' => 'in',
            'quantity' => 3,
        ]);

        $this->actingAs($owner)->post(route('owner.stock.store'), [
            'branch_id' => $second->id,
            'variant_id' => $secondVariant->id,
            'mode' => 'in',
            'quantity' => 9,
        ]);

        session()->forget('status');

        $this->actingAs($staff)
            ->get(route('staff.activity.index'))
            ->assertOk()
            ->assertSee('Jagung Manis')
            ->assertDontSee('Jagung Pedas');

        $this->actingAs($staff)
            ->get(route('staff.activity.index', ['branch_id' => $second->id]))
            ->assertOk()
            ->assertDontSee('Jagung Pedas');
    }

    public function test_staff_without_a_branch_sees_an_empty_activity_page(): void
    {
        $staff = User::factory()->create();

        $this->actingAs($staff)
            ->get(route('staff.activity.index'))
            ->assertOk()
            ->assertSee('Akun belum terhubung ke cabang');
    }

    public function test_role_guards_for_stock_and_activity_pages(): void
    {
        $owner = User::factory()->owner()->create();
        $staff = User::factory()->create();

        $this->actingAs($staff)->get(route('owner.stock.index'))->assertForbidden();
        $this->actingAs($staff)->post(route('owner.stock.store'))->assertForbidden();
        $this->actingAs($staff)->get(route('owner.activity.index'))->assertForbidden();

        $this->actingAs($owner)->get(route('staff.activity.index'))->assertForbidden();
        $this->actingAs($owner)->get(route('staff.stock.index'))->assertForbidden();
    }

    public function test_owner_stock_page_lists_branch_stock_and_modes(): void
    {
        $owner = User::factory()->owner()->create();
        $branch = Branch::create(['name' => 'Pusat', 'is_active' => true]);
        $variant = $this->createVariant();

        BranchStock::create([
            'branch_id' => $branch->id,
            'menu_variant_id' => $variant->id,
            'quantity' => 8,
        ]);

        $this->actingAs($owner)
            ->get(route('owner.stock.index', ['branch_id' => $branch->id]))
            ->assertOk()
            ->assertSee('Kelola Stok')
            ->assertSee('Stok 8')
            ->assertSee('Koreksi (=)')
            ->assertSee($variant->variant_name);
    }

    private function createVariant(string $menuName = 'Jagung Bakar'): MenuVariant
    {
        $menu = Menu::create([
            'name' => $menuName,
            'description' => 'Jagung bakar manis.',
            'category' => 'makanan',
            'is_active' => true,
        ]);

        return $menu->variants()->create([
            'variant_name' => 'Serut',
            'selling_price' => 10000,
            'is_active' => true,
        ]);
    }
}
