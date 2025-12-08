<div class="p-6 bg-white shadow-md rounded-lg">
    
    <div class="flex flex-cols">
        <div>
            <h2 class="text-xl font-semibold mb-4">Create Terms & Conditions</h2>
        </div>
        @if (session()->has('message'))
            <div class="p-3 mb-3 bg-green-500 text-white rounded-md">
                {{ session('message') }}
            </div>
        @endif
    </div>
        
    <form wire:submit.prevent="save">
        <div class="mb-4">
            <label class="block font-semibold">Title</label>
            <input type="text" wire:model="title" class="w-full border p-2 rounded-md bg-white dark:bg-dark-900 datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-4 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
            @error('title') <span class="text-error-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Terms Content</label>
            <textarea id="editor" wire:model="term_content" class="bg-white dark:bg-dark-900 datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-4 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"></textarea>
            @error('term_content') <span class="text-error-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <input type="hidden" wire:model="team_id">

        <button type="submit" class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 hover:bg-brand-600">
            Save
        </button>
    </form>
</div>

<script src="https://cdn.ckeditor.com/4.20.1-lts/standard/ckeditor.js"></script>
<script>
    document.addEventListener("livewire:init", function () {
        var editor = CKEDITOR.replace('editor');
        // Sync CKEditor content with Livewire
        editor.on('change', function () {
            @this.set('term_content', editor.getData());
        });

        // Optional: Set the initial content if Livewire has data
        Livewire.hook('message.processed', (message, component) => {
            editor.setData(@this.term_content);
        });
    });
</script>
