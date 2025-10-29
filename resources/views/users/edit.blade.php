@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Editează utilizator: {{ $user->name }}</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('utilizatori.update', $user->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Nume complet*</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Adresă de e-mail*</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="mb-3">
                    <label for="role" class="form-label">Rol*</label>
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                        <option value="">Selectează un rol</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" {{ $user->hasRole($role) ? 'selected' : '' }}>{{ $role }}</option>
                        @endforeach
                    </select>
                     @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <hr>
                <p class="text-muted">Completează câmpurile de mai jos doar dacă dorești să schimbi parola.</p>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Parolă nouă</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                         @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Confirmă parola nouă</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>
                </div>

                <hr>
                <button type="submit" class="btn btn-primary">Salvează modificările</button>
                <a href="{{ route('utilizatori.index') }}" class="btn btn-secondary">Anulează</a>
            </form>
        </div>
    </div>
</div>
@endsection