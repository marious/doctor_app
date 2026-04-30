<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPaymentProfile;
use Illuminate\Support\Facades\Log;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class AuthorizeNetService
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function getMerchantAuth(): AnetAPI\MerchantAuthenticationType
    {
        $auth = new AnetAPI\MerchantAuthenticationType();
        $auth->setName(config('authorize.login_id'));
        $auth->setTransactionKey(config('authorize.transaction_key'));
        return $auth;
    }

    private function getEnvironment(): string
    {
        return config('authorize.sandbox')
            ? \net\authorize\api\constants\ANetEnvironment::SANDBOX
            : \net\authorize\api\constants\ANetEnvironment::PRODUCTION;
    }

    private function getIframeUrl(): string
    {
        return config('authorize.sandbox')
            ? config('authorize.endpoint.sandbox')
            : config('authorize.endpoint.production');
    }

    // -------------------------------------------------------------------------
    // Step 1 — Create a CIM Customer Profile
    // Called once per user. Stores the returned ID on the users table.
    // -------------------------------------------------------------------------

    /**
     * @throws \RuntimeException
     */
    public function createCustomerProfile($user): string
    {
        // Return existing profile if already created
        // if ($user->authorize_customer_profile_id) {
        //     return $user->authorize_customer_profile_id;
        // }

        $userId = $user->id + time();
        $customerProfile = new AnetAPI\CustomerProfileType();
        $customerProfile->setMerchantCustomerId((string) $userId);
        $customerProfile->setEmail('admin'.time().'@admin.com');
        $customerProfile->setDescription('User #' . $userId);


        $request = new AnetAPI\CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($this->getMerchantAuth());
        $request->setProfile($customerProfile);
        $request->setValidationMode('none');
        $request->setClientId(null); // Fix E00003 bug

        $controller = new AnetController\CreateCustomerProfileController($request);
        $response   = $controller->executeWithApiResponse($this->getEnvironment());
        // dd($response->getResultCode());

        // if ($response && $response->getResultCode() === 'Ok') {
            $profileId = $response->getCustomerProfileId();

            $user->update(['authorize_customer_profile_id' => $profileId]);

            Log::info('Authorize.net: customer profile created', [
                'user_id'    => $user->id,
                'profile_id' => $profileId,
            ]);

            return $profileId;
        // }

        $errors = collect($response?->getMessages()?->getMessage() ?? [])
            ->map(fn($e) => "[{$e->getCode()}] {$e->getText()}")
            ->implode(', ');

        Log::error('Authorize.net: createCustomerProfile failed', [
            'user_id' => $user->id,
            'errors'  => $errors,
        ]);

        throw new \RuntimeException('Could not create customer profile: ' . $errors);
    }

    // -------------------------------------------------------------------------
    // Step 2 — Get Hosted Profile Page Token (for the iFrame)
    // -------------------------------------------------------------------------

    /**
     * @throws \RuntimeException
     */
    public function getHostedProfileToken(string $customerProfileId): string
    {
        // --- iFrame communicator (required by Authorize.net) ---
        $setting_communicator = new AnetAPI\SettingType();
        $setting_communicator->setSettingName('hostedProfileIFrameCommunicatorUrl');
        $setting_communicator->setSettingValue(url('/iframe-communicator'));

        // --- Return URL after save ---
        $setting_return = new AnetAPI\SettingType();
        $setting_return->setSettingName('hostedProfileReturnUrl');
        $setting_return->setSettingValue(route('payment.profile.return'));

        $setting_return_text = new AnetAPI\SettingType();
        $setting_return_text->setSettingName('hostedProfileReturnUrlText');
        $setting_return_text->setSettingValue('Continue');

        // --- Show sections inside the iFrame ---
        $setting_billing = new AnetAPI\SettingType();
        $setting_billing->setSettingName('hostedProfileBillingAddressOptions');
        $setting_billing->setSettingValue('showBillingAddress');

        // $setting_shipping = new AnetAPI\SettingType();
        // $setting_shipping->setSettingName('hostedProfileShippingAddressOptions');
        // $setting_shipping->setSettingValue('');

        // --- Hide the "save address" checkbox (we always save) ---
        $setting_manageable = new AnetAPI\SettingType();
        $setting_manageable->setSettingName('hostedProfileManageOptions');
        $setting_manageable->setSettingValue('showPayment');

        // --- Validation mode: none = no $0 auth, just tokenise ---
        $setting_validation = new AnetAPI\SettingType();
        $setting_validation->setSettingName('hostedProfileValidationMode');
        $setting_validation->setSettingValue('none');

        $request = new AnetAPI\GetHostedProfilePageRequest();
        $request->setMerchantAuthentication($this->getMerchantAuth());
        $request->setCustomerProfileId($customerProfileId);
        $request->setHostedProfileSettings([
            $setting_communicator,
            // $setting_return,
            $setting_return_text,
            $setting_billing,
            // $setting_shipping,
            $setting_manageable,
            $setting_validation,
        ]);
        $request->setClientId(null); // Fix E00003 bug

        $controller = new AnetController\GetHostedProfilePageController($request);
        $response   = $controller->executeWithApiResponse($this->getEnvironment());
        // if ($response && $response->getResultCode() === 'Ok') {
            return $response->getToken();
        // }

        $errors = collect($response?->getMessages()?->getMessage() ?? [])
            ->map(fn($e) => "[{$e->getCode()}] {$e->getText()}")
            ->implode(', ');

        Log::error('Authorize.net: getHostedProfileToken failed', [
            'profile_id' => $customerProfileId,
            'errors'     => $errors,
        ]);

        throw new \RuntimeException('Could not load payment form: ' . $errors);
    }

    // -------------------------------------------------------------------------
    // Step 3 — Fetch & store payment profiles after iFrame completes
    // Call this from the return-URL controller action.
    // -------------------------------------------------------------------------

    public function syncPaymentProfiles(User $user): void
    {
        $profileId = 1;
        if (!$profileId) return;

        $request = new AnetAPI\GetCustomerProfileRequest();
        $request->setMerchantAuthentication($this->getMerchantAuth());
        $request->setCustomerProfileId($profileId);
        $request->setClientId(null);

        $controller = new AnetController\GetCustomerProfileController($request);
        $response   = $controller->executeWithApiResponse($this->getEnvironment());

        if (!$response || $response->getResultCode() !== 'Ok') {
            Log::warning('Authorize.net: syncPaymentProfiles — could not fetch profile', [
                'user_id'    => $user->id,
                'profile_id' => $profileId,
            ]);
            return;
        }

        $remoteProfile         = $response->getProfile();
        $remotePaymentProfiles = $remoteProfile->getPaymentProfiles() ?? [];

        // Collect remote payment profile IDs
        $remoteIds = [];

        foreach ($remotePaymentProfiles as $pp) {
            $ppId = $pp->getCustomerPaymentProfileId();
            $remoteIds[] = $ppId;

            $payment    = $pp->getPayment();
            $creditCard = $payment?->getCreditCard();
            $billing    = $pp->getBillTo();

            // Upsert into our DB
            UserPaymentProfile::updateOrCreate(
                [
                    'user_id'                        => $user->id,
                    'authorize_payment_profile_id'   => $ppId,
                ],
                [
                    'card_type'       => $creditCard?->getCardType(),
                    'card_last_four'  => substr($creditCard?->getCardNumber() ?? '', -4),
                    'card_expiry'     => $creditCard?->getExpirationDate(),
                    'first_name'      => $billing?->getFirstName(),
                    'last_name'       => $billing?->getLastName(),
                ]
            );
        }

        // Sync shipping address from the customer profile's shipping addresses
        $shippingAddresses = $remoteProfile->getShipToList() ?? [];

        foreach ($shippingAddresses as $addr) {
            // Match by user — update the most recently created payment profile row
            $paymentRow = UserPaymentProfile::where('user_id', $user->id)
                ->latest()
                ->first();

            if ($paymentRow) {
                $paymentRow->update([
                    'shipping_first_name' => $addr->getFirstName(),
                    'shipping_last_name'  => $addr->getLastName(),
                    'shipping_address'    => $addr->getAddress(),
                    'shipping_city'       => $addr->getCity(),
                    'shipping_state'      => $addr->getState(),
                    'shipping_zip'        => $addr->getZip(),
                    'shipping_country'    => $addr->getCountry(),
                ]);
            }
        }

        // Remove DB rows for payment profiles deleted on Authorize.net side
        UserPaymentProfile::where('user_id', $user->id)
            ->whereNotIn('authorize_payment_profile_id', $remoteIds)
            ->delete();

        // Mark first profile as default if none set
        $hasDefault = UserPaymentProfile::where('user_id', $user->id)
            ->where('is_default', true)
            ->exists();

        if (!$hasDefault) {
            UserPaymentProfile::where('user_id', $user->id)
                ->oldest()
                ->first()
                ?->update(['is_default' => true]);
        }

        Log::info('Authorize.net: payment profiles synced', [
            'user_id' => $user->id,
            'count'   => count($remoteIds),
        ]);
    }

    // -------------------------------------------------------------------------
    // Step 4 — Charge a saved card (call anytime later)
    // -------------------------------------------------------------------------

    /**
     * @throws \RuntimeException
     */
    public function chargePaymentProfile(
        string $customerProfileId,
        string $paymentProfileId,
        float  $amount,
        string $description = ''
    ): string {
        $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId($customerProfileId);

        $paymentProfile = new AnetAPI\PaymentProfileType();
        $paymentProfile->setPaymentProfileId($paymentProfileId);
        $profileToCharge->setPaymentProfile($paymentProfile);

        $transactionRequest = new AnetAPI\TransactionRequestType();
        $transactionRequest->setTransactionType('authCaptureTransaction');
        $transactionRequest->setAmount(number_format($amount, 2, '.', ''));
        $transactionRequest->setProfile($profileToCharge);

        if ($description) {
            $order = new AnetAPI\OrderType();
            $order->setDescription($description);
            $transactionRequest->setOrder($order);
        }

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($this->getMerchantAuth());
        $request->setTransactionRequest($transactionRequest);
        $request->setClientId(null);

        $controller = new AnetController\CreateTransactionController($request);
        $response   = $controller->executeWithApiResponse($this->getEnvironment());

        $tResponse = $response?->getTransactionResponse();

        if ($response && $response->getResultCode() === 'Ok' && $tResponse && $tResponse->getResponseCode() === '1') {
            Log::info('Authorize.net: charge successful', [
                'trans_id'   => $tResponse->getTransId(),
                'amount'     => $amount,
                'profile_id' => $customerProfileId,
            ]);
            return $tResponse->getTransId();
        }

        $errors = collect($tResponse?->getErrors() ?? [])
            ->map(fn($e) => "[{$e->getErrorCode()}] {$e->getErrorText()}")
            ->implode(', ');

        Log::error('Authorize.net: charge failed', [
            'profile_id'         => $customerProfileId,
            'payment_profile_id' => $paymentProfileId,
            'errors'             => $errors,
        ]);

        throw new \RuntimeException('Charge failed: ' . $errors);
    }

    // -------------------------------------------------------------------------
    // Utility — Delete a payment profile from Authorize.net + our DB
    // -------------------------------------------------------------------------

    public function deletePaymentProfile(User $user, UserPaymentProfile $paymentProfile): bool
    {
        $request = new AnetAPI\DeleteCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($this->getMerchantAuth());
        $request->setCustomerProfileId($user->authorize_customer_profile_id);
        $request->setCustomerPaymentProfileId($paymentProfile->authorize_payment_profile_id);
        $request->setClientId(null);

        $controller = new AnetController\DeleteCustomerPaymentProfileController($request);
        $response   = $controller->executeWithApiResponse($this->getEnvironment());

        if ($response && $response->getResultCode() === 'Ok') {
            $paymentProfile->delete();
            return true;
        }

        Log::warning('Authorize.net: deletePaymentProfile failed', [
            'user_id'            => $user->id,
            'payment_profile_id' => $paymentProfile->authorize_payment_profile_id,
        ]);

        return false;
    }

    // -------------------------------------------------------------------------
    // Convenience getters
    // -------------------------------------------------------------------------

    public function getIframeBaseUrl(): string
    {
        return $this->getIframeUrl();
    }
}