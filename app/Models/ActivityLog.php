<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'action',
    'subject_type',
    'subject_id',
    'subject_label',
    'description',
    'changes',
    'ip_address',
])]
class ActivityLog extends Model
{
    public const ACTION_MENU_CREATED = 'menu.created';

    public const ACTION_MENU_UPDATED = 'menu.updated';

    public const ACTION_MENU_ACTIVATED = 'menu.activated';

    public const ACTION_MENU_DEACTIVATED = 'menu.deactivated';

    public const ACTION_VARIANT_CREATED = 'menu_variant.created';

    public const ACTION_VARIANT_UPDATED = 'menu_variant.updated';

    public const ACTION_VARIANT_ACTIVATED = 'menu_variant.activated';

    public const ACTION_VARIANT_DEACTIVATED = 'menu_variant.deactivated';

    /**
     * @var array<string, string>
     */
    public const ACTION_LABELS = [
        self::ACTION_MENU_CREATED => 'Tambah menu',
        self::ACTION_MENU_UPDATED => 'Ubah menu',
        self::ACTION_MENU_ACTIVATED => 'Aktifkan menu',
        self::ACTION_MENU_DEACTIVATED => 'Nonaktifkan menu',
        self::ACTION_VARIANT_CREATED => 'Tambah varian',
        self::ACTION_VARIANT_UPDATED => 'Ubah varian',
        self::ACTION_VARIANT_ACTIVATED => 'Aktifkan varian',
        self::ACTION_VARIANT_DEACTIVATED => 'Nonaktifkan varian',
    ];

    /**
     * @var array<string, string>
     */
    public const ACTION_TONES = [
        self::ACTION_MENU_CREATED => 'status-ready',
        self::ACTION_MENU_UPDATED => 'status-processing',
        self::ACTION_MENU_ACTIVATED => 'status-ready',
        self::ACTION_MENU_DEACTIVATED => 'status-cancelled',
        self::ACTION_VARIANT_CREATED => 'status-ready',
        self::ACTION_VARIANT_UPDATED => 'status-processing',
        self::ACTION_VARIANT_ACTIVATED => 'status-ready',
        self::ACTION_VARIANT_DEACTIVATED => 'status-cancelled',
    ];

    /**
     * @var array<string, string>
     */
    private const FIELD_LABELS = [
        'name' => 'Nama',
        'description' => 'Deskripsi',
        'category' => 'Kategori',
        'is_active' => 'Status',
        'image' => 'Foto',
        'variant_name' => 'Nama varian',
        'type' => 'Tipe',
        'selling_price' => 'Harga',
    ];

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function actionLabel(): string
    {
        return self::ACTION_LABELS[$this->action] ?? Str::headline($this->action);
    }

    public function actionTone(): string
    {
        return self::ACTION_TONES[$this->action] ?? 'badge-muted';
    }

    /**
     * Human readable lines describing the changed fields.
     *
     * @return list<string>
     */
    public function changeLines(): array
    {
        $changes = $this->getAttribute('changes');

        if (! is_array($changes) || $changes === []) {
            return [];
        }

        $lines = [];

        foreach ($changes as $field => $change) {
            if (! is_array($change)) {
                continue;
            }

            $label = self::FIELD_LABELS[$field] ?? Str::headline($field);
            $old = $this->formatValue($field, $change['old'] ?? null);
            $new = $this->formatValue($field, $change['new'] ?? null);
            $hasOld = array_key_exists('old', $change) && $old !== null;

            $lines[] = $hasOld
                ? $label.': '.$old.' → '.$new
                : $label.': '.$new;
        }

        return $lines;
    }

    private function formatValue(string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($field) {
            'is_active' => $value ? 'Aktif' : 'Nonaktif',
            'image' => $value ? 'Ada' : 'Tidak ada',
            'category' => Menu::categoryLabelFor((string) $value),
            'selling_price' => 'Rp '.number_format((float) $value, 0, ',', '.'),
            'description' => Str::limit((string) $value, 60),
            default => Str::limit((string) $value, 80),
        };
    }
}
