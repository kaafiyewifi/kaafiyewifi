<div
    x-show="showCreateCustomer"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 z-50 flex items-start justify-center pt-16"
>
    <div class="absolute inset-0 bg-black/50"></div>

    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md min-h-[550px] p-8">
        <button @click="showCreateCustomer=false"
            class="absolute top-4 right-4 text-gray-400 text-xl">✕</button>

        <h2 class="text-xl font-semibold text-center mb-8">
            Register Customer
        </h2>

        <form method="POST" action="{{ route('admin.customers.store') }}" class="space-y-6">
            @csrf

            <input name="name" required placeholder="Customer Name"
                class="w-full px-4 py-3 border rounded-xl">

            <div class="flex border rounded-xl overflow-hidden">
                <div class="px-4 py-3 bg-gray-100">+252</div>
                <input name="phone" required placeholder="61xxxxxxx"
                    class="flex-1 px-4 py-3">
            </div>

            <input name="address" placeholder="Address"
                class="w-full px-4 py-3 border rounded-xl">

            <select name="location_id[]" multiple
                class="w-full border rounded-xl px-3 py-3">
                @foreach($locations as $location)
                    <option value="{{ $location->id }}">
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>

            <p class="text-sm text-center text-gray-500">
                Default password: <b>123456</b>
            </p>

            <div class="flex gap-4 pt-4">
                <button type="button" @click="showCreateCustomer=false"
                    class="flex-1 border rounded-xl py-3">
                    Cancel
                </button>

                <button type="submit"
                    class="flex-1 bg-[#5b146b] text-white rounded-xl py-3">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>
