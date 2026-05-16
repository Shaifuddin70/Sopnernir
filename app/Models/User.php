<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\PublicMediaUrl;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'is_admin',
    'phone',
    'nid_number',
    'address',
    'image',
    'email_verified_at',
    'is_active',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    public const ROLE_ADMIN = 'admin';

    public const ROLE_INVESTOR = 'investor';

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            if ($user->image) {
                Storage::disk('web_public')->delete($user->image);
                Storage::disk('public')->delete($user->image);
            }
            if ($user->nominee?->image) {
                Storage::disk('web_public')->delete($user->nominee->image);
                Storage::disk('public')->delete($user->nominee->image);
            }
        });
    }

    public function profileImageUrl(): ?string
    {
        return PublicMediaUrl::forPath($this->image);
    }

    public function isAdmin(): bool
    {
        if ($this->is_admin) {
            return true;
        }

        return $this->role === self::ROLE_ADMIN;
    }

    public function nominee()
    {
        return $this->hasOne(Nominee::class);
    }

    public function investmentParticipants()
    {
        return $this->hasMany(InvestmentParticipant::class);
    }

    public function investmentPeriodUsers()
    {
        return $this->hasMany(InvestmentPeriodUser::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
