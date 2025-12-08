@extends('layouts.app')

@section('content')
<style>
    .main{
        position: relative;
        top:150px;
    }
</style>
<div class="main">
    <div class="max-w-xl mx-auto p-6 bg-white shadow rounded">
        <h2 class="text-2xl font-semibold mb-4">Two-Factor Authentication</h2>

        {{-- <div class="text-green-600 mb-4">Google Authenticator is <strong>enabled</strong>.</div> --}}
  
        <!-- <div class="border rounded-lg p-4 bg-gray-50">
                    <ol class="list-decimal list-inside text-sm text-gray-600">
                        <li>Use a time-based one-time password (TOTP) authenticator app.</li>
                        <li>Scan the QR code or enter this code:</li>
                    </ol>

                    <div>{!! $qrCode !!}</div>

                    <div class="font-mono mt-2 text-lg bg-white p-2 border rounded text-gray-700">
                        Use Code : {{ $secretKey }}
                    </div>

                  
                </div> -->

        @if ($errors->any())
            <div class="text-red-600 mb-4">{{ $errors->first('code') }}</div>
        @endif

        <form method="POST" action="{{ route('2fa.store') }}">
            @csrf
            <label class="block mb-2">Enter the 6-digit code from Google Authenticator:</label>
            <input type="text" name="code" class="border rounded w-full p-2 mb-4" required autofocus>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Verify</button>
        </form>
    </div>
</div>
@endsection


