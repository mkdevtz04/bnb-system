@extends('layouts.app')

@section('title', 'Messages · CoastalCharmz')

@section('content')
<div class="shell" style="padding: 32px 16px 48px; max-width: 820px;">

    <header class="mb-6">
        <h1 style="font-size:28px; margin-bottom:6px;">Messages</h1>
        <p class="muted" style="font-size:14.5px;">
            {{ $user->isAdmin()
                ? 'Every conversation with your guests.'
                : 'Your conversations with the host.' }}
        </p>
    </header>

    <div class="card overflow-hidden">
        <div class="divide-y-soft">
            @forelse ($threads as $booking)
                @php
                    // Who the other party is depends on who is reading.
                    $other = $user->isAdmin() ? $booking->user : null;
                    $last = $booking->latestMessage;
                    $fromMe = $last && $last->sender_id === $user->id;
                    $unread = (int) $booking->unread_count;
                @endphp

                <a href="{{ route('messages.show', $booking) }}"
                   class="flex items-start gap-4 p-4 hover:bg-slate-50"
                   style="text-decoration:none; color:inherit;
                          {{ $unread ? 'background: var(--brand-50);' : '' }}">

                    @if ($other)
                        <span class="avatar shrink-0">{{ $other->initials }}</span>
                    @else
                        <span class="avatar shrink-0" style="background:var(--brand-700);">
                            <i class="fa-solid fa-house"></i>
                        </span>
                    @endif

                    <div class="min-w-0 flex-1">
                        <div class="flex items-baseline justify-between gap-3">
                            <strong class="truncate" style="font-size:15px; color:var(--ink-900);">
                                {{ $other?->display_name ?? 'The host' }}
                            </strong>
                            @if ($last)
                                <span class="muted shrink-0" style="font-size:12px;">
                                    {{ $last->created_at->diffForHumans(short: true) }}
                                </span>
                            @endif
                        </div>

                        <div class="muted truncate" style="font-size:13px; margin-top:1px;">
                            {{ $booking->apartment->name }}
                            <span class="price">· {{ $booking->display_reference }}</span>
                        </div>

                        @if ($last)
                            {{-- Collapsed to one line here; newlines belong in the
                                 thread, not in a list row. --}}
                            <p class="truncate" style="font-size:13.5px; margin-top:4px;
                                      color: {{ $unread ? 'var(--ink-900)' : 'var(--ink-500)' }};
                                      font-weight: {{ $unread ? '600' : '400' }};">
                                @if ($fromMe)
                                    <span class="muted">You:</span>
                                @endif
                                {{ Str::limit(preg_replace('/\s+/', ' ', $last->message), 90) }}
                            </p>
                        @endif
                    </div>

                    <div class="flex shrink-0 flex-col items-end gap-2">
                        @if ($unread)
                            <span class="badge badge-brand">{{ $unread }} new</span>
                        @endif

                        @if ($booking->isPending())
                            <span class="badge badge-warning">Pending</span>
                        @elseif ($booking->isCancelled())
                            <span class="badge badge-danger">Cancelled</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="text-center" style="padding:56px 24px;">
                    <i class="fa-regular fa-comments" style="font-size:40px; color:var(--ink-300);"></i>
                    <h2 style="font-size:20px; margin:16px 0 8px;">No messages yet</h2>
                    <p class="muted" style="font-size:14.5px;">
                        {{ $user->isAdmin()
                            ? 'Conversations started by your guests will appear here.'
                            : 'Open any booking to ask the host a question.' }}
                    </p>
                    <a href="{{ $user->isAdmin() ? route('admin.bookings') : route('bookings.history') }}"
                       class="btn btn-secondary mt-5">
                        {{ $user->isAdmin() ? 'View bookings' : 'My bookings' }}
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    @if ($threads->hasPages())
        <div class="mt-6">{{ $threads->links() }}</div>
    @endif
</div>
@endsection
