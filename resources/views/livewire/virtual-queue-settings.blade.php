<div class="p-6">
    <div class="max-w-6xl mx-auto">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Virtual Queue Settings</h1>
            <p class="text-gray-600">Configure AI and Human agent settings for virtual queue system</p>
        </div>

        @if (session()->has('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <form wire:submit.prevent="save">
            
            <!-- Feature Toggles -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    Feature Toggles
                </h2>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <label class="font-medium text-gray-800">Enable Virtual Queue</label>
                            <p class="text-sm text-gray-600">Allow customers to join virtual queue</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="enable_virtual_queue" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg {{ !$enable_virtual_queue ? 'opacity-50' : '' }}">
                        <div>
                            <label class="font-medium text-gray-800">Enable AI Agent</label>
                            <p class="text-sm text-gray-600">Allow AI-powered virtual assistance</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="enable_ai_agent" class="sr-only peer" @if(!$enable_virtual_queue) disabled @endif>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600 peer-disabled:opacity-50 peer-disabled:cursor-not-allowed"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg {{ !$enable_virtual_queue ? 'opacity-50' : '' }}">
                        <div>
                            <label class="font-medium text-gray-800">Enable Human Agent</label>
                            <p class="text-sm text-gray-600">Allow human agent virtual assistance</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="enable_human_agent" class="sr-only peer" @if(!$enable_virtual_queue) disabled @endif>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 peer-disabled:opacity-50 peer-disabled:cursor-not-allowed"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- AI Agent Configuration -->
            @if($enable_ai_agent)
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 7H7v6h6V7z"></path>
                        <path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"></path>
                    </svg>
                    AI Agent Configuration
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">AI Provider</label>
                        <select wire:model="ai_provider" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="openai">OpenAI</option>
                            <option value="azure">Azure OpenAI</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">AI Model</label>
                        <select wire:model="ai_model" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="gpt-4">GPT-4</option>
                            <option value="gpt-4-turbo">GPT-4 Turbo</option>
                            <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">AI Voice</label>
                        <select wire:model="ai_voice" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="alloy">Alloy</option>
                            <option value="echo">Echo</option>
                            <option value="fable">Fable</option>
                            <option value="onyx">Onyx</option>
                            <option value="nova">Nova</option>
                            <option value="shimmer">Shimmer</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Default Language</label>
                        <select wire:model="default_language" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="en">English</option>
                            <option value="es">Spanish</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                            <option value="ar">Arabic</option>
                            <option value="hi">Hindi</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                        <input type="password" wire:model="ai_api_key" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="Enter AI API Key">
                    </div>
                </div>
            </div>

            <!-- Avatar Settings -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">AI Avatar Settings</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Avatar Type</label>
                        <select wire:model="ai_avatar_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="default">Default Avatar</option>
                            <option value="custom">Custom Avatar URL</option>
                            <option value="heygen">HeyGen Avatar</option>
                        </select>
                    </div>

                    @if($ai_avatar_type === 'custom')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Avatar URL</label>
                        <input type="url" wire:model="ai_avatar_url" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="https://...">
                    </div>
                    @endif

                    @if($ai_avatar_type === 'heygen')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">HeyGen Avatar ID</label>
                        <input type="text" wire:model="heygen_avatar_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">HeyGen API Key</label>
                        <input type="password" wire:model="heygen_api_key" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Video Call Settings -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Video Call Settings
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Video Provider</label>
                        <select wire:model="video_provider" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="twilio">Twilio Video</option>
                            <option value="agora">Agora</option>
                            <option value="daily">Daily.co</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                        <input type="password" wire:model="video_api_key" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">API Secret</label>
                        <input type="password" wire:model="video_api_secret" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Queue Management Settings -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Queue Management</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max AI Sessions</label>
                        <input type="number" wire:model="max_concurrent_ai_sessions" min="1" max="100" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        @error('max_concurrent_ai_sessions') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Human Sessions</label>
                        <input type="number" wire:model="max_concurrent_human_sessions" min="1" max="50" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        @error('max_concurrent_human_sessions') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Session Timeout (min)</label>
                        <input type="number" wire:model="session_timeout_minutes" min="5" max="120" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500">
                        @error('session_timeout_minutes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max AI Attempts</label>
                        <input type="number" wire:model="max_ai_attempts" min="1" max="10" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        @error('max_ai_attempts') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Transfer Timeout (sec)</label>
                        <input type="number" wire:model="transfer_timeout_seconds" min="60" max="600" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500">
                        @error('transfer_timeout_seconds') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="auto_transfer_on_failure" class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Auto Transfer on Failure</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Notification Settings</h2>

                <div class="space-y-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="send_sms_notification" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-3 text-sm font-medium text-gray-700">Send SMS Notifications</span>
                    </label>

                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="send_email_notification" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-3 text-sm font-medium text-gray-700">Send Email Notifications</span>
                    </label>

                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="send_whatsapp_notification" class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <span class="ml-3 text-sm font-medium text-gray-700">Send WhatsApp Notifications</span>
                    </label>
                </div>
            </div>

            <!-- Custom Messages -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Custom Messages</h2>

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">AI Greeting Message</label>
                        <textarea wire:model="ai_greeting_message" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="Leave empty for default greeting"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Transfer Message</label>
                        <textarea wire:model="transfer_message" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Leave empty for default transfer message"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">AI System Prompt</label>
                        <textarea wire:model="ai_system_prompt" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="Custom system prompt for AI behavior"></textarea>
                    </div>
                </div>
            </div>

            <!-- Save Button Section -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6 sticky bottom-4 z-10 border-2 border-purple-200">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="text-center md:text-left">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center justify-center md:justify-start">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Save Your Configuration
                        </h3>
                        <p class="text-sm text-gray-600">Click save to apply all changes to virtual queue settings</p>
                    </div>
                    <button type="submit" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold py-4 px-10 rounded-lg hover:from-purple-700 hover:to-pink-700 transform transition-all duration-200 hover:scale-105 shadow-xl whitespace-nowrap">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Settings
                        </span>
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>
