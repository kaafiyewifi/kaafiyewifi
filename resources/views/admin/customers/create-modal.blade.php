<div x-show="showCreate" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <form method="POST" action="{{ route('admin.customers.store') }}"
          class="bg-white w-full max-w-md p-6 rounded-xl">
        @csrf

        <h2 class="text-lg font-semibold mb-4">Create Customer</h2>

        <input name="name" class="w-full mb-3 border p-2 rounded" placeholder="Name">
        <input name="phone" class="w-full mb-3 border p-2 rounded" placeholder="Phone">
        <input name="address" class="w-full mb-3 border p-2 rounded" placeholder="Address">

        <select name="location_id[]" multiple class="w-full border p-2 rounded mb-4">
            @foreach($locations as $l)
                <option value="{{ $l->id }}">{{ $l->name }}</option>
            @endforeach
        </select>

        <div class="flex gap-3">
            <button type="button" @click="showCreate=false" class="flex-1 border rounded py-2">Cancel</button>
            <button class="flex-1 bg-[#5b146b] text-white rounded py-2">Save</button>
        </div>
    </form>
</div>
