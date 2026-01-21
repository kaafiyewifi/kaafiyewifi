<form method="post" action="{{ route('profile.update') }}" class="space-y-4">
    @csrf
    @method('patch')

    <div>
        <label class="block text-xs font-bold tracking-wide uppercase text-gray-600 dark:text-gray-300">Name</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus
               class="mt-2 w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900
                      placeholder:text-gray-400
                      focus:border-[#ff4b2b] focus:ring-2 focus:ring-[#ff4b2b]/25
                      dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <label class="block text-xs font-bold tracking-wide uppercase text-gray-600 dark:text-gray-300">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
               class="mt-2 w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900
                      placeholder:text-gray-400
                      focus:border-[#ff4b2b] focus:ring-2 focus:ring-[#ff4b2b]/25
                      dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500" />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

    <div class="pt-2 flex items-center justify-end gap-3">
        @if (session('status') === 'profile-updated')
            <span class="text-xs font-semibold text-green-600 dark:text-green-400">Saved</span>
        @endif

        <button type="submit" class="btn-primary">
            Save
        </button>
    </div>
</form>
