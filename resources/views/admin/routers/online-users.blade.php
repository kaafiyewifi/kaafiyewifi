<x-admin-layout>

<div class="max-w-6xl mx-auto px-4 py-6">

<h1 class="text-2xl font-bold mb-4 text-primary">
Online Hotspot Users
</h1>

<div class="bg-white rounded-xl shadow border overflow-hidden">

<table class="w-full text-sm">
<thead class="bg-slate-50 text-left">
<tr>
<th class="px-4 py-3">User</th>
<th class="px-4 py-3">IP</th>
<th class="px-4 py-3">MAC</th>
<th class="px-4 py-3">Uptime</th>
<th class="px-4 py-3">Status</th>
</tr>
</thead>

<tbody class="divide-y">

@forelse($users as $u)
<tr class="hover:bg-slate-50">
<td class="px-4 py-2 font-medium">{{ $u['user'] ?? '-' }}</td>
<td class="px-4 py-2">{{ $u['address'] ?? '-' }}</td>
<td class="px-4 py-2">{{ $u['mac-address'] ?? '-' }}</td>
<td class="px-4 py-2">{{ $u['uptime'] ?? '-' }}</td>
<td class="px-4 py-2">
<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">
Online
</span>
</td>
</tr>
@empty
<tr>
<td colspan="5" class="text-center py-8 text-gray-400">
No online users
</td>
</tr>
@endforelse

</tbody>
</table>

</div>

</div>

</x-admin-layout>
