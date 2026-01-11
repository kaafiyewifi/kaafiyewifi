<x-admin-layout>

<div class="max-w-2xl mx-auto bg-white rounded-xl shadow p-6">

    <h2 class="text-xl font-semibold mb-6 text-gray-800">
        Create Subscription Plan
    </h2>

    {{-- ERROR MESSAGES --}}
    @if ($errors->any())
        <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
            <ul class="text-sm list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.subscription-plans.store') }}"
          class="space-y-4">
        @csrf

        {{-- PLAN NAME --}}
        <div>
            <label class="block text-sm font-medium mb-1">Plan Name</label>
            <input type="text"
                   name="name"
                   value="{{ old('name') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Example: Gold Plan"
                   required>
        </div>

        {{-- PRICE --}}
        <div>
            <label class="block text-sm font-medium mb-1">Price</label>
            <input type="number"
                   step="0.01"
                   name="price"
                   value="{{ old('price') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="10.00"
                   required>
        </div>

        {{-- SPEED LIMIT --}}
        <div>
            <label class="block text-sm font-medium mb-1">Speed Limit</label>
            <input type="text"
                   name="speed_limit"
                   value="{{ old('speed_limit') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="5 Mbps / 10 Mbps">
        </div>

        {{-- BANDWIDTH LIMIT --}}
        <div>
            <label class="block text-sm font-medium mb-1">
                Bandwidth Limit (MB)
            </label>
            <input type="number"
                   name="bandwidth_limit"
                   value="{{ old('bandwidth_limit') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="10240">
        </div>

        {{-- DATA TYPE --}}
        <div>
            <label class="block text-sm font-medium mb-1">Data Type</label>
            <select name="data_type"
                    class="w-full border rounded-lg px-3 py-2"
                    required>
                <option value="">Select</option>
                <option value="limited"
                    {{ old('data_type')=='limited'?'selected':'' }}>
                    Limited
                </option>
                <option value="unlimited"
                    {{ old('data_type')=='unlimited'?'selected':'' }}>
                    Unlimited
                </option>
            </select>
        </div>

        {{-- SIMULTANEOUS USERS --}}
        <div>
            <label class="block text-sm font-medium mb-1">
                Simultaneous Devices
            </label>
            <input type="number"
                   name="simultaneous_users"
                   value="{{ old('simultaneous_users',1) }}"
                   class="w-full border rounded-lg px-3 py-2"
                   min="1"
                   required>
        </div>

        {{-- STATUS --}}
        <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select name="status"
                    class="w-full border rounded-lg px-3 py-2"
                    required>
                <option value="active"
                    {{ old('status')=='active'?'selected':'' }}>
                    Active
                </option>
                <option value="inactive"
                    {{ old('status')=='inactive'?'selected':'' }}>
                    Inactive
                </option>
            </select>
        </div>

        {{-- ACTIONS --}}
        <div class="flex gap-3 pt-4">
            <a href="{{ route('admin.subscription-plans.index') }}"
               class="flex-1 text-center border rounded-lg py-2">
                Cancel
            </a>

            <button type="submit"
                    class="flex-1 bg-[#5b146b] text-white rounded-lg py-2 hover:bg-purple-800">
                Save Plan
            </button>
        </div>

    </form>
</div>

</x-admin-layout>
