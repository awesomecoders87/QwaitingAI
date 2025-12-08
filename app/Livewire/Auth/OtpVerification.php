<?php
namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtp;
use Livewire\Attributes\On;

#[Layout('components.layouts.custom-layout')]
class OtpVerification extends Component
{
    public string $otp = '';
    public int $resendCount = 0;
    public bool $resendDisabled = true;
    public int $countdown = 30;

    public function mount()
    {
    
        if (!session()->has('otp_user_id')) {
            return redirect()->route('tenant.login');
        }

            session()->forget(['otp_resend_count']);
        $this->startCountdown();
    }

    public function startCountdown()
    {
        $this->resendDisabled = true;
        $this->countdown = 30;

        $this->dispatch('start-countdown');
    }
    

    public function decrementCountdown()
{
    if ($this->countdown > 0) {
        $this->countdown--;
    }
}
  public function resendOtp()
{
    $userId = session('otp_user_id');

    // Check if blocked due to max attempts
    if (session()->has('otp_resend_blocked_until')) {
        if (now()->lessThan(session('otp_resend_blocked_until'))) {
            $this->addError('otp', 'Too many attempts. Please wait before trying again.');
            return;
        } else {
            // Expired block, reset
            session()->forget('otp_resend_blocked_until');
            session()->forget('otp_resend_count');
        }
    }

    // Get current count
    $resendCount = session('otp_resend_count', 0);

    if ($resendCount >= 3) {
        session(['otp_resend_blocked_until' => now()->addMinutes(6)]);
        $this->addError('otp', 'Max resend attempts reached. Please wait 6 minutes.');
        return;
    }

    // ✅ Expire all previous OTPs
    OtpCode::where('user_id', $userId)
        ->where('used', false)
        ->update(['used' => true]);

    // Generate and store new OTP
    $otp = rand(100000, 999999);

    OtpCode::create([
        'user_id' => $userId,
        'code' => $otp,
        'expires_at' => now()->addMinutes(10),
    ]);

    if(!empty(Auth::user()->email)){
        Mail::to(Auth::user()->email)->send(new SendOtp($otp));
    }
    // Mail::to("aksh@stelleninfotech.in")->send(new SendOtp($otp));

    // Increment resend count
    session(['otp_resend_count' => $resendCount + 1]);

    $this->startCountdown();
}


    public function verifyOtp()
{
    $this->validate([
        'otp' => 'required|digits:6',
    ]);

    $otpCode = OtpCode::where('user_id', session('otp_user_id'))
        ->where('code', $this->otp)
        ->where('used', false)
        ->first();

    if (!$otpCode) {
        $this->addError('otp', 'Incorrect OTP. Please try again.');
        return;
    }

    if ($otpCode->expires_at->isPast()) {
        $this->addError('otp', 'OTP has expired. Please request a new one.');
        return;
    }

    $otpCode->update(['used' => true]);

    // Clear session flags
    session()->forget(['otp_user_id', 'otp_resend_count', 'otp_resend_blocked_until']);
    session(['verify_otp' => true]);

    Auth::loginUsingId(session('otp_user_id'));

    return redirect()->route('tenant.dashboard');
}
    public function render()
    {
        return view('livewire.auth.otp-verification');
    }
}
