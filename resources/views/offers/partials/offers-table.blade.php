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
                            <td id="assign-cell-{{ $offer->id }}">
                            <div class="dropdown">
                                <a class="text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                            <td class="text-end">{{ number_format($offer->total_value, 2, ',', '.') }} RON</td>
                            <td class="status-cell" id="status-cell-{{ $offer->id }}">
                                <div class="dropdown">
                                    <a class="badge {{ $offer->getStatusColorClass() }} dropdown-toggle text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                    @empty
                        <tr><td colspan="7" class="text-center">Nu aveți nicio ofertă creată.</td></tr>
                    @endforelse
                </tbody>
            </table>
             <div class="mt-3">{{ $offers->links() }}</div>