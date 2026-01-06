<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('getLoggedInUserInfo')) {
    /**
     * Get logged in user information
     *
     * Returns an object containing email, role, and depo information
     * for the currently authenticated user.
     *
     * @return object|null Returns object with email, role, and depo properties, or null if not authenticated
     *
     * @example
     * $userInfo = getLoggedInUserInfo();
     * if ($userInfo) {
     *     echo $userInfo->email;  // user@example.com
     *     echo $userInfo->role;   // admin
     *     print_r($userInfo->depo); // ['SC', 'JKT', 'BDG']
     * }
     */
    function getLoggedInUserInfo(): ?object
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        // Parse depo field - it's stored as pipe-delimited string (e.g., "SC|JKT|BDG")
        $depoArray = [];
        if (! empty($user->depo)) {
            $depoArray = explode('|', $user->depo);
        }

        return (object) [
            'email' => $user->email,
            'role' => $user->role,
            'depo' => $user->depo == 'all' ? ['all'] : $depoArray,
        ];
    }
}
