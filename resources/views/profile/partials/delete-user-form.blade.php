<section class="card">
    <header class="card-header">
        <h2 class="text-lg font-semibold">Delete Account</h2>
        <p class="mt-1 text-sm muted">
            Once your account is deleted, all of its resources and data will be permanently deleted.
        </p>
    </header>

    <div class="card-body">
        <button
            x-data
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="inline-flex items-center rounded-xl px-4 py-2 font-semibold text-white bg-red-600 hover:bg-red-700"
        >
            Delete Account
        </button>

        <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
            <form method="post" action="{{ route('profile.destroy') }}" class="p-6 space-y-4">
                @csrf
                @method('delete')

                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Are you sure you want to delete your account?
                </h2>

                <p class="text-sm muted">
                    Please enter your password to confirm you would like to permanently delete your account.
                </p>

                <div>
                    <x-input-label for="password_delete" :value="__('Password')" />
                    <x-text-input id="password_delete" name="password" type="password" class="mt-1"
                                  autocomplete="current-password" />
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button"
                            class="btn-outline"
                            x-on:click="$dispatch('close')">
                        Cancel
                    </button>

                    <button type="submit"
                            class="inline-flex items-center rounded-xl px-4 py-2 font-semibold text-white bg-red-600 hover:bg-red-700">
                        Delete Account
                    </button>
                </div>
            </form>
        </x-modal>
    </div>
</section>
