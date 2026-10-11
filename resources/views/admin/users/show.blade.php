<x-layouts.app :title="'Detail User'">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke daftar user
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- User Info --}}
        <div class="lg:col-span-1">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 text-center">
                    <div class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center text-2xl font-bold text-gray-600 mx-auto">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <h3 class="mt-3 text-lg font-medium text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    <div class="mt-2">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $user->roleBadgeCss() }}">
                            {{ ucfirst($user->role) }}
                        </span>
                        @if($user->is_active)
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Aktif</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Nonaktif</span>
                        @endif
                    </div>
                </div>
                <div class="p-4 space-y-3">
                    <a href="{{ route('admin.users.edit', $user) }}" class="block w-full rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white text-center shadow-sm hover:bg-blue-500">
                        Edit User
                    </a>
                    <div class="text-center text-xs text-gray-500">
                        Bergabung {{ $user->created_at->format('d M Y') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats & Tickets --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white shadow rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['reported'] }}</p>
                    <p class="text-sm text-gray-500">Ticket Dilaporkan</p>
                </div>
                <div class="bg-white shadow rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['assigned'] }}</p>
                    <p class="text-sm text-gray-500">Ticket Ditugaskan</p>
                </div>
                <div class="bg-white shadow rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['resolved'] }}</p>
                    <p class="text-sm text-gray-500">Ticket Diselesaikan</p>
                </div>
            </div>

            @if($user->isTechnician())
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Ticket Ditugaskan</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioritas</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($user->assignedTickets->take(10) as $ticket)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('tickets.show', $ticket) }}" class="text-sm text-blue-600 hover:text-blue-900">{{ Str::limit($ticket->title, 40) }}</a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $ticket->statusBadgeCss() }}">
                                                {{ $ticket->statusLabel() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $ticket->priorityBadgeCss() }}">
                                                {{ $ticket->priorityLabel() }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada ticket.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
