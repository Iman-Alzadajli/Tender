<x-guest-layout>
    <div class="auth-header">
        <h2>Create Account</h2>
        <p>Join us today</p>
    </div>

    <div class="auth-body">
        <!-- <form method="POST" action="{{ route('register') }}"> -->
            @csrf

            <!-- Name -->
            <div class="mb-3">
                <label for="name" class="form-label">{{ __('Name') }}</label>
                <input id="name" 
                       class="form-control" 
                       type="text" 
                       name="name" 
                       value="{{ old('name') }}" 
                       required 
                       autofocus 
                       autocomplete="name">
                
                @if ($errors->get('name'))
                    <div class="text-danger small mt-1">
                        {{ $errors->get('name')[0] }}
                    </div>
                @endif
            </div>

            <!-- Email Address -->
            <div class="mb-3">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input id="email" 
                       class="form-control" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
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

            <div class="d-flex justify-content-between align-items-center">
                <a class="text-gradient text-decoration-none small" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <button type="submit" class="btn btn-gradient">
                    {{ __('Register') }}
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>