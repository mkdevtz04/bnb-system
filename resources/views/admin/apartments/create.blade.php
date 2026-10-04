@extends('layouts.app')

@section('title', 'Add property · Admin')

@section('content')
<div class="shell" style="padding: 32px 16px 48px; max-width: 900px;">

    <header class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 style="font-size:26px;">Add a property</h1>
        <a href="{{ route('admin.apartments') }}" class="btn btn-ghost">
            <i class="fa-solid fa-chevron-left"></i> Back
        </a>
    </header>

    <form method="POST" action="{{ route('admin.apartments.store') }}" enctype="multipart/form-data">
        @csrf

        @include('admin.apartments._form', ['apartment' => $apartment])

        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit" class="btn btn-primary btn-lg">Publish property</button>
            <a href="{{ route('admin.apartments') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
