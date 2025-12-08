<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Update Profile Information
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Update your account's profile information and email address.
        </p>
    </header>

    <form method="post" action="{{ route('superadmin.profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block font-medium text-sm text-gray-700">Name</label>
            <input id="name" name="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('name', $superadmin->name) }}" required autofocus autocomplete="name" />
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
            <input id="email" name="email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('email', $superadmin->email) }}" required autocomplete="username" />
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Save
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600"
                >Saved.</p>
            @endif
        </div>
    </form>
</section>

