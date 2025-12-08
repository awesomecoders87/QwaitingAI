<header  x-data="{menuToggle: false}"
  class="sticky top-0 flex w-full bg-white border-gray-200 z-99999 dark:border-gray-800 dark:bg-gray-900 lg:border-b"
>
  <div
    class="flex flex-col items-center justify-between flex-grow lg:flex-row ltr:lg:px-6 rtl:lg:pl-15" style="width:100%"
  >
    <div
      class="flex-grow flex items-center justify-between w-full gap-2 px-3 py-3 border-b border-gray-200 dark:border-gray-800 sm:gap-4 lg:justify-normal lg:border-b-0 lg:px-0 lg:py-4"
    >
      <!-- Hamburger Toggle BTN -->
      <button
        :class="sidebarToggle ? 'lg:bg-transparent dark:lg:bg-transparent bg-gray-100 dark:bg-gray-800' : ''"
        class="flex items-center justify-center w-10 h-10 text-gray-500 border-gray-200 rounded-lg z-99999 dark:border-gray-800 dark:text-gray-400 lg:h-11 lg:w-11 lg:border"
        @click.stop="sidebarToggle = !sidebarToggle"
      >
        <svg
          class="hidden fill-current lg:block"
          width="16"
          height="12"
          viewBox="0 0 16 12"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z"
            fill=""
          />
        </svg>

        <svg
          :class="sidebarToggle ? 'hidden' : 'block lg:hidden'"
          class="fill-current lg:hidden"
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M3.25 6C3.25 5.58579 3.58579 5.25 4 5.25L20 5.25C20.4142 5.25 20.75 5.58579 20.75 6C20.75 6.41421 20.4142 6.75 20 6.75L4 6.75C3.58579 6.75 3.25 6.41422 3.25 6ZM3.25 18C3.25 17.5858 3.58579 17.25 4 17.25L20 17.25C20.4142 17.25 20.75 17.5858 20.75 18C20.75 18.4142 20.4142 18.75 20 18.75L4 18.75C3.58579 18.75 3.25 18.4142 3.25 18ZM4 11.25C3.58579 11.25 3.25 11.5858 3.25 12C3.25 12.4142 3.58579 12.75 4 12.75L12 12.75C12.4142 12.75 12.75 12.4142 12.75 12C12.75 11.5858 12.4142 11.25 12 11.25L4 11.25Z"
            fill=""
          />
        </svg>

        <!-- cross icon -->
        <svg
          :class="sidebarToggle ? 'block lg:hidden' : 'hidden'"
          class="fill-current"
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
            fill=""
          />
        </svg>
      </button>
      <!-- Hamburger Toggle BTN -->
      <?php
       $sidebarlocation = Session::get( 'selectedLocation' );
        $settingsidebar = App\Models\SiteDetail::viewImage('business_logo',tenant('id'),$sidebarlocation)

        ?>
      <a href="/" class="lg:hidden flex-1" >
        <img class="dark:hidden" src="{{ url($settingsidebar) }}" alt="Logo" height="40" width="150" style="max-height:50px;width:auto"/>
        <img
          class="hidden dark:block border border-red-400"
          src="{{ url($settingsidebar) }}"
          alt="Logo" height="40" width="150" style="max-height:50px;width:auto"
        />
      </a>

      <!-- Application nav menu button -->
       <button id="theme-toggle" @click="darkMode = !darkMode" class="p-3 rounded-full bg-gray-200 dark:bg-gray-700">
    <!-- Light mode icon -->
    <svg id="sun-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 block dark:hidden stroke-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M12 3v1m0 16v1m8.66-12.66l-.707.707M4.047 19.953l-.707-.707M21 12h-1M4 12H3m16.66 6.66l-.707-.707M4.047 4.047l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>

    <!-- Dark mode icon -->
    <svg id="moon-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z" />
    </svg>
  </button>
      <div  class="items-center justify-between gap-4 px-5 py-2 lg:flex lg:justify-end lg:px-2 lg:shadow-none md:absolute md:right-0 rtl:right-auto rtl:left-0">

      <!-- User Area -->
      <div
        class="relative here"
        x-data="{ dropdownOpen: false }"
        @click.outside="dropdownOpen = false"
      >
        <a
          class="flex items-center text-gray-700 dark:text-gray-400 justify-between"
          href="#"
          @click.prevent="dropdownOpen = ! dropdownOpen"
        >

          <span class="w-10 h-10 rounded-full bg-gray-300 flex items-center"><svg width="40" height="40" x="0" y="0" viewBox="0 0 53 53" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="m18.613 41.552-7.907 4.313a7.106 7.106 0 0 0-1.269.903A26.377 26.377 0 0 0 26.5 53c6.454 0 12.367-2.31 16.964-6.144a7.015 7.015 0 0 0-1.394-.934l-8.467-4.233a3.229 3.229 0 0 1-1.785-2.888v-3.322c.238-.271.51-.619.801-1.03a19.482 19.482 0 0 0 2.632-5.304c1.086-.335 1.886-1.338 1.886-2.53v-3.546c0-.78-.347-1.477-.886-1.965v-5.126s1.053-7.977-9.75-7.977-9.75 7.977-9.75 7.977v5.126a2.644 2.644 0 0 0-.886 1.965v3.546c0 .934.491 1.756 1.226 2.231.886 3.857 3.206 6.633 3.206 6.633v3.24a3.232 3.232 0 0 1-1.684 2.833z" style="" fill="#ffffff" data-original="#e7eced" class="" opacity="1"></path><path d="M26.953.004C12.32-.246.254 11.414.004 26.047-.138 34.344 3.56 41.801 9.448 46.76a7.041 7.041 0 0 1 1.257-.894l7.907-4.313a3.23 3.23 0 0 0 1.683-2.835v-3.24s-2.321-2.776-3.206-6.633a2.66 2.66 0 0 1-1.226-2.231v-3.546c0-.78.347-1.477.886-1.965v-5.126S15.696 8 26.499 8s9.75 7.977 9.75 7.977v5.126c.54.488.886 1.185.886 1.965v3.546c0 1.192-.8 2.195-1.886 2.53a19.482 19.482 0 0 1-2.632 5.304c-.291.411-.563.759-.801 1.03V38.8c0 1.223.691 2.342 1.785 2.888l8.467 4.233a7.05 7.05 0 0 1 1.39.932c5.71-4.762 9.399-11.882 9.536-19.9C53.246 12.32 41.587.254 26.953.004z" style="" fill="#556080" data-original="#556080" class=""></path></g></svg></span>
          <!-- <span class="block mr-1 font-medium text-theme-sm"> {{ auth()->user()->name }} </span> -->

          <!-- <svg
            :class="dropdownOpen && 'rotate-180'"
            class="stroke-gray-500 dark:stroke-gray-400"
            width="18"
            height="20"
            viewBox="0 0 18 20"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M4.3125 8.65625L9 13.3437L13.6875 8.65625"
              stroke=""
              stroke-width="1.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg> -->
        </a>

        <!-- Dropdown Start -->
       <div
  x-show="dropdownOpen"
  class="absolute right-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white  shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark  rtl:right-auto rtl:left-0"
