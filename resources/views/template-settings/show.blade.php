@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Creator șabloane ofertă</h1>

    @if (session('success'))
        <div class="alert alert-success"> {{ session('success') }} </div>
    @endif

    <form method="POST" action="{{ route('template.update') }}">
        @csrf
        <div class="row">
            <!-- Coloana cu setări -->
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">Personalizare șablon</div>
                    <div class="card-body">
                        <!-- Layout -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Layout General</label>
                            <select name="layout" id="layout" class="form-select">
                                <option value="classic" {{ (old('layout', $settings->layout ?? 'classic') == 'classic') ? 'selected' : '' }}>Clasic</option>
                                <option value="modern" {{ (old('layout', $settings->layout ?? 'classic') == 'modern') ? 'selected' : '' }}>Modern</option>
                                <option value="compact" {{ (old('layout', $settings->layout ?? 'classic') == 'compact') ? 'selected' : '' }}>Compact</option>
                            </select>
                        </div>
                        <hr>
                        <!-- Fonturi și culori -->
                        <div class="mb-4">
                             <label class="form-label fw-bold">Fonturi și Culori</label>
                             <div class="mb-3">
                                 <label for="font_family" class="form-label small">Familie font</label>
                                 <select name="font_family" id="font_family" class="form-select">
                                     <option value="Roboto" {{ (old('font_family', $settings->font_family ?? 'Roboto') == 'Roboto') ? 'selected' : '' }}>Roboto (Modern, curat)</option>
                                     <option value="Lato" {{ (old('font_family', $settings->font_family ?? 'Roboto') == 'Lato') ? 'selected' : '' }}>Lato (Elegant, prietenos)</option>
                                     <option value="Lora" {{ (old('font_family', $settings->font_family ?? 'Roboto') == 'Lora') ? 'selected' : '' }}>Lora (Clasic, serif)</option>
                                     <option value="Merriweather" {{ (old('font_family', $settings->font_family ?? 'Roboto') == 'Merriweather') ? 'selected' : '' }}>Merriweather (Formal, serif)</option>
                                 </select>
                             </div>
                             <div>
                                <label for="accent_color" class="form-label small">Culoare de accent</label>
                                <input type="color" class="form-control form-control-color" id="accent_color" name="accent_color" value="{{ old('accent_color', $settings->accent_color ?? '#0d6efd') }}" title="Alege o culoare">
                             </div>
                        </div>
                        <hr>
                        <!-- Tabel -->
                        <div class="mb-4">
                             <label class="form-label fw-bold">Tabel Produse</label>
                             <select name="table_style" id="table_style" class="form-select">
                                <option value="grid" {{ (old('table_style', $settings->table_style ?? 'grid') == 'grid') ? 'selected' : '' }}>Cu toate bordurile (Clasic)</option>
                                <option value="striped" {{ (old('table_style', $settings->table_style ?? 'grid') == 'striped') ? 'selected' : '' }}>Cu rânduri colorate (Dungi)</option>
                             </select>
                        </div>
                        <hr>
                         <!-- Subsol -->
                        <div class="mb-3">
                            <label for="footer_text" class="form-label fw-bold">Text subsol (Termeni și condiții)</label>
                            <textarea class="form-control" id="footer_text" name="footer_text" rows="5">{{ old('footer_text', $settings->footer_text ?? '') }}</textarea>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-primary w-100">Salvează șablonul</button>
                    </div>
                </div>
            </div>

            <!-- Coloana cu previzualizare -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">Previzualizare Live</div>
                    <div class="card-body">
                        <div id="preview-container" class="border p-3" style="font-family: 'Roboto', sans-serif;">
                            <!-- Header -->
                            <div id="preview-header" class="d-flex justify-content-between align-items-start mb-4">
                                <div id="preview-logo" class="bg-light border p-3">Logo Firmă</div>
                                <div id="preview-title-section" class="text-end">
                                    <h4 id="preview-title" style="color: #0d6efd;">OFERTĂ COMERCIALĂ</h4>
                                    <small>Nr: OFC-101/2025</small><br>
                                    <small>Data: 29.10.2025</small>
                                </div>
                            </div>
                            <!-- Info -->
                            <div class="d-flex justify-content-between mb-4">
                                <div><small><strong>Furnizor:</strong><br>Numele Firmei Tale</small></div>
                                <div><small><strong>Către:</strong><br>Nume Client SRL</small></div>
                            </div>
                            <!-- Tabel -->
                            <table id="preview-table" class="table table-bordered">
                                <thead id="preview-table-head">
                                    <tr><th>Descriere</th><th>Cant.</th><th>Preț</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Produs/Serviciu 1</td><td>1</td><td>100.00</td></tr>
                                    <tr><td>Produs/Serviciu 2</td><td>2</td><td>50.00</td></tr>
                                </tbody>
                            </table>
                            <!-- Footer -->
                             <div id="preview-footer" class="text-muted small mt-4 border-top pt-2">
                                Termenii și condițiile vor apărea aici.
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lato&family=Lora&family=Merriweather&family=Roboto&display=swap" rel="stylesheet">

