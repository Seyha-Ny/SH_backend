<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private const UNAUTHENTICATED_MESSAGE = 'Unauthenticated. Please provide a valid authentication token.';
    // Message returned for abort(403) on API requests: this Laravel version's
    // abort() throws a plain HttpException, so bootstrap/app.php's HttpException
    // render callback echoes the middleware's own message.
    private const FORBIDDEN_MESSAGE = 'Forbidden. Admin access required.';

    /**
     * Create a user with a real Sanctum token (persisted in DB).
     *
     * @return array{0: User, 1: string}
     */
    private function makeUserWithToken(array $attributes = []): array
    {
        $user = User::factory()->create($attributes);
        $token = $user->createToken('admin-test')->plainTextToken;

        return [$user, $token];
    }

    // ------------------------------------------------------------------ //
    // CDN purge (API — admin only)
    // ------------------------------------------------------------------ //

    public function test_cdn_purge_unauthenticated_returns_401(): void
    {
        $this->postJson('/api/cdn/purge')
            ->assertUnauthorized()
            ->assertJson(['message' => self::UNAUTHENTICATED_MESSAGE]);
    }

    public function test_cdn_purge_forbidden_for_regular_user(): void
    {
        [, $token] = $this->makeUserWithToken(['is_admin' => false, 'role' => 'customer']);

        $this->withToken($token)
            ->postJson('/api/cdn/purge')
            ->assertForbidden()
            ->assertJson(['message' => self::FORBIDDEN_MESSAGE]);
    }

    public function test_cdn_purge_forbidden_for_admin_role_but_not_admin_flag(): void
    {
        // A user with role 'admin' but is_admin = false must NOT pass.
        [, $token] = $this->makeUserWithToken(['is_admin' => false, 'role' => 'admin']);

        $this->withToken($token)
            ->postJson('/api/cdn/purge')
            ->assertForbidden()
            ->assertJson(['message' => self::FORBIDDEN_MESSAGE]);
    }

    public function test_cdn_purge_allows_admin(): void
    {
        config([
            'services.cloudflare.api_token' => 'test-token',
            'services.cloudflare.zone_id' => 'test-zone',
        ]);

        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => true], 200),
        ]);

        [, $token] = $this->makeUserWithToken(['is_admin' => true, 'role' => 'admin']);

        $this->withToken($token)
            ->postJson('/api/cdn/purge', ['urls' => ['https://example.com/products']])
            ->assertOk()
            ->assertJsonPath('message', 'CDN cache purge submitted successfully.');

        // Specific URLs must be sent as a "files" payload to Cloudflare.
        Http::assertSent(fn ($request) =>
            str_contains($request->url(), 'purge_cache')
            && data_get($request->data(), 'files') === ['https://example.com/products']
        );
    }

    public function test_cdn_purge_allows_super_admin(): void
    {
        config([
            'services.cloudflare.api_token' => 'test-token',
            'services.cloudflare.zone_id' => 'test-zone',
        ]);

        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => true], 200),
        ]);

        [, $token] = $this->makeUserWithToken(['is_admin' => true, 'role' => 'super_admin']);

        $this->withToken($token)
            ->postJson('/api/cdn/purge')
            ->assertOk()
            ->assertJsonPath('message', 'CDN cache purge submitted successfully.');

        // No URLs provided → the controller must send purge_everything.
        Http::assertSent(fn ($request) =>
            str_contains($request->url(), 'purge_cache')
            && data_get($request->data(), 'purge_everything') === true
        );
    }

    public function test_cdn_purge_returns_501_when_not_configured(): void
    {
        config([
            'services.cloudflare.api_token' => null,
            'services.cloudflare.zone_id' => null,
        ]);

        // Fake the CDN endpoint anyway so the test process is fully isolated:
        // assertNothingSent() then genuinely proves no request was attempted.
        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => true], 200),
        ]);

        [, $token] = $this->makeUserWithToken(['is_admin' => true, 'role' => 'admin']);

        $this->withToken($token)
            ->postJson('/api/cdn/purge')
            ->assertStatus(501)
            ->assertJsonPath('message', 'CDN purge is not configured. Set CLOUDFLARE_API_TOKEN and CLOUDFLARE_ZONE_ID in .env');

        // No external request should be made when the CDN is not configured.
        Http::assertNothingSent();
    }

    // ------------------------------------------------------------------ //
    // Admin login — unified storefront form only (no /admin/login page)
    // ------------------------------------------------------------------ //

    public function test_admin_login_route_does_not_exist(): void
    {
        // The separate admin login page was removed: admins sign in only
        // through the storefront form, so /admin/login must be a 404.
        $this->get('/admin/login')->assertNotFound();
    }

    public function test_admin_logout_works_even_with_stale_csrf_token(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);

        // A stale/wrong CSRF token must never block logout (419) — the request
        // is CSRF-exempt so admins can always sign out, even after their
        // session rotated or expired in another tab. FRONTEND_URL is unset in
        // tests, so the logout redirect target is '/'. 
        $this->actingAs($admin)
            ->post('/admin/logout', ['_token' => 'stale-or-wrong-token'])
            ->assertRedirect('/');
    }

    // ------------------------------------------------------------------ //
    // Admin dashboard (web — admin only)
    // ------------------------------------------------------------------ //

    public function test_dashboard_redirects_guest_to_storefront_auth(): void
    {
        // There is no separate admin login page — guests are sent to the
        // unified storefront sign-in form (relative /auth in tests, since
        // FRONTEND_URL is unset).
        $this->get('/admin/dashboard')->assertRedirect('/auth');
    }

    public function test_dashboard_forbidden_for_regular_user(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'role' => 'customer']);

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_dashboard_allows_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_dashboard_allows_super_admin(): void
    {
        $superAdmin = User::factory()->create(['is_admin' => true, 'role' => 'super_admin']);

        $this->actingAs($superAdmin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    // ------------------------------------------------------------------ //
    // Unified login — the storefront /api/login form signs in both roles
    // ------------------------------------------------------------------ //

    public function test_admin_api_login_establishes_web_session_for_admin_panel(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'admin',
            'password' => Hash::make('secret123'),
        ]);

        // Simulate a stateful SPA request (Sanctum boots the session for the
        // storefront origin) so the unified login can create the web session.
        $response = $this->withHeaders(['Origin' => 'http://localhost:5173'])
            ->postJson('/api/login', [
                'email' => $admin->email,
                'password' => 'secret123',
            ]);

        $response->assertOk()
            ->assertJsonPath('user.is_admin', true)
            ->assertJsonPath('user.role', 'admin')
            ->assertJsonStructure(['token', 'user']);

        // The same login must have authenticated the web guard too.
        $this->assertAuthenticated('web');

        // And the session established by the API login must unlock /admin.
        $cookie = collect($response->headers->getCookies())
            ->first(fn ($c) => $c->getName() === config('session.cookie'));

        $this->assertNotNull($cookie, 'API login did not set a session cookie.');

        $this->withCookie($cookie->getName(), $cookie->getValue())
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_customer_api_login_does_not_grant_admin_panel_access(): void
    {
        $customer = User::factory()->create([
            'is_admin' => false,
            'role' => 'customer',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->withHeaders(['Origin' => 'http://localhost:5173'])
            ->postJson('/api/login', [
                'email' => $customer->email,
                'password' => 'secret123',
            ]);

        $response->assertOk()
            ->assertJsonPath('user.is_admin', false);

        // Customers are NOT signed into the web guard by the storefront form.
        $this->assertGuest('web');

        // Even carrying the session cookie, the admin panel stays off-limits
        // and the visitor is sent to the storefront sign-in form.
        $cookie = collect($response->headers->getCookies())
            ->first(fn ($c) => $c->getName() === config('session.cookie'));

        $this->withCookie($cookie->getName(), $cookie->getValue())
            ->get('/admin/dashboard')
            ->assertRedirect('/auth');
    }

    public function test_api_logout_with_session_and_bearer_token_revokes_token_and_invalidates_session(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $token = $admin->createToken('test')->plainTextToken;

        // A unified-login request carries BOTH the web session (which Sanctum's
        // guard prefers) and a bearer token. The old code called
        // currentAccessToken()->delete() on the session-resolved user — a
        // TransientToken — which threw and 500'd every SPA logout. It must
        // now return 200, revoke the real token, and end the web session.
        $this->actingAs($admin)
            ->withToken($token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJson(['message' => 'Logged out successfully']);

        // The web session (admin panel access) is ended...
        $this->assertGuest('web');

        // ...and the real bearer token row is revoked.
        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', $token),
        ]);
    }

    public function test_api_logout_when_already_logged_out_returns_200(): void
    {
        // Expired or already-logged-out sessions must be a graceful no-op, not
        // a 401/500 — the storefront relies on logout always succeeding.
        $this->postJson('/api/logout')
            ->assertOk()
            ->assertJson(['message' => 'Logged out successfully']);
    }
}
