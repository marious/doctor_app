@extends('layouts.app')

@section('title', 'Saved Payment Methods')

@section('content')
<div class="max-w-2xl mx-auto py-10 px-4">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Saved Payment Methods</h1>
        <a href="{{ route('payment.profile.add') }}"
           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700 transition">
            + Add New Card
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    @forelse ($profiles as $profile)
        <div class="bg-white border rounded-lg p-5 mb-4 shadow-sm flex items-start justify-between">

            <div>
                {{-- Card info --}}
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-medium text-gray-800">
                        {{ $profile->card_type ?: 'Card' }} •••• {{ $profile->card_last_four }}
                    </span>
                    @if ($profile->is_default)
                        <span class="text-xs px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full font-medium">
                            Default
                        </span>
                    @endif
                </div>

                {{-- Billing name --}}
                @if ($profile->first_name || $profile->last_name)
                    <p class="text-sm text-gray-500">
                        {{ trim("{$profile->first_name} {$profile->last_name}") }}
                    </p>
                @endif

                {{-- Shipping address --}}
                @if ($profile->shipping_address)
                    <p class="text-sm text-gray-400 mt-1">
                        🚚 {{ $profile->shipping_address }},
                        {{ $profile->shipping_city }},
                        {{ $profile->shipping_state }}
                        {{ $profile->shipping_zip }}
                    </p>
                @endif
            </div>

            <div class="flex flex-col items-end gap-2 ml-4 shrink-0">
                {{-- Set as default --}}
                @unless ($profile->is_default)
                    <form method="POST" action="{{ route('payment.profile.default', $profile) }}">
                        @csrf
                        <button type="submit"
                                class="text-xs text-indigo-600 hover:underline">
                            Set as default
                        </button>
                    </form>
                @endunless

                {{-- Delete --}}
                <form method="POST" action="{{ route('payment.profile.destroy', $profile) }}"
                      onsubmit="return confirm('Remove this card?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="text-xs text-red-500 hover:underline">
                        Remove
                    </button>
                </form>
            </div>

        </div>
    @empty
        <div class="text-center py-16 text-gray-400">
            <p class="text-lg mb-4">No saved payment methods yet.</p>
            <a href="{{ route('payment.profile.add') }}"
               class="inline-flex items-center px-5 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                Add your first card
            </a>
        </div>
    @endforelse

</div>
@endsection