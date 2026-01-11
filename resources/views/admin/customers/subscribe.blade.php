<form method="POST" action="{{ route('admin.customers.subscribe',$customer) }}">
@csrf

<div class="space-y-4">

{{-- PLAN --}}
<select name="plan_id" required class="w-full border rounded p-2">
@foreach($plans as $plan)
<option value="{{ $plan->id }}">
{{ $plan->name }} - ${{ $plan->price }}
</option>
@endforeach
</select>

{{-- DURATION --}}
<select name="duration" required class="w-full border rounded p-2">
<option value="7">7 Days</option>
<option value="30">30 Days</option>
<option value="90">90 Days</option>
</select>

<button class="bg-purple-700 text-white px-4 py-2 rounded">
Subscribe Customer
</button>

</div>
</form>
