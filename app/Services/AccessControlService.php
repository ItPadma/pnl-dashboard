<?php

namespace App\Services;

use App\Models\AccessGroup;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Collection;

class AccessControlService
{
    /**
     * Get user's effective access level for a specific menu.
     *
     * @param User $user
     * @param Menu|string $menu Menu model or slug
     * @return int Access level (0-4)
     */
    public function getUserAccessLevel(User $user, $menu): int
    {
        if (is_string($menu)) {
            $menu = Menu::where('slug', $menu)->first();
        }

        if (!$menu) {
            return AccessGroup::LEVEL_NO_ACCESS;
        }

        return $menu->getUserAccessLevel($user);
    }

    /**
     * Check if user can access a menu with required level.
     *
     * @param User $user
     * @param Menu|string $menu Menu model or slug
     * @param int $requiredLevel
     * @return bool
     */
    public function canAccess(User $user, $menu, int $requiredLevel = AccessGroup::LEVEL_READ): bool
    {
        return $this->getUserAccessLevel($user, $menu) >= $requiredLevel;
    }

    /**
     * Assign a user to an access group.
     *
     * @param User $user User to assign
     * @param AccessGroup $group Access group
     * @param int|null $customLevel Custom access level (null to use group default)
     * @param string|null $assignedBy Email of user who made the assignment
     * @return void
     */
    public function assignUserToGroup(User $user, AccessGroup $group, ?int $customLevel = null, ?string $assignedBy = null): void
    {
        // Check if user is already in the group
        if ($group->hasUser($user)) {
            // Update the custom access level
            $group->updateUserAccessLevel($user, $customLevel);
        } else {
            // Add user to group
            $group->assignUser($user, $customLevel, $assignedBy);
        }
    }

    /**
     * Remove a user from an access group.
     *
     * @param User $user
     * @param AccessGroup $group
     * @return void
     */
    public function removeUserFromGroup(User $user, AccessGroup $group): void
    {
        $group->removeUser($user);
    }

    /**
     * Assign an access group to a menu.
     *
     * @param AccessGroup $group
     * @param Menu $menu
     * @return void
     */
    public function assignGroupToMenu(AccessGroup $group, Menu $menu): void
    {
        if (!$group->menus()->where('menus.id', $menu->id)->exists()) {
            $group->menus()->attach($menu->id);
        }
    }

    /**
     * Remove an access group from a menu.
     *
     * @param AccessGroup $group
     * @param Menu $menu
     * @return void
     */
    public function removeGroupFromMenu(AccessGroup $group, Menu $menu): void
    {
        $group->menus()->detach($menu->id);
    }

    /**
     * Get all menus accessible by a user.
     *
     * @param User $user
     * @param int $minLevel Minimum access level required
     * @return Collection
     */
    public function getAccessibleMenus(User $user, int $minLevel = AccessGroup::LEVEL_READ): Collection
    {
        return Menu::getAccessibleMenus($user, $minLevel);
    }

    /**
     * Get hierarchical menu structure for a user.
     *
     * @param User $user
     * @param int $minLevel Minimum access level required
     * @return Collection
     */
    public function getMenuHierarchy(User $user, int $minLevel = AccessGroup::LEVEL_READ): Collection
    {
        $accessibleMenus = $this->getAccessibleMenus($user, $minLevel);
        
        // Get root menus (no parent) and sort by order
        $rootMenus = $accessibleMenus->filter(function ($menu) {
            return $menu->parent_id === null;
        })->sortBy('order')->values();

        // Build hierarchy
        return $rootMenus->map(function ($menu) use ($accessibleMenus) {
            return $this->buildMenuTree($menu, $accessibleMenus);
        });
    }

    /**
     * Build menu tree recursively.
     *
     * @param Menu $menu
     * @param Collection $allMenus
     * @return array
     */
    private function buildMenuTree(Menu $menu, Collection $allMenus): array
    {
        $children = $allMenus->filter(function ($m) use ($menu) {
            return $m->parent_id === $menu->id;
        })->sortBy('order')->values();

        $menuArray = $menu->toArray();
        
        if ($children->isNotEmpty()) {
            $menuArray['children'] = $children->map(function ($child) use ($allMenus) {
                return $this->buildMenuTree($child, $allMenus);
            })->values()->toArray();
        }

        return $menuArray;
    }

    /**
     * Get all users in an access group with their effective access levels.
     *
     * @param AccessGroup $group
     * @return Collection
     */
    public function getGroupUsers(AccessGroup $group): Collection
    {
        return $group->users->map(function ($user) use ($group) {
            $effectiveLevel = $user->pivot->custom_access_level ?? $group->default_access_level;
            
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'custom_access_level' => $user->pivot->custom_access_level,
                'effective_access_level' => $effectiveLevel,
                'access_level_name' => AccessGroup::getAccessLevelName($effectiveLevel),
                'assigned_by' => $user->pivot->assigned_by,
                'assigned_at' => $user->pivot->assigned_at,
            ];
        });
    }

    /**
     * Get all menus assigned to an access group.
     *
     * @param AccessGroup $group
     * @return Collection
     */
    public function getGroupMenus(AccessGroup $group): Collection
    {
        return $group->menus;
    }

    /**
     * Bulk assign users to an access group.
     *
     * @param array $userIds
     * @param AccessGroup $group
     * @param int|null $customLevel
     * @param string|null $assignedBy Email of user who made the assignment
     * @return void
     */
    public function bulkAssignUsersToGroup(array $userIds, AccessGroup $group, ?int $customLevel = null, ?string $assignedBy = null): void
    {
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $this->assignUserToGroup($user, $group, $customLevel, $assignedBy);
            }
        }
    }

    /**
     * Bulk assign menus to an access group.
     *
     * @param array $menuIds
     * @param AccessGroup $group
     * @return void
     */
    public function bulkAssignMenusToGroup(array $menuIds, AccessGroup $group): void
    {
        foreach ($menuIds as $menuId) {
            $menu = Menu::find($menuId);
            if ($menu) {
                $this->assignGroupToMenu($group, $menu);
            }
        }
    }
}
