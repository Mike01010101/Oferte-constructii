@extends('layouts.dashboard')

@section('title', 'Creator Șabloane')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Creator șabloane ofertă</h1>

    {{-- Folosim enctype pentru a permite încărcarea de fișiere --}}
    <form method="POST" action="{{ route('template.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <!-- Coloana cu setări -->
            <div class="col-lg-4">
                {{-- Card Layout General --}}
                <div class="card mb-4">
                    <div class="card-header"><i class="fa-solid fa-layer-group me-2"></i> Layout General</div>
                    <div class="card-body">
                        <select name="layout" id="layout" class="form-select">
                            <option value="classic" {{ (old('layout', $settings->layout ?? 'classic') == 'classic') ? 'selected' : '' }}>Clasic</option>
                            <option value="modern" {{ (old('layout', $settings->layout ?? 'classic') == 'modern') ? 'selected' : '' }}>Modern</option>
                            <option value="compact" {{ (old('layout', $settings->layout ?? 'classic') == 'compact') ? 'selected' : '' }}>Compact</option>
                            <option value="elegant" {{ (old('layout', $settings->layout ?? 'classic') == 'elegant') ? 'selected' : '' }}>Elegant</option>
                            <option value="minimalist" {{ (old('layout', $settings->layout ?? 'classic') == 'minimalist') ? 'selected' : '' }}>Minimalist</option>
                        </select>
                    </div>
                </div>

                {{-- Card Stil Vizual --}}
                <div class="card mb-4">
                    <div class="card-header"><i class="fa-solid fa-palette me-2"></i> Stil Vizual</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="document_title" class="form-label small">Titlu Document</label>
                            <input type="text" class="form-control" id="document_title" name="document_title" value="{{ old('document_title', $settings->document_title ?? 'DEVIZ OFERTĂ') }}">
                        </div>
                        <div class="mb-3">
                            <label for="font_family" class="form-label small">Familie font</label>
                            <select name="font_family" id="font_family" class="form-select">
                                 <option value="Roboto" {{ (old('font_family', $settings->font_family ?? 'Roboto') == 'Roboto') ? 'selected' : '' }}>Roboto (Modern)</option>
                                 <option value="Lato" {{ (old('font_family', $settings->font_family ?? 'Roboto') == 'Lato') ? 'selected' : '' }}>Lato (Elegant)</option>
                                 <option value="Merriweather" {{ (old('font_family', $settings->font_family ?? 'Roboto') == 'Merriweather') ? 'selected' : '' }}>Merriweather (Formal, Serif)</option>
                                 <option value="Open Sans" {{ (old('font_family', $settings->font_family ?? 'Roboto') == 'Open Sans') ? 'selected' : '' }}>Open Sans (Prietenos)</option>
                                 <option value="Montserrat" {{ (old('font_family', $settings->font_family ?? 'Roboto') == 'Montserrat') ? 'selected' : '' }}>Montserrat (Stilat)</option>
                                 <option value="Source Serif Pro" {{ (old('font_family', $settings->font_family ?? 'Roboto') == 'Source Serif Pro') ? 'selected' : '' }}>Source Serif Pro (Clasic, Serif)</option>
                            </select>
                        </div>
                        <div>
                           <label for="accent_color" class="form-label small">Culoare de accent</label>
                           <input type="color" class="form-control form-control-color" id="accent_color" name="accent_color" value="{{ old('accent_color', $settings->accent_color ?? '#0d6efd') }}" title="Alege o culoare">
                        </div>
                    </div>
                </div>

                {{-- Card Tabel Produse --}}
                <div class="card mb-4">
                    <div class="card-header"><i class="fa-solid fa-table me-2"></i> Tabel Produse</div>
                    <div class="card-body">
                        <select name="table_style" id="table_style" class="form-select">
                           <option value="grid" {{ (old('table_style', $settings->table_style ?? 'grid') == 'grid') ? 'selected' : '' }}>Cu toate bordurile</option>
                           <option value="striped" {{ (old('table_style', $settings->table_style ?? 'grid') == 'striped') ? 'selected' : '' }}>Cu rânduri colorate</option>
                        </select>
                    </div>
                </div>

                {{-- NOU: Card Ștampilă --}}
                <div class="card mb-4">
                    <div class="card-header"><i class="fa-solid fa-stamp me-2"></i> Ștampilă și Semnătură</div>
                    <div class="card-body">
                        <label for="stamp" class="form-label small">Încarcă imagine ștampilă (PNG transparent)</label>
                        <input class="form-control" type="file" id="stamp" name="stamp" accept="image/png">
                        
                        @if ($settings && $settings->stamp_path)
                        <div class="mt-2 text-center">
                            <p class="small text-muted mb-1">Ștampila curentă:</p>
                            <img src="{{ asset('storage/' . $settings->stamp_path) }}" class="img-thumbnail" style="max-height: 70px; background: #eee;">
                        </div>
                        @endif

                        <div class="mt-3">
                            <label for="stamp_size" class="form-label small">Mărime ștampilă în PDF</label>
                            <div class="d-flex align-items-center">
                                <input type="range" class="form-range" id="stamp_size" name="stamp_size" min="50" max="300" value="{{ old('stamp_size', $settings->stamp_size ?? 150) }}">
                                <span id="stamp_size_value" class="ms-3 fw-bold text-muted">{{ old('stamp_size', $settings->stamp_size ?? 150) }}px</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Subsol --}}
                <div class="card mb-4">
                    <div class="card-header"><i class="fa-solid fa-file-alt me-2"></i> Subsol</div>
                    <div class="card-body">
                        <label for="footer_text" class="form-label small">Text subsol (Termeni și condiții)</label>
                        <textarea class="form-control" id="footer_text" name="footer_text" rows="4">{{ old('footer_text', $settings->footer_text ?? '') }}</textarea>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Salvează șablonul</button>
                </div>
            </div>

            {{-- Coloana cu previzualizare --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        Previzualizare Live
                        <small class="text-muted">Simulare format A4</small>
                    </div>
                    <div class="card-body" style="background-color: var(--main-bg);">
                        <div id="preview-page" style="width: 210mm; height: 297mm; background-color: var(--element-bg); color: var(--text-primary); margin: auto; box-shadow: 0 0 15px var(--shadow-color); padding: 15mm; position: relative;">
                            
                            <div id="preview-header-container"></div>
                            
                            <div class="mb-4">
                                <p style="font-size: 10px;"><strong>Către:</strong><br>NUME CLIENT EXEMPLU SRL<br>Adresa clientului, Nr. 1, Oraș, Județ</p>
                            </div>

                            <table id="preview-table" class="table table-sm" style="font-size: 10px;">
                                <thead id="preview-table-head">
                                    <tr><th>Nr.</th><th>Descriere</th><th>Cant.</th><th class="text-end">Preț</th><th class="text-end">Total</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>1</td><td>Produs/Serviciu Exemplu 1</td><td>1</td><td class="text-end">150.00</td><td class="text-end">150.00</td></tr>
                                    <tr id="preview-striped-row"><td>2</td><td>Al doilea produs/serviciu</td><td>2</td><td class="text-end">75.00</td><td class="text-end">150.00</td></tr>
                                </tbody>
                            </table>
                            
                            <div id="preview-signature" style="position: absolute; bottom: 60mm; right: 15mm; text-align: center;">
                                <p style="font-size: 10px;">Ofertant,</p>
                                <img id="preview-stamp" src="{{ $settings && $settings->stamp_path ? asset('storage/' . $settings->stamp_path) : '' }}" style="display: {{ $settings && $settings->stamp_path ? 'block' : 'none' }}; width: 150px; height: auto; margin-top: 5px;">
                            </div>

                             <div id="preview-footer" class="text-muted" style="font-size: 8px; position: absolute; bottom: 15mm; left: 15mm; right: 15mm; border-top: 1px solid var(--border-color); padding-top: 5px;">
                                Termenii și condițiile vor apărea aici.
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('head_links')
{{-- Acest cod încarcă dinamic fonturile Google pentru a fi disponibile în previzualizare --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lato&family=Merriweather&family=Roboto&family=Open+Sans&family=Montserrat&family=Source+Serif+Pro&display=swap" rel="stylesheet">
@endpush
@endsection