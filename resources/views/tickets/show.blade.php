<x-layouts.app :title="$ticket->title">
    {{-- Breadcrumb --}}
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('tickets.index') }}" class="hover:text-gray-700 transition-colors">Ticket</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900 font-medium">{{ Str::limit($ticket->title, 40) }}</span>
        </nav>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Ticket Header --}}
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $ticket->statusBadgeCss() }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $ticket->statusDotCss() }}"></span>
                            {{ $ticket->statusLabel() }}
                        </span>
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $ticket->priorityBadgeCss() }}">
                            {{ $ticket->priorityLabel() }}
                        </span>
                        <span class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                            {{ $ticket->categoryLabel() }}
                        </span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $ticket->title }}</h1>
                    <div class="flex items-center gap-3 mt-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-semibold">
                                {{ substr($ticket->user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $ticket->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $ticket->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="prose max-w-none">
                        <p class="text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $ticket->description }}</p>
                    </div>
                </div>
            </div>

            {{-- Comments --}}
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Komentar</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $ticket->comments->count() }} komentar</p>
                </div>
                <div class="p-6">
                    {{-- Comment Form --}}
                    <form method="POST" action="{{ route('comments.store', $ticket) }}" class="mb-8">
                        @csrf
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div class="flex-1">
                                <textarea name="comment" rows="3" required
                                          class="block w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"
                                          placeholder="Tulis komentar..."></textarea>
                                <div class="mt-3 flex justify-end">
                                    <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-2 rounded-xl text-sm font-semibold text-white shadow-md shadow-blue-500/25 hover:shadow-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        </svg>
                                        Kirim
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Comment List --}}
                    <div class="space-y-6">
                        @forelse($ticket->comments as $comment)
                            <div class="flex gap-4">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-400 to-gray-500 flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                                    {{ substr($comment->user->name, 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <div class="bg-gray-50 rounded-2xl px-5 py-4">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-sm font-semibold text-gray-900">{{ $comment->user->name }}</span>
                                            @if($comment->user->isAdmin())
                                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-semibold text-purple-700">Admin</span>
                                            @elseif($comment->user->isTechnician())
                                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700">Teknisi</span>
                                            @endif
                                            <span class="text-xs text-gray-400">&middot; {{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $comment->comment }}</p>
                                    </div>
                                    @if($comment->user_id === Auth::id() || Auth::user()->isAdmin())
                                        <div class="mt-2 flex items-center gap-3">
                                            <form method="POST" action="{{ route('comments.destroy', $comment) }}" onsubmit="return confirm('Hapus komentar ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors">Hapus</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <p class="mt-3 text-sm text-gray-500">Belum ada komentar.</p>
                                <p class="text-xs text-gray-400">Jadilah yang pertama berkomentar!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Actions --}}
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Aksi</h3>
                </div>
                <div class="p-4 space-y-3">
                    @if(Auth::user()->isAdmin() || $ticket->user_id === Auth::id())
                        <a href="{{ route('tickets.edit', $ticket) }}" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Ticket
                        </a>
                    @endif

                    @php
                        $canChangeStatus = Auth::user()->isAdmin()
                            || (Auth::user()->isTechnician() && $ticket->technician_id === Auth::id());
                    @endphp

                    @if($canChangeStatus)
                        {{-- Status Update --}}
                        <div class="space-y-2">
                            @if($ticket->status !== 'in_progress')
                                <form method="POST" action="{{ route('tickets.update', $ticket) }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" name="status" value="in_progress" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl bg-amber-500 text-sm font-semibold text-white hover:bg-amber-600 transition-colors shadow-md shadow-amber-500/25">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        </svg>
                                        Mulai Kerjakan
                                    </button>
                                </form>
                            @endif
                            @if($ticket->status !== 'resolved')
                                <form method="POST" action="{{ route('tickets.update', $ticket) }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" name="status" value="resolved" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl bg-emerald-500 text-sm font-semibold text-white hover:bg-emerald-600 transition-colors shadow-md shadow-emerald-500/25">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Tandai Selesai
                                    </button>
                                </form>
                            @endif
                            @if($ticket->status !== 'closed')
                                <form method="POST" action="{{ route('tickets.update', $ticket) }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" name="status" value="closed" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl bg-gray-500 text-sm font-semibold text-white hover:bg-gray-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Tutup Ticket
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif

                    @if(Auth::user()->isAdmin())
                        {{-- Assign Technician --}}
                        <div class="pt-2">
                            <form method="POST" action="{{ route('tickets.assign', $ticket) }}">
                                @csrf
                                <label for="technician_id" class="block text-sm font-semibold text-gray-700 mb-2">Assign Teknisi</label>
                                <select name="technician_id" id="technician_id" class="block w-full py-2.5 px-4 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm mb-2">
                                    <option value="">Pilih teknisi</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}" {{ $ticket->technician_id === $tech->id ? 'selected' : '' }}>
                                            {{ $tech->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-sm font-semibold text-white shadow-md shadow-blue-500/25 hover:shadow-lg transition-all">
                                    Assign Sekarang
                                </button>
                            </form>
                        </div>
                    @endif

                    @if(Auth::user()->isAdmin() || $ticket->user_id === Auth::id())
                        <div class="pt-2">
                            <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" onsubmit="return confirm('Hapus ticket ini? Tindakan ini tidak dapat dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl bg-red-50 text-sm font-semibold text-red-600 hover:bg-red-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Hapus Ticket
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Info --}}
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Detail</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Pelapor</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $ticket->user->name }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Teknisi</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $ticket->technician->name ?? 'Belum ditugaskan' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Dibuat</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $ticket->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>

                    @if($ticket->resolved_at)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Diselesaikan</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $ticket->resolved_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($ticket->closed_at)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Ditutup</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $ticket->closed_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- History --}}
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Riwayat</h3>
                </div>
                <div class="p-4">
                    @forelse($ticket->histories as $history)
                        <div class="flex gap-3 p-3 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-400">{{ $history->created_at->diffForHumans() }}</p>
                                <p class="text-sm text-gray-700 mt-0.5">
                                    <span class="font-semibold">{{ $history->user->name }}</span> mengubah
                                    <span class="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded">{{ $history->field }}</span>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <span class="text-red-500">{{ $history->old_value ?: '-' }}</span>
                                    →
                                    <span class="text-green-600 font-semibold">{{ $history->new_value ?: '-' }}</span>
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6">
                            <p class="text-sm text-gray-500">Belum ada riwayat perubahan.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
