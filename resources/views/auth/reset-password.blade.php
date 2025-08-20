<x-guest-layout>
    <div class="auth-header">
        <h2>Reset Password</h2>
        <p>Enter your new password</p>
    </div>

    <div class="auth-body">
        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div class="mb-3">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input id="email" 
                       class="form-control" 
                       type="email" 
                       name="email" 
                       value="{{ old('email', $request->email) }}" 
                       required 
                       autofocus 
                       autocomplete="username">
                
                @if ($errors->get('email'))
                    <div class="text-danger small mt-1">
                        {{ $errors->get('email')[0] }}
                    </div>
                @endif
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <input id="password" 
                       class="form-control" 
                       type="password" 
                       name="password" 
                       required 
                       autocomplete="new-password">
                
                @if ($errors->get('password'))
                    <div class="text-danger small mt-1">
                        {{ $errors->get('password')[0] }}
                    </div>
                @endif
            </div>

            <!-- Confirm Password -->
            <div class="mb-3">
                <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                <input id="password_confirmation" 
                       class="form-control" 
                       type="password" 
                       name="password_confirmation" 
                       required 
                       autocomplete="new-password">
                
                @if ($errors->get('password_confirmation'))
                    <div class="text-danger small mt-1">
                        {{ $errors->get('password_confirmation')[0] }}
                    </div>
                @endif
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-gradient">
                    {{ __('Reset Password') }}
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>