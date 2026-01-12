<x-admin-layout>

<div class="flex justify-center">
    <div class="w-full max-w-lg bg-white rounded-xl shadow border p-6">

        <h2 class="text-xl font-semibold mb-4">
            Add Subscription – {{ $customer->name }}
        </h2>

        <form method="POST"
              action="{{ route('admin.customers.subscribe.store',$customer) }}"
              class="space-y-4">
            @csrf

            {{-- PLAN --}}
            <div>
                <label class="text-sm font-medium">Package</label>
                <select name="plan_id"
                        required
                        class="w-full border rounded-lg px-3 py-2">
                    <option value="">Select plan</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">
                            {{ $plan->name }} - ${{ $plan->price }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- DAYS --}}
            <div>
                <label class="text-sm font-medium">Duration (days)</label>
                <input type="number"
                       name="days"
                       min="1"
                       required
                       class="w-full border rounded-lg px-3 py-2"
                       placeholder="30">
            </div>

            {{-- ACTIONS --}}
            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('admin.customers.show',$customer) }}"
                   class="px-4 py-2 border rounded-lg">
                    Cancel
                </a>

                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg">
                    Save Subscription
                </button>
            </div>
        </form>

    </div>
</div>

</x-admin-layout>
