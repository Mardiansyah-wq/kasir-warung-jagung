<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['menu_id', 'type', 'variant_name', 'sku', 'selling_price', 'is_active'])]
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

    public function displayName(): string
    {
        return $this->type
            ? $this->type.' · '.$this->variant_name
            : $this->variant_name;
    }

    /**
     * Label jenis penyajian (misal: Utuh, Serut, Pisang Raja, Es, Panas).
     */
    public function typeLabel(): ?string
    {
        if (strcasecmp((string) $this->variant_name, 'utuh') === 0 || strcasecmp((string) $this->type, 'utuh') === 0) {
            return 'Utuh';
        }

        if (strcasecmp((string) $this->variant_name, 'serut') === 0 || strcasecmp((string) $this->type, 'serut') === 0) {
            return 'Serut';
        }

        return $this->type;
    }

    /**
     * Label rasa / variasi spesifik (misal: Pedas Manis, Original, Keju Susu).
     */
    public function flavourLabel(): string
    {
        if (strcasecmp((string) $this->variant_name, 'utuh') === 0 || strcasecmp((string) $this->variant_name, 'serut') === 0) {
            return $this->type ?: $this->variant_name;
        }

        return $this->variant_name;
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
