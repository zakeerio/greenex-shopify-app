<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;

class VerifyShopifyEmbedded
{
    public function handle(Request $request, Closure $next)
    {
        $shop = $request->get('shop');
        $idToken = $request->get('id_token');
        $hmac = $request->get('hmac');

        dd($request->all());

        // 1️⃣ Embedded app: validate id_token if present
        if ($idToken) {
            try {
                $decoded = JWT::decode($idToken, new Key(config('shopify-app.api_secret'), 'HS256'));
                if ($decoded->iss !== "https://{$shop}/admin") {
                    abort(403, "Invalid shop");
                }
            } catch (\Exception $e) {
                abort(403, "Invalid id_token");
            }
        }

        // 2️⃣ Fallback HMAC validation (if id_token not present)
        if (!$idToken && $hmac) {
            $query = $request->query();
            unset($query['hmac']); // exclude hmac itself
            ksort($query);

            $message = urldecode(http_build_query($query));
            $calculatedHmac = hash_hmac('sha256', $message, config('shopify-app.api_secret'));

            if (!hash_equals($hmac, $calculatedHmac)) {
                abort(403, "HMAC verification failed");
            }
        }

        // 3️⃣ Auto-create or update user/shop in DB
        if ($shop) {
            $accessToken = $request->get('token', Str::random(32)); // store random if not present

            $user = User::updateOrCreate(
                ['shopify_domain' => $shop],
                [
                    'name' => $shop,
                    'email' => "{$shop}@greenex-app.local",
                    'password' => bcrypt(Str::random(16)),
                    'access_token' => $accessToken,
                ]
            );

            Auth::login($user);
            Session::put("shopify_token_{$shop}", $accessToken);
        }

        return $next($request);
    }
}
