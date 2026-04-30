<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Services\AuthorizeNetService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private AuthorizeNetService $service) {}

    public function showForm()
    {
        $token = $this->service->getHostedFormToken(
            returnUrl: route('payment.return'),
            cancelUrl: route('payment.cancel'),
        );

        if (!$token) {
            return back()->withErrors('Could not load payment form. Please try again.');
        }

        $iframeUrl = config('authorize.sandbox')
            ? 'https://test.authorize.net/payment/payment'
            : 'https://accept.authorize.net/payment/payment';

        return view('payment.form', compact('token', 'iframeUrl'));
    }

    public function handleReturn(Request $request)
    {
        // Authorize.net POSTs back customerInformationID, etc.
        // Store what you need, void the $0 auth if desired
        $data = $request->all();
        \Log::info('Authorize.net return', $data);

        // TODO: save to DB, void the $0 auth transaction
        return redirect()->route('home')->with('success', 'Card saved successfully!');
    }

    public function handleCancel()
    {
        return redirect()->route('home')->with('info', 'Payment form cancelled.');
    }
}