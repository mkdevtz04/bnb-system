@extends('layouts.app')

@section('title', 'Messages · Admin')

@section('content')
<div class="shell" style="padding: 32px 16px 48px; max-width: 900px;">

    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 style="font-size:28px;">Contact messages</h1>
            <p class="muted" style="font-size:14.5px;">{{ $inquiries->total() }} received</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            <i class="fa-solid fa-chevron-left"></i> Dashboard
        </a>
    </header>

    <div class="mb-5 flex gap-2">
        <a href="{{ route('admin.inquiries') }}"
           class="chip {{ request()->boolean('unread') ? '' : 'chip-active' }}">All</a>
        <a href="{{ route('admin.inquiries', ['unread' => 1]) }}"
           class="chip {{ request()->boolean('unread') ? 'chip-active' : '' }}">Unread</a>
        <a href="{{ route('admin.inquiries', ['awaiting' => 1]) }}"
           class="chip {{ request()->boolean('awaiting') ? 'chip-active' : '' }}">Awaiting reply</a>
    </div>

    <div class="stack">
        @forelse ($inquiries as $inquiry)
            <article class="card card-pad"
                     @unless ($inquiry->is_read) style="border-left:4px solid var(--brand-500);" @endunless
                     x-data="{ replying: false }">

                <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <strong style="font-size:15px;">{{ $inquiry->name }}</strong>
                            @unless ($inquiry->is_read)
                                <span class="badge badge-info">New</span>
                            @endunless
                            @if ($inquiry->hasReply())
                                <span class="badge badge-success">
                                    <i class="fa-solid fa-reply"></i> Replied
                                </span>
                            @endif
                        </div>
                        <a href="mailto:{{ $inquiry->email }}"
                           style="color:var(--brand-700); font-size:13.5px; text-decoration:none;">{{ $inquiry->email }}</a>
                    </div>
                    <span class="muted" style="font-size:12.5px;">{{ $inquiry->created_at->diffForHumans() }}</span>
                </div>

                @if ($inquiry->subject)
                    <h2 style="font-size:16px; font-family:var(--font-body); margin-bottom:6px;">{{ $inquiry->subject }}</h2>
                @endif

                <p style="font-size:14.5px; line-height:1.65; white-space:pre-line;">{{ $inquiry->message }}</p>

                {{-- What was already sent, so nobody answers the same person twice. --}}
                @if ($inquiry->hasReply())
                    <div class="panel mt-4" style="padding:14px 16px; background:var(--brand-50);
                                border-color:var(--brand-100);">
                        <p class="muted" style="font-size:11px; letter-spacing:.06em; text-transform:uppercase;
                                  font-weight:600; margin-bottom:6px;">
                            Replied {{ $inquiry->replied_at->diffForHumans() }}
                            @if ($inquiry->repliedBy) by {{ $inquiry->repliedBy->display_name }} @endif
                        </p>
                        <p style="font-size:14px; line-height:1.6; white-space:pre-line;">{{ $inquiry->reply }}</p>
                    </div>
                @endif

                <div class="mt-4 flex flex-wrap gap-2" style="border-top:1px solid var(--ink-200); padding-top:14px;">
                    <button type="button" class="btn btn-primary btn-sm" @click="replying = !replying"
                            :aria-expanded="replying ? 'true' : 'false'">
                        <i class="fa-solid fa-reply"></i>
                        <span x-text="replying ? 'Cancel' : @js($inquiry->hasReply() ? 'Reply again' : 'Reply')"></span>
                    </button>

                    @unless ($inquiry->is_read)
                        <form method="POST" action="{{ route('admin.inquiries.read', $inquiry) }}">
                            @csrf
                            <button class="btn btn-ghost btn-sm">Mark as read</button>
                        </form>
                    @endunless

                    {{-- Kept as a secondary option for anyone who does have a mail
                         client set up and would rather answer from their own
                         inbox. It is no longer the only way to reply, because on a
                         machine without one it silently does nothing. --}}
                    <a href="mailto:{{ $inquiry->email }}?subject={{ rawurlencode($inquiry->replySubject()) }}"
                       class="btn btn-ghost btn-sm" title="Open in your mail app">
                        <i class="fa-regular fa-envelope"></i>
                        <span class="hidden sm:inline">Open in mail app</span>
                    </a>
                </div>

                {{-- Reply box --}}
                <form x-show="replying" x-cloak x-transition method="POST"
                      action="{{ route('admin.inquiries.reply', $inquiry) }}" class="mt-4">
                    @csrf

                    <label class="field-label" for="reply-{{ $inquiry->id }}">
                        Your reply to {{ $inquiry->name }}
                    </label>

                    <textarea id="reply-{{ $inquiry->id }}" name="reply" rows="5" required
                              maxlength="5000" class="textarea"
                              placeholder="Write your reply — it will be emailed to {{ $inquiry->email }}.">{{ old('reply') }}</textarea>

                    @error('reply') <p class="field-error">{{ $message }}</p> @enderror

                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-regular fa-paper-plane"></i> Send reply
                        </button>
                        <span class="muted" style="font-size:12.5px;">
                            Sent from {{ config('mail.from.address') }} · replies come back to you
                        </span>
                    </div>
                </form>
            </article>
        @empty
            <div class="card card-pad text-center" style="padding:56px 24px;">
                <i class="fa-regular fa-envelope" style="font-size:40px; color:var(--ink-300);"></i>
                <h2 style="font-size:20px; margin:16px 0 8px;">No messages yet</h2>
                <p class="muted" style="font-size:14.5px;">
                    Messages sent from the homepage contact form arrive here.
                </p>
            </div>
        @endforelse
    </div>

    @if ($inquiries->hasPages())
        <div class="mt-8">{{ $inquiries->links() }}</div>
    @endif
</div>
@endsection
