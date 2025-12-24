<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AccessGroup extends Model
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
    protected $table = 'access_groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'default_access_level',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'default_access_level' => 'integer',
    ];

    /**
     * Access level constants.
     */
    const LEVEL_NO_ACCESS = 0;
    const LEVEL_READ = 1;
    const LEVEL_READ_WRITE = 2;
    const LEVEL_FULL = 3;
    const LEVEL_ADMIN = 4;

    /**
     * Get the user who created this access group.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'email');
    }

    /**
     * Get all users in this access group.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'access_group_user',
            'access_group_id',
            'user_id'
        )
        ->withPivot('custom_access_level', 'assigned_by', 'assigned_at')
        ->withTimestamps();
    }

    /**
     * Get all menus accessible by this access group.
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(
            Menu::class,
            'access_group_menu',
            'access_group_id',
            'menu_id'
        )->withTimestamps();
    }

    /**
     * Scope a query to only include active access groups.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the access level name.
     *
     * @param int $level
     * @return string
     */
    public static function getAccessLevelName(int $level): string
    {
        return match($level) {
            self::LEVEL_NO_ACCESS => 'No Access',
            self::LEVEL_READ => 'Read Only',
            self::LEVEL_READ_WRITE => 'Read & Write',
            self::LEVEL_FULL => 'Full Access',
            self::LEVEL_ADMIN => 'Admin',
            default => 'Unknown',
        };
    }

    /**
     * Get all available access levels.
     *
     * @return array
     */
    public static function getAccessLevels(): array
    {
        return [
            self::LEVEL_NO_ACCESS => 'No Access',
            self::LEVEL_READ => 'Read Only',
            self::LEVEL_READ_WRITE => 'Read & Write',
            self::LEVEL_FULL => 'Full Access',
            self::LEVEL_ADMIN => 'Admin',
        ];
    }

    /**
     * Get user's effective access level in this group.
     *
     * @param User $user
     * @return int|null
     */
    public function getUserAccessLevel(User $user): ?int
    {
        $pivot = $this->users()->where('users.id', $user->id)->first();
        
        if (!$pivot) {
            return null;
        }

        // Return custom level if set, otherwise return group default
        return $pivot->pivot->custom_access_level ?? $this->default_access_level;
    }

    /**
     * Check if user is in this group.
     *
     * @param User $user
     * @return bool
     */
    public function hasUser(User $user): bool
    {
        return $this->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Assign a user to this group.
     *
     * @param User $user
     * @param int|null $customLevel
     * @param string|null $assignedBy Email of user who assigned
     * @return void
     */
    public function assignUser(User $user, ?int $customLevel = null, ?string $assignedBy = null): void
    {
        $this->users()->attach($user->id, [
            'custom_access_level' => $customLevel,
            'assigned_by' => $assignedBy,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Remove a user from this group.
     *
     * @param User $user
     * @return void
     */
    public function removeUser(User $user): void
    {
        $this->users()->detach($user->id);
    }

    /**
     * Update user's custom access level.
     *
     * @param User $user
     * @param int|null $customLevel
     * @return void
     */
    public function updateUserAccessLevel(User $user, ?int $customLevel): void
    {
        $this->users()->updateExistingPivot($user->id, [
            'custom_access_level' => $customLevel,
        ]);
    }
}
