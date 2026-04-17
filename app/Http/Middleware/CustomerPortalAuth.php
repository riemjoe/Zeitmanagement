<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerPortalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $customerId = session('portal_customer_id');

        if (! $customerId) {
            return redirect()->route('portal.login')->with('error', 'Bitte melden Sie sich an.');
        }

        $customer = \App\Models\Customer::find($customerId);

        if (! $customer || ! $customer->portal_enabled) {
            session()->forget('portal_customer_id');
            session()->forget('portal_2fa_verified');
            return redirect()->route('portal.login')->with('error', 'Ihr Portal-Zugang ist deaktiviert.');
        }

        // Passwort ändern erzwingen
        if ($customer->portal_must_change_password && ! $request->routeIs('portal.change-password*')) {
            return redirect()->route('portal.change-password');
        }

        // 2FA-Verifizierung erzwingen (wenn aktiviert)
        if (
            $customer->portal_2fa_enabled &&
            ! session('portal_2fa_verified') &&
            ! $request->routeIs('portal.2fa*')
        ) {
            return redirect()->route('portal.2fa.verify');
        }

        // Kunden in Request verfügbar machen
        $request->merge(['portal_customer' => $customer]);

        return $next($request);
    }
}
