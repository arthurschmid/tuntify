<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Fillable(['shortname', 'salutation', 'alias_given_name', 'alias_family_name', 'preferred_locale', 'postal_locale', 'privacy_status'])]
#[Hidden(['password_hash', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'auth_version'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public static function normalizeLoginEmail(string $email): string
    {
        return Str::lower(trim($email));
    }

    public function canAccessAdmin(): bool
    {
        return $this->disabled_at === null
            && filled($this->login_email)
            && filled($this->password_hash)
            && DB::table('user_roles')
                ->join('roles', 'roles.id', '=', 'user_roles.role_id')
                ->where('user_roles.user_id', $this->getKey())
                ->where('roles.code', 'admin')
                ->exists();
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_hash' => 'hashed',
            'disabled_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }
}
