<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessGroup;
use App\Models\Menu;
use App\Models\User;
use App\Services\AccessControlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccessGroupController extends Controller
{
    /**
     * The access control service instance.
     *
     * @var AccessControlService
     */
    protected $accessControl;

    /**
     * Create a new controller instance.
     *
     * @param AccessControlService $accessControl
     */
    public function __construct(AccessControlService $accessControl)
    {
        $this->accessControl = $accessControl;
    }

    /**
     * Display a listing of access groups.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return view('admin.access-groups.index');
        }

        $groups = AccessGroup::with(['creator', 'users', 'menus'])
            ->orderBy('name')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'default_access_level' => $group->default_access_level,
                    'access_level_name' => AccessGroup::getAccessLevelName($group->default_access_level),
                    'is_active' => $group->is_active,
                    'created_by' => $group->creator ? $group->creator->name : null,
                    'users_count' => $group->users->count(),
                    'menus_count' => $group->menus->count(),
                    'created_at' => $group->created_at,
                    'updated_at' => $group->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $groups,
        ]);
    }

    /**
     * Store a newly created access group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'default_access_level' => 'required|integer|min:0|max:4',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $group = AccessGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'default_access_level' => $request->default_access_level,
            'is_active' => $request->is_active ?? true,
            'created_by' => Auth::user()->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Access group created successfully',
            'data' => $group,
        ], 201);
    }

    /**
     * Display the specified access group.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $group = AccessGroup::with(['creator', 'users', 'menus'])->find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Access group not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'group' => $group,
                'users' => $this->accessControl->getGroupUsers($group),
                'menus' => $this->accessControl->getGroupMenus($group),
            ],
        ]);
    }

    /**
     * Update the specified access group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $group = AccessGroup::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Access group not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'default_access_level' => 'sometimes|required|integer|min:0|max:4',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $updateData = $request->only(['name', 'description', 'default_access_level', 'is_active']);
        
        // Convert is_active to boolean if present
        if (isset($updateData['is_active'])) {
            $updateData['is_active'] = (bool) $updateData['is_active'];
        }

        $group->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Access group updated successfully',
            'data' => $group,
        ]);
    }

    /**
     * Remove the specified access group.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $group = AccessGroup::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Access group not found',
            ], 404);
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Access group deleted successfully',
        ]);
    }

    /**
     * Assign a user to the access group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignUser(Request $request, $id)
    {
        $group = AccessGroup::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Access group not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'custom_access_level' => 'nullable|integer|min:0|max:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::find($request->user_id);
        $this->accessControl->assignUserToGroup(
            $user,
            $group,
            $request->custom_access_level,
            Auth::user()->email
        );

        return response()->json([
            'success' => true,
            'message' => 'User assigned to access group successfully',
        ]);
    }

    /**
     * Remove a user from the access group.
     *
     * @param  int  $id
     * @param  int  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeUser($id, $userId)
    {
        $group = AccessGroup::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Access group not found',
            ], 404);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $this->accessControl->removeUserFromGroup($user, $group);

        return response()->json([
            'success' => true,
            'message' => 'User removed from access group successfully',
        ]);
    }

    /**
     * Assign a menu to the access group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignMenu(Request $request, $id)
    {
        $group = AccessGroup::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Access group not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'menu_ids' => 'required|array',
            'menu_ids.*' => 'exists:menus,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $group->menus()->syncWithoutDetaching($request->menu_ids);

        return response()->json([
            'success' => true,
            'message' => count($request->menu_ids) . ' menu(s) assigned to access group successfully',
        ]);
    }

    /**
     * Remove a menu from the access group.
     *
     * @param  int  $id
     * @param  int  $menuId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeMenu($id, $menuId)
    {
        $group = AccessGroup::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Access group not found',
            ], 404);
        }

        $menu = Menu::find($menuId);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Menu not found',
            ], 404);
        }

        $this->accessControl->removeGroupFromMenu($group, $menu);

        return response()->json([
            'success' => true,
            'message' => 'Menu removed from access group successfully',
        ]);
    }

    /**
     * Get all available access levels.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccessLevels()
    {
        return response()->json([
            'success' => true,
            'data' => AccessGroup::getAccessLevels(),
        ]);
    }
}
