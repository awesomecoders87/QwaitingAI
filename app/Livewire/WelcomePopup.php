<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Welcome Popup Component
 * 
 * This component displays a welcome popup modal that appears only once after login.
 * Once closed (via skip, close, or explore dashboard), it will never show again.
 * 
 * The popup can only be closed by clicking the close button or action buttons.
 * Clicking outside the popup will NOT close it.
 * 
 * @package App\Livewire
 * @author QWaiting Development Team
 */
class WelcomePopup extends Component
{
    public $show = false;

    /**
     * Mount the component
     * Automatically show popup when component is mounted (page load)
     * Only show if user is logged in AND hasn't dismissed it before
     */
    public function mount()
    {
        // Show popup automatically when user is logged in and hasn't dismissed it
        if (Auth::check() && !Session::has('welcome_popup_dismissed')) {
            $this->show = true;
        }
    }

    /**
     * Handle navigation event
     * Don't show popup on navigation if it was already dismissed
     */
    public function handleNavigation()
    {
        // Don't show popup if it was already dismissed
        if (Auth::check() && !Session::has('welcome_popup_dismissed')) {
            $this->show = true;
        }
    }

    /**
     * Close the welcome popup
     * This method is called when user clicks close button or action buttons
     * Marks the popup as dismissed so it won't show again
     */
    public function close()
    {
        $this->show = false;
        // Mark as dismissed in session so it won't show again
        Session::put('welcome_popup_dismissed', true);
    }

    /**
     * Redirect to dashboard
     * This method is called when user clicks "Explore Dashboard" button
     * Marks the popup as dismissed so it won't show again
     */
    public function redirectToDashboard()
    {
        $this->show = false;
        // Mark as dismissed in session so it won't show again
        Session::put('welcome_popup_dismissed', true);
        return redirect()->route('tenant.dashboard');
    }

    /**
     * Render the component view
     */
    public function render()
    {
        return view('livewire.welcome-popup');
    }
}

