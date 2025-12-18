<?php
/**
 * Quick script to check if Reverb server is running and configured correctly
 * Run: php check-reverb.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Reverb Configuration Check ===\n\n";

// Check BROADCAST_DRIVER
$broadcastDriver = env('BROADCAST_DRIVER', 'null');
echo "BROADCAST_DRIVER: " . ($broadcastDriver ?: 'not set (defaults to null)') . "\n";
if ($broadcastDriver !== 'reverb') {
    echo "⚠️  WARNING: BROADCAST_DRIVER should be set to 'reverb'\n";
} else {
    echo "✅ BROADCAST_DRIVER is set to 'reverb'\n";
}

echo "\n";

// Check Reverb environment variables
$reverbKey = env('REVERB_APP_KEY');
$reverbSecret = env('REVERB_APP_SECRET');
$reverbAppId = env('REVERB_APP_ID');
$reverbHost = env('REVERB_HOST', '127.0.0.1');
$reverbPort = env('REVERB_PORT', 8080);
$reverbScheme = env('REVERB_SCHEME', 'http');

echo "REVERB_APP_KEY: " . ($reverbKey ? '✅ Set' : '❌ Not set') . "\n";
echo "REVERB_APP_SECRET: " . ($reverbSecret ? '✅ Set' : '❌ Not set') . "\n";
echo "REVERB_APP_ID: " . ($reverbAppId ? '✅ Set' : '❌ Not set') . "\n";
echo "REVERB_HOST: $reverbHost\n";
echo "REVERB_PORT: $reverbPort\n";
echo "REVERB_SCHEME: $reverbScheme\n";

echo "\n";

// Check if Reverb server is running
$socket = @fsockopen($reverbHost, $reverbPort, $errno, $errstr, 2);
if ($socket) {
    echo "✅ Reverb server appears to be running on $reverbHost:$reverbPort\n";
    fclose($socket);
} else {
    echo "❌ Reverb server is NOT running on $reverbHost:$reverbPort\n";
    echo "   Error: $errstr ($errno)\n";
    echo "\n";
    echo "To start Reverb server, run:\n";
    echo "   php artisan reverb:start\n";
}

echo "\n=== End of Check ===\n";

