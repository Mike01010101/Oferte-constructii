@if ($templateSettings && $templateSettings->intro_text)
    @php
        // Textul original din setări
        $baseText = $templateSettings->intro_text;

        // Înlocuim variabilele cu placeholder-uri temporare cu delimitatori unici
        $placeholders = [
            '{obiect}' => '%%OBJECT%%',
            '{total_fara_tva}' => '%%TOTAL_WITHOUT_VAT%%',
            '{tva}' => '%%VAT_VALUE%%',
            '{total_cu_tva}' => '%%GRAND_TOTAL%%',
            '{client}' => '%%CLIENT_NAME%%',
        ];
        $textWithPlaceholders = str_replace(array_keys($placeholders), array_values($placeholders), $baseText);

        // Securizăm tot textul pentru a preveni XSS
        $safeText = e($textWithPlaceholders);

        // Acum înlocuim placeholder-urile sigure cu valorile reale, învelite în <strong>
        $finalText = str_replace(
            [
                '%%OBJECT%%',
                '%%TOTAL_WITHOUT_VAT%%',
                '%%VAT_VALUE%%',
                '%%GRAND_TOTAL%%',
                '%%CLIENT_NAME%%'
            ],
            [
                '<strong>' . e($offerOrStatement->object ?? 'N/A') . '</strong>',
                '<strong>' . number_format($calculations->totalWithoutVat, 2, ',', '.') . '</strong>',
                '<strong>' . number_format($calculations->vatValue, 2, ',', '.') . '</strong>',
                '<strong>' . number_format($calculations->grandTotal, 2, ',', '.') . '</strong>',
                '<strong>' . e($offerOrStatement->client->name ?? 'N/A') . '</strong>'
            ],
            $safeText
        );
    @endphp
    {{-- Afișăm textul procesat, permițând tag-urile <strong> și <br> --}}
    <div class="mb-4" style="font-size: 10px; line-height: 1.5;">
        {!! nl2br($finalText) !!}
    </div>
@endif