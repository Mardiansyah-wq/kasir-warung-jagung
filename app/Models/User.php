<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['branch_id', 'name', 'email', 'phone', 'address', 'photo_path', 'username', 'password_hash', 'role', 'is_active'])]
#[Hidden(['password_hash'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_OWNER = 'owner';

    public const ROLE_STAFF = 'staff';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'is_active' => 'bool',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function avatarUrl(): ?string
    {
        return $this->photo_path
            ? route('media.users.avatar', $this)
            : null;
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name)) ?: [];

        return collect($words)->filter()->take(2)->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))->implode('');
    }

    /**
     * The password column used by Laravel's authentication system.
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function homeRouteName(): string
    {
        return $this->isOwner() ? 'owner.dashboard' : 'staff.dashboard';
    }

    public function canAccessBranch(int $branchId): bool
    {
        return $this->isOwner() || (int) $this->branch_id === $branchId;
    }
}
