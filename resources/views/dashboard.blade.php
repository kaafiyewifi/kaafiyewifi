@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        
        <!-- Dashboard stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold">Users</h3>
                <p class="text-3xl font-bold mt-2">12</p>
            </div>
            <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold">Today Revenue</h3>
                <p class="text-3xl font-bold mt-2">$0.00</p>
            </div>
            <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold">Total Revenue</h3>
                <p class="text-3xl font-bold mt-2">$0.00</p>
            </div>
                <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold">Total Revenue</h3>
                <p class="text-3xl font-bold mt-2">$0.00</p>
            </div>
            
        </div>
    </div>
@endsection