<div class="table-responsive">
    <table class="table table-hover">
    <thead>
        <tr>
            <th>Nr. Ofertă / Articol</th>
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
            {{-- Rândul principal pentru ofertă --}}
            <tr>
                <td>
                    <strong>{{ $offer->offer_number }}</strong>
                </td>
                <td>{{ $offer->client->name }}</td>
                <td id="assign-cell-{{ $offer->id }}">
                    <div class="dropdown">
                        <a class="text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-popper-config='{"strategy":"fixed"}'>
                            {{ $offer->assignedTo->name ?? 'N/A' }}
                        </a>

                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li>
                                <a class="dropdown-item assign-user-btn" href="#" 
                                data-offer-id="{{ $offer->id }}" 
                                data-user-id=""
                                data-url="{{ route('oferte.assignUser', $offer->id) }}">
                                N/A
                                </a>
                            </li>
                            @foreach ($users as $user)
                                <li>
                                    <a class="dropdown-item assign-user-btn" href="#" 
                                    data-offer-id="{{ $offer->id }}" 
                                    data-user-id="{{ $user->id }}"
                                    data-url="{{ route('oferte.assignUser', $offer->id) }}">
                                    {{ $user->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </td>
                <td>{{ \Carbon\Carbon::parse($offer->offer_date)->format('d.m.Y') }}</td>
                <td class="text-end">
                    {{-- Afișăm valoarea doar dacă NU este o căutare activă --}}
                    @if(empty($searchTerm))
                        {{ number_format($offer->visible_total_value, 2, ',', '.') }} RON
                    @endif
                </td>
                <td class="status-cell" id="status-cell-{{ $offer->id }}">
                    <div class="dropdown">
                        <a class="badge {{ $offer->getStatusColorClass() }} dropdown-toggle text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-popper-config='{"strategy":"fixed"}'>
                            {{ $offer->status }}
                        </a>

                        <ul class="dropdown-menu dropdown-menu-dark">
                            @foreach (App\Models\Offer::STATUSES as $key => $value)
                                <li>
                                    <a class="dropdown-item update-status-btn" href="#" 
                                    data-offer-id="{{ $offer->id }}" 
                                    data-new-status="{{ $key }}"
                                    data-url="{{ route('oferte.updateStatus', $offer->id) }}">
                                    {{ $value }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </td>
                <td class="text-center">
                    {{-- NOUL BUTON PENTRU SITUAȚII DE PLATĂ --}}
                    <a href="{{ route('oferte.situatii-plata.index', $offer->id) }}" class="btn btn-sm btn-secondary" title="Situații de plată">
                        <i class="fa-solid fa-file-invoice-dollar text-success"></i>
                    </a>

                    <a href="{{ route('oferte.show', $offer->id) }}" class="btn btn-sm btn-secondary" title="Vezi"><i class="fa-solid fa-eye"></i></a>
                    <a href="{{ route('oferte.edit', $offer->id) }}" class="btn btn-sm btn-secondary" title="Editează"><i class="fa-solid fa-pencil"></i></a>
                    <a href="{{ route('oferte.pdf', $offer->id) }}" class="btn btn-sm btn-secondary" title="Descarcă PDF" target="_blank" data-swup-ignore>
                        <i class="fa-solid fa-file-pdf text-danger"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary" title="Șterge" 
                            data-bs-toggle="modal" data-bs-target="#deleteOfferModal" 
                            data-form-id="delete-form-{{ $offer->id }}">
                        <i class="fa-solid fa-trash-can text-danger"></i>
                    </button>
                    <form action="{{ route('oferte.destroy', $offer->id) }}" method="POST" class="d-none" id="delete-form-{{ $offer->id }}">
                        @csrf
                        @method('DELETE')
                    </form>
                </td>
            </tr>

            {{-- NOU: Dacă există o căutare și avem articole care se potrivesc, le afișăm --}}
            @if(!empty($searchTerm) && $offer->relationLoaded('matching_items') && !$offer->matching_items->isEmpty())
                @foreach($offer->matching_items as $item)
                    <tr class="offer-item-sub-row">
                        <td class="ps-4 text-break">
                            <span class="text-muted">↳</span> 
                            {{-- Evidențiem termenul căutat în descriere --}}
                            {!! str_ireplace(e($searchTerm), "<mark class='p-0'>" . e($searchTerm) . "</mark>", e($item->description)) !!}
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-end">{{ number_format($item->quantity * ($item->material_price + $item->labor_price + $item->equipment_price), 2, ',', '.') }} RON</td>
                        <td colspan="2"></td> {{-- Celule goale pentru a păstra structura tabelului --}}
                    </tr>
                @endforeach
            @endif

        @empty
            <tr><td colspan="7" class="text-center">Nu s-au găsit oferte conform criteriilor.</td></tr>
        @endforelse
    </tbody>
</table>
</div>
<div class="mt-3">{{ $offers->links() }}</div>