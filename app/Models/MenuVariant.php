<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['menu_id', 'variant_name', 'sku', 'selling_price', 'is_active'])]
class MenuVariant extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'is_active' => 'bool',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(BranchStock::class);
    }

    public function stockFor(?int $branchId): int
    {
        if (! $branchId) {
            return 0;
        }

        return (int) ($this->stocks->firstWhere('branch_id', $branchId)?->quantity ?? 0);
    }
}
