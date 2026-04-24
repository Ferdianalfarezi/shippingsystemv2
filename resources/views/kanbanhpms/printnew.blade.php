
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
            @page { margin: 0; padding: 0; size: 645px 491px; }
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
            background: #ffffff; width: 645px; height: 491px;
            position: relative; overflow: hidden; margin-bottom: 8px;
        }

        .rectangle-1 {
            background: rgba(217,217,217,0); border: 2px solid #000;
            width: 623px; height: 463px; position: absolute; left: 11px; top: 13px;
        }
        .rectangle-2 {
            background: rgba(217,217,217,0); border: 2px solid #000;
            width: 600px; height: 429px; position: absolute; left: 22px; top: 34px;
        }

        .line { position: absolute; border: 0; }
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
        .line-7   { border-top: 2px solid #000; width: 599px; left: 23px;   top: 362px; }
        .line-8   { border-top: 2px solid #000; width: 599px; left: 23px;   top: 441px; }
        .line-21  { border-top: 2px solid #000; width: 120px; left: 22px;   top: 63px; }
        .line-22  { border-top: 2px solid #000; width: 119px; left: 503px;  top: 63px; }
        .line-1  { border-top: 2px solid #000; width: 328px; left: 142px; top: 35px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-18 { border-top: 2px solid #000; width: 172px; left: 503px; top: 35px; transform-origin: 0 0; transform: rotate(90deg); }
        .line-2  { border-top: 2px solid #000; width: 215px; left: 385.21px; top: 148px; transform-origin: 0 0; transform: rotate(90.05deg); }
        .line-3  { border-top: 2px solid #000; width: 87px;  left: 263.21px; top: 120px; transform-origin: 0 0; transform: rotate(90.138deg); }
        .line-4  { border-top: 2px solid #000; width: 65px;  left: 263.21px; top: 298px; transform-origin: 0 0; transform: rotate(90.145deg); }
        .line-5  { border-top: 2px solid #000; width: 65px;  left: 502.21px; top: 298px; transform-origin: 0 0; transform: rotate(90.145deg); }

        .lbl { position: absolute; font-size: 11px; font-weight: 400; color: #000; white-space: nowrap; }
        .val { position: absolute; font-size: 11px; font-weight: 400; color: #000; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .parts-contents-card-lbl { left: 260px; top: 17px; font-size: 14px; }
        .from-lbl       { left: 29px;  top: 38px;  font-size: 15px; }
        .to-lbl         { left: 28px;  top: 93px;  font-size: 15px; }
        .qr-code-lbl    { left: 29px;  top: 150px; font-size: 15px; }
        .part-color-lbl       { left: 149px; top: 123px; font-size: 13px; }
        .prod-seq-no-lbl      { left: 150px; top: 209px; font-size: 13px; }
        .inv-cat-lbl          { left: 148px; top: 258px; font-size: 13px; }
        .sp-order-no-lbl      { left: 150px; top: 300px; font-size: 13px; }
        .ms-id-lbl            { left: 28px;  top: 300px; font-size: 13px; }
        .schedule-lbl         { left: 28px;  top: 345px; font-size: 13px; }
        .supply-adr-lbl       { left: 269px; top: 150px; font-size: 13px; }
        .ps-code-lbl          { left: 149px; top: 150px; font-size: 13px; }
        .part-weight-lbl      { left: 270px; top: 300px; }
        .adjusment-lbl        { left: 270px; top: 343px; }
        .next-supply-addr-lbl { left: 391px; top: 150px; font-size: 13px; }
        .kd-lot-no-lbl        { left: 392px; top: 209px; }
        .hns-lbl              { left: 391px; top: 300px; }
        .ship-lbl             { left: 509px; top: 40px;  font-size: 13px; }
        .order-class-lbl      { left: 510px; top: 150px; font-size: 13px; }
        .prod-day-lbl         { left: 510px; top: 300px; }
        .check-lbl            { left: 510px; top: 343px; }

        .from-val            { left: 29px;  top: 68px;  width: 110px; font-size: 13px; font-weight: bold; }
        .to-val              { left: 28px;  top: 127px; width: 110px; font-size: 13px; font-weight: bold; }
        .part-no-val         { left: 183px; top: 42px;  width: 340px; font-size: 35px; font-weight: 700; }
        .part-name-val       { left: 220px; top: 95px;  width: 340px; font-size: 16px; font-weight: 700; }
        .ship-val            { left: 530px; top: 73px;  width: 110px; font-size: 55px; font-weight: 700; }
        .part-color-val      { left: 210px; top: 122px; width: 150px; font-size: 11px; }
        .ps-code-val         { left: 149px; top: 188px; width: 110px; font-size: 13px; font-weight: 700; }
        .supply-adr-val      { left: 269px; top: 188px; width: 110px; font-size: 13px; font-weight: 700; }
        .next-supply-addr-val{ left: 391px; top: 162px; width: 108px; font-size: 11px; }
        .order-class-val     { left: 510px; top: 188px; width: 108px; font-size: 13px; font-weight: 700; }
        .prod-seq-no-val     { left: 150px; top: 235px; width: 240px; font-size: 15px; font-weight: 700; }
        .kd-lot-no-val       { left: 392px; top: 235px; width: 225px; font-size: 25px; font-weight: 700; text-align: center; }
        .inv-cat-val         { left: 148px; top: 280px; width: 110px; font-size: 15px; font-weight: 700; }
        .sp-order-no-val     { left: 150px; top: 310px; width: 108px; font-size: 11px; }
        .part-weight-val     { left: 270px; top: 310px; width: 108px; font-size: 11px; }
        .hns-val             { left: 391px; top: 310px; width: 108px; font-size: 11px; }
        .prod-day-val        { left: 510px; top: 310px; width: 108px; font-size: 11px; }
        .ms-id-val           { left: 28px;  top: 325px; width: 108px; font-size: 13px; font-weight: 700; }
        .schedule-val        { left: 28px;  top: 345px; width: 228px; font-size: 11px; }
        .adjusment-val       { left: 270px; top: 345px; width: 228px; font-size: 11px; }
        .check-val           { left: 510px; top: 355px; width: 108px; font-size: 11px; }

        .rack-no-val {
            position: absolute;
            left: 150px;
            top: 345px;
            font-size: 15px;
            font-weight: 700;
            color: #000;
            white-space: nowrap;
        }

        .barcode-wrap {
            position: absolute;
            left: 23px;
            top: 374px;
            width: 599px;
            height: 58px;
            background: #ffffff00;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 12px;   /* ← tambah ini */
        }

        .barcode-wrap svg {
            display: block;
            width: 100%;
            height: 100%;
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

        .date-val {
            position: absolute; left: 465px; top: 444px;
            width: 250px; font-size: 15px; font-weight: bold; text-align: center;
            color: #000; letter-spacing: 0.5px;
        }

        .qr-wrapper {
            position: absolute;
            left: 29px;
            top: 187px;
            width: 108px;
            height: 108px;
            display: flex;
            align-items: center;
            justify-content: center;
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
@endphp

@foreach($kanbanhpms as $index => $item)

@php
    $diPart       = preg_replace('/^DI/', '', $item->di_no);
    $seqPart      = str_pad($item->item_seq, 5, '0', STR_PAD_LEFT);
    $shipPart     = str_pad($item->ship, 2, '0', STR_PAD_LEFT);
    $barcodeValue = $diPart . $seqPart . '0000' . $shipPart;
    $qrValue      = $diPart . $seqPart . $item->part_no;

    $barcodeSvg = $barcodeGenerator->getBarcode(
    $barcodeValue,
    BarcodeGeneratorSVG::TYPE_CODE_39,
    2,      // widthFactor naik → bar lebih lebar, gap lebih jelas
    77     // height sedikit turun biar proporsional
);

    $rackNo = \App\Models\HpmAddress::where('part_no', $item->part_no)
                ->value('rack_no') ?? '';

    $dateDisplay = '';
    if (!empty($item->datetime)) {
        $parts       = explode(' ', trim($item->datetime));
        $dateDisplay = $parts[0] ?? '';
    }
@endphp

<div class="frame-1">

    <div class="rectangle-1"></div>
    <div class="rectangle-2"></div>

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
    <div class="line line-1"></div>
    <div class="line line-18"></div>
    <div class="line line-2"></div>
    <div class="line line-3"></div>
    <div class="line line-4"></div>
    <div class="line line-5"></div>

    <div class="lbl parts-contents-card-lbl">Part Content Card</div>
    <div class="lbl from-lbl">FROM</div>
    <div class="lbl to-lbl">TO</div>
    <div class="lbl qr-code-lbl">QR CODE</div>
    <div class="lbl part-color-lbl">PART COLOR</div>
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
    <div class="lbl ship-lbl">SHIP</div>

    <div class="val from-val">{{ fmtAddr($item->from) }}</div>
    <div class="val to-val">{{ fmtAddr($item->to) }}</div>
    <div class="val part-no-val">{{ $item->part_no }}</div>
    <div class="val part-name-val">{{ $item->part_name }}</div>
    <div class="val ship-val">{{ $item->ship }}</div>
    <div class="val part-color-val">{{ $item->part_color_code }}</div>
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

    {{-- QR Code --}}
    <div class="qr-wrapper">
        <div id="qr-{{ $index }}" data-qr="{{ $qrValue }}"></div>
    </div>

    {{--
        BARCODE — server-side SVG dari picqer/php-barcode-generator
        - Tidak ada JS rendering
        - Tidak ada font dependency
        - Bar width matematis presisi
        - * otomatis ditambah library (Code39 spec)
        - Quiet zone sudah include di SVG viewBox
    --}}
    <div class="barcode-wrap">
        {!! $barcodeSvg !!}
    </div>

    <div class="barcode-text-val">{{ $barcodeValue }}</div>
    <div class="date-val">{{ $dateDisplay }}</div>

</div>

@endforeach

<script>
document.addEventListener('DOMContentLoaded', function () {
    /* ── QR Code ── */
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