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
<div class="relative p-0 bg-white z-1 dark:bg-gray-900 sm:p-0 h-screen h-sm pt-sm-40">
    <div class="relative flex-row-reverse flex flex-col justify-center w-full h-screen  h-sm dark:bg-gray-900 sm:p-0">
    
        <!-- Form -->
        <div class="flex flex-col flex-1 w-full lg:w-1/2">
   
          <div
            class="flex flex-col md:justify-center flex-1 w-full max-w-md mx-auto"
          >
            <div>
              <div class="mb-5 sm:mb-8">
                <div class="mb-4">
                    <a href="">
                        <img src="{{url($logo)}}" alt="Logo" width="140" height="100" />
                    </a>
                </div>
                <h1
                  class="mb-2 font-semibold text-gray-800  text-xl  dark:text-white/90"
                >
                Reset Password
                </h1>
               
              </div>
              <div>
        
                <form method="POST" action="{{ route('tenant.password.update') }}" autocomplete="off">
                  @csrf
                  <input type="hidden" name="token" value="{{ $token }}">
                  <div class="space-y-5">
                    <!-- Email -->
                    <div>
                      <!-- <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >
                        <span class="text-error-500">*</span>
                      </label> -->
                      <input type="hidden" name="email" value="{{ $email }}">
                      <!-- <input
                        type="text"
                        id="email"
                        name="email"
                        placeholder="Your email address"
                        readonly
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" value="{{ old('email') }}" 
                      /> -->
                      @error('email')
                <span class="text-sm text-red-500" style="color:red;font-weight:bold;"> {{ $message }} </span>
            @enderror
                    </div>
                    <!-- Password -->
                    <div>
                      <!-- <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >
                        <span class="text-error-500">*</span>
                      </label> -->
                      <div class="relative">
                        <input
                          type="password"
                          placeholder="Password"
                          class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" name="password"
                        />
                        @error('password')
                <span class="text-sm text-red-500" style="color:red;font-weight:bold;"> {{ $message }} </span>
            @enderror
                      </div>
                    </div>
                   <!-- confirm Password -->    
                    <div>
                      <!-- <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >
                       Confirm Password<span class="text-error-500">*</span>
                      </label> -->
                      <div class="relative">
                        <input
                          type="password"
                          placeholder="Confirm Password"
                          class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" name="password_confirmation"
                        />
                        @error('password_confirmation')
                <span class="text-sm text-red-500" style="color:red;font-weight:bold;"> {{ $message }} </span>
            @enderror
                      </div>
                    </div>
                 
                   
                    <!-- Button -->
                    <div>
                      <button type="submit"
                        class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600"
                      >
                      Reset Password
                      </button>
                    </div>
                  </div>
                </form>
               
              </div>
            </div>
          </div>
        </div>

        @include('tenant/auth/right-side')
        
      </div>
    </div>
@endsection

