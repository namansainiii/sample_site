@if(session('success') || session('warning') || $errors->any())
    @if($wrap ?? false)<div class="container" style="padding-top:16px;">@endif
    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning" role="alert">{{ session('warning') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error" role="alert">
            <strong>Please fix the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if($wrap ?? false)</div>@endif
@endif
