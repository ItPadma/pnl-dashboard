<?php

namespace App\Http\Middleware;

use App\Models\AccessGroup;
use App\Models\Menu;
use App\Services\AccessControlService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    /**
     * The access control service instance.
     *
     * @var AccessControlService
     */
    protected $accessControl;

    /**
     * Create a new middleware instance.
     *
     * @param AccessControlService $accessControl
     */
    public function __construct(AccessControlService $accessControl)
    {
        $this->accessControl = $accessControl;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $menuSlug  The menu slug to check access for
     * @param  int  $requiredLevel  The minimum access level required (default: 1 = Read)
     */
    public function handle(Request $request, Closure $next, string $menuSlug, int $requiredLevel = AccessGroup::LEVEL_READ): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user has required access level
        if (!$this->accessControl->canAccess($user, $menuSlug, $requiredLevel)) {
            abort(403, 'You do not have permission to access this resource. Required level: ' . AccessGroup::getAccessLevelName($requiredLevel));
        }

        return $next($request);
    }
}