>
  <div class="p-3 px-5 bg-blue-600 rounded-t-2xl">
    <span class="block font-medium text-white text-theme-xl dark:text-white">
      {{ Auth::user()->name }}
    </span>
    <span class="mt-0.5 block text-theme-xs text-white dark:text-white">
      {{ Auth::user()->email }}
    </span>
  </div>

  <ul class="flex flex-col gap-1 pt-4 pb-3 p-3 border-b border-gray-200 dark:border-gray-800">
    @can('Profile Setting')
    <li>
      <a
        href="{{ route('tenant.profile') }}"
        class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
      >
        <svg
          class="fill-gray-500 group-hover:fill-gray-700 dark:fill-gray-400 dark:group-hover:fill-gray-300"
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z"
            fill=""
          />
        </svg>
        {{ __('text.Profile') }}
      </a>
    </li>
    @endcan
   @if(Auth::user()->is_admin == 1)
    <li>
      <a
        href="{{ route('buy-subcription') }}"
        class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
      >
      <svg class="fill-gray-500 group-hover:fill-gray-700 dark:fill-gray-400 dark:group-hover:fill-gray-300" x="0" y="0" viewBox="0 0 8.467 8.467" style="enable-background:new 0 0 512 512" xml:space="preserve" height="22" width="22"><g transform="matrix(1.05,0,0,1.05,-0.21169998794794065,-0.21169999986886978)"><path d="M4.233.265A3.973 3.973 0 0 0 .265 4.233a3.973 3.973 0 0 0 3.968 3.97 3.973 3.973 0 0 0 3.97-3.97A3.973 3.973 0 0 0 4.232.265zm0 .529a3.437 3.437 0 0 1 3.442 3.44 3.44 3.44 0 0 1-3.442 3.441 3.437 3.437 0 0 1-3.44-3.442 3.436 3.436 0 0 1 3.44-3.44zM4.23 2.118a.265.265 0 0 0-.26.268v.223c-.427.08-.793.355-.793.832 0 .33.15.598.35.754s.426.224.624.29c.199.067.37.13.467.206.098.075.144.14.144.338 0 .44-1.056.44-1.056 0a.265.265 0 1 0-.529 0c0 .476.366.752.793.832v.222a.265.265 0 1 0 .529 0V5.86c.427-.08.793-.356.793-.832 0-.331-.15-.599-.35-.754-.2-.156-.426-.226-.624-.292-.199-.066-.37-.129-.468-.204-.097-.076-.144-.14-.144-.338 0-.441 1.056-.441 1.056 0a.265.265 0 1 0 .53 0c0-.476-.366-.751-.793-.832v-.223a.265.265 0 0 0-.27-.268z" fill=""></path></g></svg>
        {{ __('report.Billing') }}
      </a>
    </li>
    @endif
    @can('Change Password')
    <li>
      <a
        href="{{ route('tenant.change-password') }}"
        class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
      >
        <svg
          class="fill-gray-500 group-hover:fill-gray-700 dark:fill-gray-400 dark:group-hover:fill-gray-300"
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M12 2C8.68629 2 6 4.68629 6 8V10H5C3.89543 10 3 10.8954 3 12V19C3 20.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19V12C21 10.8954 20.1046 10 19 10H18V8C18 4.68629 15.3137 2 12 2ZM8 8C8 5.79086 9.79086 4 12 4C14.2091 4 16 5.79086 16 8V10H8V8ZM5 12H19V19H5V12ZM12 14C11.4477 14 11 14.4477 11 15V17C11 17.5523 11.4477 18 12 18C12.5523 18 13 17.5523 13 17V15C13 14.4477 12.5523 14 12 14Z"
            fill=""
          />
        </svg>
        {{ __('text.Change Password') }}
      </a>
    </li>
    @endcan
  </ul>

 <form id="logoutForm" action="{{ route('tenant.logout') }}" method="post" class="px-3 pb-4">
    @csrf
    <button
        type="button" {{-- changed to button so it doesn't auto-submit --}}
        id="logoutBtn"
        class="flex w-full items-center gap-3 px-3 py-2 mt-3 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
    >
        <svg
            class="fill-gray-500 group-hover:fill-gray-700 dark:group-hover:fill-gray-300"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M15.1007 19.247C14.6865 19.247 14.3507 18.9112 14.3507 18.497L14.3507 14.245H12.8507V18.497C12.8507 19.7396 13.8581 20.747 15.1007 20.747H18.5007C19.7434 20.747 20.7507 19.7396 20.7507 18.497L20.7507 5.49609C20.7507 4.25345 19.7433 3.24609 18.5007 3.24609H15.1007C13.8581 3.24609 12.8507 4.25345 12.8507 5.49609V9.74501L14.3507 9.74501V5.49609C14.3507 5.08188 14.6865 4.74609 15.1007 4.74609L18.5007 4.74609C18.9149 4.74609 19.2507 5.08188 19.2507 5.49609L19.2507 18.497C19.2507 18.9112 18.9149 19.247 18.5007 19.247H15.1007ZM3.25073 11.9984C3.25073 12.2144 3.34204 12.4091 3.48817 12.546L8.09483 17.1556C8.38763 17.4485 8.86251 17.4487 9.15549 17.1559C9.44848 16.8631 9.44863 16.3882 9.15583 16.0952L5.81116 12.7484L16.0007 12.7484C16.4149 12.7484 16.7507 12.4127 16.7507 11.9984C16.7507 11.5842 16.4149 11.2484 16.0007 11.2484L5.81528 11.2484L9.15585 7.90554C9.44864 7.61255 9.44847 7.13767 9.15547 6.84488C8.86248 6.55209 8.3876 6.55226 8.09481 6.84525L3.52309 11.4202C3.35673 11.5577 3.25073 11.7657 3.25073 11.9984Z"
                fill=""
            />
        </svg>
        {{ __('text.Sign out') }}
    </button>
</form>
</div>

        <!-- Dropdown End -->
      </div>

      <!-- User Area -->
    </div>
    </div>
    <div class="flex md:pr-8" style="align-items: center;">


            @if((Auth::check() && Auth::user()->hasRole('Admin')) || (Auth::check() && Auth::user()->team_id == 264))
        <div class="md:pr-2" >
         <livewire:location-selector />
        </div>
         @endif

        <div class="mr-2 rtl:ml-2">
         <livewire:language-selector />
        </div>


      </div>




  </div>


</header>
<script>
    const html = document.documentElement;
    const btn = document.getElementById('theme-toggle');

    btn.addEventListener('click', () => {
      html.classList.toggle('dark');
    });
  </script>
