<?php

namespace App\Models;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// class User extends Authenticatable implements MustVerifyEmail
class User extends Authenticatable implements JWTSubject
{
    use  HasFactory, Notifiable;


    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'phone_verified'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified' => 'boolean',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    // public function hasRole($role): bool
    // {
    //     return $this->roles()->where('slug', $role)->exists();
    // }




    public function assignRole($role): void
    {
        $this->roles()->syncWithoutDetaching(
            Role::where('slug', $role)->firstOrFail()
        );
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('super-admin');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

        public function hasRole(string $role): bool
        {
            return $this->roles()->where('slug', $role)->exists();
        }

        /**
         * Check if the user has any of the given roles.
         *
         * @param array<string> $roles
         * @return bool
         */
        public function hasAnyRole(array $roles): bool
        {
            return $this->roles()->whereIn('slug', $roles)->exists();
        }
}
