<section>
    <header>
        {{-- استخدمنا كلاسات Bootstrap لتنسيق العناوين --}}
        <h2 class="h5 font-weight-bold text-dark">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-muted">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    {{-- هذا الفورم لإعادة إرسال بريد التحقق، يبقى كما هو --}}
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    {{-- هذا هو الفورم الرئيسي لتحديث الملف الشخصي --}}
    <form method="post" action="{{ route('profile.update') }}" class="mt-4">
        @csrf
        @method('patch')

        {{-- حقل الاسم --}}
        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            {{-- عرض رسائل الخطأ الخاصة بالاسم --}}
            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- حقل البريد الإلكتروني --}}
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
            {{-- عرض رسائل الخطأ الخاصة بالبريد الإلكتروني --}}
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror

            {{-- التحقق إذا كان البريد الإلكتروني بحاجة لتأكيد --}}
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="small text-muted">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="btn btn-link text-decoration-underline p-0 small">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    {{-- رسالة التأكيد بعد إرسال الرابط --}}
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 small fw-bold text-success">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- زر الحفظ ورسالة التأكيد --}}
        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

            {{-- رسالة "تم الحفظ" التي تختفي بعد ثانيتين --}}
            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-muted m-0"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
