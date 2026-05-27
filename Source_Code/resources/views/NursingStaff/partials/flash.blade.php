@if(session('status'))
    <div class="nurse-alert nurse-alert-success">{{ session('status') }}</div>
@endif

@if($errors->any())
    <div class="nurse-alert nurse-alert-error">
        {{ $errors->first() }}
    </div>
@endif
