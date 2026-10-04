{{-- Session feedback, rendered once in the layout so no page has to repeat it. --}}
@if (session('success') || session('status') || $errors->any())
    <div class="shell" style="padding-top:16px;">
        @if (session('success') || session('status'))
            <div class="alert alert-success" role="status">
                <i class="fa-solid fa-circle-check mt-0.5"></i>
                <span>{{ session('success') ?? session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error mt-3" role="alert">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
