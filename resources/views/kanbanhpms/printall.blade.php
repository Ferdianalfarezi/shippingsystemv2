<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print All - Kanban HPM</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        .frame-1,
        .frame-1 * {
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }

        .frame-1 {
            background: #ffffff;
            width: 850px;
            height: 310px;
            position: relative;
            overflow: hidden;
        }

        @media print {
            @page {
                margin: 0;
                padding: 0;
                size: 850px 310px;
            }
            body { margin: 0; padding: 0; }
            .frame-1 {
                page-break-after: always;
                page-break-inside: avoid;
                margin: 0 !important;
                padding: 0 !important;
                position: relative;
            }
            .no-print { display: none !important; }
        }

        .lbl {
            color: #000000;
            font-size: 9px;
            font-weight: 400;
            position: absolute;
            white-space: nowrap;
        }

        .val {
            color: #000000;
            font-size: 10px;
            font-weight: 400;
            position: absolute;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .from-lbl                { left: 30px;  top: 42px; }
        .to-lbl                  { left: 30px;  top: 82px; }
        .supply-address-lbl      { left: 30px;  top: 133px; }
        .next-supply-address-lbl { left: 31px;  top: 155px; }
        .ms-id-lbl               { left: 30px;  top: 176px; }
        .inventory-category-lbl  { left: 30px;  top: 197px; }
        .part-weight-lbl         { left: 146px; top: 197px; }
        .part-color-code-lbl     { left: 287px; top: 91px; }
        .ps-code-lbl             { left: 287px; top: 123px; }
        .order-cls-lbl           { left: 410px; top: 123px; }
        .sp-order-lbl            { left: 500px; top: 123px; }
        .production-seq-no-lbl   { left: 287px; top: 155px; }
        .kdn-lot-no-lbl          { left: 287px; top: 186px; }
        .date-lbl                { left: 643px; top: 103px; }
        .time-lbl                { left: 643px; top: 135px; }
        .hns-lbl                 { left: 605px; top: 174px; }
        .dcc-date-lbl            { left: 595px; top: 206px; }
        .dcc-time-lbl            { left: 713px; top: 206px; }
        .production-day-lbl      { left: 635px; top: 173px; font-size: 8px; }
        .production-lbl          { left: 715px; top: 173px; font-size: 8px; }
        .ship-quantity-lbl       { left: 592px; top: 256px; font-size: 8px; }
        .page-lbl                { left: 700px; top: 18px;  font-size: 12px; }
        .ship-lbl                { left: 650px; top: 45px;  font-size: 10px; }
        .parts-contents-card-lbl {
            left: 318px; top: 16px;
            font-size: 18px;
            font-weight: 400;
            width: 246px;
        }

        .from-val                { left: 100px; top: 40px;  width: 155px; font-size: 12px; }
        .to-val                  { left: 100px; top: 82px;  width: 155px; font-size: 12px; }
        .supply-address-val      { left: 150px; top: 133px; width: 155px; font-size: 13px; }
        .next-supply-address-val { left: 31px;  top: 163px; width: 110px; font-size: 9px; }
        .ms-id-val               { left: 150px; top: 175px; width: 110px; font-size: 13px; }
        .inventory-category-val  { left: 30px;  top: 207px; width: 110px; font-size: 15px; }
        .part-weight-val         { left: 146px; top: 207px; width: 100px; font-size: 9px; }

        .part-name-val {
            left: 287px; top: 42px;
            width: 340px;
            font-size: 13px;
            font-weight: 400;
        }
        .part-no-val {
            left: 287px; top: 70px;
            width: 340px;
            font-size: 11px;
            font-weight: 400;
        }

        .part-color-code-val     { left: 390px; top: 91px;  width: 200px; font-size: 9px; }
        .ps-code-val             { left: 287px; top: 133px; width: 110px; font-size: 14px; }
        .order-cls-val           { left: 435px; top: 135px; width: 60px;  font-size: 12px; }
        .production-seq-no-val   { left: 288px; top: 168px; width: 200px; font-size: 12px; }
        .kdn-lot-no-val          { left: 286px; top: 195px; width: 290px; font-size: 16px; }

        .date-val                { left: 643px; top: 113px; width: 175px; font-size: 17px; }
        .ship-val                { left: 790px; top: 42px;  width: 40px;  font-size: 17px; }
        .time-val                { left: 643px; top: 145px; width: 175px; font-size: 17px; }
        .hns-val                 { left: 660px; top: 184px; width: 160px; font-size: 9px; }
        .dcc-date-val            { left: 595px; top: 216px; width: 110px; font-size: 9px; }
        .dcc-time-val            { left: 713px; top: 216px; width: 110px; font-size: 9px; }
        .production-day-val      { left: 635px; top: 181px; width: 70px;  font-size: 8px; }
        .production-val          { left: 715px; top: 181px; width: 110px; font-size: 8px; }
        .ship-quantity-val       { left: 660px; top: 263px; width: 150px; font-size: 10px; font-weight: 400; }
        .page-val                { left: 756px; top: 18px;  font-size: 12px; }

        .hpm-honda-prospect-motor {
            color: #000000;
            text-align: left;
            font-family: Arial, sans-serif;
            font-size: 15px;
            font-weight: 400;
            position: absolute;
            left: 23px;
            top: 240px;
            width: 170px;
            height: 40px;
            line-height: 1.3;
        }

        .barcode-canvas {
            position: absolute;
            left: 277px;
            top: 220px;
            width: 308px;
            height: 38px;
            display: block;
        }

        .barcode-text-val {
            position: absolute;
            left: 277px;
            top: 267px;
            width: 308px;
            font-size: 12px;
            font-family: 'Courier New', Courier, monospace;
            text-align: center;
            color: #000;
            letter-spacing: 0.5px;
        }

        .rectangle-1  { background: rgba(217,217,217,0); border: 2px solid #000; width: 824px; height: 275px; position: absolute; left: 13px; top: 12px; }
        .rectangle-2  { background: rgba(217,217,217,0); border-radius: 3px; border: 1px solid #000; width: 243px; height: 79px; position: absolute; left: 21px; top: 37px; }
        .rectangle-4  { background: rgba(217,217,217,0); border-radius: 3px; border: 1px solid #000; width: 352px; height: 70px; position: absolute; left: 277px; top: 37px; }
        .rectangle-5  { background: rgba(217,217,217,0); border-radius: 3px; border: 1px solid #000; width: 306px; height: 99px; position: absolute; left: 277px; top: 116px; }
        .rectangle-3  { background: rgba(217,217,217,0); border-radius: 3px; border: 1px solid #000; width: 243px; height: 100px; position: absolute; left: 21px; top: 126px; }
        .rectangle-6  { background: rgba(217,217,217,0); border-radius: 3px; border: 1px solid #000; width: 184px; height: 25px; position: absolute; left: 641px; top: 38px; }
        .rectangle-7  { background: rgba(217,217,217,0); border: 1px dashed #000; width: 165px; height: 30px; position: absolute; left: 659px; top: 68px; }
        .rectangle-8  { background: rgba(217,217,217,0); border: 1px dashed #000; width: 112px; height: 31px; position: absolute; left: 712px; top: 136px; }
        .rectangle-9  { background: rgba(217,217,217,0); border-radius: 3px; border: 1px solid #000; width: 231px; height: 31px; position: absolute; left: 594px; top: 171px; }
        .rectangle-10 { background: rgba(217,217,217,0); border: 1px dashed #000; width: 112px; height: 31px; position: absolute; left: 595px; top: 217px; }
        .rectangle-11 { background: rgba(217,217,217,0); border: 1px dashed #000; width: 112px; height: 31px; position: absolute; left: 713px; top: 217px; }
        .rectangle-12 { background: rgba(217,217,217,0); border: 1px dashed #000; width: 112px; height: 31px; position: absolute; left: 712px; top: 102px; }
        .rectangle-102{ background: rgba(217,217,217,0); border: 1px dashed #000; width: 165px; height: 30px; position: absolute; left: 660px; top: 250px; }

        .line-1  { border-top: 1px solid #000; width: 242px; height: 0; position: absolute; left: 22px;   top: 79px; }
        .line-2  { border-top: 1px solid #000; width: 242px; height: 0; position: absolute; left: 22px;   top: 150px; }
        .line-3  { border-top: 1px solid #000; width: 242px; height: 0; position: absolute; left: 22px;   top: 172px; }
        .line-4  { border-top: 1px solid #000; width: 242px; height: 0; position: absolute; left: 22px;   top: 193px; }
        .line-5  { border-top: 1px solid #000; width: 33px;  height: 0; position: absolute; left: 142px;  top: 193px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-6  { border-top: 1px solid #000; width: 350px; height: 0; position: absolute; left: 278px;  top: 86px; }
        .line-33 { border-top: 1px solid #000; width: 351px; height: 0; position: absolute; left: 277px;  top: 64px; }
        .line-34 { border-top: 1px solid #000; width: 306px; height: 0; position: absolute; left: 277px;  top: 150.64px; }
        .line-39 { border-top: 1px solid #000; width: 305.01px; height: 0; position: absolute; left: 277px; top: 183px; }
        .line-31 { border-top: 1px solid #000; width: 35px;  height: 0; position: absolute; left: 398px;  top: 117px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-32 { border-top: 1px solid #000; width: 35px;  height: 0; position: absolute; left: 484px;  top: 117px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-18 { border-top: 1px solid #000; width: 30px;  height: 0; position: absolute; left: 632px;  top: 171px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-19 { border-top: 1px solid #000; width: 30px;  height: 0; position: absolute; left: 712px;  top: 171px; transform-origin: 0 0; transform: rotate(90deg); }

        .line-7  { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 686px; top: 68px;  transform-origin: 0 0; transform: rotate(90deg); }
        .line-8  { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 713px; top: 68px;  transform-origin: 0 0; transform: rotate(90deg); }
        .line-9  { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 741px; top: 68px;  transform-origin: 0 0; transform: rotate(90deg); }
        .line-10 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 768px; top: 68px;  transform-origin: 0 0; transform: rotate(90deg); }
        .line-11 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 796px; top: 68px;  transform-origin: 0 0; transform: rotate(90deg); }
        .line-12 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 796px; top: 102px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-13 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 768px; top: 102px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-14 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 741px; top: 103px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-15 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 796px; top: 137px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-16 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 768px; top: 137px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-17 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 741px; top: 136px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-20 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 651px; top: 217px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-21 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 679px; top: 217px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-22 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 624px; top: 218px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-23 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 769px; top: 217px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-24 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 797px; top: 217px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-25 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 742px; top: 218px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-26 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 687px; top: 250px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-27 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 714px; top: 250px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-28 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 742px; top: 250px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-29 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 769px; top: 250px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-30 { border-top: 1px dashed #000; width: 30px; height: 0; position: absolute; left: 797px; top: 250px; transform-origin: 0 0; transform: rotate(90deg); }

        .print-btn-wrap { text-align: center; padding: 16px 0 8px; }
        .print-btn { background: #1a56db; color: #fff; border: none; padding: 10px 32px; font-size: 15px; border-radius: 6px; cursor: pointer; font-family: Arial, sans-serif; }
        .print-btn:hover { background: #1e429f; }
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

    <div class="lbl from-lbl">FROM:</div>
    <div class="lbl to-lbl">TO:</div>
    <div class="lbl supply-address-lbl">Supply Address</div>
    <div class="lbl next-supply-address-lbl">Next Supply Address</div>
    <div class="lbl inventory-category-lbl">Inventory Category</div>
    <div class="lbl part-weight-lbl">Part Weight</div>
    <div class="lbl ms-id-lbl">M/S ID</div>
    <div class="lbl part-color-code-lbl">Part Color Code:</div>
    <div class="lbl ps-code-lbl">P/S Code</div>
    <div class="lbl order-cls-lbl">Order Class</div>
    <div class="lbl sp-order-lbl">SP Order No.</div>
    <div class="lbl production-seq-no-lbl">Production SEQ No</div>
    <div class="lbl kdn-lot-no-lbl">KD Lot No.</div>
    <div class="lbl date-lbl">Date:</div>
    <div class="lbl time-lbl">Time:</div>
    <div class="lbl hns-lbl">HNS</div>
    <div class="lbl dcc-date-lbl">DCC Date</div>
    <div class="lbl dcc-time-lbl">DCC Time</div>
    <div class="lbl production-day-lbl">Production day</div>
    <div class="lbl production-lbl">Production</div>
    <div class="lbl ship-quantity-lbl">Ship Quantity</div>
    <div class="lbl page-lbl">PAGE :</div>
    <div class="lbl ship-lbl">Ship</div>
    <div class="lbl parts-contents-card-lbl">Parts Contents Card</div>

    <div class="val from-val">{{ fmtAddr($item->from) }}</div>
    <div class="val to-val">{{ fmtAddr($item->to) }}</div>
    <div class="val supply-address-val">{{ $item->supply_address }}</div>
    <div class="val next-supply-address-val">{{ $item->next_supply_address }}</div>
    <div class="val inventory-category-val">{{ $item->inventory_category }}</div>
    <div class="val part-weight-val">{{ $item->part_weight }}</div>
    <div class="val ms-id-val">{{ $item->ms_id }}</div>
    <div class="val part-name-val">{{ $item->part_no }}</div>
    <div class="val part-no-val">{{ $item->part_name }}</div>
    <div class="val part-color-code-val">{{ $item->part_color_code }}</div>
    <div class="val ps-code-val">{{ $item->ps_code }}</div>
    <div class="val order-cls-val">{{ $item->order_class }}</div>
    <div class="val production-seq-no-val">{{ $item->seq_no }}</div>
    <div class="val kdn-lot-no-val">{{ $item->kd_lot_no }}</div>
    <div class="val ship-val">{{ $item->ship }}</div>
    <div class="val date-val">{{ $dateDisplay }}</div>
    <div class="val time-val">{{ $timeDisplay }}</div>
    <div class="val hns-val">{{ $item->hns }}</div>
    <div class="val dcc-date-val">{{ $item->dcc_date }}</div>
    <div class="val dcc-time-val">{{ $item->dcc_time }}</div>
    <div class="val production-day-val">{{ $item->production_day }}</div>
    <div class="val production-val">{{ $item->production }}</div>
    <div class="val ship-quantity-val">{{ $item->ship_quantity }}</div>
    <div class="val page-val">{{ $item->page ?? ($index + 1) }}</div>

    <div class="hpm-honda-prospect-motor">HPM<br>Honda Prospect Motor</div>

    <canvas class="barcode-canvas"
            id="barcode-{{ $index }}"
            data-barcode="{{ $barcodeValue }}">
    </canvas>
    <div class="barcode-text-val">{{ $barcodeValue }}</div>

    <div class="rectangle-1"></div>
    <div class="rectangle-2"></div>
    <div class="rectangle-4"></div>
    <div class="rectangle-5"></div>
    <div class="rectangle-3"></div>
    <div class="rectangle-6"></div>
    <div class="rectangle-7"></div>
    <div class="rectangle-8"></div>
    <div class="rectangle-9"></div>
    <div class="rectangle-10"></div>
    <div class="rectangle-11"></div>
    <div class="rectangle-12"></div>
    <div class="rectangle-102"></div>

    <div class="line-1"></div>
    <div class="line-2"></div>
    <div class="line-3"></div>
    <div class="line-4"></div>
    <div class="line-5"></div>
    <div class="line-6"></div>
    <div class="line-7"></div>
    <div class="line-8"></div>
    <div class="line-9"></div>
    <div class="line-10"></div>
    <div class="line-11"></div>
    <div class="line-12"></div>
    <div class="line-13"></div>
    <div class="line-14"></div>
    <div class="line-15"></div>
    <div class="line-16"></div>
    <div class="line-17"></div>
    <div class="line-18"></div>
    <div class="line-19"></div>
    <div class="line-20"></div>
    <div class="line-21"></div>
    <div class="line-22"></div>
    <div class="line-23"></div>
    <div class="line-24"></div>
    <div class="line-25"></div>
    <div class="line-26"></div>
    <div class="line-27"></div>
    <div class="line-28"></div>
    <div class="line-29"></div>
    <div class="line-30"></div>
    <div class="line-31"></div>
    <div class="line-32"></div>
    <div class="line-33"></div>
    <div class="line-34"></div>
    <div class="line-39"></div>
</div>
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('canvas[data-barcode]').forEach(function (canvas) {
        var val = canvas.getAttribute('data-barcode');
        if (!val) return;

        var dpr = window.devicePixelRatio || 2;

        // Render ke temp canvas dengan resolusi tinggi
        var temp = document.createElement('canvas');
        JsBarcode(temp, val, {
            format:       'CODE39',
            displayValue: false,
            margin:       0,
            width:        1,
            height:       45 * dpr,
            background:   '#ffffff',
            lineColor:    '#000000',
        });

        // Set canvas internal resolution tinggi (dpr x lipat)
        canvas.width  = 308 * dpr;
        canvas.height = 48  * dpr;

        // CSS size tetap 308x48
        canvas.style.width  = '308px';
        canvas.style.height = '43px';

        var ctx = canvas.getContext('2d');
        ctx.imageSmoothingEnabled = false; // matikan anti-alias = bar lebih tajam
        ctx.drawImage(temp, 0, 0, 308 * dpr, 48 * dpr);
    });
});
</script>

</body>
</html>