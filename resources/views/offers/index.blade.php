@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Listă oferte</h1>
        <a href="{{ route('oferte.create') }}" class="btn btn-primary">Adaugă ofertă nouă</a>
    </div>

    <!-- Bara de căutare -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="search" class="form-control" id="search-input" name="search" placeholder="Caută instant după nr. ofertă, nume client, articole din ofertă..." value="{{ request('search') }}">
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="card-body">
            <div id="offers-table-container">
                @include('offers.partials.offers-table', ['offers' => $offers])
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const deleteModal = document.getElementById('deleteOfferModal');
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
        // Script pentru căutare live
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search-input');
        const tableContainer = document.getElementById('offers-table-container');
        let debounceTimer;

        searchInput.addEventListener('keyup', function () {
            clearTimeout(debounceTimer);
            
            debounceTimer = setTimeout(() => {
                const searchTerm = this.value;
                const url = `{{ route('oferte.index') }}?search=${encodeURIComponent(searchTerm)}`;

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                })
                .catch(error => console.error('A apărut o eroare:', error));
            }, 300);
        });
    });
</script>
@endpush
@endsection