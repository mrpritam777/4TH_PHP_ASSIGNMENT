<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureContractorApproved
{
    /**
     * Blocks pending or rejected contractors from protected contractor
     * features (tenders, bids, cancellations). Enforcement happens on the
     * server side — never only in the UI.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->isContractor()) {
            abort(403, 'You are not authorized to access this area.');
        }

        $contractor = $user->contractor;

        if (! $user->isActive() || ! $contractor || ! $contractor->isApproved()) {
            return redirect()
                ->route('contractor.dashboard')
                ->with('error', 'Your contractor account must be approved by an administrator before you can access this area.');
        }

        return $next($request);
    }
}
