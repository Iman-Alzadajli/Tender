<x-guest-layout>
    <div class="auth-header">
        <h2>Welcome Back</h2>
        <p>Please sign in to your account</p>
    </div>

    <div class="auth-body">
        <!-- Session Status -->
        @if (session('status'))
        <div class="alert alert-info small" role="alert">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email"
                    class="form-control"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username">

                @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input id="password"
                    class="form-control"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password">

                @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                <label class="form-check-label" for="remember_me">
                    Remember me
                </label>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                @if (Route::has('password.request'))
                <a class="text-gradient text-decoration-none small" href="{{ route('password.request') }}">
                    Forgot your password?
                </a>
                @endif



                <button type="submit" class="btn btn-outline-info ">
                    Log in
                </button>
            </div>

      


        </form>
    </div>
</x-guest-layout>