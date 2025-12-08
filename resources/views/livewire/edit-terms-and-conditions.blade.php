<div class="p-4">
    <h2 class="text-xl font-semibold mb-4">{{ __('setting.Edit Terms & Conditions') }}</h2>
    <div class="p-6 bg-white shadow-md rounded-lg">

        @if (session()->has('message'))
            <div class="p-3 mb-3 bg-green-500 text-white rounded-md">
                {{ session('message') }}
            </div>
        @endif

        <form wire:submit.prevent="update">
            <div class="mb-4">
                <label class="block font-semibold">{{ __('setting.Title') }}</label>
                <input type="text" wire:model="title" class="w-full border p-2 rounded-md">
                @error('title') <span class="text-error-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-3">{{ __('setting.Terms and Conditions Content') }}</label>
                <textarea id="editor" name="content" class="w-full border border-gray-300 p-2 rounded-md" style="height:200px"></textarea>
                <!--div><textarea wire:model="term_content" class="w-full border border-gray-300 p-2 rounded-md">{{ $term_content  ?? ''}}</textarea>
                @error('term_content') <span class="text-error-500 text-sm">{{ $message }}</span> @enderror </!--div-->
            </div>

            <button type="submit" class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 hover:bg-brand-600">
                {{ __('setting.Save') }}
            </button>
        </form>
    </div>
</div>


<!-- <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
  ClassicEditor
    .create(document.querySelector('#editor'))
    .catch(error => console.error(error));
</script> -->

<script src="{{ asset('js/tinymce.min.js') }}"></script>
<script>
  tinymce.init({
    selector: 'textarea#editor',
    menubar: true
  });
</script>

