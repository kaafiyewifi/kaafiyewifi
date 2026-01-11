@extends('layouts.admin')

@section('content')

<h1 class="text-2xl font-bold mb-6 text-left">
    Dashboard
</h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <div class="bg-white rounded-xl p-6 shadow text-center">
        <p class="text-sm text-gray-500">Today Revenue</p>
        <p class="text-3xl font-bold text-orange-600">$0.00</p>
    </div>

    <div class="bg-white rounded-xl p-6 shadow text-center">
        <p class="text-sm text-gray-500">This Month</p>
        <p class="text-3xl font-bold text-orange-600">$0.00</p>
    </div>

    <div class="bg-white rounded-xl p-6 shadow text-center">
        <p class="text-sm text-gray-500">Total Revenue</p>
        <p class="text-3xl font-bold text-orange-600">$0.00</p>
    </div>

</div>

@endsection
