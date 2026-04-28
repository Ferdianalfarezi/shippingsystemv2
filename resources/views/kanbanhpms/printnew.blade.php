<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print All - Kanban HPM</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }
        body { margin: 0; padding: 0; background: #fff; }

        @media print {
            @page { margin: 0; padding: 0; size: 100mm 85mm; }
            body { margin: 0; padding: 0; }
            .frame-1 { page-break-after: always; page-break-inside: avoid; }
            .no-print { display: none !important; }
        }

        .print-btn-wrap { text-align: center; padding: 16px 0 8px; }
        .print-btn {
            background: #1a56db; color: #fff; border: none;
            padding: 10px 32px; font-size: 15px; border-radius: 6px;
            cursor: pointer; font-family: Arial, sans-serif;
        }
        .print-btn:hover { background: #1e429f; }

        .frame-1, .frame-1 * { box-sizing: border-box; }
        .frame-1 {
            background: #ffffff;
            width: 731px;
            height: 521px;
            position: relative;
            overflow: hidden;
            margin-bottom: 8px;
        }

        /* ── Separator Page ── */
        .separator-page {
            background: #ffffff;
            width: 731px;
            height: 521px;
            position: relative;
            overflow: hidden;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #000;
        }
        .separator-inner { text-align: center; }
        .separator-plant-label {
            font-size: 90px; font-weight: 900; letter-spacing: 10px;
            color: #000; line-height: 1; text-transform: uppercase;
        }
        .separator-sub { font-size: 20px; color: #444; margin-top: 16px; letter-spacing: 2px; }
        .separator-line { width: 400px; height: 4px; background: #000; margin: 20px auto; }
        .separator-count { font-size: 16px; color: #666; margin-top: 8px; }

        /* ─── RECTANGLE BORDERS ─── */
        .rectangle-1 {
            background: rgba(217,217,217,0); border: 2px solid #000;
            width: 706px; height: 493px; position: absolute; left: 12px; top: 13px;
        }
        .rectangle-2 {
            background: rgba(217,217,217,0); border: 2px solid #000;
            width: 680px; height: 459px; position: absolute; left: 25px; top: 34px;
        }

        /* ─── HORIZONTAL LINES ─── */
        .line { position: absolute; border: 0; }

        .line-21 { border-top: 2px solid #000; width: 136px; left: 25px;   top: 63px; }
        .line-22 { border-top: 2px solid #000; width: 135px; left: 570px;  top: 63px; }
        .line-14 { border-top: 2px solid #000; width: 544px; left: 26px;   top: 88px; }
        .line-15 { border-top: 2px solid #000; width: 544px; left: 26px;   top: 119px; }
        .line-17 { border-top: 2px solid #000; width: 543px; left: 26px;   top: 147px; }
        .line-date-bottom { border-top: 2px solid #000; width: 679px; left: 26px; top: 177px; }
        .line-6  { border-top: 2px solid #000; width: 679px; left: 26px;   top: 214px; }
        .line-16 { border-top: 2px solid #000; width: 544px; left: 161px;  top: 236px; }
        .line-13 { border-top: 2px solid #000; width: 544px; left: 161px;  top: 257px; }
        .line-19 { border-top: 2px solid #000; width: 275px; left: 161px;  top: 285px; }
        .line-20 { border-top: 2px solid #000; width: 275px; left: 161px;  top: 307px; }
        .line-12 { border-top: 2px solid #000; width: 680px; left: 25px;   top: 327px; }
        .line-11 { border-top: 2px solid #000; width: 680px; left: 25px;   top: 350px; }
        .line-10 { border-top: 2px solid #000; width: 679px; left: 26px;   top: 371px; }
        .line-7  { border-top: 2px solid #000; width: 679px; left: 26px;   top: 392px; }
        .line-8  { border-top: 2px solid #000; width: 679px; left: 26px;   top: 471px; }

        /* ─── VERTICAL LINES ─── */
        .line-0  {
            border-top: 2px solid #000; width: 214px;
            left: 161px; top: 178px;
            transform-origin: 0 0; transform: rotate(90deg);
        }
        .line-1  {
            border-top: 2px solid #000; width: 112px;
            left: 161px; top: 36px;
            transform-origin: 0 0; transform: rotate(90deg);
        }
        .line-18 {
            border-top: 2px solid #000; width: 202px;
            left: 570px; top: 35px;
            transform-origin: 0 0; transform: rotate(90deg);
        }
        .line-2  {
            border-top: 2px solid #000; width: 215px;
            left: 436px; top: 178px;
            transform-origin: 0 0; transform: rotate(90.05deg);
        }
        .line-3  {
            border-top: 2px solid #000; width: 95px;
            left: 298px; top: 120px;
            transform-origin: 0 0; transform: rotate(90.138deg);
        }
        .line-4  {
            border-top: 2px solid #000; width: 65px;
            left: 298px; top: 328px;
            transform-origin: 0 0; transform: rotate(90.145deg);
        }
        .line-5  {
            border-top: 2px solid #000; width: 65px;
            left: 569px; top: 328px;
            transform-origin: 0 0; transform: rotate(90.145deg);
        }

        /* ─── LABELS & VALUES ─── */
        .lbl { position: absolute; font-size: 11px; font-weight: 400; color: #000; white-space: nowrap; }
        .val { position: absolute; font-size: 11px; font-weight: 400; color: #000; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* Labels — top < 147 */
        .parts-contents-card-lbl { left: 295px; top: 17px;  font-size: 14px; }
        .from-lbl                { left:  33px; top: 38px;  font-size: 15px; }
        .to-lbl                  { left:  32px; top: 93px;  font-size: 15px; }
        .part-color-lbl          { left: 169px; top: 123px; font-size: 13px; }
        .ship-lbl                { left: 577px; top: 40px;  font-size: 13px; }

        /* Labels — row Date / Adjust Date */
        .date-row-lbl         { left:  33px; top: 154px; font-size: 13px; }
        .adjust-date-row-lbl  { left: 305px; top: 154px; font-size: 13px; }

        /* Labels — top >= 147 (+30) */
        .qr-code-lbl          { left:  33px; top: 180px; font-size: 15px; }
        .ps-code-lbl          { left: 169px; top: 180px; font-size: 13px; }
        .supply-adr-lbl       { left: 305px; top: 180px; font-size: 13px; }
        .next-supply-addr-lbl { left: 443px; top: 180px; font-size: 13px; }
        .order-class-lbl      { left: 578px; top: 180px; font-size: 13px; }
        .prod-seq-no-lbl      { left: 170px; top: 239px; font-size: 13px; }
        .kd-lot-no-lbl        { left: 444px; top: 239px; }
        .inv-cat-lbl          { left: 168px; top: 288px; font-size: 13px; }
        .ms-id-lbl            { left:  32px; top: 330px; font-size: 13px; }
        .sp-order-no-lbl      { left: 170px; top: 330px; font-size: 13px; }
        .part-weight-lbl      { left: 306px; top: 330px; }
        .hns-lbl              { left: 443px; top: 330px; }
        .prod-day-lbl         { left: 578px; top: 330px; }
        .schedule-lbl         { left:  32px; top: 375px; font-size: 13px; }
        .adjusment-lbl        { left: 306px; top: 373px; }
        .check-lbl            { left: 578px; top: 373px; }

        /* Values — top < 147 */
        .from-val     { left:  33px; top: 68px;  width: 125px; font-size: 13px; font-weight: bold; }
        .to-val       { left:  32px; top: 127px; width: 125px; font-size: 13px; font-weight: bold; }
        .part-no-val  { left: 161px; width: 409px; top: 42px; font-size: 35px; font-weight: 700; text-align: center; }
        .part-name-val{ left: 249px; top: 95px;  width: 385px; font-size: 16px; font-weight: 700; }
        .ship-val     { left: 605px; top: 90px;  width: 125px; font-size: 55px; font-weight: 700; }
        .part-color-val{ left: 238px; top: 122px; width: 170px; font-size: 11px; }

        /* Values — row Date / Adjust Date */
        .date-row-val        { left:  80px; top: 155px; width: 325px; font-size: 15px; font-weight: 700; text-transform: uppercase; }
        .adjust-date-row-val { left: 410px; top: 155px; width: 290px; font-size: 15px; font-weight: 700; text-transform: uppercase; }

        /* Values — top >= 147 (+30) */
        .ps-code-val          { left: 169px; top: 218px; width: 125px; font-size: 13px; font-weight: 700; }
        .supply-adr-val       { left: 305px; top: 218px; width: 125px; font-size: 13px; font-weight: 700; }
        .next-supply-addr-val { left: 443px; top: 192px; width: 122px; font-size: 11px; }
        .order-class-val      { left: 578px; top: 218px; width: 122px; font-size: 13px; font-weight: 700; }
        .prod-seq-no-val      { left: 170px; top: 265px; width: 272px; font-size: 15px; font-weight: 700; }
        .kd-lot-no-val        { left: 444px; top: 265px; width: 255px; font-size: 25px; font-weight: 700; text-align: center; }
        .inv-cat-val          { left: 168px; top: 310px; width: 125px; font-size: 15px; font-weight: 700; }
        .ms-id-val            { left:  32px; top: 355px; width: 122px; font-size: 11px; font-weight: 700; }
        .sp-order-no-val      { left: 170px; top: 340px; width: 122px; font-size: 11px; }
        .part-weight-val      { left: 306px; top: 340px; width: 122px; font-size: 11px; }
        .hns-val              { left: 443px; top: 340px; width: 122px; font-size: 11px; }
        .prod-day-val         { left: 578px; top: 340px; width: 122px; font-size: 11px; }
        .schedule-val         { left:  32px; top: 375px; width: 258px; font-size: 11px; }
        .adjusment-val        { left: 306px; top: 375px; width: 258px; font-size: 11px; }
        .check-val            { left: 578px; top: 385px; width: 122px; font-size: 11px; }

        .rack-no-val {
            position: absolute;
            left: 170px; top: 375px;
            font-size: 15px; font-weight: 700;
            color: #000; white-space: nowrap;
        }

        /* Barcode */
        .barcode-wrap {
            position: absolute;
            left: 26px; top: 404px;
            width: 679px; height: 58px;
            background: #ffffff00; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            padding: 0 14px;
        }
        .barcode-wrap svg { display: block; width: 100%; height: 100%; }

        .barcode-text-val {
            position: absolute;
            left: 113px; top: 474px;
            width: 498px; font-size: 13px; text-align: center;
            color: #000; letter-spacing: 0.5px;
        }

        .date-val {
            position: absolute; left: 527px; top: 474px;
            width: 283px; font-size: 15px; font-weight: bold; text-align: center;
            color: #000; letter-spacing: 0.5px;
        }

        /* QR wrapper */
        .qr-wrapper {
            position: absolute;
            left: 40px; top: 217px;
            width: 108px; height: 108px;
            display: flex; align-items: center; justify-content: center;
        }
    </style>
</head>
<body>

@php
    use Picqer\Barcode\BarcodeGeneratorSVG;

    function fmtAddr($val) {
        return strtoupper(implode('   -   ', array_map('trim', explode('-', $val))));
    }

    $barcodeGenerator = new BarcodeGeneratorSVG();

    $partNos = $kanbanhpms->pluck('part_no')->unique()->toArray();
    $rackMap = \App\Models\HpmAddress::whereIn('part_no', $partNos)
                    ->pluck('rack_no', 'part_no');

    $plant1Items = $kanbanhpms->filter(function ($item) use ($rackMap) {
        $rack = strtoupper(trim($rackMap[$item->part_no] ?? ''));
        return !str_starts_with($rack, 'K');
    })->values();

    $plant2Items = $kanbanhpms->filter(function ($item) use ($rackMap) {
        $rack = strtoupper(trim($rackMap[$item->part_no] ?? ''));
        return str_starts_with($rack, 'K');
    })->values();

    $globalIndex = 0;
@endphp

{{-- ════════════════════════════════════════════
     PLANT 1
════════════════════════════════════════════ --}}

@foreach($plant1Items as $item)
@php
    $index        = $globalIndex++;
    $diPart       = preg_replace('/^DI/', '', $item->di_no);
    $seqPart      = str_pad($item->item_seq, 5, '0', STR_PAD_LEFT);
    $shipPart     = str_pad($item->ship, 2, '0', STR_PAD_LEFT);
    $barcodeValue = $diPart . $seqPart . '0000' . $shipPart;
    $qrValue      = $diPart . $seqPart .'-'. $item->part_no;

    $barcodeSvg = $barcodeGenerator->getBarcode(
        $barcodeValue,
        BarcodeGeneratorSVG::TYPE_CODE_39,
        2,
        77
    );

    $rackNo = $rackMap[$item->part_no] ?? '';

    $dateDisplay = '';
    if (!empty($item->datetime)) {
        $parts       = explode(' ', trim($item->datetime));
        $dateDisplay = $parts[0] ?? '';
    }
@endphp

<div class="frame-1">

    <div class="rectangle-1"></div>
    <div class="rectangle-2"></div>

    {{-- Horizontal lines --}}
    <div class="line line-21"></div>
    <div class="line line-22"></div>
    <div class="line line-14"></div>
    <div class="line line-15"></div>
    <div class="line line-17"></div>
    <div class="line line-date-bottom"></div>
    <div class="line line-6"></div>
    <div class="line line-16"></div>
    <div class="line line-13"></div>
    <div class="line line-19"></div>
    <div class="line line-20"></div>
    <div class="line line-12"></div>
    <div class="line line-11"></div>
    <div class="line line-10"></div>
    <div class="line line-7"></div>
    <div class="line line-8"></div>

    {{-- Vertical lines --}}
    <div class="line line-0"></div>
    <div class="line line-1"></div>
    <div class="line line-18"></div>
    <div class="line line-2"></div>
    <div class="line line-3"></div>
    <div class="line line-4"></div>
    <div class="line line-5"></div>

    {{-- Labels --}}
    <div class="lbl parts-contents-card-lbl">Part Content Card</div>
    <div class="lbl from-lbl">FROM</div>
    <div class="lbl to-lbl">TO</div>
    <div class="lbl ship-lbl">SHIP</div>
    <div class="lbl part-color-lbl">PART COLOR</div>

    {{-- FIX: label Date/Adjust Date sekarang UPPERCASE konsisten --}}
    <div class="lbl date-row-lbl">DATE</div>
    <div class="lbl adjust-date-row-lbl">ADJUST DATE</div>

    <div class="lbl qr-code-lbl">QR CODE</div>
    <div class="lbl ps-code-lbl">P/S CODE</div>
    <div class="lbl supply-adr-lbl">SUPPLY ADR</div>
    <div class="lbl next-supply-addr-lbl">NEXT SUPPLY<br>ADDR</div>
    <div class="lbl order-class-lbl">ORDER CLASS</div>
    <div class="lbl prod-seq-no-lbl">PROD SEQ NO</div>
    <div class="lbl kd-lot-no-lbl">KD LOT NO</div>
    <div class="lbl inv-cat-lbl">INV CAT</div>
    <div class="lbl ms-id-lbl">M/S ID</div>
    <div class="lbl sp-order-no-lbl">SP ORDER NO</div>
    <div class="lbl part-weight-lbl">PART WEIGHT</div>
    <div class="lbl hns-lbl">HNS</div>
    <div class="lbl prod-day-lbl">PROD DAY</div>
    <div class="lbl schedule-lbl">ADDRESS</div>
    <div class="lbl adjusment-lbl">ADJUSMENT</div>
    <div class="lbl check-lbl">CHECK</div>

    {{-- Values --}}
    <div class="val from-val">{{ fmtAddr($item->from) }}</div>
    <div class="val to-val">{{ fmtAddr($item->to) }}</div>
    <div class="val part-no-val">{{ $item->part_no }}</div>
    <div class="val part-name-val">{{ $item->part_name }}</div>
    <div class="val ship-val">{{ $item->ship }}</div>
    <div class="val part-color-val">{{ $item->part_color_code }}</div>
    <div class="val date-row-val">{{ $item->datetime ?? '' }}</div>
    <div class="val adjust-date-row-val">{{ $item->adjusted_datetime ?? '' }}</div>
    <div class="val ps-code-val">{{ $item->ps_code }}</div>
    <div class="val supply-adr-val">{{ $item->supply_address }}</div>
    <div class="val next-supply-addr-val">{{ $item->next_supply_address }}</div>
    <div class="val order-class-val">{{ $item->order_class }}</div>
    <div class="val prod-seq-no-val">{{ $item->seq_no }}</div>
    <div class="val kd-lot-no-val">
        {{ substr($item->kd_lot_no, 0, 3) }}<br>
        {{ substr($item->kd_lot_no, 3) }}
    </div>
    <div class="val inv-cat-val">{{ $item->inventory_category }}</div>
    <div class="val ms-id-val">{{ $item->ms_id }}</div>
    <div class="val sp-order-no-val">{{ $item->sp_order_no ?? '' }}</div>
    <div class="val part-weight-val">{{ $item->part_weight }}</div>
    <div class="val hns-val">{{ $item->hns }}</div>
    <div class="val prod-day-val">{{ $item->production_day }}</div>
    <div class="val schedule-val">{{ $item->schedule ?? '' }}</div>
    <div class="val adjusment-val">{{ $item->adjustment ?? '' }}</div>
    <div class="val check-val">{{ $item->check ?? '' }}</div>

    <div class="rack-no-val">{{ $rackNo }}</div>

    <div class="qr-wrapper">
        <div id="qr-{{ $index }}" data-qr="{{ $qrValue }}"></div>
    </div>

    <div class="barcode-wrap">
        {!! $barcodeSvg !!}
    </div>

    <div class="barcode-text-val">{{ $barcodeValue }}</div>
   

</div>
@endforeach


{{-- ════════════════════════════════════════════
     SEPARATOR PAGE — PLANT 2
════════════════════════════════════════════ --}}

@if($plant2Items->isNotEmpty())
<div class="frame-1 separator-page">
    <div class="separator-inner">
        <div class="separator-line"></div>
        <div class="separator-plant-label">PLANT 2</div>
        <div class="separator-line"></div>
        <div class="separator-count">{{ $plant2Items->count() }} kanban</div>
    </div>
</div>


{{-- ════════════════════════════════════════════
     PLANT 2
════════════════════════════════════════════ --}}

@foreach($plant2Items as $item)
@php
    $index        = $globalIndex++;
    $diPart       = preg_replace('/^DI/', '', $item->di_no);
    $seqPart      = str_pad($item->item_seq, 5, '0', STR_PAD_LEFT);
    $shipPart     = str_pad($item->ship, 2, '0', STR_PAD_LEFT);
    $barcodeValue = $diPart . $seqPart . '0000' . $shipPart;
    $qrValue      = $diPart . $seqPart . $item->part_no;

    $barcodeSvg = $barcodeGenerator->getBarcode(
        $barcodeValue,
        BarcodeGeneratorSVG::TYPE_CODE_39,
        2,
        77
    );

    $rackNo = $rackMap[$item->part_no] ?? '';

    // SESUDAH — ambil dd-mm-yyyy, buang HH:MM
$src         = !empty($item->adjusted_datetime) ? $item->adjusted_datetime : ($item->datetime ?? '');
$dateDisplay = !empty($src) ? (explode(' ', trim($src))[0] ?? '') : '';
@endphp

<div class="frame-1">

    <div class="rectangle-1"></div>
    <div class="rectangle-2"></div>
    <div class="line line-21"></div>
    <div class="line line-22"></div>
    <div class="line line-14"></div>
    <div class="line line-15"></div>
    <div class="line line-17"></div>
    <div class="line line-date-bottom"></div>
    <div class="line line-6"></div>
    <div class="line line-16"></div>
    <div class="line line-13"></div>
    <div class="line line-19"></div>
    <div class="line line-20"></div>
    <div class="line line-12"></div>
    <div class="line line-11"></div>
    <div class="line line-10"></div>
    <div class="line line-7"></div>
    <div class="line line-8"></div>
    <div class="line line-0"></div>
    <div class="line line-1"></div>
    <div class="line line-18"></div>
    <div class="line line-2"></div>
    <div class="line line-3"></div>
    <div class="line line-4"></div>
    <div class="line line-5"></div>

    <div class="lbl parts-contents-card-lbl">Part Content Card</div>
    <div class="lbl from-lbl">FROM</div>
    <div class="lbl to-lbl">TO</div>
    <div class="lbl ship-lbl">SHIP</div>
    <div class="lbl part-color-lbl">PART COLOR</div>
    <div class="lbl date-row-lbl">DATE</div>
    <div class="lbl adjust-date-row-lbl">ADJUST DATE</div>
    <div class="lbl qr-code-lbl">QR CODE</div>
    <div class="lbl ps-code-lbl">P/S CODE</div>
    <div class="lbl supply-adr-lbl">SUPPLY ADR</div>
    <div class="lbl next-supply-addr-lbl">NEXT SUPPLY<br>ADDR</div>
    <div class="lbl order-class-lbl">ORDER CLASS</div>
    <div class="lbl prod-seq-no-lbl">PROD SEQ NO</div>
    <div class="lbl kd-lot-no-lbl">KD LOT NO</div>
    <div class="lbl inv-cat-lbl">INV CAT</div>
    <div class="lbl ms-id-lbl">M/S ID</div>
    <div class="lbl sp-order-no-lbl">SP ORDER NO</div>
    <div class="lbl part-weight-lbl">PART WEIGHT</div>
    <div class="lbl hns-lbl">HNS</div>
    <div class="lbl prod-day-lbl">PROD DAY</div>
    <div class="lbl schedule-lbl">ADDRESS</div>
    <div class="lbl adjusment-lbl">ADJUSMENT</div>
    <div class="lbl check-lbl">CHECK</div>

    <div class="val from-val">{{ fmtAddr($item->from) }}</div>
    <div class="val to-val">{{ fmtAddr($item->to) }}</div>
    <div class="val part-no-val">{{ $item->part_no }}</div>
    <div class="val part-name-val">{{ $item->part_name }}</div>
    <div class="val ship-val">{{ $item->ship }}</div>
    <div class="val part-color-val">{{ $item->part_color_code }}</div>
    <div class="val date-row-val">{{ $item->datetime ?? '' }}</div>
    <div class="val adjust-date-row-val">{{ $item->adjusted_datetime ?? '' }}</div>
    <div class="val ps-code-val">{{ $item->ps_code }}</div>
    <div class="val supply-adr-val">{{ $item->supply_address }}</div>
    <div class="val next-supply-addr-val">{{ $item->next_supply_address }}</div>
    <div class="val order-class-val">{{ $item->order_class }}</div>
    <div class="val prod-seq-no-val">{{ $item->seq_no }}</div>
    <div class="val kd-lot-no-val">
        {{ substr($item->kd_lot_no, 0, 3) }}<br>
        {{ substr($item->kd_lot_no, 3) }}
    </div>
    <div class="val inv-cat-val">{{ $item->inventory_category }}</div>
    <div class="val ms-id-val">{{ $item->ms_id }}</div>
    <div class="val sp-order-no-val">{{ $item->sp_order_no ?? '' }}</div>
    <div class="val part-weight-val">{{ $item->part_weight }}</div>
    <div class="val hns-val">{{ $item->hns }}</div>
    <div class="val prod-day-val">{{ $item->production_day }}</div>
    <div class="val schedule-val">{{ $item->schedule ?? '' }}</div>
    <div class="val adjusment-val">{{ $item->adjustment ?? '' }}</div>
    <div class="val check-val">{{ $item->check ?? '' }}</div>

    <div class="rack-no-val">{{ $rackNo }}</div>

    <div class="qr-wrapper">
        <div id="qr-{{ $index }}" data-qr="{{ $qrValue }}"></div>
    </div>

    <div class="barcode-wrap">
        {!! $barcodeSvg !!}
    </div>

    <div class="barcode-text-val">{{ $barcodeValue }}</div>
    <div class="date-val">{{ $dateDisplay }}</div>

</div>
@endforeach

@endif {{-- end @if($plant2Items->isNotEmpty()) --}}


<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('div[data-qr]').forEach(function (div) {
        var val = div.getAttribute('data-qr');
        if (!val) return;
        new QRCode(div, {
            text:         val,
            width:        100,
            height:       100,
            colorDark:    "#000000",
            colorLight:   "#ffffff",
            correctLevel: QRCode.CorrectLevel.L
        });
    });
});
</script>

</body>
</html>