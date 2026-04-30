@extends('layouts.app')

@section('title', 'Add Payment Method')

@section('content')
<div class="max-w-2xl mx-auto py-10 px-4">

    <h1 class="text-2xl font-semibold text-gray-800 mb-2">Add Payment & Shipping</h1>
    <p class="text-gray-500 mb-6">
        Your card details are stored securely. <strong>No charge will be made.</strong>
    </p>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- The iFrame target -- Authorize.net renders into this --}}
    <div id="iframe-wrapper" class="border rounded-lg overflow-scroll shadow-sm bg-white">
        <iframe
            id="add_payment_iframe"
            name="add_payment_iframe"
            frameborder="0"
            width="100%"
            height="1800"
            scrolling="no"
            style="display:block;"
        ></iframe>
    </div>

    {{-- 
        IMPORTANT: Authorize.net requires a POST form submission to load the hosted profile page.
        Setting the iframe src directly (GET) will result in a blank iframe.
        This form posts the token to Authorize.net and targets the iframe above.
    --}}
    <form
        id="authorize_form"
        method="POST"
        action="{{ $iframeUrl }}"
        target="add_payment_iframe"
        style="display:none;"
    >
        <input type="hidden" name="token" value="{{ $token }}" />
    </form>

</div>
@endsection

@push('scripts')
<script>
    // Submit the hidden form on page load to inject content into the iframe
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('authorize_form').submit();
    });

    // Listen for messages from Authorize.net via the iframe communicator
window.addEventListener('message', function (event) {
    let data;
    
    // 1. Parse the incoming data
    try {
        // Authorize.net sometimes sends a string that needs parsing
        data = (typeof event.data === 'string') ? JSON.parse(event.data) : event.data;
    } catch (e) {
        // If it's not JSON, it might be query-string formatted
        const params = new URLSearchParams(event.data);
        data = Object.fromEntries(params.entries());
    }


    // 2. LISTEN FOR CANCEL BUTTON
    if (data.action === 'cancel') {
        console.log("User clicked cancel.");
        
        // Redirect the user or show a local notification
    }

    // 3. LISTEN FOR SUCCESSFUL TRANSACTION
    if (data.action === 'transactResponse') {
        let response = JSON.parse(data.response);
        
        // If the transaction was successful (Response Code 1)
        if (response.responseCode === '1') {
            document.getElementById('paymentStatus').classList.add('show');
            
            // Redirect to your success route with the transaction ID
        } else {
            alert("Payment failed: " + response.errors[0].errorText);
        }
    }

    // 4. HANDLE IFRAME RESIZING (Smooth UI)
    if (data.action === 'resizeWindow') {
        const iframe = document.getElementById('authnet-iframe');
        if (iframe && data.height) {
            iframe.style.height = data.height + 'px';
        }
    }
}, false);
</script>
@endpush