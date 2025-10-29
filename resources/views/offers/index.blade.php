@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Listă oferte</h1>
        <a href="{{ route('oferte.create') }}" class="btn btn-primary">Adaugă ofertă nouă</a>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nr. Ofertă</th>
                        <th>Client</th>
                        <th>Alocată lui</th>
                        <th>Data</th>
                        <th class="text-end">Valoare</th>
                        <th>Status</th>
                        <th class="text-center">Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($offers as $offer)
                        <tr>
                            <td><strong>{{ $offer->offer_number }}</strong></td>
                            <td>{{ $offer->client->name }}</td>
                            <td>{{ $offer->assignedTo->name ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($offer->offer_date)->format('d.m.Y') }}</td>
                            <td class="text-end">{{ number_format($offer->total_value, 2, ',', '.') }} RON</td>
                            <td><span class="badge bg-secondary">{{ $offer->status }}</span></td>
                            <td class="text-center">
                                <a href="{{ route('oferte.show', $offer->id) }}" class="btn btn-sm btn-outline-info" title="Vezi"><i class="fa-solid fa-eye"></i></a>
                                <a href="{{ route('oferte.edit', $offer->id) }}" class="btn btn-sm btn-outline-secondary" title="Editează"><i class="fa-solid fa-pencil"></i></a>
                                <form action="{{ route('oferte.destroy', $offer->id) }}" method="POST" class="d-inline" id="delete-form-{{ $offer->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Șterge" 
                                            data-bs-toggle="modal" data-bs-target="#deleteOfferModal" 
                                            data-form-id="delete-form-{{ $offer->id }}">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Nu aveți nicio ofertă creată.</td></tr>
                    @endforelse
                </tbody>
            </table>
             <div class="mt-3">{{ $offers->links() }}</div>
        </div>
    </div>
</div>

<!-- Modal de Confirmare -->
<div class="modal fade" id="deleteOfferModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmare ștergere</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Sunteți sigur că doriți să ștergeți această ofertă? Acțiunea este ireversibilă.</p>
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
</script>
@endpush
@endsection