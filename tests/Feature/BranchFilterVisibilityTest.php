<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchFilterVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_filters_are_hidden_when_there_is_only_one_branch(): void
    {
        Branch::create(['name' => 'Cabang Pusat', 'is_active' => true]);
        $owner = User::factory()->owner()->create();

        foreach ([
            route('owner.stock.index'),
            route('owner.reports.index'),
            route('owner.reports.weekly'),
            route('owner.activity.index'),
            route('owner.orders.index'),
        ] as $url) {
            $this->actingAs($owner)
                ->get($url)
                ->assertOk()
                ->assertDontSee('id="branch_id"', false);
        }
    }

    public function test_branch_filters_are_visible_when_there_are_multiple_branches(): void
    {
        Branch::create(['name' => 'Cabang Pusat', 'is_active' => true]);
        Branch::create(['name' => 'Cabang Timur', 'is_active' => true]);
        $owner = User::factory()->owner()->create();

        foreach ([
            route('owner.stock.index'),
            route('owner.reports.index'),
            route('owner.reports.weekly'),
            route('owner.activity.index'),
            route('owner.orders.index'),
        ] as $url) {
            $this->actingAs($owner)
                ->get($url)
                ->assertOk()
                ->assertSee('id="branch_id"', false);
        }
    }

    public function test_reports_with_one_branch_default_to_that_branch(): void
    {
        $branch = Branch::create(['name' => 'Cabang Pusat', 'is_active' => true]);
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('owner.reports.index'))
            ->assertOk()
            ->assertSee($branch->name)
            ->assertDontSee('Semua Cabang');

        $this->actingAs($owner)
            ->get(route('owner.reports.weekly'))
            ->assertOk()
            ->assertSee($branch->name)
            ->assertDontSee('Semua Cabang');
    }

    public function test_reports_with_multiple_branches_keep_the_all_branches_default(): void
    {
        Branch::create(['name' => 'Cabang Pusat', 'is_active' => true]);
        Branch::create(['name' => 'Cabang Timur', 'is_active' => true]);
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('owner.reports.index'))
            ->assertOk()
            ->assertSee('Semua Cabang');

        $this->actingAs($owner)
            ->get(route('owner.reports.weekly'))
            ->assertOk()
            ->assertSee('Semua Cabang');
    }
}