<script>
document.addEventListener('DOMContentLoaded', function () {
    const controls = {
        layout: document.getElementById('layout'),
        font: document.getElementById('font_family'),
        color: document.getElementById('accent_color'),
        tableStyle: document.getElementById('table_style'),
        footer: document.getElementById('footer_text'),
    };

    const preview = {
        container: document.getElementById('preview-container'),
        header: document.getElementById('preview-header'),
        logo: document.getElementById('preview-logo'),
        titleSection: document.getElementById('preview-title-section'),
        title: document.getElementById('preview-title'),
        table: document.getElementById('preview-table'),
        tableHead: document.getElementById('preview-table-head'),
        footer: document.getElementById('preview-footer'),
    };

    function getLogoAlignment() {
        return document.querySelector('input[name="logo_alignment"]:checked').value;
    }

    function updatePreview() {
    // Font & Color
    preview.container.style.fontFamily = `'${controls.font.value}', sans-serif`;
    preview.title.style.color = controls.color.value;
    preview.tableHead.style.backgroundColor = controls.color.value;
    preview.tableHead.style.color = 'white';

    // Footer
    preview.footer.textContent = controls.footer.value || 'Termenii și condițiile vor apărea aici.';

    // Table Style
    preview.table.classList.remove('table-bordered', 'table-striped');
    if (controls.tableStyle.value === 'grid') {
        preview.table.classList.add('table-bordered');
    } else { // 'striped'
         preview.table.classList.add('table-striped');
    }

    const layoutStyle = controls.layout.value;

    // Resetare stiluri comune
    preview.header.className = '';
    preview.logo.style.margin = '';
    preview.titleSection.style.textAlign = 'end';
    preview.header.style.backgroundColor = 'transparent';
    preview.header.style.color = 'inherit';
    preview.logo.classList.add('bg-light', 'border', 'p-3');
    preview.logo.textContent = "Logo Firmă";
    preview.title.style.fontSize = '1.5rem';

    // Logica layout-ului (fără aliniere)
    if (layoutStyle === 'classic') {
         preview.header.className = 'd-flex justify-content-between align-items-start mb-4';
    } else if (layoutStyle === 'modern') {
        preview.header.className = 'text-center mb-4';
        preview.logo.style.margin = '0 auto 1rem auto';
        preview.titleSection.style.textAlign = 'center';
        preview.titleSection.style.width = '100%';
    } else if (layoutStyle === 'compact') {
        preview.header.className = 'd-flex justify-content-between align-items-center mb-4 p-3 rounded';
        preview.header.style.backgroundColor = controls.color.value;
        preview.header.style.color = 'white';
        preview.title.style.color = 'white';
        preview.title.style.fontSize = '1.2rem';
        preview.logo.classList.remove('bg-light', 'border', 'p-3');
        preview.logo.textContent = "LOGO";
    }
}

    // Attach event listeners to all controls
    Object.values(controls).forEach(control => {
        if (control instanceof NodeList) { // For radio buttons
            control.forEach(radio => radio.addEventListener('change', updatePreview));
        } else {
            control.addEventListener('input', updatePreview);
        }
    });

    // Initial preview update on page load
    updatePreview();
});
</script>
@endpush

<style>
    .table-clean tbody tr td {
        border-top: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        border-left: none;
        border-right: none;
    }
    .table-clean thead tr th {
         border-left: none;
        border-right: none;
    }
</style>
@endsection