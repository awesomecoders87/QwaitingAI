<?php 
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Auth;
use App\Models\Addon;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;

class TwoFactorController extends Controller
{
    public function index()
    {
        
        $addon = Addon::where('team_id', Auth::user()->team_id)
                        ->where('user_id', Auth::id())
                        ->first();

        $google2fa = new Google2FA();
        if(isset($addon)){
            $secretKey = $addon?->secretKey;
        }else{

            $secretKey = $google2fa->generateSecretKey();
        }

        $name = tenant('name');

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            $name,
            Auth::user()->email,
            $secretKey
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCode = $writer->writeString($qrCodeUrl);
     
        return view('auth.2fa', compact('addon','secretKey','qrCode'));
    }

    public function store(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $google2fa = new Google2FA();

         $addon = Addon::where('team_id', Auth::user()->team_id)
                        ->where('user_id', Auth::id())
                        ->first();


        if ($google2fa->verifyKey($addon?->secretKey, $request->code)) {
            session(['2fa_passed' => true]);
            return redirect()->intended('/dashboard'); // or your home route
        }

        return back()->withErrors(['code' => 'Invalid code']);
    }
}