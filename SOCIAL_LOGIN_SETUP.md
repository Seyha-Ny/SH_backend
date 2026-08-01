Social login setup:
1. Backend .env:
   GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=
   GOOGLE_REDIRECT_URL=http://localhost

2. Frontend .env:
   VITE_GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com

3. Backend routes:
   Added `POST /api/auth/social/callback` implemented in `App\Http\Controllers\Api\AuthController@socialCallback` using `App\Actions\SocialLoginAction`.

4. Frontend changes:
   - `src/views/Auth.vue` loads Google Identity Services and sends ID token to backend.
   - Backend verifies Google token, finds/creates user, and returns existing `{token, user}` shape.
   - Auth store `setAuth` path handles it automatically.

Notes:
- Current flow is client-side Google ID token exchange. If Google client is not configured, the button stays disabled.
- New users from social login are created as non-admin customers with random password.