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
aaaaaaaaaaaaaaaaaaaa
<div class="relative p-0 bg-white z-1 dark:bg-gray-900 sm:p-0 h-screen h-sm pt-sm-40">
    <div class="relative flex-row-reverse flex flex-col justify-center w-full h-screen  h-sm dark:bg-gray-900 sm:p-0">

        <!-- Form -->
        <div class="flex flex-col flex-1 w-full lg:w-1/2">

            <div class="flex flex-col md:justify-center flex-1 w-full max-w-md mx-auto">
                <div class="p-6">
                    <div class="mb-5 sm:mb-8">
                        <!-- Logo -->
                        <div class="mb-4 md:text-left text-center">
                            <a href="" class="inline-block">
                                <img src="{{url($logo)}}" alt="Logo" width="140" height="100" />
                            </a>
                        </div>
                        @if (session('status'))
                        <div style="color: green;">
                            {{ session('status') }}
                        </div>
                        @endif
                        <h1 class="mb-2 font-semibold text-gray-800 text-xl dark:text-white/90  md:text-left text-center">
                            Login for Qwaiting
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400  md:text-left text-center">
                            Enter your email and password to sign in!
                        </p>
                    </div>
                    <div>

                        @if (session('success'))
                        <div class="mb-4 p-3 text-sm text-green-800 bg-green-100 border border-green-300 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                        <form method="POST" action="{{ route('tenant.loginstore') }}">
                            @csrf
                            <div class="space-y-5">
                                <!-- Email or Username -->
                                <div>
                                    <!-- <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                      <span class="text-error-500">*</span>
                                    </label> -->
                                    <input type="text" id="login" name="login" placeholder="Username or Email"
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                    @error('login')
                                    <span class="text-sm text-red-500" style="color:red;font-weight:bold;">
                                        {{ $message }} </span>
                                    @enderror
                                </div>
                                <!-- Password -->
                                <div>
                                    <!-- <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        <span class="text-error-500">*</span>
                                    </label> -->
                                    <div class="relative">
                                        <input type="password" id="password" placeholder="Your Password"
                                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                            name="password" />
                                            <button type="button" onclick="togglePassword()"
            class="absolute inset-y-0 top_6 right-3 flex items-center text-gray-500 dark:text-gray-400">
            <span id="eyeIcon" class="absolute right-1 z-30  cursor-pointer">
                <!-- Eye Open Icon -->
                <svg id="eyeOpen" class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10 13.86c-2.77 0-5.14-1.73-6.08-4.16C4.86 7.27 7.23 5.54 10 5.54s5.14 1.73 6.08 4.16c-.94 2.43-3.31 4.16-6.08 4.16zM10 4.04c-3.52 0-6.5 2.27-7.58 5.42-.06.16-.06.33 0 .49 1.08 3.15 4.06 5.42 7.58 5.42s6.5-2.27 7.58-5.42c.06-.16.06-.33 0-.49C16.5 6.31 13.52 4.04 10 4.04zm-.01 3.8c-1.03 0-1.86.83-1.86 1.86s.83 1.86 1.86 1.86h.01c1.03 0 1.86-.83 1.86-1.86s-.83-1.86-1.86-1.86h-.01z"></path>
                </svg>

                <!-- Eye Closed Icon -->
                <svg id="eyeClosed" class="fill-gray-500 dark:fill-gray-400 hidden" width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.64 3.58a.85.85 0 00-1.06 1.06l1.28 1.28C3.75 6.84 2.89 8.06 2.42 9.46a.84.84 0 000 .49c1.08 3.15 4.06 5.42 7.58 5.42 1.26 0 2.45-.29 3.5-.84l1.86 1.86a.85.85 0 001.06-1.06L4.64 3.58zM12.36 13.42L10.45 11.5a2 2 0 01-1.31.06L5.92 6.98c-.88.71-1.58 1.65-2 2.72 1.08 3.15 4.06 5.42 7.58 5.42 1.26 0 2.46-.29 3.5-.84L12.36 13.42zm3.71-3.71c-.3.75-.74 1.44-1.28 2.05l1.06 1.06a7.66 7.66 0 002.7-3.78c.06-.16.06-.33 0-.49-1.08-3.15-4.06-5.42-7.58-5.42-1.26 0-2.46.14-3.5.43l1.23 1.23c.41-.09.84-.14 1.27-.14 2.77 0 5.14 1.73 6.08 4.16z"></path>
                </svg>
            </span>
        </button>
                                        @error('password')
                                        <span class="text-sm text-red-500"
                                            style="color:red;font-weight:bold;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Checkbox -->
                                <div class="flex items-center justify-between">
                                    <div x-data="{ checkboxToggle: false }">
                                        <label for="checkboxLabelOne"
                                            class="flex items-center text-sm font-normal text-gray-700 cursor-pointer select-none dark:text-gray-400">
                                            <div class="relative">
                                                <input type="checkbox" id="checkboxLabelOne" class="sr-only" name="remember"
                                                    @change="checkboxToggle = !checkboxToggle" />
                                                <label for="checkboxLabelOne"
                                                    class="mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]" id="loginlabel">
                                                    <span :class="checkboxToggle ? '' : 'opacity-0'" >

                                                    </span>
                                                </label>
                                            </div>
                                            Keep me sign in
                                        </label>
                                    </div>
                                    <a href="{{ route('tenant.password.request') }}"
                                        class="text-sm text-brand-500 hover:text-brand-600">Forgot password?</a>
                                </div>
                                <!-- Button -->
                                <div>
                                    <button type="submit"
                                        class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg  shadow-theme-xs bg-brand-500 hover:bg-brand-600">
                                        Sign In
                                    </button>
                                </div>
                            </div>
                        </form>
                        @if(isset($addon) && $addon->office_enabled == 1)
                        <div class="mt-5">

                    <a
                      href="{{ url('office365/login') }}"

                      ><button type="submit"
                                        class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg  shadow-theme-xs bg-brand-500 hover:bg-brand-600">Login With Office 365</button> </a
                    >

                </div>
                @endif
                    </div>
                </div>
            </div>
        </div>

        @include('tenant/auth/right-side')

    </div>
</div>

<script>
function togglePassword() {
    let passwordField = document.getElementById("password");
    let eyeOpen = document.getElementById("eyeOpen");
    let eyeClosed = document.getElementById("eyeClosed");

    if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeOpen.classList.add("hidden");
        eyeClosed.classList.remove("hidden");
    } else {
        passwordField.type = "password";
        eyeOpen.classList.remove("hidden");
        eyeClosed.classList.add("hidden");
    }
}
</script>
@endsection
