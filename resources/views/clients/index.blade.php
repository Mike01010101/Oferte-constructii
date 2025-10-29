@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Listă clienți</h1>
        <a href="{{ route('clienti.create') }}" class="btn btn-primary">Adaugă client nou</a>
    </div>

    <!-- Bara de căutare -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="search" class="form-control" id="search-input" name="search" placeholder="Caută instant după nume, CUI, contact..." value="{{ request('search') }}">
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success"> {{ session('success') }} </div>
    @endif

    <div class="card">
        <div class="card-body">
            <!-- Containerul unde se va încărca tabelul -->
            <div id="clients-table-container">
                @include('clients.partials.clients-table', ['clients' => $clients])
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmare Ștergere -->
<div class="modal fade" id="deleteClientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmare ștergere</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Sunteți sigur că doriți să ștergeți acest client? Acțiunea este ireversibilă.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulează</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Da, șterge</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const deleteModal = document.getElementById('deleteClientModal');
    if (deleteModal) {
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        let formToSubmitId = null;

        deleteModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            formToSubmitId = button.getAttribute('data-form-id');
        });

        confirmDeleteBtn.addEventListener('click', () => {
            if (formToSubmitId) {
                document.getElementById(formToSubmitId).submit();
            }
        });
    }
        // NOU: Scriptul pentru căutare live
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search-input');
        const tableContainer = document.getElementById('clients-table-container');
        let debounceTimer;

        searchInput.addEventListener('keyup', function () {
            // Folosim un 'debounce' pentru a nu trimite cereri la fiecare tastă apăsată
            clearTimeout(debounceTimer);
            
            debounceTimer = setTimeout(() => {
                const searchTerm = this.value;
                const url = `{{ route('clienti.index') }}?search=${encodeURIComponent(searchTerm)}`;

                // Trimitem cererea AJAX
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    // Înlocuim conținutul containerului cu noul tabel
                    tableContainer.innerHTML = html;
                })
                .catch(error => console.error('A apărut o eroare:', error));
            }, 300); 
        });
    });
</script>
@endpush
@endsection