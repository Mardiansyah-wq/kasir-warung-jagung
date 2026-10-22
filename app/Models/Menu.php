<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'category', 'is_active', 'image_path', 'thumb_path'])]
class Menu extends Model
{
    public const CATEGORY_MAKANAN = 'makanan';

    public const CATEGORY_MINUMAN = 'minuman';

    public const CATEGORY_CEMILAN = 'cemilan';

    public const CATEGORIES = [
        self::CATEGORY_MAKANAN,
        self::CATEGORY_MINUMAN,
        self::CATEGORY_CEMILAN,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'bool',
        ];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MenuVariant::class);
    }

    public function activeVariants(): HasMany
    {
        return $this->variants()->where('is_active', true);
    }

    public function hasToppingOption(): bool
    {
        return $this->category === self::CATEGORY_MAKANAN && $this->toppingPrice() > 0;
    }

    public function toppingPrice(): int
    {
        return (int) config('hotmad.topping_price', 2000);
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            self::CATEGORY_MINUMAN => 'Minuman',
            self::CATEGORY_CEMILAN => 'Cemilan',
            default => 'Makanan',
        };
    }

    public function categoryEmoji(): string
    {
        return match ($this->category) {
            self::CATEGORY_MINUMAN => '🥤',
            self::CATEGORY_CEMILAN => '🍿',
            default => '🍽️',
        };
    }

    public function priceRange(): string
    {
        $prices = $this->variants
            ->where('is_active', true)
            ->pluck('selling_price')
            ->map(fn ($price) => (float) $price);

        if ($prices->isEmpty()) {
            return 'Belum ada harga';
        }

        $min = $prices->min();
        $max = $prices->max();

        if ($min === $max) {
            return 'Rp '.number_format($min, 0, ',', '.');
        }

        return 'Rp '.number_format($min, 0, ',', '.').'–'.number_format($max, 0, ',', '.');
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return route('media.menus.show', [
            'menu' => $this,
            'size' => 'image',
            'v' => $this->imageVersion(),
        ]);
    }

    public function thumbUrl(): ?string
    {
        if (! $this->thumb_path && ! $this->image_path) {
            return null;
        }

        return route('media.menus.show', [
            'menu' => $this,
            'size' => $this->thumb_path ? 'thumb' : 'image',
            'v' => $this->imageVersion(),
        ]);
    }

    private function imageVersion(): ?string
    {
        return $this->image_path
            ? substr(md5($this->image_path), 0, 8)
            : null;
    }
}
