<div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nume client</th>
                            <th>CUI / CIF</th>
                            <th>Persoană de contact</th>
                            <th>Telefon</th>
                            <th class="text-center">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clients as $client)
                            <tr>
                                <td>{{ $client->name }}</td>
                                <td>{{ $client->vat_number ?? '-' }}</td>
                                <td>{{ $client->contact_person ?? '-' }}</td>
                                <td>{{ $client->phone ?? '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('clienti.edit', $client->id) }}" class="btn btn-sm btn-secondary" title="Editează">
                                        <i class="fa-solid fa-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-secondary" title="Șterge" 
                                            data-bs-toggle="modal" data-bs-target="#deleteClientModal" 
                                            data-form-id="delete-form-{{ $client->id }}">
                                        <i class="fa-solid fa-trash-can text-danger"></i>
                                    </button>
                                    {{-- Am scos formularul in afara butoanelor pentru claritate --}}
                                    <form action="{{ route('clienti.destroy', $client->id) }}" method="POST" class="d-none" id="delete-form-{{ $client->id }}">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    @if(request('search'))
                                        Niciun client găsit pentru termenul "{{ request('search') }}".
                                    @else
                                        Nu aveți niciun client adăugat.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $clients->links() }}
            </div>