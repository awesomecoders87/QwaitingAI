<div>
@if($showWarning)
    <div class="bg-yellow-100 text-yellow-800 border-l-4 border-yellow-500 p-4 rounded mb-4">
        <strong>⚠️ Your subscription will expire on {{ $expiryDate }}.</strong>
        <a href="{{ url('/buy-subscription') }}" class="underline text-blue-600 ml-2">Renew Now</a>
    </div>
@endif
</div>