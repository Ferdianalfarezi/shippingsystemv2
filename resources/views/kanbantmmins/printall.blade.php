<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Print Delivery Note</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    @php
        $isSingleItem = isset($note) && !isset($groupedData) && !isset($allData);
        $isMultipleItems = isset($groupedData) || isset($allData);
        
        if ($isSingleItem) {
            $itemsToProcess = collect([$note]);
        } elseif (isset($groupedData)) {
            $itemsToProcess = $groupedData->flatten();
        } elseif (isset($allData)) {
            $itemsToProcess = $allData;
        } elseif (isset($kanbantmmins)) {
            $itemsToProcess = $kanbantmmins;
        } else {
            $itemsToProcess = collect([]);
        }
        
        // Check if we need to show Plant 2 separator
        $showPlantSeparator = $showPlantSeparator ?? false;
        $plantFilter = $plantFilter ?? 'all';
    @endphp

    @if($itemsToProcess->isEmpty())
    <script>
        console.error('Error: No data to print');
        alert('No data available to print');
    </script>
    @endif

    <style>
        body {
            margin: 0;
            padding: 0px;
            width: 794px;
            height: 283px; 
            font-family: Arial, sans-serif;
            box-sizing: border-box;
        }

        .page-break {
            page-break-before: always;
        }

        /* Plant 2 Separator Page */
        .plant-separator {
            width: 794px;
            height: 283px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            margin-left: 15px;
            margin-top: 5px;
            border: 3px solid #000;
        }

        .plant-separator-content {
            text-align: center;
        }

        .plant-separator-title {
            font-size: 72px;
            font-weight: bold;
            color: #000;
            letter-spacing: 5px;
        }

        .plant-separator-line {
            width: 300px;
            height: 4px;
            background: #000;
            margin: 15px auto;
        }

        .plant-separator-subtitle {
            font-size: 18px;
            color: #333;
            margin-top: 10px;
        }

        .border {
            border: 2px solid black;
            height: 283px; 
            width: 794px;
            margin-left: 15px;
            margin-top: 5px;
            position: relative;
        }

        .delivery-note-container {
            position: absolute;
            top: 3px;
            left: 0;
            display: flex;
            justify-content: flex-start;
            width: 100%;
            gap: 10px;
        }

        .supplier-box {
            width: 266px;
            height: 65px;
            border: 2px solid #000000;
            display: flex;
            text-align: center;
            flex-direction: column;
            margin-left: 3px;
        }

        .supplier-box-header {
            background-color: #afafaf;
            font-weight: bold;
            font-size: 10px;
            padding: 2px;
            border-bottom: 2px solid #000;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .supplier-box-content {
            margin-top: 2px;
            margin-bottom: 2px;
            font-size: 10px;
            font-weight: bold;
        }

        .supplier-code {
            font-size: 30px;
            font-weight: bold;
        }

        .company-box {
            margin-top: 6px;
            text-align: center;
            font-size: 13px;
            margin-left: -42px;
            font-weight: bold;
            width: 288px;
        }

        .plant-name {
            font-weight: bold;
            font-size: 30px;
            margin-top: 10px;
        }

        .dock-code-box {
            width: 114px;
            height: 72px;
            border: 2px solid #000000;
            display: flex;
            margin-left: -42px;
            flex-direction: row; 
        }

        .dock-box-header {
            background-color: #afafaf;
            font-weight: bold;
            padding: 2px;
            border-right: 2px solid #000; 
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            writing-mode: vertical-lr;
            text-orientation: mixed; 
            height: 100%; 
            width: 20px;
        }

        .dock-box-header span {
            writing-mode: vertical-lr;
            font-size: 10px;
            text-orientation: mixed;
            white-space: nowrap;
            transform: rotate(180deg);
        }

        .dock-box-content {
            margin-top: 2px;
            margin-bottom: 2px;
            font-size: 50px;
            font-weight: bold;
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .progress-line-box {
            width: 166px;
            height: 73px;
            border: 2px solid #000000;
            display: flex;
            flex-direction: row;
            border-bottom: none;
            margin-left: -4px;
        }

        .progress-line-box-header {
            background-color: #afafaf;
            font-weight: bold;
            font-size: 8px;
            padding: 2px;
            border-right: 2px solid #000; 
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            writing-mode: vertical-lr;
            text-orientation: mixed; 
            height: 100%; 
            width: 20px; 
        }

        .progress-line-box-header span {
            writing-mode: vertical-lr;
            font-size: 7px;
            text-orientation: mixed;
            white-space: nowrap;
            transform: rotate(180deg);
        }

        .progress-line-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            font-size: 60px;
            font-weight: bold;
            justify-content: center;
        }

        .departure-arrival-container {
            position: absolute;
            top: 74px;
            left: 3px;
            display: flex;
            width: 102%;
            gap: -1px;
        }

        .daparture-box {
            width: 132px;
            height: 40px;
            border: 2px solid #000000;
            border-right: none;
            text-align: center;
            flex-direction: column;
        }

        .daparture-box-header {
            background-color: #afafaf;
            font-weight: bold;
            font-size: 12px;
            padding: 2px;
            border-bottom: 2px solid #000;
            box-sizing: border-box;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .daparture-box-content {
            margin-top: 1px;
            margin-bottom: 3px;
            font-size: 13px;
        }

        .arrival-box {
            width: 132px;
            height: 40px;
            border: 2px solid #000000;
            display: flex;
            text-align: center;
            flex-direction: column;
        }

        .arrival-box-header {
            background-color: #afafaf;
            font-weight: bold;
            font-size: 12px;
            padding: 2px;
            border-bottom: 2px solid #000;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .arrival-box-content {
            margin-top: 1px;
            margin-bottom: 3px;
            font-size: 13px;
        }

        .route-cycle-container {
            position: absolute;
            top: 122px;
            left: 3px;
            display: flex;
            flex-direction: row;
            gap: 0px;
        }

        .route-box {
            width: 151px;
            height: 51px;
            border: 2px solid #000000;
            display: flex;
            flex-direction: row;
        }

        .route-box-header {
            background-color: #afafaf;
            font-weight: bold;
            font-size: 12px;
            padding: 2px;
            border-right: 2px solid #000; 
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            writing-mode: vertical-lr;
            text-orientation: mixed; 
            height: 100%; 
            width: 20px;
        }

        .route-box-header span {
            writing-mode: vertical-lr;
            font-size: 13px;
            text-orientation: mixed;
            white-space: nowrap;
            transform: rotate(180deg);
        }

        .route-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 45px;
            font-weight: bold;
        }

        .cycle-box {
            width: 113px;
            height: 51px;
            border: 2px solid #000000;
            border-left: none;
            display: flex;
            flex-direction: row;
        }

        .cycle-box-header {
            background-color: #afafaf;
            font-weight: bold;
            font-size: 12px;
            padding: 2px;
            border-right: 2px solid #000; 
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            writing-mode: vertical-lr;
            text-orientation: mixed; 
            height: 100%; 
            width: 20px;
        }

        .cycle-box-header span {
            writing-mode: vertical-lr;
            font-size: 13px;
            text-orientation: mixed;
            white-space: nowrap;
            transform: rotate(180deg);
        }

        .cycle-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            font-weight: bold;
        }

        .part-no-container {
            position: absolute;
            top: 7px;
            left: 273px;
            display: flex;
            flex-direction: column;
            width: 331px;
        }

        .part-no-box {
            width: 335px;
            height: 63px;
            border: 2px solid #000000;
            display: flex;
            text-align: center;
            flex-direction: column;
        }

        .part-no-box-header {
            background-color: #afafaf;
            font-weight: bold;
            font-size: 12px;
            padding: 2px;
            border-bottom: 2px solid #000;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .part-no-box-content {
            margin-top: 2px;
            margin-bottom: 2px;
            font-size: 25px;
            font-weight: bold;
        }

        .part-no-box-content-2 {
            margin-top: 8px;
            margin-bottom: 2px;
            font-size: 13px;
            font-weight: bold;
        }

        .part-no-row {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
        }

        .sequence {
            margin-left: 10px;
            font-size: 13px;
            font-weight: bold;
        }

        .unique-qr-container {
            display: flex;
            flex-direction: row;
            margin-top: 4px;
            width: 100%;
        }

        .unique-no-box {
            min-width: 250px;
            height: 53px;
            border: 2px solid #000000;
            display: flex;
            flex-direction: row;
        }

        .unique-no-box-header {
            background-color: #afafaf;
            font-weight: bold;
            font-size: 10px;
            padding: 2px;
            border-right: 2px solid #000; 
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            writing-mode: vertical-lr;
            text-orientation: mixed; 
            height: 100%; 
            width: 20px;
        }

        .unique-no-box-header span {
            writing-mode: vertical-lr;
            font-size: 8px;
            text-orientation: mixed;
            white-space: nowrap;
            transform: rotate(180deg);
        }

        .unique-no-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            font-weight: bold;
        }

        .unique-no-box-reverse {
            min-width: 250px;
            height: 53px;
            border: 2px solid #000000;
            background: #000000;
            display: flex;
            flex-direction: row;
        }

        .unique-no-content-reverse {
            flex-grow: 1;
            display: flex;
            align-items: center;
            color:#ffffff;
            justify-content: center;
            font-size: 50px;
            font-weight: bold;
        }

        .keterangan-box {
            width: 20px;
            height: 60px;
            display: flex;
            text-align: center;
            box-sizing: border-box;
            margin-left: 5px;
        }

        .keterangan-text {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            transform: rotate(180deg);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            padding: 5px 0;
            margin-bottom: 2px;
        }

        .qr-code {
            width: 60px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
            box-sizing: border-box;
            margin-left: 5px;
        }

        .conveyance-box {
            min-width: 166px;
            height: 53px;
            border: 2px solid #000000;
            display: flex;
            flex-direction: row;
            margin-left: 7px;
            margin-top: 0px;
        }

        .conveyance-box-header {
            background-color: #afafaf;
            font-weight: bold;
            font-size: 5px;
            padding: 2px;
            border-right: 2px solid #000; 
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            text-orientation: mixed; 
            height: 100%; 
            width: 20px;
        }

        .conveyance-box-header span {
            writing-mode: vertical-lr;
            text-orientation: mixed;
            white-space: nowrap;
            transform: rotate(180deg);
        }

        .conveyance-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: bold;
        }

        .out-manifest-container {
            position: absolute;
            top: 4px;
            left: 618px;
            display: flex;
            flex-direction: column;
        }

        .out-box {
            width: 166px;
            height: 25px;
            border: 2px solid #000000;
            display: flex;
            flex-direction: row;
            margin-right: 10px;
        }

        .out-box-header {
            background-color: #afafaf;
            font-weight: bold;
            font-size: 7px;
            padding: 2px;
            border-right: 2px solid #000; 
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            writing-mode: vertical-lr;
            text-orientation: mixed; 
            height: 100%; 
            width: 20px; 
        }

        .out-box-header span {
            writing-mode: vertical-lr;
            font-size: 10px;
            text-orientation: mixed;
            white-space: nowrap;
            transform: rotate(180deg);
        }

        .out-box-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .manifest-box {
            width: 166px;
            height: 35px;
            border: 2px solid #000000;
            margin-top: 2px;
            display: flex;
            flex-direction: column;
            margin-right: 2px;
        }

        .manifest-box-header {
            background-color: #afafaf;
            font-weight: bold;
            font-size: 12px;
            padding: 2px;
            border-bottom: 2px solid #000;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            height: 20px;
            width: 100%;
        }

        .manifest-box-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .manifest-number {
            font-size: 13px;
            font-weight: bold;
        }

        .supplier-data-box {
            position: absolute;
            top: 181px;
            left: 3px;
            width: 266px;
            height: 95px;
            border: 2px solid #000000;
            display: flex;
            flex-direction: column;
        }

        .supplier-data-box-header {
            background-color: #afafaf;
            font-weight: bold;
            font-size: 12px;
            padding: 2px;
            border-bottom: 2px solid #000;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .supplier-data-box-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .barcode-container {
            width: 100%;
            margin-top: 5px;
            text-align: center;
        }

        .barcode-container canvas {
            max-width: 100%;
            max-height: 60px;
            display: block;
            margin: 0 auto;
        }

        .pcs-box {
            position: absolute;
            top: 214px;
            left: 276px;
            width: 80px;
            height: 62px;
            border: 2px solid #000000;
            display: flex;
            flex-direction: column;
        }

        .pcs-box-header {
            background-color: #afafaf;
            font-weight: bold;
            font-size: 12px;
            padding: 2px;
            border-bottom: 2px solid #000;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            height: 20px;
        }

        .pcs-box-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            font-weight: bold;
        }

        .order-no-box {
            position: absolute;
            top: 214px;
            left: 363px;
            width: 182px;
            height: 62px;
            border: 2px solid #000000;
            display: flex;
            flex-direction: row;
        }

        .order-no-box-header {
            background-color: #afafaf;
            font-weight: bold;
            font-size: 7px;
            padding: 2px;
            border-right: 2px solid #000;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            writing-mode: vertical-lr;
            text-orientation: mixed;
            height: 100%;
            width: 20px;
        }

        .order-no-box-header span {
            writing-mode: vertical-lr;
            font-size: 7px;
            text-orientation: mixed;
            white-space: nowrap;
            transform: rotate(180deg);
        }

        .order-no-box-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
        }

        .highlight-digits {
            font-size: 25px;
            font-weight: bold;
        }

        .part-address-box {
            position: absolute;
            top: 214px;
            left: 552px;
            width: 235px;
            height: 62px;
            border: 2px solid #000000;
            display: flex;
            flex-direction: row;
        }

        .part-address-box-header {
            background-color: #afafaf;
            font-weight: bold;
            font-size: 7px;
            padding: 2px;
            border-right: 2px solid #000;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            writing-mode: vertical-lr;
            text-orientation: mixed;
            height: 100%;
            width: 20px;
        }

        .part-address-box-header span {
            writing-mode: vertical-lr;
            font-size: 7px;
            text-orientation: mixed;
            white-space: nowrap;
            transform: rotate(180deg);
        }

        .part-address-box-content {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
        }

        @media print {
            .plant-separator {
                page-break-after: always;
            }
        }

        /* Plant 2 Separator Page */
        .plant-separator {
            width: 794px;
            height: 283px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            margin-left: 15px;
            margin-top: 5px;
            border: 3px solid #000;
            position: relative;
        }

        /* Blok hitam pojok kiri atas */
        .plant-separator::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 50px;
            height: 50px;
            background: #000;
        }

        /* Blok hitam pojok kanan atas */
        .plant-separator::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 50px;
            height: 50px;
            background: #000;
        }

        .plant-separator-content {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        /* Blok hitam pojok kiri bawah */
        .plant-separator-content::before {
            content: '';
            position: absolute;
            bottom: -141px;
            left: -372px;
            width: 50px;
            height: 50px;
            background: #000;
        }

        /* Blok hitam pojok kanan bawah */
        .plant-separator-content::after {
            content: '';
            position: absolute;
            bottom: -141px;
            right: -372px;
            width: 50px;
            height: 50px;
            background: #000;
        }
    </style>
</head>
<body>
    {{-- Plant 2 Separator Page (only shown when printing Plant 2) --}}
    @if($showPlantSeparator)
        <div class="plant-separator">
            <div class="plant-separator-content">
                <div class="plant-separator-title">PLANT 2</div>
                <div class="plant-separator-line"></div>
                <div class="plant-separator-subtitle">{{ $itemsToProcess->count() }} Items | {{ now()->format('d M Y H:i') }}</div>
            </div>
        </div>
    @endif

    @foreach($itemsToProcess as $index => $currentNote)
        @if($index > 0 || $showPlantSeparator)
            <div class="page-break"></div>
        @endif

        <div class="border">
            <div class="delivery-note-container">
                <div class="supplier-box">
                    <div class="supplier-box-header">SUPPLIER</div>
                    <div class="supplier-box-content">
                        <div><div>{{$currentNote->supplier??'-'}}</div><div class="supplier-code">{{$currentNote->supplier_code??'5007-1'}}@if(!empty($currentNote->customer_address))-{{$currentNote->customer_address}}@endif</div></div>
                    </div>
                </div>

                <div class="company-box">
                    <div class="box-content">
                        <div>
                            <div>PT. TOYOTA MOTOR</div>
                            <div>MANUFACTURING INDONESIA</div>
                        </div>
                        <div class="plant-name">{{ $currentNote->dock ?? '-' }}</div>
                    </div>
                </div>

                <div class="dock-code-box">
                    <div class="dock-box-header">
                        <span>DOCK CODE</span>
                    </div>
                    <div class="dock-box-content">
                        <div>
                            <div class="dock-code-content">{{ $currentNote->dock_code ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="progress-line-box">
                    <div class="progress-line-box-header">
                        <span>PROGRESS LINE NO</span>
                    </div>
                    <div class="progress-line-content">{{ $currentNote->plo !== null ? sprintf('%02d', $currentNote->plo) : '' }}</div>
                </div>
            </div>

            <div class="departure-arrival-container">
                <div class="daparture-box">
                    <div class="daparture-box-header">DAPARTURE TIME</div>
                    <div class="daparture-box-content">
                        <div>
                            <div class="date-time-value">
                                @if($currentNote && $currentNote->departure_time)
                                    @php
                                        $dt = \Carbon\Carbon::parse($currentNote->departure_time);
                                    @endphp
                                    <strong style="font-size: 1.2em;">{{ $dt->format('d/m') }}</strong>
                                    {{ $dt->format(' Y-H:i') }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="arrival-box">
                    <div class="arrival-box-header">ARRIVAL TIME</div>
                    <div class="arrival-box-content">
                        <div>
                            @if($currentNote && $currentNote->arrival_time)
                                @php
                                    $dt = \Carbon\Carbon::parse($currentNote->arrival_time);
                                @endphp
                                <strong style="font-size: 1.2em;">{{ $dt->format('d/m') }}</strong>
                                {{ $dt->format(' Y-H:i') }}
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="part-no-container">
                    <div class="part-no-box">
                        <div class="part-no-row">
                            <div class="part-no-box-content">{{ $currentNote->part_no ?? '-' }}</div>
                            <div class="sequence">
                                @php
                                    $currentSequence = 1;
                                    $totalSequence = 1;
                                    
                                    if($isSingleItem && isset($currentNote->qr_code) && !empty($currentNote->qr_code)) {
                                        $currentQrCode = $currentNote->qr_code;
                                        
                                        if(preg_match('/^(.+)(\d)$/', $currentQrCode, $matches)) {
                                            $baseQrCode = $matches[1];
                                            $currentDigit = (int)$matches[2];
                                            
                                            $relatedItems = \App\Models\KanbanTmmins::where('qr_code', 'LIKE', $baseQrCode . '%')->get();
                                            
                                            if($relatedItems->count() > 0) {
                                                $allDigits = [];
                                                foreach($relatedItems as $item) {
                                                    if(preg_match('/(\d)$/', $item->qr_code, $digitMatches)) {
                                                        $allDigits[] = (int)$digitMatches[1];
                                                    }
                                                }
                                                sort($allDigits);
                                                $currentSequence = array_search($currentDigit, $allDigits) + 1;
                                                $totalSequence = count($allDigits);
                                            } else {
                                                $currentSequence = $currentDigit > 0 ? $currentDigit : 1;
                                                $totalSequence = 1;
                                            }
                                        } else {
                                            $currentPartNo = $currentNote->part_no ?? '';
                                            if(!empty($currentPartNo)) {
                                                $samePartNumbers = \App\Models\KanbanTmmins::where('part_no', $currentPartNo)->get();
                                                $totalSequence = $samePartNumbers->count();
                                                if($totalSequence > 1) {
                                                    $sortedItems = $samePartNumbers->sortBy('qr_code');
                                                    $currentSequence = 1;
                                                    foreach($sortedItems as $idx => $item) {
                                                        if($item->qr_code === $currentNote->qr_code) {
                                                            $currentSequence = $idx + 1;
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    elseif($isMultipleItems && isset($currentNote->qr_code) && !empty($currentNote->qr_code)) {
                                        $currentQrCode = $currentNote->qr_code;
                                        
                                        if(preg_match('/^(.+)(\d)$/', $currentQrCode, $matches)) {
                                            $baseQrCode = $matches[1];
                                            $currentDigit = (int)$matches[2];
                                            
                                            $relatedItems = $itemsToProcess->filter(function($item) use ($baseQrCode) {
                                                if(isset($item->qr_code) && !empty($item->qr_code)) {
                                                    return strpos($item->qr_code, $baseQrCode) === 0;
                                                }
                                                return false;
                                            });
                                            
                                            if($relatedItems->count() > 0) {
                                                $allDigits = [];
                                                foreach($relatedItems as $item) {
                                                    if(preg_match('/(\d)$/', $item->qr_code, $digitMatches)) {
                                                        $allDigits[] = (int)$digitMatches[1];
                                                    }
                                                }
                                                sort($allDigits);
                                                $currentSequence = array_search($currentDigit, $allDigits) + 1;
                                                $totalSequence = count($allDigits);
                                            } else {
                                                $currentSequence = $currentDigit > 0 ? $currentDigit : 1;
                                                $totalSequence = 1;
                                            }
                                        } else {
                                            $currentPartNo = $currentNote->part_no ?? '';
                                            if(!empty($currentPartNo)) {
                                                $samePartNumbers = $itemsToProcess->where('part_no', $currentPartNo);
                                                $totalSequence = $samePartNumbers->count();
                                                $sortedItems = $samePartNumbers->sortBy('qr_code')->values();
                                                foreach($sortedItems as $idx => $item) {
                                                    if($item->qr_code === $currentNote->qr_code) {
                                                        $currentSequence = $idx + 1;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    elseif($isMultipleItems) {
                                        $currentPartNo = $currentNote->part_no ?? '';
                                        if(!empty($currentPartNo)) {
                                            $samePartNumbers = $itemsToProcess->where('part_no', $currentPartNo);
                                            $totalSequence = $samePartNumbers->count();
                                            $sortedItems = $samePartNumbers->sortBy('id')->values();
                                            foreach($sortedItems as $idx => $item) {
                                                if($item->id === $currentNote->id) {
                                                    $currentSequence = $idx + 1;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    
                                    echo $currentSequence . '/' . $totalSequence;
                                @endphp
                            </div>
                        </div>
                        <div class="part-no-box-content-2">{{ $currentNote->part_name ?? '-' }}</div>
                    </div>
                    
                    <div class="unique-qr-container">
                        @php
                            $isRunOut = false;
                            if (isset($currentNote->unique_no) && !empty($currentNote->unique_no)) {
                                $isRunOut = \DB::table('run_outs')
                                    ->where('unique_no', $currentNote->unique_no)
                                    ->exists();
                            }
                        @endphp
                        
                        <div class="{{ $isRunOut ? 'unique-no-box-reverse' : 'unique-no-box' }}">
                            <div class="unique-no-box-header">
                                <span>UNIQUE NO</span>
                            </div>
                            <div class="{{ $isRunOut ? 'unique-no-content-reverse' : 'unique-no-content' }}">
                                {{ $currentNote->unique_no ?? '-' }}
                            </div>
                        </div>
                        
                        <div class="keterangan-box">
                            <div class="keterangan-text">{{ $currentNote->keterangan ?? '' }}</div>
                        </div>
                        
                        <div class="qr-code">
                            <div id="qrcode-{{ $index }}" data-qr="{{ $currentNote->qr_code ?? 'NO-QR' }}"></div>
                        </div>
                        
                        <div class="conveyance-box">
                            <div class="conveyance-box-header">
                                <span>CONVEYANCE NO</span>
                            </div>
                            <div class="conveyance-content">{{ $currentNote->conveyance_no ?? '' }}</div>
                        </div>
                    </div>
                </div>

                <div class="out-manifest-container">
                    <div class="out-box">
                        <div class="out-box-header">
                            <span>OUT</span>
                        </div>
                        <div class="out-box-content">
                            @if($currentNote && $currentNote->out_time)
                                @php
                                    $dt = \Carbon\Carbon::parse($currentNote->out_time);
                                @endphp
                                <strong style="font-size: 1.2em;">
                                    {{ $dt->format('d/m') }}&nbsp;
                                </strong>
                                {{ $dt->format(' Y-H:i') }}
                            @endif
                        </div>
                    </div>
                    
                    <div class="manifest-box">
                        <div class="manifest-box-header">MANIFEST NO</div>
                        <div class="manifest-box-content">
                            <div class="manifest-number">{{ $currentNote->manifest_no ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="route-cycle-container">
                <div class="route-box">
                    <div class="route-box-header">
                        <span>ROUTE</span>
                    </div>
                    <div class="route-content">{{ $currentNote->route ?? '' }}</div>
                </div>
                <div class="cycle-box">
                    <div class="cycle-box-header">
                        <span>CYCLE</span>
                    </div>
                    <div class="cycle-content">{{ $currentNote->cycle ? sprintf('%02d', $currentNote->cycle) : '' }}</div>
                </div>
            </div>

            <div class="supplier-data-box">
                <div class="supplier-data-box-header">SUPPLIER DATA</div>
                <div class="supplier-data-box-content">
                    <div class="barcode-container">
                        <canvas id="barcode-{{ $index }}"></canvas>
                    </div>
                </div>
            </div>

            <div class="pcs-box">
                <div class="pcs-box-header">PCS/KBN</div>
                <div class="pcs-box-content">
                    {{ $currentNote->pcs ?? '0' }}
                </div>
            </div>

            <div class="order-no-box">
                <div class="order-no-box-header">
                    <span style="font-size: 10px;">ORDER NO</span>
                </div>
                <div class="order-no-box-content">
                    @if(isset($currentNote->order_no))
                        @php
                            $orderNo = $currentNote->order_no;
                            if(strlen($orderNo) >= 8) {
                                $prefix = substr($orderNo, 0, 4);
                                $highlight = substr($orderNo, 4, 4);
                                $suffix = substr($orderNo, 8);
                                echo $prefix . '<span class="highlight-digits">' . $highlight . '</span>' . $suffix;
                            } else {
                                echo $orderNo;
                            }
                        @endphp
                    @else
                        -
                    @endif
                </div>
            </div>

            <div class="part-address-box">
                <div class="part-address-box-header">
                    <span>PART ADDRESS</span>
                </div>
                <div class="part-address-box-content">
                    {{ $currentNote->part_address ?? '-' }}
                </div>
            </div>
        </div>
    @endforeach

    <script>
        console.log('=== BARCODE DEBUG INFO ===');
        console.log('Total items to process: {{ $itemsToProcess->count() }}');
        console.log('Plant filter: {{ $plantFilter }}');
        console.log('Show separator: {{ $showPlantSeparator ? "true" : "false" }}');
        
        window.onload = function() {
            setTimeout(function() {
                console.log('Starting barcode and QR code generation...');
                
                @foreach($itemsToProcess as $barcodeIndex => $barcodeItem)
                    (function(index) {
                        try {
                            const canvas = document.getElementById('barcode-' + index);
                            if (canvas) {
                                const addressValue = "{{ $barcodeItem->address ?? ($barcodeItem->dock_code . '-' . $barcodeItem->id) }}";
                                JsBarcode("#barcode-" + index, addressValue, {
                                    format: "CODE128",
                                    width: 2,
                                    height: 40,
                                    displayValue: true
                                });
                            }
                        } catch (error) {
                            console.error('Error generating barcode ' + index + ':', error);
                        }
                        
                        try {
                            const qrDiv = document.getElementById('qrcode-' + index);
                            if (qrDiv) {
                                const qrValue = qrDiv.getAttribute('data-qr') || 'NO-QR';
                                new QRCode(qrDiv, {
                                    text: qrValue,
                                    width: 55,
                                    height: 55,
                                    colorDark: "#000000",
                                    colorLight: "#ffffff",
                                    correctLevel: QRCode.CorrectLevel.L
                                });
                            }
                        } catch (error) {
                            console.error('Error generating QR code ' + index + ':', error);
                        }
                    })({{ $barcodeIndex }});
                @endforeach
                
                console.log('All barcodes and QR codes generated');
            }, 500);
        }

        // Auto print untuk Plant 2
        @if($showPlantSeparator)
            window.onload = function() {
                setTimeout(function() {
                    console.log('Starting barcode and QR code generation...');
                    
                    @foreach($itemsToProcess as $barcodeIndex => $barcodeItem)
                        (function(index) {
                            try {
                                const canvas = document.getElementById('barcode-' + index);
                                if (canvas) {
                                    const addressValue = "{{ $barcodeItem->address ?? ($barcodeItem->dock_code . '-' . $barcodeItem->id) }}";
                                    JsBarcode("#barcode-" + index, addressValue, {
                                        format: "CODE128",
                                        width: 2,
                                        height: 40,
                                        displayValue: true
                                    });
                                }
                            } catch (error) {
                                console.error('Error generating barcode ' + index + ':', error);
                            }
                            
                            try {
                                const qrDiv = document.getElementById('qrcode-' + index);
                                if (qrDiv) {
                                    const qrValue = qrDiv.getAttribute('data-qr') || 'NO-QR';
                                    new QRCode(qrDiv, {
                                        text: qrValue,
                                        width: 55,
                                        height: 55,
                                        colorDark: "#000000",
                                        colorLight: "#ffffff",
                                        correctLevel: QRCode.CorrectLevel.L
                                    });
                                }
                            } catch (error) {
                                console.error('Error generating QR code ' + index + ':', error);
                            }
                        })({{ $barcodeIndex }});
                    @endforeach
                    
                    console.log('All barcodes and QR codes generated');
                    
                    // Auto print untuk Plant 2
                    setTimeout(function() {
                        window.print();
                    }, 1000);
                }, 500);
            }
        @else
            window.onload = function() {
                setTimeout(function() {
                    console.log('Starting barcode and QR code generation...');
                    
                    @foreach($itemsToProcess as $barcodeIndex => $barcodeItem)
                        (function(index) {
                            try {
                                const canvas = document.getElementById('barcode-' + index);
                                if (canvas) {
                                    const addressValue = "{{ $barcodeItem->address ?? ($barcodeItem->dock_code . '-' . $barcodeItem->id) }}";
                                    JsBarcode("#barcode-" + index, addressValue, {
                                        format: "CODE128",
                                        width: 2,
                                        height: 40,
                                        displayValue: true
                                    });
                                }
                            } catch (error) {
                                console.error('Error generating barcode ' + index + ':', error);
                            }
                            
                            try {
                                const qrDiv = document.getElementById('qrcode-' + index);
                                if (qrDiv) {
                                    const qrValue = qrDiv.getAttribute('data-qr') || 'NO-QR';
                                    new QRCode(qrDiv, {
                                        text: qrValue,
                                        width: 55,
                                        height: 55,
                                        colorDark: "#000000",
                                        colorLight: "#ffffff",
                                        correctLevel: QRCode.CorrectLevel.L
                                    });
                                }
                            } catch (error) {
                                console.error('Error generating QR code ' + index + ':', error);
                            }
                        })({{ $barcodeIndex }});
                    @endforeach
                    
                    console.log('All barcodes and QR codes generated');
                }, 500);
            }
        @endif
    </script>
</body>
</html>