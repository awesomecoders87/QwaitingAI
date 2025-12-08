<div>

    @if(1== 1)
    <div class="block p-4">
        <div x-data="{ activeTab: 'summary' }" class="border-b border-gray-200 dark:border-gray-700">
            <div class="mb-4 border-b border-gray-200">
                <ul class="flex text-sm font-semibold text-center text-gray-500 tabs-nav">
                    <li class="mr-2">

                        <a href="javascript:void(0)"
                            :class="activeTab === 'summary' ? 'inline-block px-4 py-2 text-white bg-blue-600 rounded-lg active-tab active' : 'inline-block px-4 py-2 rounded-lg hover:text-gray-600 hover:bg-gray-100 bg-white tex-blue-600 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500'"

                            @click="activeTab = 'summary'">{{ __('text.summary') }}</a>
                    </li>
                    <li class="mr-2">
                        <a href="javascript:void(0)"
                            :class="activeTab === 'walkin' ? 'inline-block px-4 py-2 text-white bg-blue-600 rounded-lg active-tab active' : 'inline-block px-4 py-2 rounded-lg hover:text-gray-600 hover:bg-gray-100 bg-white tex-blue-600 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500'"

                            @click="activeTab = 'walkin'">{{ __('text.Walk-In') }}</a>
                    </li>
                    @if($bookingsystem)
                    <li class="mr-2">
                        <a href="javascript:void(0)"
                            :class="activeTab === 'appointments' ? 'inline-block px-4 py-2 text-white bg-blue-600 rounded-lg active-tab active' : 'inline-block px-4 py-2 rounded-lg hover:text-gray-600 hover:bg-gray-100 bg-white tex-blue-600 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500'"

                            @click="activeTab = 'appointments'">{{ __('text.Appointments') }}</a>
                    </li>
                    @endif

                    @if($activeUsersList)
                    <li class="mr-2">
                        <a href="javascript:void(0)"
                            :class="activeTab === 'users' ? 'inline-block px-4 py-2 text-white bg-blue-600 rounded-lg active-tab active' : 'inline-block px-4 py-2 rounded-lg hover:text-gray-600 hover:bg-gray-100 bg-white tex-blue-600 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500'"

                            @click="activeTab = 'users'">{{ __('text.Active Users') }}</a>
                    </li>
                    @endif
                </ul>
            </div>

            <!-- Summary Tab Content -->
            <div x-show="activeTab === 'summary'" style="display:block">
                <div class="max-w-7xll mx-auto">

                    <!-- Date Filter -->
                    <div class="flex items-center space-x-4 mb-6">
                        <div>
                            <label class="block">{{ __('text.start date') }}</label>
                            <input type="date" wire:model.live="summaryfromSelectedDate" onclick="this.showPicker()" class="bg-white text-gray-900 border border-gray-300 rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Start Date">
                        </div>
                        <div>
                            <label class="block">{{ __('text.end date') }}</label>
                            <input type="date" wire:model.live="summarytoSelectedDate"
                                onclick="this.showPicker()" class="bg-white border border-gray-300 text-gray-900 rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="End Date">
                        </div>
                    </div>

                    <!-- KPI Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                        <div class="bg-white p-4 rounded shadow dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                            <div class="flex justify-between mb-3 items-center flex-row-reverse">
                                <div class="h-sm bg-purple-200 rounded-md avatar avatar-md">
                                    <i class="ri-group-lines"><svg width="33" height="24" viewBox="0 0 33 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path opacity="0.587821" d="M24.6473 6.66699C26.8726 6.66709 28.6766 8.45791 28.6766 10.667C28.6764 12.8759 26.8725 14.6669 24.6473 14.667C22.422 14.667 20.6182 12.876 20.618 10.667C20.618 8.45785 22.4219 6.66699 24.6473 6.66699ZM12.5584 0C15.5255 0 17.9313 2.38764 17.9315 5.33301C17.9315 8.27853 15.5256 10.667 12.5584 10.667C9.59145 10.6667 7.18634 8.27837 7.18634 5.33301C7.18652 2.3878 9.59156 0.000256905 12.5584 0Z" fill="#5C4AE4" />
                                            <path d="M12.5359 13.3333C18.9666 13.3333 24.2505 16.3908 24.6443 22.9329C24.66 23.1935 24.6442 24.0001 23.6355 24.0003H1.44802C1.11117 24.0003 0.443098 23.2786 0.471456 22.932C0.992199 16.5687 6.19468 13.3335 12.5359 13.3333ZM24.1121 16.0023C28.687 16.0521 32.4225 18.347 32.7039 23.1995C32.7152 23.395 32.7038 24.0002 31.9754 24.0003H26.7967C26.7967 20.9997 25.7973 18.2303 24.1121 16.0023Z" fill="#5C4AE4" />
                                        </svg></i>
                                </div>
                                <div>
                                    <p class="text-base text-gray mb-3">{{ __('text.Total Visits') }}</p>
                                    <h3 class="text-4xl font-semibold">{{ $totalVisits }}</h3>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                {{-- <div>
                                    <span class="text-purple">8.5%</span> <span class=" text-gray inline-flex leading-3 ltr:mr-1 rtl:ml-1">{{ __('text.This Month') }}</span> <!--span class="text-[0.75rem] text-textmuted">Visitor Today</!--span-->
                                </div> --}}

                            </div>
                        </div>
                        <div class="bg-white p-4 rounded shadow dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                            <div class="flex justify-between mb-3 items-center  flex-row-reverse">
                                <div class="h-sm bg-green-light rounded-md avatar avatar-md">
                                    <i class="ri-walk-lines"><svg width="29" height="28" viewBox="0 0 29 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3.61934 24.8889H27.1242C27.9897 24.8889 28.6912 25.5853 28.6912 26.4444C28.6912 27.3036 27.9897 28 27.1242 28H2.05235C1.18692 28 0.485352 27.3036 0.485352 26.4444V1.55556C0.485352 0.696446 1.18692 0 2.05235 0C2.91777 0 3.61934 0.696446 3.61934 1.55556V24.8889Z" fill="#4AD991" />
                                            <path opacity="0.5" d="M9.46335 18.175C8.87145 18.8018 7.8798 18.8335 7.24844 18.2459C6.61708 17.6584 6.58509 16.674 7.17699 16.0472L13.0532 9.82498C13.6257 9.21884 14.5769 9.16627 15.2136 9.7056L19.8514 13.6344L25.8942 6.03611C26.4304 5.36181 27.4158 5.24673 28.0951 5.77907C28.7743 6.31141 28.8903 7.28959 28.354 7.96389L21.3025 16.8306C20.7518 17.5231 19.7316 17.6227 19.0555 17.05L14.3168 13.0358L9.46335 18.175Z" fill="#4AD991" />
                                        </svg></i>
                                </div>
                                <div>
                                    <p class="text-base text-gray mb-3">{{ __('text.Served Visits') }}</p>
                                    <h3 class="text-4xl font-semibold">{{ $servedVisits }}</h3>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                {{-- <div>

                                    <span class="text-green">4.3%</span> <span class=" text-gray inline-flex leading-3 ltr:mr-1 rtl:ml-1">{{ __('text.Visitors today') }}</span>
                                </div> --}}

                            </div>
                        </div>
                        <!-- <div class="bg-white p-4 rounded shadow">
                             <div class="flex justify-between mb-3 items-center flex-row-reverse">
                                <div class="h-sm bg-indigo-200 rounded-md avatar avatar-md">
                                    <i class="ri-group-line"></i>
                                </div>
                                <div>
                                <p class="text-base text-gray mb-3">{{ __('text.No Show') }}</p>
                                <h3 class="text-4xl font-semibold">{{ $noShow }}</h3>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div>

                                    <span class="badge px-2 inline-flex leading-3 text-xs py-1 font-medium ltr:mr-1 rtl:ml-1">{{ __('text.This Month') }}</span>
                                </div>

                             </div>
                        </div> -->
                        <div class="bg-white p-4 rounded shadow dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                            <div class="flex justify-between mb-3 items-center flex-row-reverse">
                                <div class="h-sm bg-red-100 rounded-md avatar avatar-md">
                                    <i class="ri-calendar-close-lines"><svg width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M4.72398 1.37783C4.72398 0.616873 5.34086 0 6.10181 0H7.41403C8.17498 0 8.79186 0.616873 8.79186 1.37783V2.62443H20.2081V1.37783C20.2081 0.616873 20.825 0 21.586 0H22.8982C23.6591 0 24.276 0.616873 24.276 1.37783V2.62443H25.6538C27.5019 2.62443 29 4.12256 29 5.97059V25.6538C29 27.5019 27.5019 29 25.6538 29H3.34615C1.49813 29 0 27.5019 0 25.6538V5.97059C0 4.12256 1.49813 2.62443 3.34615 2.62443H4.72398V1.37783ZM24.276 4.06787V5.31448C24.276 6.07543 23.6591 6.69231 22.8982 6.69231H21.586C20.825 6.69231 20.2081 6.07543 20.2081 5.31448V4.06787H8.79186V5.31448C8.79186 6.07543 8.17498 6.69231 7.41403 6.69231H6.10181C5.34086 6.69231 4.72398 6.07543 4.72398 5.31448V4.06787H3.34615C2.29531 4.06787 1.44344 4.91976 1.44344 5.97059V7.34842H27.5566V5.97059C27.5566 4.91976 26.7047 4.06787 25.6538 4.06787H24.276ZM6.16742 1.44344V5.24887H7.34842V1.44344H6.16742ZM1.44344 8.79186V25.6538C1.44344 26.7047 2.29531 27.5566 3.34615 27.5566H25.6538C26.7047 27.5566 27.5566 26.7047 27.5566 25.6538V8.79186H1.44344ZM21.6516 5.24887V1.44344H22.8326V5.24887H21.6516Z" fill="#DC2626" />
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M20.1601 13.5259C20.9288 14.2946 20.9288 15.5409 20.1601 16.3096L18.2115 18.2581L20.1601 20.2067C20.9288 20.9753 20.9288 22.2216 20.1601 22.9903L19.2322 23.9182C18.4635 24.6869 17.2172 24.6869 16.4485 23.9182L14.5 21.9697L12.5515 23.9182C11.7828 24.6869 10.5365 24.6869 9.76782 23.9182L8.83995 22.9903C8.07125 22.2216 8.07125 20.9753 8.83995 20.2067L10.7885 18.2581L8.83995 16.3096C8.07125 15.5409 8.07125 14.2946 8.83995 13.5259L9.76782 12.5981C10.5365 11.8294 11.7828 11.8294 12.5514 12.5981L14.5 14.5466L16.4486 12.5981C17.2172 11.8294 18.4635 11.8294 19.2322 12.5981L20.1601 13.5259ZM19.1394 14.5466C19.3444 14.7516 19.3444 15.0839 19.1394 15.2889L16.5414 17.887C16.3364 18.092 16.3364 18.4243 16.5414 18.6293L19.1394 21.2273C19.3444 21.4323 19.3444 21.7647 19.1394 21.9697L18.2115 22.8975C18.0065 23.1025 17.6742 23.1025 17.4692 22.8975L14.8712 20.2995C14.6662 20.0945 14.3338 20.0945 14.1288 20.2995L11.5308 22.8975C11.3258 23.1025 10.9935 23.1025 10.7885 22.8975L9.86059 21.9697C9.65562 21.7647 9.65562 21.4323 9.86059 21.2273L12.4587 18.6293C12.6636 18.4243 12.6636 18.092 12.4587 17.887L9.86059 15.2889C9.65562 15.0839 9.65562 14.7516 9.86059 14.5466L10.7885 13.6188C10.9935 13.4137 11.3258 13.4137 11.5308 13.6188L14.1288 16.2168C14.3338 16.4218 14.6662 16.4218 14.8712 16.2168L17.4692 13.6188C17.6742 13.4137 18.0065 13.4137 18.2115 13.6188L19.1394 14.5466Z" fill="#DC2626" />
                                        </svg></i>
                                </div>
                                <div>
                                    <p class="text-base text-gray mb-3">{{ __('text.Cancelled') }}</p>
                                    <h3 class="text-4xl font-semibold">{{ $cancelled }}</h3>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                {{-- <div>

                                    <span class="text-red-600">1.5%</span> <span class=" text-gray inline-flex leading-3 ltr:mr-1 rtl:ml-1">{{ __('text.Cancelled today') }}</span>
                                </div> --}}
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded shadow dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                            <div class="flex justify-between mb-3 items-center flex-row-reverse">
                                <div class="h-sm bg-yellow-light rounded-md avatar avatar-md">
                                    <i class="ri-timer-lines"><svg width="29" height="31" viewBox="0 0 29 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path opacity="0.78" fill-rule="evenodd" clip-rule="evenodd" d="M13.2093 9.80855C13.2294 9.54817 13.4466 9.34718 13.7078 9.34718H14.136C14.3927 9.34718 14.6077 9.54163 14.6335 9.79707L15.2597 16.0138L19.7074 18.5368C19.864 18.6256 19.9607 18.7917 19.9607 18.9717V19.3605C19.9607 19.6898 19.6479 19.9292 19.33 19.8431L12.9733 18.1221C12.7414 18.0593 12.5869 17.8404 12.6054 17.6008L13.2093 9.80855Z" fill="#FEC53D" />
                                            <path opacity="0.901274" d="M6.38184 1.18574C6.47746 0.787759 6.98875 0.667975 7.25391 0.981635L9.06836 3.12812C10.7637 2.41152 12.6289 2.01392 14.5879 2.01386L14.9521 2.01874C22.5728 2.21033 28.6914 8.40264 28.6914 16.0139C28.6914 23.7458 22.3767 30.0139 14.5879 30.0139C6.79924 30.0136 0.48536 23.7457 0.485352 16.0139C0.485352 14.7008 0.66732 13.4299 1.00781 12.2248L3.59473 12.9445C3.31523 13.9337 3.17188 14.9631 3.17188 16.0139C3.17188 22.2729 8.28283 27.3467 14.5879 27.3469C20.8931 27.3469 26.0049 22.2731 26.0049 16.0139C26.0049 9.95024 21.208 4.99904 15.1758 4.6955L14.5879 4.68085C13.3105 4.6809 12.0668 4.88936 10.8936 5.2873L12.71 7.43574C12.9756 7.74995 12.7667 8.23091 12.3545 8.2541L5.25195 8.65351C4.9163 8.67237 4.65735 8.36388 4.73535 8.03925L6.38184 1.18574Z" fill="#FEC53D" />
                                        </svg></i>
                                </div>
                                <div>
                                    <p class="text-base text-gray mb-3">{{ __('text.waiting') }}</p>
                                    <h3 class="text-4xl font-semibold">{{ $waiting }}</h3>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                {{-- <div>

                                    <span class="text-yellow">1.8%</span> <span class=" text-gray inline-flex leading-3  ltr:mr-1 rtl:ml-1">{{ __('text.Served yesterday') }}</span>
                                </div> --}}
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded shadow dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                            <div class="flex justify-between mb-3 items-center flex-row-reverse">
                                <div class="h-sm bg-yellow-light rounded-md avatar avatar-md">
                                    <i class="ri-timer-lines"><svg width="29" height="31" viewBox="0 0 29 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path opacity="0.78" fill-rule="evenodd" clip-rule="evenodd" d="M13.2093 9.80855C13.2294 9.54817 13.4466 9.34718 13.7078 9.34718H14.136C14.3927 9.34718 14.6077 9.54163 14.6335 9.79707L15.2597 16.0138L19.7074 18.5368C19.864 18.6256 19.9607 18.7917 19.9607 18.9717V19.3605C19.9607 19.6898 19.6479 19.9292 19.33 19.8431L12.9733 18.1221C12.7414 18.0593 12.5869 17.8404 12.6054 17.6008L13.2093 9.80855Z" fill="#FEC53D" />
                                            <path opacity="0.901274" d="M6.38184 1.18574C6.47746 0.787759 6.98875 0.667975 7.25391 0.981635L9.06836 3.12812C10.7637 2.41152 12.6289 2.01392 14.5879 2.01386L14.9521 2.01874C22.5728 2.21033 28.6914 8.40264 28.6914 16.0139C28.6914 23.7458 22.3767 30.0139 14.5879 30.0139C6.79924 30.0136 0.48536 23.7457 0.485352 16.0139C0.485352 14.7008 0.66732 13.4299 1.00781 12.2248L3.59473 12.9445C3.31523 13.9337 3.17188 14.9631 3.17188 16.0139C3.17188 22.2729 8.28283 27.3467 14.5879 27.3469C20.8931 27.3469 26.0049 22.2731 26.0049 16.0139C26.0049 9.95024 21.208 4.99904 15.1758 4.6955L14.5879 4.68085C13.3105 4.6809 12.0668 4.88936 10.8936 5.2873L12.71 7.43574C12.9756 7.74995 12.7667 8.23091 12.3545 8.2541L5.25195 8.65351C4.9163 8.67237 4.65735 8.36388 4.73535 8.03925L6.38184 1.18574Z" fill="#FEC53D" />
                                        </svg></i>
                                </div>
                                <div>
                                    <p class="text-base text-gray mb-3">{{ __('report.Average Rating') }}</p>
                                    <h3 class="text-4xl font-semibold">{{ number_format($averageRating, 2) }}</h3>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                {{-- <div>

                                    <span class="text-yellow">1.8%</span> <span class=" text-gray inline-flex leading-3  ltr:mr-1 rtl:ml-1">{{ __('text.Served yesterday') }}</span>
                                </div> --}}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 flex-wrap gap-4">
                        <!-- Hourly Bar Chart -->
                        <div class="bg-white p-4 rounded shadow mb-6 dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white" wire:ignore>
                            <h4 class="mb-2 font-semibold  uppercase">{{ __('text.Hourly Visits (12 AM - 11 PM)') }}</h4>
                            <canvas id="hourlyChart"></canvas>
                        </div>

                        <!-- Monthly Bar Chart -->
                        <div class="bg-white p-4 rounded shadow mb-6 dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white" wire:ignore>
                            <h4 class="mb-2 font-semibold  uppercase">{{ __('text.Monthly Visits') }}</h4>
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                    @php
                    function fmt($s) {
                    return sprintf('%02d:%02d:%02d', floor($s/3600), floor($s%3600/60), $s%60);
                    }
                    @endphp
                    <!-- Additional KPI Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white p-4 rounded shadow dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                            <div class="flex  gap-3 mb-3 mt-3 flex-row-reverse justify-between">
                                <div class="h-sm bg-green-light text-green-800 rounded-md avatar avatar-md">
                                    <i class="ri-time-lines">
                                        <svg width="33" height="34" viewBox="0 0 33 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <mask id="mask0_66_969" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0" width="33" height="34">
                                                <path d="M0 -3.8147e-06H33V34H0V-3.8147e-06Z" fill="white" />
                                            </mask>
                                            <g mask="url(#mask0_66_969)">
                                                <path d="M26.6106 7.22942C28.9781 9.79905 30.424 13.2307 30.424 17.0001C30.424 24.9662 23.9661 31.424 16.0001 31.424C8.03396 31.424 1.57611 24.9662 1.57611 17.0001C1.57611 9.03399 8.03396 2.5762 16.0001 2.5762C16.5954 2.5762 17.1824 2.61229 17.7589 2.68237" stroke="#22C03C" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M10.3517 8.02111C14.4493 5.43836 19.9298 5.9316 23.4991 9.50095C27.6408 13.6427 27.6408 20.3576 23.4991 24.4993C19.3575 28.641 12.6425 28.641 8.50076 24.4993C4.92613 20.9247 4.4367 15.4333 7.0324 11.3338" stroke="#22C03C" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M26.5077 9.37323V7.14667H28.7343" stroke="#22C03C" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M14.5674 17.0002C14.5674 17.7914 15.2088 18.4329 16 18.4329C16.7912 18.4329 17.4326 17.7914 17.4326 17.0002C17.4326 16.2091 16.7912 15.5676 16 15.5676C15.2088 15.5676 14.5674 16.2091 14.5674 17.0002Z" stroke="#22C03C" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M24.3398 17.0097H23.6171" stroke="#22C03C" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M7.66016 16.9907H8.38279" stroke="#22C03C" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M16.0093 8.66036V9.383" stroke="#22C03C" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M15.9905 25.34V24.6174" stroke="#22C03C" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M14.9867 15.9871L12.6484 13.6488" stroke="#22C03C" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M17.0128 15.9871L21.372 11.6279" stroke="#22C03C" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                            </g>
                                        </svg>
                                    </i>
                                </div>
                                <div class="">
                                    <p class="pb-1 text-gray">{{ __('text.Avg Served Time') }}</p>
                                    <h3 class="text-2xl font-semibold">{{ fmt($avgServedTime) }}</h3>
                                </div>
                            </div>

                        </div>
                        <div class="bg-white p-4 rounded shadow dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                            <div class="flex  gap-3 mb-3 mt-3 flex-row-reverse justify-between">
                                <div class="h-sm bg-purple-200 text-blue-800 rounded-md avatar avatar-md">
                                    <i class="ri-timer-lines">
                                        <svg width="28" height="29" viewBox="0 0 28 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M16.8287 26.2742C16.5978 26.3322 16.3625 26.3837 16.1288 26.427C15.518 26.5414 15.1142 27.1331 15.2272 27.7489C15.283 28.0519 15.4538 28.3042 15.6857 28.4689C15.9247 28.6384 16.2289 28.7149 16.5389 28.6568C16.8171 28.6048 17.0975 28.5436 17.3727 28.4744C17.9758 28.3232 18.3433 27.7078 18.1928 27.1005C18.0427 26.4927 17.4322 26.1229 16.8287 26.2742Z" fill="#5C4AE4" />
                                            <path d="M25.1525 11.0771C25.2313 11.3165 25.3818 11.5119 25.572 11.6469C25.8539 11.8469 26.2228 11.9142 26.5748 11.7969C27.165 11.5995 27.4848 10.9585 27.2895 10.3643C27.2006 10.0934 27.1019 9.82166 26.9967 9.55708C26.7658 8.97567 26.1111 8.6926 25.5336 8.92517C24.9567 9.15761 24.6756 9.81729 24.9067 10.3989C24.995 10.6213 25.0778 10.8495 25.1525 11.0771Z" fill="#5C4AE4" />
                                            <path d="M20.4862 24.6555C20.2877 24.7875 20.0827 24.9153 19.8763 25.035C19.3377 25.348 19.1532 26.0415 19.4638 26.5838C19.5481 26.7313 19.6604 26.8519 19.7898 26.9441C20.137 27.1901 20.6085 27.227 21.0011 26.9992C21.2466 26.8566 21.4906 26.7049 21.7271 26.5474C22.2456 26.2023 22.3883 25.4989 22.0456 24.9764C21.7029 24.4537 21.0049 24.3102 20.4862 24.6555Z" fill="#5C4AE4" />
                                            <path d="M27.9894 14.2437C27.9649 13.6179 27.4416 13.1309 26.8202 13.1553C26.1995 13.18 25.7156 13.7072 25.74 14.3328C25.7493 14.572 25.7518 14.8147 25.7463 15.0536C25.7376 15.4461 25.9281 15.7959 26.2242 16.0062C26.4006 16.1313 26.6147 16.207 26.8469 16.2123C27.4681 16.2262 27.983 15.7299 27.9967 15.1038C28.0029 14.8183 28.0006 14.529 27.9894 14.2437Z" fill="#5C4AE4" />
                                            <path d="M24.9689 21.6728C24.4707 21.2963 23.7659 21.3985 23.3928 21.8994C23.2499 22.0913 23.0995 22.2807 22.9454 22.4631C22.5427 22.9397 22.5996 23.6554 23.0727 24.0613C23.0996 24.0844 23.1269 24.1055 23.1552 24.1254C23.6255 24.4594 24.2791 24.3827 24.6594 23.9332C24.8434 23.7155 25.0227 23.4892 25.1935 23.2601C25.5666 22.7592 25.4657 22.0488 24.9689 21.6728Z" fill="#5C4AE4" />
                                            <path d="M26.6234 17.5743C26.0303 17.3869 25.3986 17.7195 25.2128 18.317C25.1416 18.5453 25.0628 18.7746 24.9777 18.9992C24.7908 19.4937 24.9699 20.0378 25.3803 20.3293C25.4556 20.3826 25.5386 20.4278 25.6287 20.4621C26.2094 20.6853 26.8597 20.3918 27.0811 19.8066C27.1822 19.5395 27.2759 19.2667 27.3607 18.9953C27.5464 18.3977 27.2165 17.7616 26.6234 17.5743Z" fill="#5C4AE4" />
                                            <path d="M11.9179 26.4372C10.9116 26.2552 9.94628 25.9453 9.03089 25.513C9.02006 25.5073 9.01037 25.5008 8.99899 25.4956C8.78328 25.3933 8.56793 25.2838 8.3592 25.1693C8.35848 25.1685 8.35715 25.1679 8.35601 25.1675C7.97304 24.955 7.59928 24.72 7.23617 24.4625C1.94139 20.7054 0.668308 13.3097 4.39835 7.97627C5.20943 6.81696 6.19096 5.84978 7.28522 5.08322C7.2987 5.07376 7.31219 5.06436 7.32555 5.05484C11.1816 2.37853 16.4174 2.19816 20.5119 4.94832L19.6325 6.22822C19.388 6.58446 19.5384 6.84406 19.9664 6.80526L23.7863 6.46078C24.2148 6.42198 24.4711 6.04858 24.3559 5.63171L23.3301 1.90885C23.2153 1.4915 22.9212 1.44154 22.6765 1.79772L21.7951 3.08065C18.7903 1.04886 15.1863 0.27411 11.6092 0.898931C11.249 0.96174 10.8937 1.03861 10.5434 1.1281C10.5407 1.12859 10.5385 1.12889 10.5364 1.12937C10.5228 1.13271 10.5091 1.13713 10.4959 1.14083C7.41131 1.93874 4.72005 3.75094 2.79966 6.33656C2.78347 6.3559 2.76679 6.37481 2.75151 6.39585C2.68765 6.48249 2.62427 6.57112 2.56222 6.65976C2.46074 6.80502 2.36071 6.95392 2.26501 7.10282C2.25304 7.12077 2.24389 7.13901 2.23342 7.15714C0.648688 9.63078 -0.115689 12.4905 0.0153987 15.402C0.0156996 15.4116 0.0151579 15.4212 0.0153987 15.431C0.0280981 15.7154 0.0504878 16.0038 0.0808823 16.2876C0.0825073 16.3059 0.0865399 16.3233 0.0896094 16.3416C0.121027 16.627 0.16045 16.9131 0.209863 17.1991C0.712065 20.1176 2.07879 22.7438 4.12678 24.7872C4.13154 24.792 4.13647 24.7972 4.14129 24.8022C4.14297 24.8041 4.14484 24.805 4.14646 24.8068C4.69669 25.3535 5.29519 25.8591 5.93962 26.3163C7.62606 27.5133 9.50396 28.3045 11.5207 28.6691C12.1326 28.7798 12.7172 28.3695 12.827 27.7534C12.9367 27.1369 12.5297 26.5476 11.9179 26.4372Z" fill="#5C4AE4" />
                                            <path d="M13.3097 5.62982C12.8064 5.62982 12.3987 6.04087 12.3987 6.54717V15.6865L20.6967 20.0074C20.8302 20.0771 20.9732 20.1099 21.114 20.1099C21.4435 20.1099 21.7619 19.9291 21.9237 19.6137C22.1545 19.1634 21.9799 18.6103 21.5329 18.3778L14.2198 14.5694V6.54717C14.2197 6.04087 13.8125 5.62982 13.3097 5.62982Z" fill="#5C4AE4" />
                                        </svg>

                                    </i>
                                </div>
                                <div class="">
                                    <p class="pb-1 text-gray">{{ __('text.Avg Waiting Time') }}</p>
                                    <h3 class="text-2xl font-semibold">{{ fmt($avgWaitingTime) }}</h3>
                                </div>

                            </div>


                        </div>
                        <div class="bg-white p-4 rounded shadow dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                            <div class="flex  gap-3 mb-3 mt-3 flex-row-reverse justify-between">
                                <div class="h-sm bg-red-100 text-red-800 rounded-md avatar avatar-md">
                                    <i class="ri-hourglass-lines"><svg width="19" height="28" viewBox="0 0 19 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M17.2005 0H2.26719C1.49533 0 0.867188 0.628141 0.867188 1.4C0.867188 2.17186 1.49533 2.8 2.26719 2.8H17.2005C17.9724 2.8 18.6005 2.17186 18.6005 1.4C18.6005 0.628141 17.9723 0 17.2005 0ZM17.2005 1.86665H2.26719C2.01005 1.86665 1.80054 1.65758 1.80054 1.4C1.80054 1.14242 2.01005 0.933352 2.26719 0.933352H17.2005C17.4577 0.933352 17.6672 1.14242 17.6672 1.4C17.6672 1.65758 17.4576 1.86665 17.2005 1.86665Z" fill="#F93A5A" />
                                            <path d="M17.2005 25.2H2.26719C1.49533 25.2 0.867188 25.8282 0.867188 26.6C0.867188 27.3719 1.49533 28 2.26719 28H17.2005C17.9724 28 18.6005 27.3719 18.6005 26.6C18.6005 25.8282 17.9723 25.2 17.2005 25.2ZM17.2005 27.0667H2.26719C2.01005 27.0667 1.80054 26.8576 1.80054 26.6C1.80054 26.3424 2.01005 26.1334 2.26719 26.1334H17.2005C17.4577 26.1334 17.6672 26.3424 17.6672 26.6C17.6672 26.8576 17.4576 27.0667 17.2005 27.0667Z" fill="#F93A5A" />
                                            <path d="M13.0593 12.2677C15.429 9.898 16.7338 6.74751 16.7338 3.39686V2.33335C16.7338 2.07528 16.5247 1.8667 16.2671 1.8667H3.20041C2.94284 1.8667 2.73376 2.07528 2.73376 2.33335V3.39686C2.73376 6.748 4.03855 9.898 6.40783 12.2677L6.87683 12.7367C7.20955 13.0694 7.40041 13.5296 7.40041 14C7.40041 14.4704 7.20955 14.9306 6.87727 15.2633L6.40827 15.7323C4.03855 18.102 2.73376 21.252 2.73376 24.6031V25.6667C2.73376 25.9247 2.94284 26.1333 3.20041 26.1333H16.2671C16.5246 26.1333 16.7337 25.9247 16.7337 25.6667V24.6031C16.7337 21.2525 15.4289 18.102 13.0592 15.7323L12.5902 15.2633C12.2528 14.9259 12.0671 14.4774 12.0671 14C12.0671 13.5226 12.2528 13.0741 12.5902 12.7367L13.0593 12.2677ZM11.9303 12.0769C11.4166 12.5902 11.1338 13.2729 11.1338 14C11.1338 14.7271 11.4166 15.4098 11.9303 15.9231L12.3993 16.3921C14.5927 18.5855 15.8004 21.5017 15.8004 24.6031V25.2H3.66712V24.6031C3.66712 21.5012 4.87483 18.5855 7.06769 16.3921L7.53669 15.9231C8.05097 15.4098 8.33376 14.7266 8.33376 14C8.33376 13.2734 8.05098 12.5902 7.53719 12.0769L7.06819 11.6079C4.87483 9.41451 3.66712 6.4983 3.66712 3.39686V2.8H15.8005V3.39686C15.8005 6.49835 14.5927 9.41451 12.3994 11.6079L11.9303 12.0769Z" fill="#F93A5A" />
                                            <path d="M13.6777 7.86195C13.6305 7.74344 13.47 7.46667 13.0005 7.46667C10.5757 7.46667 9.49539 6.94725 9.06697 6.63739C8.53262 6.25053 7.84711 6.14924 7.23155 6.36717C6.64404 6.57482 6.21376 7.03682 6.05183 7.63417C6.00983 7.7896 6.00234 7.89039 6.00234 7.89039C5.99534 7.98232 6.01541 8.08174 6.06069 8.16246C6.49797 8.9376 7.00241 9.75518 7.72855 10.4813L8.19706 10.9503C8.88727 11.6396 9.26713 12.557 9.26713 13.5333C9.26713 13.7914 9.4762 14 9.73378 14C9.99135 14 10.2004 13.7914 10.2004 13.5333C10.2004 12.5575 10.5803 11.64 11.27 10.9503L11.7395 10.4808C11.8548 10.366 12.005 10.2242 12.1702 10.0692C13.3767 8.93481 13.884 8.38274 13.6777 7.86195ZM11.5301 9.38937C11.3574 9.55223 11.1997 9.70065 11.0793 9.82102L10.6098 10.2905C10.2472 10.6527 9.95324 11.0652 9.73339 11.5122C9.5136 11.0652 9.2196 10.6527 8.85697 10.2905L8.38841 9.82151C7.81534 9.248 7.39583 8.61658 6.95948 7.85544C7.07476 7.46579 7.37341 7.30709 7.54283 7.24688C7.86855 7.1316 8.23397 7.18623 8.51955 7.39344C9.11406 7.82372 10.2863 8.33095 12.532 8.39351C12.2436 8.7183 11.791 9.14437 11.5301 9.38937Z" fill="#F93A5A" />
                                            <path d="M14.8351 23.7636C14.7179 22.2507 14.1113 21.6225 13.4094 20.8945C13.3147 20.7961 13.2158 20.6939 13.1145 20.5856C12.5284 19.9593 11.7005 19.6 10.8423 19.6H8.62515C7.76743 19.6 6.93908 19.9594 6.35294 20.5856C6.25166 20.6939 6.15322 20.7956 6.05845 20.8941C5.35609 21.6221 4.74945 22.2507 4.6328 23.7641C4.62252 23.8938 4.6673 24.0217 4.75552 24.1174C4.84416 24.2126 4.96787 24.2667 5.09802 24.2667H14.3697C14.4995 24.2667 14.6236 24.2126 14.7123 24.117C14.8005 24.0213 14.8449 23.8929 14.8351 23.7636ZM5.62959 23.3334C5.7878 22.5186 6.16487 22.1285 6.73044 21.5423C6.82844 21.4406 6.93016 21.3347 7.03473 21.2231C7.44494 20.7845 8.02452 20.5334 8.62515 20.5334H10.8423C11.4434 20.5334 12.0229 20.7849 12.4331 21.2227C12.5381 21.3356 12.6399 21.4406 12.7384 21.5428C13.3035 22.129 13.6801 22.5191 13.8388 23.3334H5.62959Z" fill="#F93A5A" />
                                            <path d="M9.71994 14.9333C9.46237 14.9333 9.2533 15.1419 9.2533 15.3999V15.8666C9.2533 16.1247 9.46237 16.3332 9.71994 16.3332C9.97752 16.3332 10.1866 16.1247 10.1866 15.8666V15.3999C10.1866 15.1419 9.97752 14.9333 9.71994 14.9333Z" fill="#F93A5A" />
                                            <path d="M9.71994 17.2667C9.46237 17.2667 9.2533 17.4752 9.2533 17.7333V18.2C9.2533 18.458 9.46237 18.6666 9.71994 18.6666C9.97752 18.6666 10.1866 18.458 10.1866 18.2V17.7333C10.1866 17.4753 9.97752 17.2667 9.71994 17.2667Z" fill="#F93A5A" />
                                        </svg>
                                    </i>
                                </div>
                                <div class="">
                                    <p class="pb-1 text-gray">{{ __('text.Max Waiting Time') }}</p>
                                    <h3 class="text-2xl font-semibold">{{ fmt($maxWaitingTime) }}</h3>
                                </div>

                            </div>


                        </div>
                        <div class="bg-white p-4 rounded shadow dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                            <div class="flex gap-3 mb-3 mt-3 flex-row-reverse justify-between">
                                <div class="h-sm  bg-cyne-light text-purple-800 rounded-md avatar avatar-md">
                                    <i class="ri-timer-flash-lines"><svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <mask id="mask0_66_1236" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0" width="28" height="29">
                                                <path d="M0 0.00012207H28V28.0001H0V0.00012207Z" fill="white" />
                                            </mask>
                                            <g mask="url(#mask0_66_1236)">
                                                <path d="M27.1797 14.0001C27.1797 21.2488 21.2487 27.1798 14 27.1798C6.75128 27.1798 0.820312 21.2488 0.820312 14.0001C0.820312 6.7514 6.75128 0.820435 14 0.820435C21.2487 0.820435 27.1797 6.7514 27.1797 14.0001Z" stroke="#00B9FF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M6.45939 20.3807C6.77975 20.0603 7.29911 20.0603 7.61947 20.3807C7.93983 20.701 7.93983 21.2204 7.61947 21.5407C7.29911 21.8611 6.77975 21.8611 6.45939 21.5407C6.13903 21.2204 6.13903 20.701 6.45939 20.3807Z" fill="#00B9FF" />
                                                <path d="M20.3805 6.45951C20.7008 6.13915 21.2203 6.13915 21.5406 6.45951C21.861 6.77987 21.861 7.29923 21.5406 7.61959C21.2203 7.93995 20.7008 7.93995 20.3805 7.61959C20.0602 7.29923 20.0602 6.77987 20.3805 6.45951Z" fill="#00B9FF" />
                                                <path d="M7.61947 6.45951C7.93983 6.77987 7.93983 7.29923 7.61947 7.61959C7.29917 7.93995 6.77975 7.93995 6.45939 7.61959C6.13903 7.29923 6.13903 6.77987 6.45939 6.45951C6.77975 6.13915 7.29917 6.13915 7.61947 6.45951Z" fill="#00B9FF" />
                                                <path d="M21.5406 20.3807C21.861 20.701 21.861 21.2204 21.5406 21.5407C21.2203 21.8611 20.7009 21.8611 20.3805 21.5407C20.0602 21.2204 20.0602 20.701 20.3805 20.3807C20.7009 20.0603 21.2203 20.0603 21.5406 20.3807Z" fill="#00B9FF" />
                                                <path d="M14 4.15637V5.797" stroke="#00B9FF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M14 22.2032V23.8439" stroke="#00B9FF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M4.15625 14.0001H5.79688" stroke="#00B9FF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M22.2031 14.0001H23.8438" stroke="#00B9FF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M14 9.07825V14.0001L18.9219 18.922" stroke="#00B9FF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                            </g>
                                        </svg>
                                    </i>
                                </div>
                                <div>
                                    <p class="pb-1 text-gray">{{ __('text.Max Served Time') }}</p>
                                    <h3 class="text-2xl font-semibold">{{ fmt($maxServedTime) }}</h3>
                                </div>
                            </div>

                            <div class="flex gap-3 justify-between items-center">

                                <!-- <div class="flex gap-3 items-center flex-1">
                                    <div class="w-[100%] bg-gray-300  rounded-full">
                                        <div class="bg-indigo-700 w-full h-2 rounded-full" style="width:68%">
                                        </div>
                                    </div>
                                    <span class="font-semibold">68%</span>
                                </div> -->
                            </div>
                        </div>
                    </div>

                    <!-- Horizontal Bar Graphs -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="bg-white p-4 rounded shadow dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                            <h4 class="mb-2 font-semibold uppercase">{{ __('text.Counter Visits') }}</h4>
                            <canvas id="counterChart"></canvas>
                        </div>
                        <div class="bg-white p-4 rounded shadow dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                            <h4 class="mb-2 font-semibold uppercase">{{ __('text.Service Visits') }}</h4>
                            <canvas id="serviceChart"></canvas>
                        </div>
                        <div class="bg-white p-4 rounded shadow dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                            <h4 class="mb-2 font-semibold uppercase">{{ __('text.Agent Served Visits') }}</h4>
                            <canvas id="agentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Walk-In Tab Content -->
            <div x-show="activeTab === 'walkin'" style="display:block">
                <div class="mt-6 mb-6">
                    <select wire:model.live="filter"
                        class="bg-white  border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        style="width: 200px;">
                        <option value="today">{{ __('text.Today') }}</option>
                        <option value="this_week">{{ __('text.This Week') }}</option>
                        <option value="this_month"> {{ __('text.This Month') }} </option>

                    </select>
                </div>
                <div class="-mx-2.5 flex flex-wrap gap-y-5">
                    <div class="w-full px-2.5 xl:w-1/2">
                        @livewire(\App\Livewire\Widgets\CallHandlingOverviewChart::class)
                    </div>
                    <div class="w-full px-2.5 xl:w-1/2">
                        @livewire(\App\Livewire\Widgets\WalkinQueueVisitsChart::class)
                    </div>
                </div>


                <!-- Include the form using $this->form -->
                <div class="flex gap-3 mt-4 flex-wrap w-full border-gray-200 py-2">

                    <div class="flex-1 flex flex-col py-4 sm:py-2">
                        <label for="fromSelectedDate" class="font-semibold mb-2  dark:text-white">{{ __('text.From Date') }}</label>
                        <input type="date" wire:model.live="fromSelectedDate" onclick="this.showPicker()"
                            class="bg-white dark:bg-dark-900 datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-4 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 flatpickr-input active" />
                    </div>
                    <div class="flex-1 flex flex-col py-4 sm:py-2">
                        <label for="toSelectedDate" class="font-semibold mb-2  dark:text-white">{{ __('text.To Date') }}</label>
                        <input type="date" wire:model.live="toSelectedDate" onclick="this.showPicker()"
                            class="bg-white dark:bg-dark-900 datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-4 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 flatpickr-input active" />
                    </div>

                </div>

                <?php isset($data) ? dd($data['start_date']) : '' ?>
                <!-- Livewire components for widgets -->
                <div class="mb-4 space-y-6 border-t border-gray-100 dark:border-gray-800">
                    <div class="-mx-2.5 flex flex-wrap gap-y-5">

                        <div class="card-header w-full px-2.5 xl:w-1/2">
                            @livewire(\App\Livewire\Widgets\OverviewChart::class)
                        </div>
                        <div class="card-header w-full px-2.5 xl:w-1/2">
                            @livewire(\App\Livewire\Widgets\StatisticsSummaryChart::class)
                        </div>
                        <div class="card-header w-full px-2.5 xl:w-1/2">
                            @livewire(\App\Livewire\Widgets\StatisticsCallHistoryChart::class)
                        </div>
                        <div class="card-header w-full px-2.5 xl:w-1/2">
                            @livewire(\App\Livewire\Widgets\StatisticsCounterHistoryChart::class)
                        </div>
                    </div>
                </div>

            </div>

            <div x-show="activeTab === 'walkin'" class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-6">
                <div>
                    @livewire(\App\Livewire\Widgets\WalkinByServiceChart::class)
                </div>
            </div>

            <!-- Appointments Tab Content -->
            <div x-show="activeTab === 'appointments'">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                    <div
                        class="block max-w-sm p-6 bg-white border border-gray-200 rounded shadow dark:bg-white/[0.03] dark:border-gray-700">
                        <h5 class="mb-2 text-base font-semibold tracking-tight text-center dark:text-white">
                            {{ __('text.Completed Appointments') }}
                        </h5>
                        <p class="text-2xl font-semibold text-center dark:text-gray-400">
                            {{ $completedAppointments }}
                        </p>
                    </div>
                    <div
                        class="block max-w-sm p-6 bg-white border border-gray-200 rounded shadow dark:bg-white/[0.03] dark:border-gray-700">
                        <h5 class="mb-2 text-base font-semibold tracking-tight text-center dark:text-white">
                            {{ __('text.Pending Appointments') }}
                        </h5>
                        <p class="text-2xl font-semibold text-center dark:text-gray-400">
                            {{ $pendingAppointments }}
                        </p>
                    </div>
                    <div
                        class="block max-w-sm p-6 bg-white border border-gray-200 rounded shadow dark:bg-white/[0.03] dark:border-gray-700">
                        <h5 class="mb-2 text-base font-semibold tracking-tight text-center dark:text-white">
                            {{ __('text.Rescheduled Appointments') }}
                        </h5>
                        <p class="text-2xl font-semibold text-center dark:text-gray-400">
                            {{ $rescheduledAppointments }}
                        </p>
                    </div>

                    <div
                        class="block max-w-sm p-6 bg-white border border-gray-200 rounded shadow dark:bg-white/[0.03] dark:border-gray-700">
                        <h5 class="mb-2 text-base font-semibold tracking-tight text-center dark:text-white">
                            {{ __('text.Cancelled Appointments') }}
                        </h5>
                        <p class="text-2xl font-semibold text-center dark:text-gray-400">
                            {{ $cancelledAppointments }}
                        </p>
                    </div>
                </div>



                <div class="grid grid-cols-2 md:grid-cols-2 gap-4 mt-6" style="display:block">
                    <div class="mt-6 mb-6">
                        <select wire:model.live="appointmentFilter"
                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            style="width: 200px;">
                            <option value="today">{{ __('text.Today') }}</option>
                            <option value="this_week">{{ __('text.This Week') }}</option>
                            <option value="this_month"> {{ __('text.This Month') }} </option>

                        </select>
                    </div>
                    <div class="-mx-2.5 flex flex-wrap gap-y-5">
                        <div class="w-full px-2.5 xl:w-1/2">
                            @livewire(\App\Livewire\Widgets\AppointmentsChart::class)
                        </div>
                        <div class="w-full px-2.5 xl:w-1/2">
                            @livewire(\App\Livewire\Widgets\AppointmentsByServicesChart::class)
                        </div>
                    </div>
                </div>
            </div>


            <div x-show="activeTab === 'appointments'" class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-6">
                <div>
                    @livewire(\App\Livewire\Widgets\AppointmentsByTimeChart::class)
                </div>
            </div>

            <div x-show="activeTab === 'users'">
                <div>



                    <div class="mb-4 flex justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-semibold dark:text-white/90">
                                {{ __('text.Active Users') }}
                            </h2>
                        </div>


                    </div>


                    <div class="mb-4 flex justify-between mb-4 gap-3 flex-wrap">

                        <div class="relative w-full lg:w-[300px]">
                            <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/4">
                                <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z"
                                        fill="" />
                                </svg>
                            </span>
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('text.Search') }}..."
                                class="bg-white dark:bg-dark-900 bg-white shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[42px] w-full rounded-lg border border-gray-300 py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                        </div>


                    </div>

                    <div
                        class="p-4 md:p-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow">
                        <div class="max-w-full overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <!-- table header start -->
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-gray-200 text-gray-800 dark:text-white">

                                        <th class="px-5 py-3 sm:px-6">
                                            {{ __('text.name') }}
                                        </th>
                                        <th class="px-5 py-3 sm:px-6">
                                            {{ __('text.Username') }}
                                        </th>
                                         <th class="px-5 py-3 sm:px-6">
                                            {{ __('text.Email') }}
                                        </th>
                                         <th class="px-5 py-3 sm:px-6">
                                            {{ __('text.status') }}
                                        </th>

                                    </tr>
                                </thead>
                                <!-- table header end -->
                                <!-- table body start -->
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
						@forelse($activeUsers as $user)
                        <tr class="border-b border-gray-100 dark:border-gray-700">

                            <td class="px-5 py-3 sm:px-6">{{ $user->name }}</td>
                            <td class="px-5 py-3 sm:px-6">{{ $user->username }}</td>
                            <td class="px-5 py-3 sm:px-6">{{ $user->email }}</td>
                            <td class="px-5 py-3 sm:px-6">
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-600">
                                   Active
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                                        <td colspan="15" class="text-center py-3">
                                            <p class="text-center"><strong>{{ __('text.No records found.') }}</strong></p>
                                        </td>
                                    </tr>
                    @endforelse




                                </tbody>
                            </table>


                            <div class="mt-4">
        {{ $activeUsers->links() }}
    </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>


    @endif


    @push('scripts')
    <script>
        // Example datepicker change event handling
        document.addEventListener('change', function(event) {
            let element = event.target;

            if (element.tagName.toLowerCase() === 'input' && element.type === 'date' && element.hasAttribute(
                    'wire:model.live')) {
                let modelAttribute = element.getAttribute('wire:model.live');
                let dateValue = element.value;

                if (modelAttribute === 'data.start_date') {
                    console.log('Start Date Changed:', dateValue);
                } else if (modelAttribute === 'data.end_date') {
                    console.log('End Date Changed:', dateValue);
                } else {
                    console.warn('Unknown wire:model.live attribute:', modelAttribute);
                }
            }
        });
    </script>

    <script>
        let hourlyChart;
        let monthlyChart;
        let counterChart;
        let categoryChart;
        let AgentChart;

        Livewire.on('hourly-visits-updated', data => {
            const ctx = document.getElementById('hourlyChart').getContext('2d');
            const labels = [...Array(24).keys()].map(i => `${i}:00`);

            if (hourlyChart) {
                hourlyChart.destroy();
            }

            hourlyChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: "{{ __('text.Visits') }}",
                        data: data['data'],
                        backgroundColor: '#0175faff'
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });


        Livewire.on('monthly-visits-updated', data => {

            console.log('monthly:' + data['data']);
            const months = ["{{ __('text.Jan') }}", "{{ __('text.Feb') }}", "{{ __('text.Mar') }}", "{{ __('text.Apr') }}", "{{ __('text.May') }}", "{{ __('text.Jun') }}", "{{ __('text.Jul') }}", "{{ __('text.Aug') }}", "{{ __('text.Sep') }}", "{{ __('text.Oct') }}", "{{ __('text.Nov') }}", "{{ __('text.Dec') }}"];

            if (monthlyChart) {
                monthlyChart.destroy();
            }
            monthlyChart = new Chart(document.getElementById('monthlyChart'), {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: "{{ __('text.Visits') }}",
                        data: data['data'],
                        backgroundColor: '#002affff'
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });

        const horizontalOptions = {
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        };

        Livewire.on('counter-updated', data => {



            if (!Array.isArray(data['data'])) {
                console.error('Expected array, got:', data);
                return;
            }

            const labels = data['data'].map(item => item.counter_name);
            const values = data['data'].map(item => item.count);

            console.log(labels, values);

            if (counterChart) {
                counterChart.destroy();
            }



            counterChart = new Chart(document.getElementById('counterChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: '#fa0606ff'
                    }]
                },
                options: horizontalOptions
            });
        });
        Livewire.on('category-updated', data => {



            if (!Array.isArray(data['data'])) {
                console.error('Expected array, got:', data);
                return;
            }

            const labels = data['data'].map(item => item.name);
            const values = data['data'].map(item => item.total);


            if (categoryChart) {
                categoryChart.destroy();
            }


            categoryChart = new Chart(document.getElementById('serviceChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: '#f88306ff'
                    }]
                },
                options: horizontalOptions
            });
        });


        Livewire.on('agent-updated', data => {

            if (!Array.isArray(data['data'])) {
                console.error('Expected array, got:', data);
                return;
            }

            const labels = data['data'].map(item => item.name);
            const values = data['data'].map(item => item.total_served);


            if (AgentChart) {
                AgentChart.destroy();
            }



            AgentChart = new Chart(document.getElementById('agentChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: '#04ff9bff'
                    }]
                },
                options: horizontalOptions
            });
        });
    </script>
    @endpush

    <!-- <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"> -->

</div>
