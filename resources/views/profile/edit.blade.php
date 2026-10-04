@extends('layouts.app')

@section('title', 'Account settings · CoastalCharmz')

@section('content')
<div class="shell" style="padding: 32px 16px 48px; max-width: 820px;">

    <header class="mb-7">
        <h1 style="font-size:28px;">Account settings</h1>
        <p class="muted" style="font-size:14.5px;">Your details and how you sign in.</p>
    </header>

    <div class="stack">
        <section class="card card-pad">
            <h2 style="font-size:18px; margin-bottom:4px;">Profile</h2>
            <p class="muted" style="font-size:13.5px; margin-bottom:18px;">
                We use this to reach you about your bookings.
            </p>
            @include('profile.partials.update-profile-information-form')
        </section>

        <section class="card card-pad">
            <h2 style="font-size:18px; margin-bottom:4px;">Password</h2>
            <p class="muted" style="font-size:13.5px; margin-bottom:18px;">
                Optional — you can always sign in with an emailed code instead.
            </p>
            @include('profile.partials.update-password-form')
        </section>

        <section class="card card-pad" style="border-color: #f5c6c6;">
            <h2 style="font-size:18px; margin-bottom:4px; color: var(--red-700);">Delete account</h2>
            <p class="muted" style="font-size:13.5px; margin-bottom:18px;">
                This permanently removes your account and booking history. It cannot be undone.
            </p>
            @include('profile.partials.delete-user-form')
        </section>
    </div>
</div>
@endsection
