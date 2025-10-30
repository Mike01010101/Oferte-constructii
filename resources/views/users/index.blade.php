@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Management utilizatori</h1>
        <a href="{{ route('utilizatori.create') }}" class="btn btn-primary">Adaugă utilizator nou</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nume</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if(!empty($user->getRoleNames()))
                                    @foreach($user->getRoleNames() as $roleName)
                                        <span class="badge">{{ $roleName }}</span>
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                @unless ($user->hasRole('Owner'))
                                    <a href="{{ route('utilizatori.edit', $user->id) }}" class="btn btn-sm btn-secondary" title="Editează"><i class="fa-solid fa-pencil"></i></a>
                                    <button type="button" class="btn btn-sm btn-secondary" title="Șterge"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteUserModal" 
                                            data-form-id="delete-form-{{ $user->id }}">
                                        <i class="fa-solid fa-trash-can text-danger"></i>
                                    </button>
                                    <form action="{{ route('utilizatori.destroy', $user->id) }}" method="POST" class="d-none" id="delete-form-{{ $user->id }}">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @else
                                    <span class="text-muted fst-italic">N/A</span>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Nu există alți utilizatori în această firmă.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
             <div class="mt-3">{{ $users->links() }}</div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteModal = document.getElementById('deleteUserModal');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            let formToSubmitId = null;

            // Când modalul este pe cale să fie afișat
            deleteModal.addEventListener('show.bs.modal', function (event) {
                // Preluăm butonul care a declanșat deschiderea modalului
                const button = event.relatedTarget;
                // Extragem ID-ul formularului din atributul 'data-form-id' al butonului
                formToSubmitId = button.getAttribute('data-form-id');
            });

            // Când se dă click pe butonul de confirmare din modal
            confirmDeleteBtn.addEventListener('click', function () {
                if (formToSubmitId) {
                    // Găsim formularul corect după ID și îl trimitem
                    const form = document.getElementById(formToSubmitId);
                    if (form) {
                        form.submit();
                    }
                }
            });
        });
    </script>
    @endpush
    @endsection