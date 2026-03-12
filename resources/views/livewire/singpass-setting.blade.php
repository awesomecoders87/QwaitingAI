<div class="min-h-screen bg-gray-50 dark:bg-transparent" x-data="singpassSettings()">

    {{-- ── Hero Header ────────────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden rounded-2xl mb-6"
         style="background: linear-gradient(135deg, #c0392b 0%, #96281b 40%, #1a1a2e 100%);">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 20% 50%, #fff 1px, transparent 1px), radial-gradient(circle at 80% 20%, #fff 1px, transparent 1px); background-size: 60px 60px;"></div>
        <div class="relative px-6 py-8 flex items-center justify-between">
            <div class="flex items-center gap-5">
                {{-- Singpass logo mark --}}
                <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight">Singpass Settings</h1>
                    <p class="text-sm text-white/60 mt-0.5">Singapore Government Digital Identity · OIDC / FAPI 2.0</p>
                </div>
            </div>

            {{-- Status pill --}}
            <div class="flex items-center gap-3">
                @if($is_enabled)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-400/20 text-green-300 border border-green-400/30 backdrop-blur-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                        Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-white/50 border border-white/20 backdrop-blur-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                        Inactive
                    </span>
                @endif
            </div>
        </div>
    </div>

    <form wire:submit.prevent="save" class="space-y-5">

        {{-- ── Card 1: Configuration ───────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm">

            {{-- Card title --}}
            <div class="px-6 pt-5 pb-4 border-b border-gray-100 dark:border-white/[0.06]">
                <p class="text-sm font-semibold text-gray-800 dark:text-white">App Configuration</p>
                <p class="text-xs text-gray-400 mt-0.5">Enter the credentials from your Singpass Developer Portal.</p>
            </div>

            <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Client ID --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-widest text-gray-400">
                        Client ID <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"/>
                            </svg>
                        </div>
                        <input type="text" wire:model="client_id"
                               placeholder="QA7M3gEqL*****************"
                               class="w-full pl-10 text-sm rounded-xl border-gray-200 dark:bg-white/5 dark:border-white/10 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                    </div>
                    @error('client_id') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Environment --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold uppercase tracking-widest text-gray-400">
                        Environment <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                            @if($environment === 'production')
                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            @else
                                <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            @endif
                        </div>
                        <select wire:model="environment"
                                class="w-full pl-10 text-sm rounded-xl border-gray-200 dark:bg-white/5 dark:border-white/10 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-transparent transition appearance-none">
                            <option value="staging">Staging (Testing)</option>
                            <option value="production">Production (Live)</option>
                        </select>
                    </div>
                    @error('environment') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

            </div>

            {{-- Enable toggle row --}}
            <div class="px-6 pb-5 flex items-center gap-4">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer select-none" for="sp-enabled-toggle">
                    Enable Singpass Login
                </label>
                <button
                    id="sp-enabled-toggle"
                    type="button"
                    role="switch"
                    aria-checked="{{ $is_enabled ? 'true' : 'false' }}"
                    wire:click="$toggle('is_enabled')"
                    style="width:44px;height:24px;padding:2px;box-sizing:border-box;"
                    class="relative inline-flex flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $is_enabled ? 'bg-brand-500' : 'bg-gray-300 dark:bg-gray-600' }}"
                >
                    <span
                        aria-hidden="true"
                        style="width:20px;height:20px;transition:transform .2s;transform:{{ $is_enabled ? 'translateX(20px)' : 'translateX(0)' }}"
                        class="inline-block rounded-full bg-white shadow pointer-events-none"
                    ></span>
                </button>
                @if($is_enabled)
                    <span class="text-xs text-green-600 dark:text-green-400">✓ Active — JWKS is live</span>
                @else
                    <span class="text-xs text-gray-400">✗ Inactive — login blocked</span>
                @endif
            </div>
        </div>

        {{-- ── Card 2: URLs to Register ────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">

            <div class="px-6 pt-5 pb-4 border-b border-gray-100 dark:border-white/[0.06]">
                <p class="text-sm font-semibold text-gray-800 dark:text-white">URLs to Register in Singpass Developer Portal</p>
                <p class="text-xs text-gray-400 mt-0.5">Copy these two URLs and paste them into your Singpass app settings.</p>
            </div>

            <div class="px-6 py-5 space-y-4">

                {{-- JWKS Endpoint --}}
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">JWKS</span>
                        <label class="text-xs font-semibold uppercase tracking-widest text-gray-400">JWKS Endpoint</label>
                    </div>
                    <div class="flex gap-2 items-center">
                        <div class="flex-1 flex items-center gap-2 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 group">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <input id="jwks-url-field" type="text" readonly value="{{ $jwksUrl }}"
                                   class="flex-1 bg-transparent border-none p-0 text-sm font-mono text-gray-600 dark:text-gray-300 focus:ring-0 focus:outline-none cursor-default min-w-0">
                        </div>
                        <button type="button" onclick="copyField('jwks-url-field', this)"
                                class="flex-shrink-0 inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white bg-brand-500 hover:bg-brand-600 rounded-xl transition-colors shadow-sm">
                            <svg id="copy-icon-jwks" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Copy
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 pl-1">Register under: Application → <span class="font-medium text-gray-500">JWKS Endpoint URI</span></p>
                </div>

                {{-- Callback URL --}}
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">Callback</span>
                        <label class="text-xs font-semibold uppercase tracking-widest text-gray-400">Redirect URL (Callback)</label>
                    </div>
                    <div class="flex gap-2 items-center">
                        <div class="flex-1 flex items-center gap-2 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            <input id="callback-url-field" type="text" readonly value="{{ $callbackUrl }}"
                                   class="flex-1 bg-transparent border-none p-0 text-sm font-mono text-gray-600 dark:text-gray-300 focus:ring-0 focus:outline-none cursor-default min-w-0">
                        </div>
                        <button type="button" onclick="copyField('callback-url-field', this)"
                                class="flex-shrink-0 inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white bg-brand-500 hover:bg-brand-600 rounded-xl transition-colors shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Copy
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 pl-1">Register under: Application → <span class="font-medium text-gray-500">Redirect URL(s)</span></p>
                </div>

            </div>
        </div>

        {{-- ── Card 3: Key Status ──────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">

            <div class="px-6 pt-5 pb-4 border-b border-gray-100 dark:border-white/[0.06] flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white">Cryptographic Key Pairs</p>
                    <p class="text-xs text-gray-400 mt-0.5">EC P-256 keys are auto-generated on every save.</p>
                </div>
                @if($keys_generated_at)
                    <span class="text-xs text-gray-400 bg-gray-100 dark:bg-white/10 px-2.5 py-1 rounded-lg">Last generated {{ $keys_generated_at }}</span>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-white/[0.06]">

                {{-- Signing Key --}}
                <div class="px-6 py-5 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $signing_key_exists ? 'bg-green-100 dark:bg-green-900/30' : 'bg-gray-100 dark:bg-white/5' }}">
                        <svg class="w-5 h-5 {{ $signing_key_exists ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-sm font-semibold text-gray-800 dark:text-white">Signing Key</p>
                            @if($signing_key_exists)
                                <span class="px-1.5 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">Active</span>
                            @else
                                <span class="px-1.5 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400">Pending</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 font-mono">EC P-256 · alg: ES256</p>
                        <p class="text-xs text-gray-400 mt-1">Used by Singpass to verify your <em>client_assertion</em> JWT.</p>
                    </div>
                </div>

                {{-- Encryption Key --}}
                <div class="px-6 py-5 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $enc_key_exists ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-gray-100 dark:bg-white/5' }}">
                        <svg class="w-5 h-5 {{ $enc_key_exists ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-sm font-semibold text-gray-800 dark:text-white">Encryption Key</p>
                            @if($enc_key_exists)
                                <span class="px-1.5 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">Active</span>
                            @else
                                <span class="px-1.5 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400">Pending</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 font-mono">EC P-256 · alg: ECDH-ES+A128KW</p>
                        <p class="text-xs text-gray-400 mt-1">Used to decrypt the encrypted <em>id_token</em> (JWE) returned by Singpass.</p>
                    </div>
                </div>
            </div>

            {{-- Regeneration warning --}}
            @if($signing_key_exists || $enc_key_exists)
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/[0.06] bg-gray-50 dark:bg-white/[0.02] flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Need to rotate keys?</p>
                    <p class="text-xs text-gray-400 mt-0.5 max-w-sm">Use this if your private key is compromised. Warning: You must update the JWKS URL in the Developer Portal immediately.</p>
                </div>
                <button type="button" 
                        wire:click="regenerateKeys" 
                        wire:confirm="WARNING: This will break existing Singpass logins until you register the new keys in the Developer Portal. Are you sure you want to proceed?"
                        class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 rounded-lg transition-colors border border-red-200 dark:border-red-500/20">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Regenerate Keys
                </button>
            </div>
            @endif
        </div>

        {{-- ── Flash Messages ──────────────────────────────────────────────────── --}}
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 rounded-2xl bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-700">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="flex items-center gap-3 p-4 rounded-2xl bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-700">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
            </div>
        @endif

        {{-- ── Save Button ─────────────────────────────────────────────────────── --}}
        <div class="flex justify-end pt-1 pb-4">
            <button type="submit"
                    class="inline-flex items-center gap-2.5 px-6 py-3 text-sm font-semibold text-white rounded-xl bg-brand-500 hover:bg-brand-600 active:scale-95 transition-all shadow-lg shadow-brand-500/30 disabled:opacity-60 disabled:cursor-not-allowed disabled:shadow-none"
                    wire:loading.attr="disabled"
                    wire:target="save">
                <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Settings
                </span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Saving...
                </span>
            </button>
        </div>

    </form>
</div>

<script>
function copyField(fieldId, btn) {
    const input = document.getElementById(fieldId);
    navigator.clipboard.writeText(input.value).then(function () {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Copied!`;
        btn.classList.add('bg-green-500');
        btn.classList.remove('bg-brand-500', 'hover:bg-brand-600');
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('bg-green-500');
            btn.classList.add('bg-brand-500', 'hover:bg-brand-600');
        }, 2000);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    Livewire.on('updated', () => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Settings Saved',
                text: 'Your Singpass configuration was updated.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });

    Livewire.on('keys-regenerated', () => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Keys Regenerated!',
                html: 'New keys created.<br><br><span class="text-red-500 font-medium">ACTION REQUIRED:</span><br><small class="text-gray-600">You must copy the new JWKS URL and replace the old one in your Singpass Developer Portal, otherwise users will not be able to log in.</small>',
                icon: 'warning',
                confirmButtonColor: '#ef4444' // red
            });
        }
    });
});
</script>
