<x-guest-layout>
    <div class="auth-body">
        <div class="mb-3 text-muted small">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </div>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <input id="password" 
                       class="form-control" 
                       type="password" 
                       name="password" 
                       required 
                       autocomplete="current-password">
                
                @if ($errors->get('password'))
                    <div class="text-danger small mt-1">
                        {{ $errors->get('password')[0] }}
                    </div>
                @endif
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-gradient">
                    {{ __('Confirm') }}
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>