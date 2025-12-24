<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

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

    /**
     * Get all access groups this user belongs to.
     */
    public function accessGroups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            AccessGroup::class,
            'access_group_user',
            'user_id',
            'access_group_id'
        )
        ->withPivot('custom_access_level', 'assigned_by', 'assigned_at')
        ->withTimestamps();
    }

    /**
     * Get user's effective access level for a specific menu.
     *
     * @param Menu|string $menu Menu model or slug
     * @return int
     */
    public function getMenuAccessLevel($menu): int
    {
        if (is_string($menu)) {
            $menu = Menu::where('slug', $menu)->first();
        }

        if (!$menu) {
            return 0;
        }

        return $menu->getUserAccessLevel($this);
    }

    /**
     * Check if user can access a menu with required level.
     *
     * @param Menu|string $menu Menu model or slug
     * @param int $requiredLevel
     * @return bool
     */
    public function canAccessMenu($menu, int $requiredLevel = 1): bool
    {
        return $this->getMenuAccessLevel($menu) >= $requiredLevel;
    }

    /**
     * Get all menus accessible by this user.
     *
     * @param int $minLevel Minimum access level required
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAccessibleMenus(int $minLevel = 1)
    {
        return Menu::getAccessibleMenus($this, $minLevel);
    }

    /**
     * Check if user is in a specific access group.
     *
     * @param AccessGroup|int $group
     * @return bool
     */
    public function inAccessGroup($group): bool
    {
        $groupId = $group instanceof AccessGroup ? $group->id : $group;
        return $this->accessGroups()->where('access_groups.id', $groupId)->exists();
    }
}
