<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\AccessControlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
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
     * Display a listing of menus.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return view('admin.menus.index');
        }

        $menus = Menu::with(['parent', 'children', 'accessGroups'])
            ->orderBy('order')
            ->get()
            ->map(function ($menu) {
                return [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'slug' => $menu->slug,
                    'route_name' => $menu->route_name,
                    'icon' => $menu->icon,
                    'parent_id' => $menu->parent_id,
                    'parent_name' => $menu->parent ? $menu->parent->name : null,
                    'order' => $menu->order,
                    'is_active' => $menu->is_active,
                    'children_count' => $menu->children->count(),
                    'access_groups_count' => $menu->accessGroups->count(),
                    'created_at' => $menu->created_at,
                    'updated_at' => $menu->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $menus,
        ]);
    }

    /**
     * Store a newly created menu.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:menus,slug',
            'route_name' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $menu = Menu::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'route_name' => $request->route_name,
            'icon' => $request->icon,
            'parent_id' => $request->parent_id,
            'order' => $request->order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Menu created successfully',
            'data' => $menu,
        ], 201);
    }

    /**
     * Display the specified menu.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $menu = Menu::with(['parent', 'children', 'accessGroups'])->find($id);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Menu not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $menu,
        ]);
    }

    /**
     * Update the specified menu.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Menu not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'slug' => 'sometimes|required|string|max:100|unique:menus,slug,' . $id,
            'route_name' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Prevent circular parent reference
        if ($request->has('parent_id') && $request->parent_id == $id) {
            return response()->json([
                'success' => false,
                'message' => 'A menu cannot be its own parent',
            ], 422);
        }

        $menu->update($request->only(['name', 'slug', 'route_name', 'icon', 'parent_id', 'order', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Menu updated successfully',
            'data' => $menu,
        ]);
    }

    /**
     * Remove the specified menu.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Menu not found',
            ], 404);
        }

        // Check if menu has children
        if ($menu->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete menu with child menus. Please delete or reassign child menus first.',
            ], 422);
        }

        $menu->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu deleted successfully',
        ]);
    }

    /**
     * Get menus accessible by the current user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccessibleMenus(Request $request)
    {
        $user = Auth::user();
        $minLevel = $request->input('min_level', 1);

        $menus = $this->accessControl->getMenuHierarchy($user, $minLevel);

        return response()->json([
            'success' => true,
            'data' => $menus,
        ]);
    }

    /**
     * Get menu hierarchy (all menus in tree structure).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHierarchy()
    {
        $rootMenus = Menu::with('children')
            ->root()
            ->active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rootMenus,
        ]);
    }
}
