<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'sqlsrv';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menus';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'route_name',
        'icon',
        'parent_id',
        'order',
        'is_active',
        'type', // 'item', 'section', 'separator'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'parent_id' => 'integer',
    ];

    /**
     * Get the parent menu.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Get the child menus.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get all access groups that have access to this menu.
     */
    public function accessGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            AccessGroup::class,
            'access_group_menu',
            'menu_id',
            'access_group_id'
        )->withTimestamps();
    }

    /**
     * Scope a query to only include active menus.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include root menus (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to order menus by their order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Check if user has access to this menu with required level.
     *
     * @param User $user
     * @param int $requiredLevel
     * @return bool
     */
    public function userHasAccess(User $user, int $requiredLevel = 1): bool
    {
        $accessLevel = $this->getUserAccessLevel($user);
        return $accessLevel >= $requiredLevel;
    }

    /**
     * Get user's access level for this menu.
     *
     * @param User $user
     * @return int
     */
    public function getUserAccessLevel(User $user): int
    {
        // Superuser always has full access (Level 4 - Owner)
        if ($user->role === 'superuser') {
            return 4; // AccessGroup::LEVEL_OWNER
        }

        // Section headers are always visible to authenticated users
        if ($this->type === 'section') {
            return 1; // Read access
        }

        $maxLevel = 0;

        // Get all access groups for this menu
        $accessGroups = $this->accessGroups()->where('is_active', true)->get();

        foreach ($accessGroups as $group) {
            // Check if user is in this group
            $pivot = $user->accessGroups()
                ->where('access_groups.id', $group->id)
                ->first();

            if ($pivot) {
                // Use custom level if set, otherwise use group default
                $level = $pivot->pivot->custom_access_level ?? $group->default_access_level;
                $maxLevel = max($maxLevel, $level);
            }
        }

        return $maxLevel;
    }

    /**
     * Get all menus accessible by a user.
     *
     * @param User $user
     * @param int $minLevel Minimum access level required
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAccessibleMenus(User $user, int $minLevel = 1)
    {
        return static::active()
            ->ordered()
            ->get()
            ->filter(function ($menu) use ($user, $minLevel) {
                return $menu->getUserAccessLevel($user) >= $minLevel;
            });
    }
}
