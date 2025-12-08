<div>
    <div class="min-h-screen p-4">

        <div class="mx-auto bg-white rounded-xl shadow p-8  dark:bg-white/[0.03]  dark:text-white">

            <!-- Heading -->
            <div class="mb-6 text-center">
                <h1 class="text-3xl font-bold text-blue-700 mb-2 dark:text-white">🎯 {{ __('text.Meta UTM Link Generator') }}</h1>
                <p class="text-gray-600  dark:text-gray-400">{{ __('text.Generate trackable booking or queue links for Meta (Facebook/Instagram) ads and campaigns') }}.</p>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-100 border-l-4 border-blue-400 text-blue-800 p-4 rounded-xl mb-6 text-sm">
                {{ __('text.Select the link type (Queue or Booking), enter UTM campaign data, and the system will auto-generate the tracking URL') }}.
            </div>

            <!-- Form -->
            <form id="utmForm" class="space-y-5" wire:submit.prevent="generateLink">

                <!-- Dropdown for Type -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1  dark:text-white">🔽 {{ __('text.Select Link Type') }}</label>
                        <select id="linkType" wire:model="link_type" wire:change="autoFillBaseUrl" class="w-full rounded-lg border border-gray-300 p-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="">-- {{ __('text.Select') }} --</option>
                            <option value="Booking">{{ __('text.Booking Link') }}</option>
                            <option value="Queue">{{ __('text.Queue Link') }}</option>
                        </select>
                        @error('link_type') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Base URL Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1 dark:text-white">📍 {{ __('text.Base URL') }}</label>
                        <input type="url" id="baseUrl" class="w-full rounded-lg border border-gray-300 p-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white" value="{{ $base_url }}" wire:model="base_url">
                        @error('base_url') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1 dark:text-white">{{ __('text.Source (utm_source)') }}</label>
                        <input type="text" id="utmSource" class="w-full rounded-lg border border-gray-300 p-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white" value="meta" wire:model="source">
                        @error('source') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1 dark:text-white">{{ __('text.Medium (utm_medium)') }}</label>
                        <input type="text" id="utmMedium" class="w-full rounded-lg border border-gray-300 p-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white" value="ad" wire:model="medium">
                        @error('medium') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1 dark:text-white">{{ __('text.Campaign Name (utm_campaign)') }}</label>
                        <input type="text" id="utmCampaign" class="w-full rounded-lg border border-gray-300 p-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="summer_sale_2025" wire:model="campaign">
                        @error('campaign') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <button type="submit" class="mt-4 bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    🔗 {{ __('text.Generate Link') }}
                </button>
            </form>

            <!-- Result -->
            <div id="resultContainer" class="mt-8 {{ !empty($generated_link) ? '' : 'hidden' }}">
                <label class="block text-sm font-semibold text-gray-700 mb-2 mt-4">{{ __('text.Generated Link') }}:</label>
                <div class="flex items-center bg-gray-100 rounded-lg overflow-hidden">
                    <input type="text" id="generatedLink" readonly class="flex-grow p-2 bg-gray-100 border-none focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white" wire:model="generated_link" value="{{ $generated_link }}" />
                    <button class="bg-green-500 text-white px-4 py-2 hover:bg-green-600 transition" onclick="copyLink('generatedLink')">📋 {{ __('text.Copy') }}</button>
                </div>
            </div>

            <!-- Listing Section -->
            <div id="listingSection" class="mt-10">
                <h2 class="text-xl font-semibold mb-4 dark:text-white">📝 {{ __('text.Generated UTM Links') }}</h2>
                <div class="flex flex-wrap items-end gap-4 mb-6">
                    <div class="flex flex-col">
                        <label for="startDate" class="text-sm font-medium text-gray-700 dark:text-white">{{ __('text.Start Date') }}</label>
                        <input type="date" id="startDate" wire:model.live="startDate" onclick="this.showPicker()" class="border rounded px-3 py-2 w-44 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div class="flex flex-col">
                        <label for="endDate" class="text-sm font-medium text-gray-700 dark:text-white">{{ __('text.End Date') }}</label>
                        <input type="date" id="endDate" wire:model.live="endDate" onclick="this.showPicker()" class="border rounded px-3 py-2 w-44 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div class="mt-1">
                        <button wire:click="resetDateFilters"
                            class="text-sm px-4 py-2 border border-gray-300 rounded bg-white hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            {{ __('text.Reset Filters') }}
                        </button>
                    </div>
                </div>
                <div class="p-4 md:p-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow dark:text-white">
                
            <div class="max-w-full overflow-x-auto">
                <table class="w-full table-auto mb-4 table-auto w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="py-2 px-3">#</th>
                                <th class="py-2 px-3">{{ __('text.type') }}</th>
                                <th class="py-2 px-3">{{ __('text.Source') }}</th>
                                <th class="py-2 px-3">{{ __('text.Medium') }}</th>
                                <th class="py-2 px-3">{{ __('text.Campaign') }}</th>
                                <th class="py-2 px-3">{{ __('text.Total Bookings') }}</th>
                                <th class="py-2 px-3">{{ __('text.Total Walk-ins') }}</th>
                                <th class="py-2 px-3">{{ __('text.Total Visits') }}</th>
                                <th class="py-2 px-3">{{ __('text.Served') }}</th>
                                <th class="py-2 px-3">{{ __('text.No Show') }}</th>
                                <th class="py-2 px-3">{{ __('text.Generated Link') }}</th>
                                <th class="py-2 px-3">{{ __('text.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="linkList">
                            @if($listing->count() > 0)
                            @php $i = ($listing->currentPage() - 1) * $listing->perPage() + 1; @endphp
                            @foreach($listing as $list)
                            <tr>
                                <td class="px-3 py-2">{{ $i }}</td>
                                <td class="px-3 py-2">{{ $list->type }}</td>
                                <td class="px-3 py-2">{{ $list->source }}</td>
                                <td class="px-3 py-2">{{ $list->medium }}</td>
                                <td class="px-3 py-2">{{ $list->campaign }}</td>
                                <td class="px-3 py-2">{{ $list->total_bookings }}</td>
                                <td class="px-3 py-2">{{ $list->total_walk_ins }}</td>
                                <td class="px-3 py-2">{{ $list->total_bookings + $list->total_walk_ins }}</td>
                                <td class="px-3 py-2">{{ $list->served }}</td>
                                <td class="px-3 py-2">{{ $list->no_show }}</td>
                                <td class="px-3 py-2 break-all text-blue-600">{{ $list->generated_link }}</td>
                                <td class="px-3 py-2 text-center">
                                    <button onclick="copySpecific('{{ addslashes($list->generated_link) }}')" class="primary-btn hover:underline">
                                        {{ __('text.Copy') }}
                                    </button>
                                </td>
                            </tr>
                            @php $i++; @endphp
                            @endforeach
                            @else
                            <tr>
                                <td colspan="15" class="text-center py-6">
                                    <p class="text-center"><strong>{{ __('report.No records found.') }}</strong></p>
                                </td>
                            </tr>
                            @endif
                        </tbody>

                    </table>
                </div>
            </div>
                <div class="mt-4">
                    {{ $listing->links() }}
                </div>

            </div>
        </div>

        <script>
            function copyLink(id) {
                const input = document.getElementById(id);
                input.select();
                document.execCommand("copy");

                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Link copied to clipboard',
                    timer: 1500,
                    showConfirmButton: false,
                });
            }

            function copySpecific(linkText) {
                const temp = document.createElement("textarea");
                temp.value = linkText;
                document.body.appendChild(temp);
                temp.select();
                document.execCommand("copy");
                document.body.removeChild(temp);

                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: `Copied: ${linkText}`,
                    timer: 1500,
                    showConfirmButton: false,
                });
            }
        </script>


    </div>
</div>