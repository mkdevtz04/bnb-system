@extends('layouts.app')

@section('title', 'Messages · ' . $booking->display_reference)

@section('content')
@php
    $me = auth()->user();
    $other = $me->isAdmin() ? $booking->user : null;
@endphp

<div class="shell" style="padding: 32px 16px 48px; max-width: 760px;">

    <a href="{{ route('messages.index') }}" class="btn btn-ghost btn-sm mb-4">
        <i class="fa-solid fa-chevron-left" style="font-size:11px;"></i> All messages
    </a>

    <div class="card overflow-hidden">
        {{-- Header --}}
        <div class="flex items-center justify-between gap-4 px-5 py-4"
             style="border-bottom:1px solid var(--ink-200);">
            <div class="flex items-center gap-3 min-w-0">
                @if ($other)
                    <span class="avatar shrink-0">{{ $other->initials }}</span>
                @else
                    <span class="avatar shrink-0" style="background:var(--brand-700);">
                        <i class="fa-solid fa-house"></i>
                    </span>
                @endif
                <div class="min-w-0">
                    <h1 class="truncate" style="font-size:17px;">
                        {{ $other?->display_name ?? 'The host' }}
                    </h1>
                    <p class="muted truncate" style="font-size:12.5px;">
                        {{ $booking->apartment->name }}
                        <span class="price">· {{ $booking->display_reference }}</span>
                    </p>
                </div>
            </div>

            <a href="{{ route('bookings.confirmation', $booking) }}" class="btn btn-ghost btn-sm shrink-0">
                <span class="hidden sm:inline">Booking</span>
                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:11px;"></i>
            </a>
        </div>

        {{-- Thread --}}
        <div class="flex flex-col gap-3 p-5" id="thread"
             style="height: 460px; overflow-y: auto; background: var(--canvas);">
            @forelse ($messages as $message)
                @php $mine = $message->sender_id === $me->id; @endphp
                <div class="flex flex-col {{ $mine ? 'items-end' : 'items-start' }}">
                    {{-- pre-wrap, because the composer allows Shift+Enter: without
                         it every multi-line message would collapse to one line. --}}
                    <div style="max-width: 78%; padding: 10px 14px; font-size: 14.5px; line-height: 1.55;
                                border-radius: var(--r-lg); white-space: pre-wrap; overflow-wrap: anywhere;
                                {{ $mine
                                    ? 'background: var(--brand-700); color: #fff; border-bottom-right-radius: 4px;'
                                    : 'background: var(--surface); border: 1px solid var(--ink-200); border-bottom-left-radius: 4px;' }}">{{ $message->message }}</div>
                    <span class="muted mt-1" style="font-size:11.5px;">
                        {{ $mine ? 'You' : $message->sender->display_name }} ·
                        {{ $message->created_at->diffForHumans() }}
                    </span>
                </div>
            @empty
                <div class="flex flex-1 flex-col items-center justify-center text-center">
                    <i class="fa-regular fa-comments" style="font-size:36px; color:var(--ink-300);"></i>
                    <p class="muted mt-3" style="font-size:14px;">No messages yet — say hello.</p>
                </div>
            @endforelse
            <span id="latest"></span>
        </div>

        {{-- Composer --}}
        <form method="POST" action="{{ route('messages.store', $booking) }}"
              class="p-4" style="border-top:1px solid var(--ink-200);"
              x-data="composer()" x-ref="form">
            @csrf

            <div class="flex items-end gap-2">
                <label class="sr-only" for="message">Message</label>

                {{-- A textarea, not an input, so a message can have paragraphs.
                     Enter sends and Shift+Enter adds a line — the convention
                     people already expect from every other chat they use. --}}
                <textarea id="message" name="message" required maxlength="2000" rows="1"
                          class="textarea flex-1"
                          style="resize:none; max-height:160px; overflow-y:auto; line-height:1.5;"
                          placeholder="Write a message…"
                          x-ref="input"
                          x-on:input="grow()"
                          x-on:keydown.enter="onEnter($event)">{{ old('message') }}</textarea>

                <button type="submit" class="btn btn-primary shrink-0" style="height:44px;">
                    <i class="fa-regular fa-paper-plane"></i>
                    <span class="hidden sm:inline">Send</span>
                </button>
            </div>

            <p class="muted mt-2" style="font-size:11.5px;">
                <kbd style="font-family:inherit; font-weight:600;">Enter</kbd> to send ·
                <kbd style="font-family:inherit; font-weight:600;">Shift + Enter</kbd> for a new line
            </p>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function composer() {
        return {
            /**
             * Grow with the content instead of scrolling inside a one-line box.
             * Height is reset first so the textarea can shrink again when text is
             * deleted, not just grow.
             */
            grow() {
                const el = this.$refs.input;
                el.style.height = 'auto';
                el.style.height = Math.min(el.scrollHeight, 160) + 'px';
            },

            onEnter(event) {
                // Shift+Enter is a newline, so let the browser handle it.
                if (event.shiftKey) return;

                // So is Enter while composing in an IME — intercepting it there
                // would send half-finished text in languages that need one.
                if (event.isComposing || event.keyCode === 229) return;

                event.preventDefault();

                if (this.$refs.input.value.trim() === '') return;

                this.$refs.form.submit();
            },

            init() {
                this.grow();
            },
        };
    }

    // Open on the newest message.
    const thread = document.getElementById('thread');
    if (thread) thread.scrollTop = thread.scrollHeight;
</script>
@endpush
@endsection
