<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Sign the admin out of the web session and return them to the storefront.
     *
     * There is no separate admin login page anymore: admins sign in via the
     * unified storefront form (POST /api/login), so after logging out they go
     * back to the storefront — same origin in production (FRONTEND_URL empty),
     * or the dev storefront URL when FRONTEND_URL is set.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $base = rtrim((string) config('app.frontend_url'), '/');

        return redirect($base ?: '/');
    }
}
