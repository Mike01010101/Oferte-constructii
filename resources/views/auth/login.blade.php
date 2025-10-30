@extends('layouts.guest')

@section('content')
<div class="row justify-content-center">
    <div class="col-sm-10 col-md-8 col-lg-5">
        <div class="card">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">Conectare</h3>
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Adresă de E-mail') }}</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Parolă') }}</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                        @error('password')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                {{ __('Ține-mă minte') }}
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                            <a class="btn btn-link btn-sm" href="{{ route('password.request') }}">
                                {{ __('Am uitat parola') }}
                            </a>
                        @endif
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            {{ __('Login') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center mt-3">
             <a class="btn btn-link" href="{{ route('register') }}">Nu am cont. Vreau să mă înregistrez.</a>
        </div>
    </div>
</div>
@endsection