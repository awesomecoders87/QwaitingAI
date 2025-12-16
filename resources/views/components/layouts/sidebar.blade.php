<aside :class="sidebarToggle ? 'translate-x-0 here lg:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed left-0 top-20 lg:top-0 z-999999 flex h-screen w-full md:w-[240px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-3 dark:border-gray-800  dark:bg-gray-900 lg:static lg:translate-x-0">
    <!-- SIDEBAR HEADER -->

    <div :class="sidebarToggle ? 'justify-center' : 'justify-between'"
        class="flex items-center gap-2 pt-3 sidebar-header pb-3 border-b border-gray-300 hidden md:block">
        <a href="{{ route('tenant.dashboard') }}" class="h-12 flex flex-1 justify-center items-center here">
            <span class="logo h-full flex items-center" :class="sidebarToggle ? 'hidden' : ''">
                <?php
                $sidebarlocation = Session::get('selectedLocation');
                $settingsidebar = App\Models\SiteDetail::viewImage('business_logo', tenant('id'), $sidebarlocation)

                ?>
        
                <img class="dark:hidden h-full" src="{{ url($settingsidebar) }}" alt="Logo" height="40" width="150" />
             
                <img class="hidden dark:block" src="{{ url($settingsidebar) }}" alt="Logo" height="40" width="150" />
            </span>

            <img class="logo-icon" :class="sidebarToggle ? 'lg:block' : 'hidden'" src="{{ url($settingsidebar) }}"
                alt="Logo" height="40" width="150" />
        </a>
    </div>

    <!-- SIDEBAR HEADER -->

    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <!-- Sidebar Menu -->
        <nav x-data="{selected: $persist('Dashboard')}">
            <!-- Menu Group -->
            <div>
                <ul class="flex flex-col gap-2 mb-6 pt-30 md:pt-0">
                    <!-- Menu Item Dashboard -->
                    @can('Dashboard')
                    <li>

                        <a data-tooltip="Dashboard" href="{{ route('tenant.dashboard') }}"
                            @click="selected = (selected === 'dashboard' ? '':'Dashboard')" class="menu-item group"
                            :class=" (selected === 'dashboard') && (page === 'dashboard') ? 'menu-item-active' : 'menu-item-inactive  dark:text-gray-300'">

                            <svg :class="(selected === 'dashboard') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                                width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z"
                                    fill="" />
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                {{ __('sidebar.Dashboard') }}
                            </span>
                        </a>
                    </li>
                    @endcan
                    <!-- Menu Item Dashboard -->

                    <!-- Menu Item Calendar -->
                    <!-- <li>
            <a
              href="{{ route('tenant.profile') }}"
              @click="selected = (selected === 'profile' ? '':'profile')"
              class="menu-item group"
              :class=" (selected === 'profile') && (page === 'profile') ? 'menu-item-active' : 'menu-item-inactive'">
              <svg
                :class="(selected === 'profile') && (page === 'profile') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.50391 4.25C8.50391 3.83579 8.83969 3.5 9.25391 3.5H15.2777C15.4766 3.5 15.6674 3.57902 15.8081 3.71967L18.2807 6.19234C18.4214 6.333 18.5004 6.52376 18.5004 6.72268V16.75C18.5004 17.1642 18.1646 17.5 17.7504 17.5H16.248V17.4993H14.748V17.5H9.25391C8.83969 17.5 8.50391 17.1642 8.50391 16.75V4.25ZM14.748 19H9.25391C8.01126 19 7.00391 17.9926 7.00391 16.75V6.49854H6.24805C5.83383 6.49854 5.49805 6.83432 5.49805 7.24854V19.75C5.49805 20.1642 5.83383 20.5 6.24805 20.5H13.998C14.4123 20.5 14.748 20.1642 14.748 19.75L14.748 19ZM7.00391 4.99854V4.25C7.00391 3.00736 8.01127 2 9.25391 2H15.2777C15.8745 2 16.4468 2.23705 16.8687 2.659L19.3414 5.13168C19.7634 5.55364 20.0004 6.12594 20.0004 6.72268V16.75C20.0004 17.9926 18.9931 19 17.7504 19H16.248L16.248 19.75C16.248 20.9926 15.2407 22 13.998 22H6.24805C5.00541 22 3.99805 20.9926 3.99805 19.75V7.24854C3.99805 6.00589 5.00541 4.99854 6.24805 4.99854H7.00391Z" fill=""></path>
              </svg>

              <span
                class="menu-item-text"
                :class="sidebarToggle ? 'lg:hidden' : ''">
                Profile
              </span>
            </a>
          </li> -->
                    @can('Staff Read')
                    <li>
                        <a data-tooltip="Staff" href="{{ route('tenant.staff.list') }}"
                            @click="selected = (selected === 'staff' ? '':'staff')" class="menu-item group"
                            :class=" (selected === 'staff') && (page === 'staff') ? 'menu-item-active' : 'menu-item-inactive dark:text-gray-300'">
                            <svg :class="(selected === 'staff') && (page === 'staff') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                                width="24" height="24" x="0" y="0" viewBox="0 0 160 160"
                                class="menu-item-icon-inactive">
                                <g>
                                    <path
                                        d="M99.924 103.055c0 11.943 9.917 21.66 22.106 21.66s22.106-9.717 22.106-21.66-9.917-21.66-22.106-21.66-22.106 9.717-22.106 21.66zm22.106-11.66c6.676 0 12.106 5.23 12.106 11.66s-5.431 11.66-12.106 11.66-12.106-5.23-12.106-11.66 5.431-11.66 12.106-11.66zM159.993 152.258c-.108-5.575-1.955-13.538-10.131-19.806-5.34-4.104-12.716-5.095-19.255-2.586a42.144 42.144 0 0 1-4.499 1.441c-2.677.69-5.508.688-8.192-.012a40.353 40.353 0 0 1-4.468-1.434c-6.535-2.5-13.909-1.508-19.246 2.587-8.18 6.271-10.027 14.233-10.136 19.809-.005.247-.006.831-.007 2.714a5 5 0 0 0 4.997 5.003h.003a5 5 0 0 0 5-4.997c.001-1.127.002-2.248.005-2.502v-.022c.096-4.917 2.189-8.977 6.223-12.069 2.602-1.995 6.276-2.449 9.563-1.191a50.258 50.258 0 0 0 5.545 1.78 26.39 26.39 0 0 0 13.225.015 52.389 52.389 0 0 0 5.575-1.787c3.306-1.27 6.974-.815 9.578 1.185 4.032 3.092 6.125 7.152 6.221 12.068v.021c.003.253.004 1.375.004 2.503a5 5 0 0 0 5 4.997h.003a5 5 0 0 0 4.997-5.003c.001-1.887 0-2.468-.005-2.714zM37.97 124.715c12.189 0 22.106-9.717 22.106-21.66s-9.917-21.66-22.106-21.66-22.106 9.717-22.106 21.66 9.917 21.66 22.106 21.66zm0-33.32c6.675 0 12.106 5.23 12.106 11.66s-5.431 11.66-12.106 11.66-12.106-5.23-12.106-11.66 5.431-11.66 12.106-11.66zM65.802 132.453c-5.34-4.104-12.716-5.095-19.255-2.586a42.072 42.072 0 0 1-4.499 1.441 16.343 16.343 0 0 1-8.192-.012 40.285 40.285 0 0 1-4.468-1.434c-6.534-2.499-13.909-1.508-19.246 2.587C1.962 138.72.115 146.682.007 152.258c-.004.243-.006.823-.007 2.714a5 5 0 0 0 4.997 5.003H5a5 5 0 0 0 5-4.997c0-1.13.002-2.252.004-2.504v-.021c.096-4.916 2.189-8.977 6.223-12.069 2.602-1.994 6.276-2.449 9.562-1.191a50.208 50.208 0 0 0 5.546 1.78 26.374 26.374 0 0 0 13.224.015 52.316 52.316 0 0 0 5.575-1.787c3.306-1.27 6.974-.815 9.578 1.185 4.033 3.092 6.125 7.151 6.222 12.068v.021c.002.253.004 1.375.004 2.503a5 5 0 0 0 5 4.997h.003a5 5 0 0 0 4.997-5.003 236.915 236.915 0 0 0-.007-2.714c-.107-5.575-1.954-13.538-10.129-19.805zM82.03 43.345c12.189 0 22.106-9.717 22.106-21.66S94.219.025 82.03.025s-22.106 9.717-22.106 21.66 9.917 21.66 22.106 21.66zm0-33.32c6.676 0 12.106 5.23 12.106 11.66s-5.431 11.66-12.106 11.66-12.106-5.23-12.106-11.66 5.431-11.66 12.106-11.66zM94.196 57.831c3.306-1.269 6.974-.816 9.578 1.185 4.032 3.092 6.125 7.152 6.221 12.068v.022c.003.257.004 1.375.004 2.502a5 5 0 0 0 5 4.997h.003a5 5 0 0 0 4.997-5.003 236.915 236.915 0 0 0-.007-2.714c-.108-5.575-1.955-13.538-10.131-19.806-5.34-4.103-12.716-5.095-19.255-2.586a42.144 42.144 0 0 1-4.499 1.441 16.343 16.343 0 0 1-8.192-.012 40.353 40.353 0 0 1-4.468-1.434c-6.534-2.5-13.909-1.508-19.246 2.587-8.18 6.271-10.027 14.233-10.136 19.809-.005.247-.006.831-.007 2.714a5 5 0 0 0 4.997 5.003h.003a5 5 0 0 0 5-4.997c.001-1.128.002-2.248.005-2.502v-.022c.096-4.917 2.189-8.977 6.223-12.069 2.602-1.994 6.276-2.449 9.563-1.191a50.258 50.258 0 0 0 5.545 1.78 26.401 26.401 0 0 0 13.225.015 52.279 52.279 0 0 0 5.577-1.787z">
                                    </path>
                                </g>
                            </svg>

                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                {{ __('sidebar.Staff') }}
                            </span>
                        </a>
                    </li>
                    @endcan

                    @can('Call Screen Read')
                    <li>
                        <a data-tooltip="Calls" href="{{ route('tenant.calls') }}" class="menu-item group"
                            :class=" (selected === 'Calls') || (page === 'calls') ? 'menu-item-active' : 'menu-item-inactive dark:text-gray-300'">
                            <svg :class="(selected === 'Calls') || (page === 'calls') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                                width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                class="menu-item-icon-inactive" viewBox="0 0 32 32">
                                <g>
                                    <path
                                        d="M30.035 22.594c-.053-.044-6.042-4.33-7.667-4.049-.781.138-1.228.67-2.123 1.737a30.54 30.54 0 0 1-.759.876 12.458 12.458 0 0 1-1.651-.672 13.7 13.7 0 0 1-6.321-6.321 12.458 12.458 0 0 1-.672-1.651c.294-.269.706-.616.882-.764 1.061-.89 1.593-1.337 1.731-2.119.283-1.619-4.005-7.613-4.049-7.667A2.289 2.289 0 0 0 7.7 1C5.962 1 1 7.436 1 8.521c0 .063.091 6.467 7.988 14.5C17.012 30.909 23.416 31 23.479 31 24.564 31 31 26.038 31 24.3a2.287 2.287 0 0 0-.965-1.706zm-6.666 6.4c-.874-.072-6.248-.781-12.967-7.382C3.767 14.857 3.076 9.468 3.007 8.633a27.054 27.054 0 0 1 4.706-5.561c.04.04.093.1.161.178a35.391 35.391 0 0 1 3.574 6.063 11.886 11.886 0 0 1-1.016.911 10.033 10.033 0 0 0-1.512 1.422l-.243.34.072.411a11.418 11.418 0 0 0 .965 2.641 15.71 15.71 0 0 0 7.248 7.247 11.389 11.389 0 0 0 2.641.966l.411.072.34-.243a10.117 10.117 0 0 0 1.428-1.518c.313-.374.732-.873.89-1.014a35.163 35.163 0 0 1 6.078 3.578c.083.07.141.124.18.159a27.031 27.031 0 0 1-5.561 4.707z"
                                        data-name="Layer 3"></path>
                                </g>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                {{ __('sidebar.Calls') }}
                            </span>
                        </a>

                    </li>
                    @endcan

                    @can('Service Read')
                    <li>
                        <a data-tooltip="Categories" href="{{ route('tenant.category-management') }}" class="menu-item group"
                            :class=" (selected === 'category-management') || (page === 'category-management') ? 'menu-item-active ' : 'menu-item-inactive dark:text-gray-300'">
                            <svg :class="(selected === 'category-management') || (page === 'category-management') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                                width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                class="menu-item-icon-inactive" viewBox="0 0 1000 1000">
                                <g transform="matrix(1.18,0,0,1.18,-90.00000000000006,-89.99910461425804)">
                                    <path
                                        d="M567.07 781.26H401.8a10 10 0 0 1-10-10V606a10 10 0 0 1 10-10h165.27a10 10 0 0 1 10 10v165.26a10 10 0 0 1-10 10zm-155.27-20h145.27V616H411.8zM567.07 342.93H401.8a10 10 0 0 1-10-10V167.66a10 10 0 0 1 10-10h165.27a10 10 0 0 1 10 10v165.27a10 10 0 0 1-10 10zm-155.27-20h145.27V177.66H411.8zM265.27 781.26H100a10 10 0 0 1-10-10V606a10 10 0 0 1 10-10h165.27a10 10 0 0 1 10 10v165.26a10 10 0 0 1-10 10zm-155.27-20h145.27V616H110zM900 781.26H734.73a10 10 0 0 1-10-10V606a10 10 0 0 1 10-10H900a10 10 0 0 1 10 10v165.26a10 10 0 0 1-10 10zm-155.27-20H890V616H744.73zM812.9 560.9a10 10 0 0 1-9.5-6.89 80.24 80.24 0 0 0-76.4-55.39H273.05A80.24 80.24 0 0 0 196.6 554a10 10 0 1 1-19-6.22 100.19 100.19 0 0 1 95.45-69.17H727a100.19 100.19 0 0 1 95.45 69.17 10 10 0 0 1-9.5 13.11z"
                                         class=""></path>
                                    <path
                                        d="M474.43 421.56h20V550.9h-20zM100 822.33h165.27v20H100zM401.8 822.33h165.27v20H401.8zM401.8 379.22h165.27v20H401.8zM734.73 822.33H900v20H734.73z">
                                    </path>
                                </g>
                            </svg>

                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                {{ __('sidebar.Services') }}
                            </span>
                        </a>

                    </li>
                    @endcan

                    @can('Counter Read')

                    <li>
                        <a data-tooltip="Counters" href="{{ route('tenant.counters') }}" class="menu-item group"
                            :class=" (selected === 'Counters') || (page === 'Counter') ? 'menu-item-active' : 'menu-item-inactive dark:text-gray-300'">
                            <svg :class="(selected === 'Counters') || (page === 'Counter') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                                width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                class="menu-item-icon-inactive" viewBox="0 0 64 64" xml:space="preserve">
                                <g transform="matrix(1.04,0,0,1.04,-1.2800000000000011,-1.2799600601196275)">
                                    <path
                                        d="M61 42.163h-2.972V38.47a9.828 9.828 0 0 0-5.86-8.979 7.394 7.394 0 1 0-7.917 0 9.83 9.83 0 0 0-5.86 8.979v3.693h-9.1v-2.257a3.406 3.406 0 0 0-3.4-3.4h-2.958v-1.8h10.36a1 1 0 0 0 1-1V14.634a1 1 0 0 0-1-1H3.211a1 1 0 0 0-1 1v19.073a1 1 0 0 0 1 1h10.355v1.8h-2.954a3.407 3.407 0 0 0-3.4 3.4v2.257H3a1 1 0 0 0-1 1v6.2a1 1 0 0 0 1 1h58a1 1 0 0 0 1-1v-6.2a1 1 0 0 0-1-1.001zm-18.184-18.9a5.394 5.394 0 1 1 5.394 5.387 5.4 5.4 0 0 1-5.394-5.392zM40.391 38.47a7.819 7.819 0 1 1 15.637 0v3.693H40.391zm-36.18-5.763V15.634h28.082v17.073h-9.36v-2.765a4.684 4.684 0 0 0-9.367 0v2.765zm11.355 1.01v-3.775a2.684 2.684 0 0 1 5.367 0V36.5h-5.367zm-6.358 6.189a1.406 1.406 0 0 1 1.4-1.4h15.28a1.405 1.405 0 0 1 1.4 1.4v2.257H9.208zM60 48.366H4v-4.2h56z"
                                        class=""></path>
                                    <path d="M27.417 20.366a1 1 0 0 0-1-1h-16.33a1 1 0 0 0 0 2h16.33a1 1 0 0 0 1-1z">
                                    </path>
                                </g>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                {{ __('sidebar.Counters') }}
                            </span>
                        </a>

                    </li>
                    @endcan

                    {{-- <li>
                        <a data-tooltip="break-reason" href="{{ route('tenant.break-reason') }}" class="menu-item group"
                            :class=" (selected === 'break-reason') || (page === 'break-reason') ? 'menu-item-active' : 'menu-item-inactive dark:text-gray-300'">
                            <svg :class="(selected === 'break-reason') || (page === 'break-reason') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                                width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="menu-item-icon-inactive">
                                <g>
                                    <path
                                        d="M8.5 20a.5.5 0 0 1-.383-.82l1.068-1.282a2.92 2.92 0 0 0 .369-3.174.499.499 0 0 1 .094-.577l1.823-1.823a1.874 1.874 0 0 0 0-2.646L5.84 14.869a.5.5 0 1 1-.678-.736l5.631-5.19a.996.996 0 0 1 1.385.028 2.874 2.874 0 0 1 0 4.061l-1.581 1.581a3.92 3.92 0 0 1-.643 3.927l-1.07 1.28A.498.498 0 0 1 8.5 20z">
                                    </path>
                                    <path
                                        d="M10.5 24h-7c-.827 0-1.5-.673-1.5-1.5v-6.596a7.542 7.542 0 0 1 2.969-5.977l2.079-1.576a.5.5 0 1 1 .605.797l-2.079 1.576A6.535 6.535 0 0 0 3 15.904V22.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5v-.998a.5.5 0 0 1 .5-.5c1.103 0 2-.897 2-2V16.75a.5.5 0 0 1 1 0v2.252A3.004 3.004 0 0 1 12 21.96v.54c0 .827-.673 1.5-1.5 1.5z">
                                    </path>
                                    <path
                                        d="M19.5 19h-2a.499.499 0 0 1-.447-.277l-.105-.211C16.481 17.58 15.543 17 14.5 17s-1.981.58-2.447 1.513l-.105.211A.503.503 0 0 1 11.5 19h-2a.5.5 0 0 1 0-1h1.692c.647-1.236 1.908-2 3.308-2s2.661.764 3.309 2H19.5c.827 0 1.5-.673 1.5-1.5v-14c0-.827-.673-1.5-1.5-1.5h-1.692C17.161 2.235 15.9 3 14.5 3s-2.661-.765-3.309-2H9.5C8.673 1 8 1.673 8 2.5v10.156a.5.5 0 0 1-1 0V2.5C7 1.122 8.122 0 9.5 0h2a.5.5 0 0 1 .447.276l.106.211C12.519 1.42 13.457 2 14.5 2s1.981-.58 2.448-1.513l.106-.211A.498.498 0 0 1 17.5 0h2C20.878 0 22 1.122 22 2.5v14c0 1.378-1.122 2.5-2.5 2.5z">
                                    </path>
                                    <path
                                        d="M8.5 7h-1a.5.5 0 0 1 0-1h1a.5.5 0 0 1 0 1zM17.73 7h-1.846a.5.5 0 0 1 0-1h1.846a.5.5 0 0 1 0 1zm-4.615 0h-1.846a.5.5 0 0 1 0-1h1.846a.5.5 0 0 1 0 1zM21.5 7h-1a.5.5 0 0 1 0-1h1a.5.5 0 0 1 0 1z">
                                    </path>
                                </g>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                {{ __('sidebar.Break Reason') }}
                            </span>
                        </a>

                    </li> --}}


                    @can('Generate Queue')
                    {{-- <li>
                        <a data-tooltip="Generate Ticket" href="{{ route('queue') }}" target="_blank" class="menu-item group
                    {{ request()->routeIs('queue') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                    <svg :class="(selected === 'queue') || (page === 'queue') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg" class="menu-item-icon-inactive">
                        <g>
                            <path
                                d="M8.5 20a.5.5 0 0 1-.383-.82l1.068-1.282a2.92 2.92 0 0 0 .369-3.174.499.499 0 0 1 .094-.577l1.823-1.823a1.874 1.874 0 0 0 0-2.646L5.84 14.869a.5.5 0 1 1-.678-.736l5.631-5.19a.996.996 0 0 1 1.385.028 2.874 2.874 0 0 1 0 4.061l-1.581 1.581a3.92 3.92 0 0 1-.643 3.927l-1.07 1.28A.498.498 0 0 1 8.5 20z">
                            </path>
                            <path
                                d="M10.5 24h-7c-.827 0-1.5-.673-1.5-1.5v-6.596a7.542 7.542 0 0 1 2.969-5.977l2.079-1.576a.5.5 0 1 1 .605.797l-2.079 1.576A6.535 6.535 0 0 0 3 15.904V22.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5v-.998a.5.5 0 0 1 .5-.5c1.103 0 2-.897 2-2V16.75a.5.5 0 0 1 1 0v2.252A3.004 3.004 0 0 1 12 21.96v.54c0 .827-.673 1.5-1.5 1.5z">
                            </path>
                            <path
                                d="M19.5 19h-2a.499.499 0 0 1-.447-.277l-.105-.211C16.481 17.58 15.543 17 14.5 17s-1.981.58-2.447 1.513l-.105.211A.503.503 0 0 1 11.5 19h-2a.5.5 0 0 1 0-1h1.692c.647-1.236 1.908-2 3.308-2s2.661.764 3.309 2H19.5c.827 0 1.5-.673 1.5-1.5v-14c0-.827-.673-1.5-1.5-1.5h-1.692C17.161 2.235 15.9 3 14.5 3s-2.661-.765-3.309-2H9.5C8.673 1 8 1.673 8 2.5v10.156a.5.5 0 0 1-1 0V2.5C7 1.122 8.122 0 9.5 0h2a.5.5 0 0 1 .447.276l.106.211C12.519 1.42 13.457 2 14.5 2s1.981-.58 2.448-1.513l.106-.211A.498.498 0 0 1 17.5 0h2C20.878 0 22 1.122 22 2.5v14c0 1.378-1.122 2.5-2.5 2.5z">
                            </path>
                            <path
                                d="M8.5 7h-1a.5.5 0 0 1 0-1h1a.5.5 0 0 1 0 1zM17.73 7h-1.846a.5.5 0 0 1 0-1h1.846a.5.5 0 0 1 0 1zm-4.615 0h-1.846a.5.5 0 0 1 0-1h1.846a.5.5 0 0 1 0 1zM21.5 7h-1a.5.5 0 0 1 0-1h1a.5.5 0 0 1 0 1z">
                            </path>
                        </g>
                    </svg>

                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        {{ __('sidebar.Generate Ticket') }}
                    </span>
                    </a>

                    </li> --}}
                    @endcan

                    @can('Display Screen')
                    {{-- <li>
                        <a data-tooltip="Display screen" href="{{ route('tenant.screens') }}" target="_blank"
                    class="menu-item group
                    {{ request()->routeIs('tenant.screens') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-400' }}">
                    <svg :class="(selected === 'display-screen-component.blade') || (page === 'display-screen-component') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                        width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        class="menu-item-icon-inactive" viewBox="0 0 409.6 409.6">
                        <g>
                            <path
                                d="M358.4 40.96H51.2C22.979 40.96 0 63.918 0 92.16V256c0 28.242 22.979 51.2 51.2 51.2h143.36v40.96H81.92c-5.652 0-10.24 4.588-10.24 10.24s4.588 10.24 10.24 10.24h245.76c5.652 0 10.24-4.588 10.24-10.24s-4.588-10.24-10.24-10.24H215.04V307.2H358.4c28.221 0 51.2-22.958 51.2-51.2V92.16c0-28.242-22.979-51.2-51.2-51.2zM389.12 256c0 16.937-13.783 30.72-30.72 30.72H51.2c-16.937 0-30.72-13.783-30.72-30.72V92.16c0-16.937 13.783-30.72 30.72-30.72h307.2c16.937 0 30.72 13.783 30.72 30.72z">
                            </path>
                        </g>
                    </svg>

                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                        {{ __('sidebar.Display Screen') }}
                    </span>
                    </a>

                    </li> --}}
                    @endcan
                  @can('Public link')
                    <li>
                        <a data-tooltip="Public Links"
                            href="javascript:void(0)"
                            class="menu-item group menu-item-inactive dark:text-gray-300" onclick="Livewire.dispatch('openPublicLinks')">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="menu-item-icon-inactive">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.50391 4.25C8.50391 3.83579 8.83969 3.5 9.25391 3.5H15.2777C15.4766 3.5 15.6674 3.57902 15.8081 3.71967L18.2807 6.19234C18.4214 6.333 18.5004 6.52376 18.5004 6.72268V16.75C18.5004 17.1642 18.1646 17.5 17.7504 17.5H16.248V17.4993H14.748V17.5H9.25391C8.83969 17.5 8.50391 17.1642 8.50391 16.75V4.25ZM14.748 19H9.25391C8.01126 19 7.00391 17.9926 7.00391 16.75V6.49854H6.24805C5.83383 6.49854 5.49805 6.83432 5.49805 7.24854V19.75C5.49805 20.1642 5.83383 20.5 6.24805 20.5H13.998C14.4123 20.5 14.748 20.1642 14.748 19.75L14.748 19ZM7.00391 4.99854V4.25C7.00391 3.00736 8.01127 2 9.25391 2H15.2777C15.8745 2 16.4468 2.23705 16.8687 2.659L19.3414 5.13168C19.7634 5.55364 20.0004 6.12594 20.0004 6.72268V16.75C20.0004 17.9926 18.9931 19 17.7504 19H16.248L16.248 19.75C16.248 20.9926 15.2407 22 13.998 22H6.24805C5.00541 22 3.99805 20.9926 3.99805 19.75V7.24854C3.99805 6.00589 5.00541 4.99854 6.24805 4.99854H7.00391Z" fill=""></path>
                            </svg>

                            <span
                                class="menu-item-text"
                                :class="sidebarToggle ? 'lg:hidden' : ''">
                                {{ __('sidebar.Public Links') }}
                            </span>
                        </a>

                    </li>
 @endcan

                    @if(App\Models\AccountSetting::where('team_id',tenant('id'))
                    ->where('location_id', Session::get('selectedLocation'))
                    ->where('slot_type', App\Models\AccountSetting::BOOKING_SLOT)->value('booking_system') == 1)
                    <li>
                        <p data-tooltip="Booking Management" @click.prevent="selected = (selected === 'booking' ? '':'booking')" class="menu-item group"
                            :class=" (selected === 'booking') || (page === 'categories-report') ? 'menu-item-active' : 'menu-item-inactive dark:text-gray-300'">

                            <svg :class="(selected === 'booking') &amp;&amp; (page === 'booking') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="menu-item-icon-inactive">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M8 2C8.41421 2 8.75 2.33579 8.75 2.75V3.75H15.25V2.75C15.25 2.33579 15.5858 2 16 2C16.4142 2 16.75 2.33579 16.75 2.75V3.75H18.5C19.7426 3.75 20.75 4.75736 20.75 6V9V19C20.75 20.2426 19.7426 21.25 18.5 21.25H5.5C4.25736 21.25 3.25 20.2426 3.25 19V9V6C3.25 4.75736 4.25736 3.75 5.5 3.75H7.25V2.75C7.25 2.33579 7.58579 2 8 2ZM8 5.25H5.5C5.08579 5.25 4.75 5.58579 4.75 6V8.25H19.25V6C19.25 5.58579 18.9142 5.25 18.5 5.25H16H8ZM19.25 9.75H4.75V19C4.75 19.4142 5.08579 19.75 5.5 19.75H18.5C18.9142 19.75 19.25 19.4142 19.25 19V9.75Z" fill=""></path>
                            </svg>

                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                {{ __('sidebar.Booking Management') }}
                            </span>

                            <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'booking') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
                                width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </p>

                        <div class="overflow-hidden transform translate"
                            :class="(selected === 'booking') ? 'block' :'hidden'">
                            <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                <li>
                                    <a href="{{ route('book-appointment') }}" target="_blank"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('book-appointment') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Online Booking') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('booking-list') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('booking-list') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Booking List') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('appointment-calendar') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('appointment-calendar') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Booking Calendar View') }}
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>
                    @endif
                     @can('Reports')

                    <li>
                        <a data-tooltip="all-report" href="{{ route('tenant.all-report') }}" class="menu-item group"
                            :class=" (selected === 'all-report') || (page === 'Counter') ? 'menu-item-active' : 'menu-item-inactive dark:text-gray-300'">
                            <svg :class="(selected === 'Reports') || (page === 'categories-report') ? 'menu-item-icon-active'  : 'menu-item-icon-inactive'"
                                width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 36 36">
                                <g
                                    transform="matrix(1.0899999999999996,0,0,1.0899999999999996,-1.619999999999994,-1.6194457483291558)">
                                    <path
                                        d="M4 33.99h2.5c1.103 0 2-.898 2-2v-3.46c0-1.103-.897-2-2-2H4c-1.103 0-2 .897-2 2v3.46c0 1.102.897 2 2 2zm0-5.46h2.5l.002 3.46H4zM12.5 33.99H15c1.103 0 2-.898 2-2v-12.4c0-1.102-.897-2-2-2h-2.5c-1.103 0-2 .898-2 2v12.4c0 1.102.897 2 2 2zm0-14.4H15l.002 12.4H12.5zM21 21.07c-1.103 0-2 .897-2 2v8.92c0 1.102.897 2 2 2h2.5c1.103 0 2-.898 2-2v-8.92c0-1.103-.897-2-2-2zm0 10.92v-8.92h2.5l.002 8.92zM34 31.99V12.57c0-1.103-.897-2-2-2h-2.5c-1.103 0-2 .897-2 2v19.42c0 1.102.897 2 2 2H32c1.103 0 2-.898 2-2zm-4.5-19.42H32l.002 19.42H29.5zM5.947 16.737l7.546-7.546a1.003 1.003 0 0 1 1.134-.197l5.956 2.801a3 3 0 0 0 3.398-.592l7.487-7.486a1 1 0 1 0-1.414-1.414L22.567 9.79a1 1 0 0 1-1.132.197l-5.956-2.803a3.012 3.012 0 0 0-3.4.594l-7.546 7.546a1 1 0 1 0 1.414 1.414z">
                                    </path>
                                </g>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                              {{ __('sidebar.All Reports') }}
                            </span>
                        </a>

                    </li>
                    @endcan

                    @can('Reports')

                    <li>
                        <a data-tooltip="Analytics" href="{{ route('tenant.analytics') }}" class="menu-item group"
                            :class=" (selected === 'analytics') || (page === 'analytics') ? 'menu-item-active' : 'menu-item-inactive dark:text-gray-300'">
                           <svg :class="(selected === 'Reports') || (page === 'categories-report') ? 'menu-item-icon-active'  : 'menu-item-icon-inactive'"
                                width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 36 36">
                                <g
                                    transform="matrix(1.0899999999999996,0,0,1.0899999999999996,-1.619999999999994,-1.6194457483291558)">
                                    <path
                                        d="M4 33.99h2.5c1.103 0 2-.898 2-2v-3.46c0-1.103-.897-2-2-2H4c-1.103 0-2 .897-2 2v3.46c0 1.102.897 2 2 2zm0-5.46h2.5l.002 3.46H4zM12.5 33.99H15c1.103 0 2-.898 2-2v-12.4c0-1.102-.897-2-2-2h-2.5c-1.103 0-2 .898-2 2v12.4c0 1.102.897 2 2 2zm0-14.4H15l.002 12.4H12.5zM21 21.07c-1.103 0-2 .897-2 2v8.92c0 1.102.897 2 2 2h2.5c1.103 0 2-.898 2-2v-8.92c0-1.103-.897-2-2-2zm0 10.92v-8.92h2.5l.002 8.92zM34 31.99V12.57c0-1.103-.897-2-2-2h-2.5c-1.103 0-2 .897-2 2v19.42c0 1.102.897 2 2 2H32c1.103 0 2-.898 2-2zm-4.5-19.42H32l.002 19.42H29.5zM5.947 16.737l7.546-7.546a1.003 1.003 0 0 1 1.134-.197l5.956 2.801a3 3 0 0 0 3.398-.592l7.487-7.486a1 1 0 1 0-1.414-1.414L22.567 9.79a1 1 0 0 1-1.132.197l-5.956-2.803a3.012 3.012 0 0 0-3.4.594l-7.546 7.546a1 1 0 1 0 1.414 1.414z">
                                    </path>
                                </g>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                             {{ __('sidebar.analytics') }}
                            </span>
                        </a>

                    </li>
                    @endcan
                    @can('Reports')
                    <li>
                        <!-- <p data-tooltip="Reports" @click.prevent="selected = (selected === 'Reports' ? '':'Reports')" class="menu-item group"
                            :class=" (selected === 'Reports') || (page === 'categories-report') ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg :class="(selected === 'Reports') || (page === 'categories-report') ? 'menu-item-icon-active'  : 'menu-item-icon-inactive'"
                                width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 36 36">
                                <g
                                    transform="matrix(1.0899999999999996,0,0,1.0899999999999996,-1.619999999999994,-1.6194457483291558)">
                                    <path
                                        d="M4 33.99h2.5c1.103 0 2-.898 2-2v-3.46c0-1.103-.897-2-2-2H4c-1.103 0-2 .897-2 2v3.46c0 1.102.897 2 2 2zm0-5.46h2.5l.002 3.46H4zM12.5 33.99H15c1.103 0 2-.898 2-2v-12.4c0-1.102-.897-2-2-2h-2.5c-1.103 0-2 .898-2 2v12.4c0 1.102.897 2 2 2zm0-14.4H15l.002 12.4H12.5zM21 21.07c-1.103 0-2 .897-2 2v8.92c0 1.102.897 2 2 2h2.5c1.103 0 2-.898 2-2v-8.92c0-1.103-.897-2-2-2zm0 10.92v-8.92h2.5l.002 8.92zM34 31.99V12.57c0-1.103-.897-2-2-2h-2.5c-1.103 0-2 .897-2 2v19.42c0 1.102.897 2 2 2H32c1.103 0 2-.898 2-2zm-4.5-19.42H32l.002 19.42H29.5zM5.947 16.737l7.546-7.546a1.003 1.003 0 0 1 1.134-.197l5.956 2.801a3 3 0 0 0 3.398-.592l7.487-7.486a1 1 0 1 0-1.414-1.414L22.567 9.79a1 1 0 0 1-1.132.197l-5.956-2.803a3.012 3.012 0 0 0-3.4.594l-7.546 7.546a1 1 0 1 0 1.414 1.414z">
                                    </path>
                                </g>
                            </svg>

                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                {{ __('sidebar.Reports') }}
                            </span>

                            <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'Reports') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
                                width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </p> -->

                        <div class="overflow-hidden transform translate hidden"
                            :class="(selected === 'Reports') ? 'block' :'hidden'">
                            <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                <li>
                                    <a href="{{ route('tenant.all-report') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.all-report') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.All Reports') }}
                                    </a>
                                </li>
                                   {{-- <li>
                        <a data-tooltip="break-request" href="{{ route('tenant.break-request') }}" class="menu-item group"
                            :class=" (selected === 'break-request') || (page === 'break-request') ? 'menu-item-active' : 'menu-item-inactive dark:text-gray-300'">
                            <svg :class="(selected === 'break-request') || (page === 'break-request') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                                width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                class="menu-item-icon-inactive" viewBox="0 0 409.6 409.6">
                                <g>
                                    <path
                                        d="M358.4 40.96H51.2C22.979 40.96 0 63.918 0 92.16V256c0 28.242 22.979 51.2 51.2 51.2h143.36v40.96H81.92c-5.652 0-10.24 4.588-10.24 10.24s4.588 10.24 10.24 10.24h245.76c5.652 0 10.24-4.588 10.24-10.24s-4.588-10.24-10.24-10.24H215.04V307.2H358.4c28.221 0 51.2-22.958 51.2-51.2V92.16c0-28.242-22.979-51.2-51.2-51.2zM389.12 256c0 16.937-13.783 30.72-30.72 30.72H51.2c-16.937 0-30.72-13.783-30.72-30.72V92.16c0-16.937 13.783-30.72 30.72-30.72h307.2c16.937 0 30.72 13.783 30.72 30.72z">
                                    </path>
                                </g>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                {{ __('sidebar.Staff Break Request') }}
                            </span>
                        </a>

                    </li> --}}
                               <li>
                                    <a href="{{ route('tenant.break-request') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.break-request') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                         {{ __('sidebar.Staff Break Request') }}
                                    </a>
                                </li>
                                <!-- <li>
                                    <a href="{{ route('tenant.monthly-report') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.monthly-report') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                        Monthly Report
                                    </a>
                                </li> -->
                                <li>
                                    <a href="{{ route('tenant.categories-report') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.categories-report') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Services Report') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('tenant.sub-categories-report') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.sub-categories-report') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Sub Services Report') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('tenant.overview-per-day-report') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.overview-per-day-report') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Overview Per Day') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('tenant.staff-performance-reports') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.staff-performance-reports') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Staff Performance Report') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('tenant.overview-per-time-period-reports') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.overview-per-time-period-reports') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Overview Per Time Period Reports') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('tenant.feedback-reports') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.feedback-reports') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Feedback Report') }}
                                    </a>
                                </li>
                                <!-- <li>
                                    <a href="{{ route('tenant.statistics-report') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.statistics-report') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                        Statistics Reports
                                    </a>
                                </li> -->
                                <!-- <li>
                                    <a href="{{ route('tenant.feedback-statistics-report') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.feedback-statistics-report') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                        Feedback Statistics Reports
                                    </a>
                                </li> -->
                                <!-- <li>
                                    <a href="{{ route('tenant.activity.logs') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.activity.logs') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                        Activity Logs
                                    </a>
                                </li> -->
                                <!-- <li>
                                    <a href="{{ route('tenant.sms-transactions-report') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.sms-transactions-report') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                        SMS Transactions
                                    </a>
                                </li> -->
                                <!-- <li>
                                    <a href="{{ route('tenant.payment-report') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.payment-report') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                        Revenue Report
                                    </a>
                                </li> -->
                            </ul>
                        </div>
                    </li>
                    @endcan

                    @if(auth()->user()->hasRole('superadmin'))
                    @can('Reports')
                    <li>
                        <p data-tooltip="Branch Reports" @click.prevent="selected = (selected === 'Branch Reports' ? '':'Branch Reports')" class="menu-item group"
                            :class=" (selected === 'Branch Reports') || (page === 'categories-report') ? 'menu-item-active' : 'menu-item-inactive dark:text-gray-300'">
                            <svg :class="(selected === 'Branch Reports') || (page === 'categories-report') ? 'menu-item-icon-active'  : 'menu-item-icon-inactive'"
                                width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 36 36">
                                <g
                                    transform="matrix(1.0899999999999996,0,0,1.0899999999999996,-1.619999999999994,-1.6194457483291558)">
                                    <path
                                        d="M4 33.99h2.5c1.103 0 2-.898 2-2v-3.46c0-1.103-.897-2-2-2H4c-1.103 0-2 .897-2 2v3.46c0 1.102.897 2 2 2zm0-5.46h2.5l.002 3.46H4zM12.5 33.99H15c1.103 0 2-.898 2-2v-12.4c0-1.102-.897-2-2-2h-2.5c-1.103 0-2 .898-2 2v12.4c0 1.102.897 2 2 2zm0-14.4H15l.002 12.4H12.5zM21 21.07c-1.103 0-2 .897-2 2v8.92c0 1.102.897 2 2 2h2.5c1.103 0 2-.898 2-2v-8.92c0-1.103-.897-2-2-2zm0 10.92v-8.92h2.5l.002 8.92zM34 31.99V12.57c0-1.103-.897-2-2-2h-2.5c-1.103 0-2 .897-2 2v19.42c0 1.102.897 2 2 2H32c1.103 0 2-.898 2-2zm-4.5-19.42H32l.002 19.42H29.5zM5.947 16.737l7.546-7.546a1.003 1.003 0 0 1 1.134-.197l5.956 2.801a3 3 0 0 0 3.398-.592l7.487-7.486a1 1 0 1 0-1.414-1.414L22.567 9.79a1 1 0 0 1-1.132.197l-5.956-2.803a3.012 3.012 0 0 0-3.4.594l-7.546 7.546a1 1 0 1 0 1.414 1.414z">
                                    </path>
                                </g>
                            </svg>

                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                {{ __('sidebar.Branch Reports') }}
                            </span>

                            <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'Branch Reports') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
                                width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </p>

                        <div class="overflow-hidden transform translate"
                            :class="(selected === 'Branch Reports') ? 'block' :'hidden'">
                            <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                <li>
                                    <a href="{{ route('tenant.queue-overview-report') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.queue-overview-report') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Queue Overview') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('tenant.overall-overview-report') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.overall-overview-report') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Overview') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('tenant.branches-monthly-report') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.branches-monthly-report') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Branches Monthly Report') }}
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>
                    @endcan
                    @endif
                    @can('Integration')
                    <li>
                        <a data-tooltip="Integrations" href="{{ route('tenant.integrations') }}" class="menu-item group"
                            :class=" (selected === 'Integrations') || (page === 'integrations') ? 'menu-item-active' : 'menu-item-inactive dark:text-gray-300'">
                            <svg :class="(selected === 'Integrations') || (page === 'integrations') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                                width="24" height="24" viewBox="0 0 32 32" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g transform="matrix(1.04,0,0,1.04,-0.6399995923042319,-0.6402819967269906)">
                                    <g data-name="Layer 2">
                                        <path
                                            d="M29.21 11.84a3.92 3.92 0 0 1-3.09-5.3 1.84 1.84 0 0 0-.55-2.07 14.75 14.75 0 0 0-4.4-2.55 1.85 1.85 0 0 0-2.09.58 3.91 3.91 0 0 1-6.16 0 1.85 1.85 0 0 0-2.09-.58 14.82 14.82 0 0 0-4.1 2.3 1.86 1.86 0 0 0-.58 2.13 3.9 3.9 0 0 1-3.25 5.36 1.85 1.85 0 0 0-1.62 1.49A14.14 14.14 0 0 0 1 16a14.32 14.32 0 0 0 .19 2.35 1.85 1.85 0 0 0 1.63 1.55A3.9 3.9 0 0 1 6 25.41a1.82 1.82 0 0 0 .51 2.18 14.86 14.86 0 0 0 4.36 2.51 2 2 0 0 0 .63.11 1.84 1.84 0 0 0 1.5-.78 3.87 3.87 0 0 1 3.2-1.68 3.92 3.92 0 0 1 3.14 1.58 1.84 1.84 0 0 0 2.16.61 15 15 0 0 0 4-2.39 1.85 1.85 0 0 0 .54-2.11 3.9 3.9 0 0 1 3.13-5.39 1.85 1.85 0 0 0 1.57-1.52A14.5 14.5 0 0 0 31 16a14.35 14.35 0 0 0-.25-2.67 1.83 1.83 0 0 0-1.54-1.49zm-.42 6.24a5.91 5.91 0 0 0-4.65 8 12.69 12.69 0 0 1-3.3 2 5.87 5.87 0 0 0-4.67-2.29 5.94 5.94 0 0 0-4.76 2.43 13.07 13.07 0 0 1-3.58-2.06 5.87 5.87 0 0 0-.29-5.26 5.93 5.93 0 0 0-4.44-2.94A13.67 13.67 0 0 1 3 16a12.28 12.28 0 0 1 .22-2.31 5.9 5.9 0 0 0 4.37-2.82 5.86 5.86 0 0 0 .46-5.14 12.79 12.79 0 0 1 3.37-1.9 5.92 5.92 0 0 0 9.16 0 12.76 12.76 0 0 1 3.63 2.11 5.92 5.92 0 0 0 4.59 7.86A12.77 12.77 0 0 1 29 16a13.46 13.46 0 0 1-.17 2.08z">
                                        </path>
                                        <path d="M16 10a6 6 0 1 0 6 6 6 6 0 0 0-6-6zm0 10a4 4 0 1 1 4-4 4 4 0 0 1-4 4z">
                                        </path>
                                    </g>
                                </g>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                {{ __('sidebar.Integrations') }}
                            </span>
                        </a>

                    </li>
                    @endcan
                    @if(Auth::user()->hasAnyPermission(['Booking Setting', 'Call Screen Setting','Color Settings','Display Settings','Reverb Settings','Logo Update','Ticket Screen Setting','QR Code Setting','Feedback Setting','Message Template Edit','Location','Form Field Read','Term and Condition','Category Read']))
                    <li>
                        <p data-tooltip="Settings" @click.prevent="selected = (selected === 'Settings' ? '':'Settings')" class="menu-item group"
                            :class=" (selected === 'Settings') || (page === 'settings') ? 'menu-item-active' : 'menu-item-inactive dark:text-gray-300'">
                            <svg :class="(selected === 'Settings') || (page === 'settings') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                                width="24" height="24" viewBox="0 0 32 32" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g transform="matrix(1.04,0,0,1.04,-0.6399995923042319,-0.6402819967269906)">
                                    <g data-name="Layer 2">
                                        <path
                                            d="M29.21 11.84a3.92 3.92 0 0 1-3.09-5.3 1.84 1.84 0 0 0-.55-2.07 14.75 14.75 0 0 0-4.4-2.55 1.85 1.85 0 0 0-2.09.58 3.91 3.91 0 0 1-6.16 0 1.85 1.85 0 0 0-2.09-.58 14.82 14.82 0 0 0-4.1 2.3 1.86 1.86 0 0 0-.58 2.13 3.9 3.9 0 0 1-3.25 5.36 1.85 1.85 0 0 0-1.62 1.49A14.14 14.14 0 0 0 1 16a14.32 14.32 0 0 0 .19 2.35 1.85 1.85 0 0 0 1.63 1.55A3.9 3.9 0 0 1 6 25.41a1.82 1.82 0 0 0 .51 2.18 14.86 14.86 0 0 0 4.36 2.51 2 2 0 0 0 .63.11 1.84 1.84 0 0 0 1.5-.78 3.87 3.87 0 0 1 3.2-1.68 3.92 3.92 0 0 1 3.14 1.58 1.84 1.84 0 0 0 2.16.61 15 15 0 0 0 4-2.39 1.85 1.85 0 0 0 .54-2.11 3.9 3.9 0 0 1 3.13-5.39 1.85 1.85 0 0 0 1.57-1.52A14.5 14.5 0 0 0 31 16a14.35 14.35 0 0 0-.25-2.67 1.83 1.83 0 0 0-1.54-1.49zm-.42 6.24a5.91 5.91 0 0 0-4.65 8 12.69 12.69 0 0 1-3.3 2 5.87 5.87 0 0 0-4.67-2.29 5.94 5.94 0 0 0-4.76 2.43 13.07 13.07 0 0 1-3.58-2.06 5.87 5.87 0 0 0-.29-5.26 5.93 5.93 0 0 0-4.44-2.94A13.67 13.67 0 0 1 3 16a12.28 12.28 0 0 1 .22-2.31 5.9 5.9 0 0 0 4.37-2.82 5.86 5.86 0 0 0 .46-5.14 12.79 12.79 0 0 1 3.37-1.9 5.92 5.92 0 0 0 9.16 0 12.76 12.76 0 0 1 3.63 2.11 5.92 5.92 0 0 0 4.59 7.86A12.77 12.77 0 0 1 29 16a13.46 13.46 0 0 1-.17 2.08z">
                                        </path>
                                        <path d="M16 10a6 6 0 1 0 6 6 6 6 0 0 0-6-6zm0 10a4 4 0 1 1 4-4 4 4 0 0 1-4 4z">
                                        </path>
                                    </g>
                                </g>
                            </svg>

                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                {{ __('sidebar.Settings') }}
                            </span>

                            <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'Settings') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
                                width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </p>

                        <div class="overflow-hidden transform translate"
                            :class="(selected === 'Settings') ? 'block' :'hidden'">
                            <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">

                                 <li>
                                    <a href="{{ route('tenant.automation') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.automation') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Automation') }}
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('tenant.addons') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.addons') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Addons') }}
                                    </a>
                                </li>

                                @can('Booking Setting')
                                <li>
                                    <a href="{{ route('tenant.booking-settings') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.booking-settings') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Booking Settings') }}
                                    </a>
                                </li>
                                @endcan

                                <li>
                                    <a href="{{ route('tenant.break-request') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.break-request') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                         {{ __('sidebar.Staff Break Request') }}
                                    </a>
                                </li>

                                  <li>
                                    <a href="{{ route('tenant.break-reason') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.break-reason') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                         {{ __('sidebar.Break Reason') }}
                                    </a>
                                </li>


                                @can('Service Read')
                                <li>
                                    <a href="{{ route('category-level') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('category-level') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Service Level') }}
                                    </a>
                                </li>
                                @endcan
                                @can('Call Screen Setting')
                                <li>
                                    <a href="{{ route('tenant.call-screen-settings') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.call-screen-settings') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Call Screen Settings') }}
                                    </a>
                                </li>
                                @endcan

                                @can('Color Settings')
                                <li>
                                    <a href="{{ route('tenant.color-settings') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.color-settings') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Color Settings') }}
                                    </a>
                                </li>
                                @endcan

                                @can('Display Settings')
                                <li>
                                    <a href="{{ route('tenant.screen-templates') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.screen-templates') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Screen Templates and Settings') }}
                                    </a>
                                </li>
                                @endcan
                                @can('Reverb Settings')
                                <li>
                                    <a href="{{ route('tenant.reverb-settings') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.reverb-settings') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Reverb Settings') }}
                                    </a>
                                </li>
                                @endcan

                                @can('Logo Update')
                                <li>
                                    <a href="{{ route('tenant.logo-update') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.logo-update') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Logo Update') }}
                                    </a>
                                </li>
                                @endcan
                                @can('Ticket Screen Setting')
                                <li>
                                    <a href="{{ route('tenant.ticket-generate-settings') }}"
                                        class="menu-dropdown-item group {{ (request()->routeIs('tenant.ticket-generate-settings') || request()->routeIs('tenant.ticket-screen-settings')) ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Ticket Screen Settings') }}
                                    </a>
                                </li>
                                @endcan

                                @can('QR Code Setting')
                                <li>
                                    <a href="{{ route('tenant.qr-code') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.qr-code') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Qr Code') }}
                                    </a>
                                </li>
                                @endcan
                                @can('Feedback Setting')
                                <li>
                                    <a href="{{ route('tenant.feedback-form') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.feedback-form') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Feedback Form') }}
                                    </a>
                                </li>
                                @endcan
                                @can('Feedback Setting')
                                <li>
                                    <a href="{{ route('tenant.feedback-settings') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.feedback-settings') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Feedback Settings') }}
                                    </a>
                                </li>
                                @endcan
                                @can('Booking Setting')
                                <li>
                                    <a href="{{ route('tenant.mobile.app.setting') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.mobile.app.setting') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Mobile API Settings') }}
                                    </a>
                                </li>
                                @endcan
                                @can('Message Template Edit')
                                <li>
                                    <a href="{{ route('tenant.notification-settings') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.notification-settings') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Notification Settings') }}
                                    </a>
                                </li>
                                @endcan


                                @can('Form Field Read')
                                <li>
                                    <a href="{{ route('tenant.form-fields') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.form-fields') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Manage Form Fields') }}
                                    </a>
                                </li>
                                @endcan
                                @can('Location')
                                <li>
                                    <a href="{{ route('tenant.locations') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.locations') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Locations') }}
                                    </a>
                                </li>
                                @endcan

                                
                                @can('Location')
                                <li>
                                    <a href="{{ route('tenant.location-map') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.location-map') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Location Map') }}
                                    </a>
                                </li>
                                @endcan

                                @can('Location')
                                <li>
                                    <a href="{{ route('tenant.location-map-settings') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.location-map-settings') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Location Map API Key') }}
                                    </a>
                                </li>
                                @endcan


                                <li>
                                    <a href="{{ route('tenant.language-settings') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.language-settings') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Language Settings') }}
                                    </a>
                                </li>


                                @can('Term and Condition')
                                <li>
                                    <a href="{{ route('tenant.terms-conditions') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.terms-conditions') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                        {{ __('sidebar.Terms & Conditions') }}
                                    </a>
                                </li>
                                @endcan
                                <li>
                                    <a href="{{ route('tenant.country-manager') }}"
                                        class="menu-dropdown-item group
           {{ request()->routeIs('tenant.country-manager') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}">
                                         {{ __('sidebar.Allowed Country') }}
                                    </a>
                                </li>
                            </ul>

                        </div>
                    </li>

                    <li>
                        <p data-tooltip="Help" @click.prevent="selected = (selected === 'Help' ? '':'Help')" class="menu-item group"
                            :class=" (selected === 'Help') || (page === 'Help') ? 'menu-item-active' : 'menu-item-inactive dark:text-gray-300'">
                            <svg :class="(selected === 'Help') || (page === 'help') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                            width="24" height="24" viewBox="0 0 32 32" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g transform="matrix(1.04,0,0,1.04,-0.6399995923042319,-0.6402819967269906)">
                                <g data-name="Help Support">
                                    <circle cx="16" cy="16" r="13" stroke="currentColor" stroke-width="2" fill="none"/>
                                    <path d="M16 11C17.6569 11 19 12.3431 19 14C19 15.6569 17.5 16.5 17.5 17.5C17.5 17.7761 17.2761 18 17 18H15C14.7239 18 14.5 17.7761 14.5 17.5C14.5 15.5 16 15 16 14C16 13.4477 15.5523 13 15 13C14.4477 13 14 13.4477 14 14H12C12 12.3431 13.3431 11 15 11H16Z"
                                        fill="currentColor"/>
                                    <rect x="15" y="20" width="2" height="2" rx="1" fill="currentColor"/>
                                </g>
                            </g>
                        </svg>


                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                {{ __('sidebar.Help') }}
                            </span>

                             <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'Settings') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
                                width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                        </p>

                        <div class="overflow-hidden transform translate"
                            :class="(selected === 'Help') ? 'block' :'hidden'">
                            <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">

                                <li>
                                    <a href="https://help.qwaiting.com/en"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.addons') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}" target="_blank">
                                        {{ __('sidebar.Help Docs') }}
                                    </a>
                                </li>

                                <li>
                                    <a href="https://help.qwaiting.com/en/collections/10041739-product-updates"
                                        class="menu-dropdown-item group {{ request()->routeIs('tenant.addons') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive dark:text-gray-300' }}" target="_blank">
                                        {{ __("sidebar.What's New") }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif
                    <!-- Menu Item Pages -->
                </ul>
            </div>

        </nav>
        <!-- Sidebar Menu -->


    </div>

</aside>
<div id="tooltip" class="tooltip-side"></div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuItems = document.querySelectorAll('.menu-item');
        const tooltip = document.getElementById('tooltip');

        menuItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                const tooltipText = this.getAttribute('data-tooltip');
                const rect = this.getBoundingClientRect();

                tooltip.textContent = tooltipText;
                tooltip.style.top = `${rect.top}px`;
                tooltip.classList.add('tooltip-visible');
            });

            item.addEventListener('mouseleave', function() {
                tooltip.classList.remove('tooltip-visible');
            });
        });
    });
</script>
