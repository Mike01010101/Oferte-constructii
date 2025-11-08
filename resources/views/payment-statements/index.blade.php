@extends('layouts.dashboard')

@section('title', 'Situații de Plată pentru Oferta ' . $offer->offer_number)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Situații de plată</h1>
            <p class="mb-0 text-muted">pentru oferta <a href="{{ route('oferte.show', $offer) }}">{{ $offer->offer_number }}</a></p>
        </div>
        <a href="{{ route('oferte.situatii-plata.create', $offer) }}" class="btn btn-primary">Adaugă situație de plată nouă</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nr. Situație</th>
                            <th>Data</th>
                            <th class="text-end">Valoare</th>
                            <th class="text-center">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($statements as $statement)
                            <tr>
                                <td><strong>{{ $statement->statement_number }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($statement->statement_date)->format('d.m.Y') }}</td>
                                <td class="text-end">{{ number_format($statement->total_value, 2, ',', '.') }} RON</td>
                                <td class="text-center">
                                    <a href="{{ route('oferte.situatii-plata.edit', ['offer' => $offer, 'statement' => $statement]) }}" class="btn btn-sm btn-secondary" title="Editează"><i class="fa-solid fa-pencil"></i></a>
                                    <a href="{{ route('oferte.situatii-plata.pdf', ['offer' => $offer, 'statement' => $statement]) }}" class="btn btn-sm btn-secondary" title="Descarcă PDF" target="_blank" data-swup-ignore>
                                        <i class="fa-solid fa-file-pdf text-danger"></i>
                                    </a>
                                    {{-- NOU: Butonul țintește acum modalul corect, #deleteOfferModal --}}
                                    <button type="button" class="btn btn-sm btn-secondary" title="Șterge" 
                                            data-bs-toggle="modal" data-bs-target="#deleteOfferModal" 
                                            data-form-id="delete-form-{{ $statement->id }}">
                                        <i class="fa-solid fa-trash-can text-danger"></i>
                                    </button>
                                    <form action="{{ route('oferte.situatii-plata.destroy', ['offer' => $offer, 'statement' => $statement]) }}" method="POST" class="d-none" id="delete-form-{{ $statement->id }}">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">Nu există nicio situație de plată pentru această ofertă.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             <div class="mt-3">{{ $statements->links() }}</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // NOU: Scriptul ascultă acum modalul corect, #deleteOfferModal, și refolosește logica existentă
    const deleteModal = document.getElementById('deleteOfferModal');
    if (deleteModal) {
        // Presupunem că modalul are un buton de confirmare cu id-ul #confirmDeleteBtn
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        let formToSubmitId = null;

        // Folosim un event listener nou pentru a nu interfera cu alte pagini
        const modalListener = event => {
            const button = event.relatedTarget;
            // Verificăm dacă butonul are atributul `data-form-id`
            if (button.hasAttribute('data-form-id')) {
                formToSubmitId = button.getAttribute('data-form-id');
            }
        };

        const confirmListener = () => {
            if (formToSubmitId) {
                document.getElementById(formToSubmitId).submit();
            }
        };

        deleteModal.addEventListener('show.bs.modal', modalListener);
        confirmDeleteBtn.addEventListener('click', confirmListener);

        // Curățăm event listeners când pagina este schimbată de Swup
        document.addEventListener('swup:willReplaceContent', () => {
            deleteModal.removeEventListener('show.bs.modal', modalListener);
            confirmDeleteBtn.removeEventListener('click', confirmListener);
        });
    }
</script>
@endpush