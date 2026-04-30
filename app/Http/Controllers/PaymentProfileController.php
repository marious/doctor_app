<?php

namespace App\Http\Controllers;

use App\Models\UserPaymentProfile;
use App\Services\AuthorizeNetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Users\Models\User;

class PaymentProfileController extends Controller
{
    public function __construct(private AuthorizeNetService $authorize) {}

    // -------------------------------------------------------------------------
    // Show the iFrame to add a new card
    // GET /payment/profile/add
    // -------------------------------------------------------------------------

    public function add()
    {
        $user = User::first();

        try {
            // 1. Ensure the user has a CIM profile
            $customerProfileId = $this->authorize->createCustomerProfile($user);

            // 2. Get a fresh token for the hosted form
            $token = $this->authorize->getHostedProfileToken($customerProfileId);

            $iframeUrl = $this->authorize->getIframeBaseUrl();

            return view('payment.add', compact('token', 'iframeUrl'));

        } catch (\RuntimeException $e) {
            return back()->withErrors(['authorize' => $e->getMessage()]);
        }
    }

    // -------------------------------------------------------------------------
    // Return URL — Authorize.net redirects here after saving
    // GET /payment/profile/return   (also accepts POST)
    // -------------------------------------------------------------------------

    public function return(Request $request)
    {
        $user = User::first();

        // Sync all payment profiles from Authorize.net into our DB
        $this->authorize->syncPaymentProfiles($user);

        return redirect()
            ->route('payment.profile.index')
            ->with('success', 'Your card and shipping details have been saved.');
    }

    // -------------------------------------------------------------------------
    // List saved cards
    // GET /payment/profile
    // -------------------------------------------------------------------------

    public function index()
    {
        $profiles = UserPaymentProfile::where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return view('payment.index', compact('profiles'));
    }

    // -------------------------------------------------------------------------
    // Set a card as default
    // POST /payment/profile/{profile}/default
    // -------------------------------------------------------------------------

    public function setDefault(UserPaymentProfile $profile)
    {
        $this->authorize($profile); // policy check

        UserPaymentProfile::where('user_id', Auth::id())
            ->update(['is_default' => false]);

        $profile->update(['is_default' => true]);

        return back()->with('success', 'Default card updated.');
    }

    // -------------------------------------------------------------------------
    // Delete a saved card
    // DELETE /payment/profile/{profile}
    // -------------------------------------------------------------------------

    public function destroy(UserPaymentProfile $profile)
    {
        $this->authorize($profile);

        $user = Auth::user();
        $this->authorize->deletePaymentProfile($user, $profile);

        return back()->with('success', 'Card removed.');
    }

    // -------------------------------------------------------------------------
    // Private helper — ensure the profile belongs to the logged-in user
    // -------------------------------------------------------------------------

    private function authorize(UserPaymentProfile $profile): void
    {
        abort_if($profile->user_id !== Auth::id(), 403);
    }
}