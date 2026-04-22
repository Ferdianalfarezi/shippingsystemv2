<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print All - Kanban HPM</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        * { box-sizing: border-box; font-family: Arial, sans-serif; }

        body { margin: 0; padding: 0; background: #fff; }

        @media print {
            @page {
                margin: 0;
                padding: 0;
                size: 645px 491px;
            }
            body { margin: 0; padding: 0; }
            .frame-1 {
                page-break-after: always;
                page-break-inside: avoid;
            }
            .no-print { display: none !important; }
        }

        .print-btn-wrap {
            text-align: center;
            padding: 16px 0 8px;
        }
        .print-btn {
            background: #1a56db;
            color: #fff;
            border: none;
            padding: 10px 32px;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }
        .print-btn:hover { background: #1e429f; }

        /* ===================== FRAME ===================== */
        .frame-1,
        .frame-1 * {
            box-sizing: border-box;
        }

        .frame-1 {
            background: #ffffff;
            width: 645px;
            height: 491px;
            position: relative;
            overflow: hidden;
            margin-bottom: 8px;
        }

        /* ---- borders / rectangles ---- */
        .rectangle-1 {
            background: rgba(217,217,217,0);
            border: 2px solid #000;
            width: 623px;
            height: 463px;
            position: absolute;
            left: 11px;
            top: 13px;
        }
        .rectangle-2 {
            background: rgba(217,217,217,0);
            border: 2px solid #000;
            width: 600px;
            height: 429px;
            position: absolute;
            left: 22px;
            top: 34px;
        }

        /* ---- grid lines ---- */
        .line {
            position: absolute;
            border: 0;
        }
        /* horizontal */
        .line-14  { border-top: 2px solid #000; width: 480px; left: 23px;   top: 88px; }
        .line-15  { border-top: 2px solid #000; width: 480px; left: 23px;   top: 119px; }
        .line-17  { border-top: 2px solid #000; width: 599px; left: 23px;   top: 147px; }
        .line-6   { border-top: 2px solid #000; width: 599px; left: 23px;   top: 184px; }
        .line-16  { border-top: 2px solid #000; width: 480px; left: 142px;  top: 206px; }
        .line-13  { border-top: 2px solid #000; width: 480px; left: 142px;  top: 227px; }
        .line-19  { border-top: 2px solid #000; width: 243px; left: 142px;  top: 255px; }
        .line-20  { border-top: 2px solid #000; width: 243px; left: 142px;  top: 277px; }
        .line-12  { border-top: 2px solid #000; width: 600px; left: 22px;   top: 297px; }
        .line-11  { border-top: 2px solid #000; width: 600px; left: 22px;   top: 320px; }
        .line-10  { border-top: 2px solid #000; width: 599px; left: 23px;   top: 341px; }
        .line-7   { border-top: 2px solid #000; width: 599px; left: 23px;   top: 380px; }
        .line-8   { border-top: 2px solid #000; width: 599px; left: 23px;   top: 441px; }

        /* top-right small lines */
        .line-21  { border-top: 2px solid #000; width: 120px; left: 22px;   top: 63px; }
        .line-22  { border-top: 2px solid #000; width: 119px; left: 503px;  top: 63px; }

        /* vertical */
        .line-1 {
            border-top: 2px solid #000;
            width: 346px;
            left: 142px; top: 35px;
            transform-origin: 0 0;
            transform: rotate(90deg);
        }
        .line-18 {
            border-top: 2px solid #000;
            width: 172px;
            left: 503px; top: 35px;
            transform-origin: 0 0;
            transform: rotate(90deg);
        }
        .line-2 {
            border-top: 2px solid #000;
            width: 233px;
            left: 385.21px; top: 148px;
            transform-origin: 0 0;
            transform: rotate(90.05deg);
        }
        .line-3 {
            border-top: 2px solid #000;
            width: 87px;
            left: 263.21px; top: 120px;
            transform-origin: 0 0;
            transform: rotate(90.138deg);
        }
        .line-4 {
            border-top: 2px solid #000;
            width: 83px;
            left: 263.21px; top: 298px;
            transform-origin: 0 0;
            transform: rotate(90.145deg);
        }
        .line-5 {
            border-top: 2px solid #000;
            width: 83px;
            left: 502.21px; top: 298px;
            transform-origin: 0 0;
            transform: rotate(90.145deg);
        }

        /* ---- label style ---- */
        .lbl {
            position: absolute;
            font-size: 11px;
            font-weight: 400;
            color: #000;
            white-space: nowrap;
        }

        /* ---- value style ---- */
        .val {
            position: absolute;
            font-size: 11px;
            font-weight: 400;
            color: #000;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ===================== LABEL POSITIONS (sesuai gambar) ===================== */
        /* Header */
        .parts-contents-card-lbl { left: 260px; top: 17px; font-size: 14px;  }

        /* Kiri atas */
        .from-lbl       { left: 29px;  top: 38px; font-size: 15px;}
        .to-lbl         { left: 28px;  top: 93px; font-size: 15px;}
        .qr-code-lbl    { left: 29px;  top: 150px; font-size: 15px;}

        /* Tengah kiri (area bagian tengah) */
        .part-color-lbl       { left: 149px; top: 123px; font-size: 13px;}
        .prod-seq-no-lbl      { left: 150px; top: 209px; font-size: 13px;}
        .inv-cat-lbl          { left: 148px; top: 258px; font-size: 13px;}
        .sp-order-no-lbl      { left: 150px; top: 300px; font-size: 13px;}
        .ms-id-lbl            { left: 28px;  top: 300px; font-size: 13px;}
        .schedule-lbl         { left: 28px;  top: 343px; font-size: 13px;}

        /* Tengah */
        .supply-adr-lbl       { left: 269px; top: 150px; font-size: 13px;}
        .ps-code-lbl          { left: 149px; top: 150px; font-size: 13px;}
        .part-weight-lbl      { left: 270px; top: 300px; }
        .adjusment-lbl        { left: 270px; top: 343px; }

        /* Kanan tengah */
        .next-supply-addr-lbl { left: 391px; top: 150px; font-size: 13px;}
        .kd-lot-no-lbl        { left: 392px; top: 209px; }
        .hns-lbl              { left: 391px; top: 300px; }

        /* Kanan */
        .ship-lbl             { left: 509px; top: 40px; font-size: 13px; }
        .order-class-lbl      { left: 510px; top: 150px; font-size: 13px;}
        .prod-day-lbl         { left: 510px; top: 300px; }
        .check-lbl            { left: 510px; top: 343px; }

        /* ===================== VALUE POSITIONS ===================== */
        /* Header kiri atas - FROM */
        .from-val       { left: 29px;  top: 68px;  width: 110px; font-size: 13px; font-weight: bold; }
        /* TO */
        .to-val         { left: 28px;  top: 127px; width: 110px; font-size: 13px; font-weight: bold; }

        /* Part name (besar di tengah atas) */
        .part-no-val    { left: 183px; top: 42px; width: 340px; font-size: 35px; font-weight: 700; }
        /* Part name/desc */
        .part-name-val  { left: 220px; top: 95px; width: 340px; font-size: 16px; font-weight: 700; }

        /* SHIP (kanan atas, besar) */
        .ship-val       { left: 530px; top: 73px; width: 110px; font-size: 55px; font-weight: 700; }

        /* Part color */
        .part-color-val { left: 210px; top: 122px; width: 150px; font-size: 11px; }

        /* P/S Code */
        .ps-code-val    { left: 149px; top: 188px; width: 110px; font-size: 13px; font-weight: 700;}

        /* QR/barcode area (kiri bawah header) */
        /* Supply ADR */
        .supply-adr-val { left: 269px; top: 188px; width: 110px; font-size: 13px; font-weight: 700;}

        /* Next Supply Addr */
        .next-supply-addr-val { left: 391px; top: 162px; width: 108px; font-size: 11px; }

        /* Order Class */
        .order-class-val { left: 510px; top: 188px; width: 108px; font-size: 13px; font-weight: 700;}

        /* Prod Seq No */
        .prod-seq-no-val { left: 150px; top: 235px; width: 240px; font-size: 15px; font-weight: 700;}

        /* KD Lot No (besar) */
        .kd-lot-no-val  { left: 392px; top: 235px; width: 225px; font-size: 25px; font-weight: 700; text-align: center;}

        /* Inv Cat (besar) */
        .inv-cat-val    { left: 148px; top: 280px; width: 110px; font-size: 15px; font-weight: 700; }

        /* SP Order No */
        .sp-order-no-val { left: 150px; top: 310px; width: 108px; font-size: 11px; }

        /* Part Weight */
        .part-weight-val { left: 270px; top: 310px; width: 108px; font-size: 11px; }

        /* HNS */
        .hns-val        { left: 391px; top: 310px; width: 108px; font-size: 11px; }

        /* Prod Day */
        .prod-day-val   { left: 510px; top: 310px; width: 108px; font-size: 11px; }

        /* MS ID */
        .ms-id-val      { left: 28px;  top: 325px; width: 108px; font-size: 13px; font-weight: 700; }

        /* Schedule */
        .schedule-val   { left: 28px;  top: 355px; width: 228px; font-size: 11px; }

        /* Adjusment */
        .adjusment-val  { left: 270px; top: 355px; width: 228px; font-size: 11px; }

        /* Check */
        .check-val      { left: 510px; top: 355px; width: 108px; font-size: 11px; }

        /* HPM Honda Prospect Motor (kiri bawah) */
        .hpm-val {
            position: absolute;
            left: 29px;
            top: 390px;
            font-family: Arial, sans-serif;
            font-size: 13px;
            font-weight: 400;
            color: #000;
            line-height: 1.4;
        }

        /* Barcode */
        .barcode-canvas {
            position: absolute;
            left: 100px;
            top: 395px;
            width: 440px;
            height: 42px;
            display: block;
        }
        .barcode-text-val {
            position: absolute;
            left: 100px;
            top: 444px;
            width: 440px;
            font-size: 13px;
            text-align: center;
            color: #000;
            letter-spacing: 0.5px;
        }

        /* QR placeholder (kiri tengah) */
        .qr-placeholder {
            position: absolute;
            left: 29px;
            top: 187px;
            width: 108px;
            height: 108px;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #555;
        }
    </style>
</head>
<body>

<div class="print-btn-wrap no-print">
    <button class="print-btn" onclick="window.print()">🖨️ Print All</button>
</div>

@php
    function fmtAddr($val) {
        return strtoupper(implode('   -   ', array_map('trim', explode('-', $val))));
    }
@endphp

@foreach($kanbanhpms as $index => $item)

@php
    $diPart       = preg_replace('/^DI/', '', $item->di_no);
    $seqPart      = str_pad($item->item_seq, 5, '0', STR_PAD_LEFT);
    $shipPart     = str_pad($item->ship, 2, '0', STR_PAD_LEFT);
    $barcodeValue = $diPart . $seqPart . '0000' . $shipPart;

    $dateDisplay = '';
    $timeDisplay = '';
    if (!empty($item->datetime)) {
        $parts       = explode(' ', trim($item->datetime));
        $dateDisplay = $parts[0] ?? '';
        $timeDisplay = $parts[1] ?? '';
    }
@endphp

<div class="frame-1">

    {{-- ===== BORDER / RECTANGLE ===== --}}
    <div class="rectangle-1"></div>
    <div class="rectangle-2"></div>

    {{-- ===== GRID LINES ===== --}}
    {{-- Horizontal --}}
    <div class="line line-21"></div>
    <div class="line line-22"></div>
    <div class="line line-14"></div>
    <div class="line line-15"></div>
    <div class="line line-17"></div>
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
    {{-- Vertical --}}
    <div class="line line-1"></div>
    <div class="line line-18"></div>
    <div class="line line-2"></div>
    <div class="line line-3"></div>
    <div class="line line-4"></div>
    <div class="line line-5"></div>

    {{-- ===== LABELS ===== --}}
    <div class="lbl parts-contents-card-lbl">Part Content Card</div>

    <div class="lbl from-lbl">FROM</div>
    <div class="lbl to-lbl">TO</div>
    <div class="lbl qr-code-lbl">QR CODE</div>

    <div class="lbl part-color-lbl">PART COLOR</div>
    <div class="lbl ps-code-lbl">P/S CODE</div>
    <div class="lbl supply-adr-lbl">SUPPLY ADR</div>
    <div class="lbl next-supply-addr-lbl">
        NEXT SUPPLY<br>ADDR
    </div>
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

    <div class="lbl ship-lbl">SHIP</div>

    {{-- ===== VALUES ===== --}}
    <div class="val from-val">{{ fmtAddr($item->from) }}</div>
    <div class="val to-val">{{ fmtAddr($item->to) }}</div>

    {{-- Part No (besar di tengah atas) --}}
    <div class="val part-no-val">{{ $item->part_no }}</div>
    {{-- Part Name (deskripsi) --}}
    <div class="val part-name-val">{{ $item->part_name }}</div>

    {{-- SHIP --}}
    <div class="val ship-val">{{ $item->ship }}</div>

    {{-- Part Color Code --}}
    <div class="val part-color-val">{{ $item->part_color_code }}</div>

    {{-- P/S Code --}}
    <div class="val ps-code-val">{{ $item->ps_code }}</div>

    {{-- Supply Address --}}
    <div class="val supply-adr-val">{{ $item->supply_address }}</div>

    {{-- Next Supply Address --}}
    <div class="val next-supply-addr-val">{{ $item->next_supply_address }}</div>

    {{-- Order Class --}}
    <div class="val order-class-val">{{ $item->order_class }}</div>

    {{-- Production SEQ No --}}
    <div class="val prod-seq-no-val">{{ $item->seq_no }}</div>

    {{-- KD Lot No (besar) --}}
    <div class="val kd-lot-no-val">
        {{ substr($item->kd_lot_no, 0, 3) }}<br>
        {{ substr($item->kd_lot_no, 3) }}
    </div>

    {{-- Inv Cat (besar) --}}
    <div class="val inv-cat-val">{{ $item->inventory_category }}</div>

    {{-- M/S ID --}}
    <div class="val ms-id-val">{{ $item->ms_id }}</div>

    {{-- SP Order No --}}
    <div class="val sp-order-no-val">{{ $item->sp_order_no ?? '' }}</div>

    {{-- Part Weight --}}
    <div class="val part-weight-val">{{ $item->part_weight }}</div>

    {{-- HNS --}}
    <div class="val hns-val">{{ $item->hns }}</div>

    {{-- Prod Day --}}
    <div class="val prod-day-val">{{ $item->production_day }}</div>

    {{-- Schedule --}}
    <div class="val schedule-val">{{ $item->schedule ?? '' }}</div>

    {{-- Adjusment --}}
    <div class="val adjusment-val">{{ $item->adjustment ?? '' }}</div>

    {{-- Check --}}
    <div class="val check-val">{{ $item->check ?? '' }}</div>

    

    {{-- QR Code placeholder (bisa diganti dengan actual QR library) --}}
    <div class="qr-placeholder">QR</div>

    {{-- Barcode --}}
    <canvas class="barcode-canvas"
            id="barcode-{{ $index }}"
            data-barcode="{{ $barcodeValue }}">
    </canvas>
    <div class="barcode-text-val">{{ $barcodeValue }}</div>

</div>

@endforeach

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('canvas[data-barcode]').forEach(function (canvas) {
        var val = canvas.getAttribute('data-barcode');
        if (!val) return;

        var dpr = window.devicePixelRatio || 2;

        var temp = document.createElement('canvas');
        JsBarcode(temp, val, {
            format:       'CODE39',
            displayValue: false,
            margin:       0,
            width:        1.5,
            height:       42 * dpr,
            background:   '#ffffff',
            lineColor:    '#000000',
        });

        canvas.width  = 440 * dpr;
        canvas.height = 32  * dpr;

        canvas.style.width  = '440px';
        canvas.style.height = '32px';

        var ctx = canvas.getContext('2d');
        ctx.imageSmoothingEnabled = false;
        ctx.drawImage(temp, 0, 0, 440 * dpr, 42 * dpr);
    });
});
</script>

</body>
</html>