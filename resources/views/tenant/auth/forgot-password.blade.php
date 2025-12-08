 


@extends('layouts.app')

@section('content')
@php 
      $url = request()->url();
      $headerPage = App\Models\SiteDetail::FIELD_BUSINESS_LOGO;

      if ( strpos( $url, 'mobile/queue' ) !== false ) {
        $headerPage = App\Models\SiteDetail::FIELD_MOBILE_LOGO;
      }

  $logo =  App\Models\SiteDetail::viewImage($headerPage);
  @endphp
<div class="relative p-0 bg-white z-1 dark:bg-gray-900 sm:p-0 h-screen h-sm  pt-sm-40">
    <div class="relative flex-row-reverse flex flex-col justify-center w-full h-screen  h-sm dark:bg-gray-900 sm:p-0">
    
        <!-- Form -->
        <div class="flex flex-col flex-1 w-full lg:w-1/2">
        
          <div
            class="flex flex-col md:justify-center flex-1 w-full max-w-md mx-auto"
          >
            <div class="p-6">
              <div class="mb-5 sm:mb-8">
             <div class="mb-4 md:text-left text-center">
                      <a href="" class="inline-block">
                        <img src="{{url($logo)}}" alt="Logo" width="140" height="100" />
                    </a>
                </div>
              <h1 class="mb-2 font-semibold text-gray-800 text-xl dark:text-white/90  md:text-left text-center">
                Forgot Your Password?
              </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400  md:text-left text-center">
                {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                </p>
              </div>
              @if (session('status'))
                  <div style="color: green;">
                      {{ session('status') }}
                  </div>
              @endif
              <div>
        
                <form method="POST" action="{{ route('tenant.password.email') }}">
                  @csrf
                  <div class="space-y-5">
                    <!-- Email -->
                    <div>
                      <!-- <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >
                        <span class="text-error-500">*</span>
                      </label> -->
                      <input
                        type="text"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                    />
                      @error('email')
                <span class="text-sm text-red-500" style="color:red;font-weight:bold;"> {{ $message }} </span>
            @enderror
                    </div>
                    <!-- Password -->
  
                    <!-- Button -->
                    <div>
                      <button type="submit"
                        class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600"
                      >
                      {{ __('Send Reset Link') }}
                      </button>
                    </div>
                  </div>
                </form>

                <div class="w-full max-w-md pt-5 pb-5 mx-auto text-center">
                <a href="{{ route('tenant.login') }}" class="inline-flex items-center text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                  <svg class="stroke-current" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M12.7083 5L7.5 10.2083L12.7083 15.4167" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                  Back to login page
                </a>
              </div>
                
              </div>
            </div>
          </div>
        </div>
        @include('tenant/auth/right-side')
        
      </div>
    </div>
@endsection
