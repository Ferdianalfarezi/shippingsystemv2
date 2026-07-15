<!DOCTYPE html>
<html lang="id">
<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.6/JsBarcode.all.min.js"></script>
    <meta charset="UTF-8">
    <title>Print Kanban ADM</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #fff;
            font-family: "Calibri", Arial, sans-serif;
        }

        @media print {
            @page { margin: 0; size: 200mm 75mm; }
            body { margin: 0; }
            .kanban-card { page-break-after: always; page-break-inside: avoid; }
            .no-print { display: none !important; }
        }

        .no-print {
            text-align: center;
            padding: 12px 0 8px;
        }
        .print-btn {
            background: #1a56db; color: #fff; border: none;
            padding: 9px 28px; font-size: 14px; border-radius: 6px;
            cursor: pointer;
        }
        .print-btn:hover { background: #1e429f; }

        .kanban-card {
            width: 756px;
            height: 280px;
            position: relative;
            overflow: hidden;
            margin-bottom: 8px;
            background-repeat: no-repeat;
            background-position: top left;
            background-size: 756px 280px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        .val {
            position: absolute;
            font-family: "Calibri", Arial, sans-serif;
            color: #000;
            white-space: nowrap;
            text-align: center;
        }

        .lbl {
            position: absolute;
            font-family: "Calibri", Arial, sans-serif;
            color: #000;
            white-space: nowrap;
            text-align: center;
        }

        .v-qr2 img,
        .v-qr2 canvas {
            pointer-events: none;
        }

        .lbl-dnno      { left: 375px;  top: 7px;  width: 118px; font-size: 13px;}
        .lbl-shop      { left: 210px;  top: 7px;  width: 118px; font-size: 13px; }
        .lbl-cyc       { left: 105px;  top: 7px;  width: 118px; font-size: 13px;}
        .lbl-arrdate   { left: 10px;  top: 7px;  width: 118px; font-size: 13px;}
        .lbl-partinfo  { left: 375px;  top: 65px;  width: 118px; font-size: 13px;}
        .lbl-jobno     { left: 310px;  top: 82px;  width: 118px; font-size: 13px;}
        .lbl-qtykbn    { left: 440px;  top: 82px;  width: 118px; font-size: 13px;}
        .lbl-stpadrs   { left: 75px;  top: 160px;  width: 118px; font-size: 13px;}
        .lbl-min       { left: 520px;  top: 25px;  width: 118px; font-size: 13px; font-weight: 700;}
        .lbl-max       { left: 618px;  top: 25px;  width: 118px; font-size: 13px; font-weight: 700;}
        .lbl-partcat   { left: 547px;  top: 45px;  width: 118px; font-size: 13px;}
        .lbl-pactype   { left: 642px;  top: 46px;  width: 118px; font-size: 12px;}
        .lbl-areacode  { left: 547px;  top: 92px;  width: 118px; font-size: 13px;}
        .lbl-whzone    { left: 642px;  top: 183px;  width: 118px; font-size: 13px;}
        .lbl-parttype  { left: 547px;  top: 183px;  width: 118px; font-size: 13px;}
        .lbl-laneno    { left: 642px;  top: 92px;  width: 118px; font-size: 13px;}
        .lbl-rackno    { left: 547px;  top: 230px;  width: 118px; font-size: 13px;}
        .lbl-racklayer { left: 642px;  top: 230px;  width: 118px; font-size: 13px;}
        .lbl-ptadm     { left: 585px;  top: 4px;  width: 118px; font-size: 15px; font-weight: 700; }

        .v-dnno      { left: 345px;  top: 30px;  width: 118px; font-size: 22px;  font-weight: 700;  }
        .v-shop      { left: 158px;  top: 32px;  width: 218px; font-size: 30px; font-weight: 700; }
        .v-supplier  { left: -10px;  top: 95px; width: 218px; font-size: 15px;  font-weight: 700; overflow: hidden; text-overflow: ellipsis; }
        .v-site      { left: -10px;  top: 130px; width: 218px; font-size: 15px;  font-weight: 700; overflow: hidden; text-overflow: ellipsis; }
        .v-jobno     { left: 175px; top: 105px;   width: 390px; font-size: 33px; font-weight: 700; }
        .v-partno    { left: 180px; top: 155px;   width: 390px; font-size: 25px; font-weight: 700; }
        .v-partname  { left: 180px; top: 190px;  width: 390px; font-size: 18px; text-align: center; }
        .v-qty       { left: 433px; top: 102px;  width: 130px; font-size: 35px;  }
        .v-route     { left: 1px; top: 65px;  width: 130px; font-size: 15px;  font-weight: 700;}
        .v-deldate   { left: -20px; top: 32px;  width: 130px; font-size: 15px;  font-weight: 700;}
        .v-deltime   { left: 32px; top: 32px;  width: 130px; font-size: 15px;  font-weight: 700;}
        .v-cycle-num { left: 135px;  top: 25px; width: 45px; font-size: 38px; font-weight: 700; text-align: center; }
        .v-cycle-sub { left: 160px; top: 52px; width: 30px; font-size: 20px; font-weight: 700; }
        .v-qr        { position: absolute; left: 210px; top: 85px; width: 70px; height: 70;}
        .v-barcode   { position: absolute; left: 188px; bottom: 15px; width: 380px; height: 40px;}
        .v-seq       { position: absolute; left: 475px; bottom: 13px; font-size: 10px; font-weight: 700; }
        .v-qr2       { position: absolute; left: 10px; top: 164px; width: 70px; height: 70px; }
        .v-rackno    { position: absolute; left: 90px; top: 185px; width: 70px; font-size: 20px; font-weight: 700; text-align: center; }
        .v-min       { left: 568px; top: 25px;  width: 118px; font-size: 13px; font-weight: 700; }
        .v-max       { left: 665px; top: 25px;  width: 118px; font-size: 13px; font-weight: 700; }
        .v-partcat   { left: 547px; top: 70px;  width: 118px; font-size: 13px; font-weight: 700;}
        .v-pactype   { left: 642px; top: 70px;  width: 118px; font-size: 13px; font-weight: 700;}
        .v-areacode  { left: 547px; top: 108px; width: 118px; font-size: 60px; font-weight: 700;}
        .v-laneno    { left: 642px; top: 108px; width: 118px; font-size: 60px; font-weight: 700;}
        .v-parttype  { left: 547px; top: 207px; width: 118px; font-size: 13px; }
        .v-whzone    { left: 642px; top: 207px; width: 118px; font-size: 13px; }
        .v-racknoars { left: 547px; top: 255px; width: 118px; font-size: 13px; font-weight: 700;}
        .v-racklayer { left: 642px; top: 255px; width: 118px; font-size: 13px; font-weight: 700;}
        .v-partnostep{ left: 48px; top: 225px; width: 70px; font-size: 20px; font-weight: 700; text-align: center; }

    </style>
</head>
<body>

@foreach($kanbanadms as $item)
@php
    $cycleNum   = (int) ($item->del_cycle   ?? 1);
    $cycleTot   = (int) ($item->cycle_total ?? 1);
    $seqNum     = str_pad($item->seq_num   ?? 1, 6, '0', STR_PAD_LEFT);
    $seqTotal   = (int) ($item->seq_total  ?? 1);
    $barcodeStr = ($item->order_no ?? '') . ($item->job_no ?? '') . $seqNum;
    $qrStr = ($item->order_no ?? '') . ($item->job_no ?? '') . $seqNum;

    // Helper: auto-shrink font berdasarkan panjang string
    $fs = fn($val) => strlen((string) $val) > 15 ? '8px' : '12px';

    $whzone    = $item->ars_wh_zone      ?? '-';
    $parttype  = $item->ars_part_type    ?? '-';
    $pactype   = $item->ars_packing_type ?? '-';
    $racknoars = $item->ars_rack_no      ?? '-';
    $racklayer = $item->ars_rack_layer   ?? '-';
    $partcat   = $item->ars_part_cat     ?? '-';
@endphp
<div class="kanban-card" style="background-image: url('{{ in_array(trim($item->shop_code), ['WELD2', 'ASSY 2']) ? $imageBase64Ro : $imageBase64 }}');">

    {{-- Label --}}
    <div class="lbl lbl-dnno">DN NO</div>
    <div class="lbl lbl-shop">SHOP</div>
    <div class="lbl lbl-cyc">CYCLE</div>
    <div class="lbl lbl-arrdate">ARRIVAL DATE</div>
    <div class="lbl lbl-partinfo">PART INFORMATION</div>
    <div class="lbl lbl-jobno">JOB NO</div>
    <div class="lbl lbl-qtykbn">QTY/KBN</div>
    <div class="lbl lbl-stpadrs">STEP ADDRESS</div>
    <div class="lbl lbl-min">MIN</div>
    <div class="lbl lbl-max">MAX</div>
    <div class="lbl lbl-partcat">PART CAT</div>
    <div class="lbl lbl-pactype">PACKAGING TYPE</div>
    <div class="lbl lbl-areacode">AREA CODE</div>
    <div class="lbl lbl-whzone">WH ZONE</div>
    <div class="lbl lbl-parttype">PART TYPE</div>
    <div class="lbl lbl-laneno">LANE NO</div>
    <div class="lbl lbl-rackno">RACK NO</div>
    <div class="lbl lbl-racklayer">RACK LAYER</div>
    <div class="lbl lbl-ptadm">
        PT ADM - {{ strtoupper($item->shop_code) === 'ENGINE' ? 'ENGINE PLANT' : 'ASSY PLANT' }}
    </div>

    {{-- Value --}}
    <div class="val v-dnno">{{ $item->order_no }}</div>
    <div class="val v-shop"
        @if(in_array(trim($item->shop_code), ['WELD2', 'ASSY 2']))
            style="color: #fff !important;"
        @endif>
        {{ $item->shop_code }}
    </div>
    <div class="val v-supplier">
        PT SARI TAKAGI ELOK<br>PRODUK
    </div>
    <div class="val v-site">3000482</div>
    <div class="val v-jobno">{{ $item->job_no }}</div>
    <div class="val v-partno">{{ $item->part_no }}</div>
    <div class="val v-partname">{{ $item->part_name }}</div>
    <div class="val v-qty">{{ $item->qty_kbn }}</div>
    <div class="val v-route">{{ $item->route }}</div>
    <div class="val v-deldate">{{ $item->del_date }}</div>
    <div class="val v-deltime">{{ $item->del_time }}</div>
    <div class="val v-cycle-num">{{ $cycleNum }}</div>
    <div class="val v-cycle-sub">/{{ $cycleTot }}</div>
    <div class="v-qr" id="qr-{{ $item->id }}"></div>
    <svg class="v-barcode" id="bc-{{ $item->id }}"></svg>
    <div class="val v-seq">SEQ:{{ $seqNum }}/{{ $seqTotal }}</div>
    <div class="v-qr2" id="qr2-{{ $item->id }}"></div>
    <div class="val v-rackno">{{ $item->rack_no ?? '-' }}</div>
    <div class="val v-min">{{ $item->ars_min }}</div>
    <div class="val v-max">{{ $item->ars_max }}</div>
    <div class="val v-partcat"   style="font-size: {{ $fs($partcat) }}; font-weight: 700;">{{ $partcat }}</div>
    <div class="val v-pactype"   style="font-size: {{ $fs($pactype) }}; font-weight: 700;">{{ $pactype }}</div>
    <div class="val v-areacode">{{ $item->ars_area_code }}</div>
    <div class="val v-laneno">{{ $item->lane }}</div>
    <div class="val v-parttype"  style="font-size: {{ $fs($parttype) }};">{{ $parttype }}</div>
    <div class="val v-whzone"    style="font-size: {{ $fs($whzone) }};">{{ $whzone }}</div>
    <div class="val v-racknoars" style="font-size: {{ $fs($racknoars) }}; font-weight: 700;">{{ $racknoars }}</div>
    <div class="val v-racklayer" style="font-size: {{ $fs($racklayer) }}; font-weight: 700;">{{ $racklayer }}</div>
    <div class="val v-partnostep">
        {{ str_ends_with($item->part_no, '-00') ? substr($item->part_no, 0, -3) : $item->part_no }}
    </div>
</div>
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function () {

    @foreach($kanbanadms as $item)
    @php
        $seqNum     = str_pad($item->seq_num ?? 1, 6, '0', STR_PAD_LEFT);
        $barcodeStr = ($item->order_no ?? '') . ($item->job_no ?? '') . $seqNum;
        $qrStr = ($item->order_no ?? '') . ($item->job_no ?? '') . $seqNum;
        $rackNo     = $item->rack_no ?? '';
        $partNoQr   = $item->part_no ?? '';
    @endphp

    new QRCode(document.getElementById('qr-{{ $item->id }}'), {
        text: '{{ addslashes($qrStr) }}' || '-',
        width: 70,
        height: 70,
        correctLevel: QRCode.CorrectLevel.M,
    });

    JsBarcode('#bc-{{ $item->id }}', '{{ $barcodeStr }}', {
        format: 'CODE128',
        width: 1.4,
        height: 35,
        displayValue: true,
        fontSize: 10,
        margin: 0,
        textAlign: 'left',
    });

    @if($partNoQr)
    new QRCode(document.getElementById('qr2-{{ $item->id }}'), {
        text: '{{ addslashes($partNoQr) }}',
        width: 55,
        height: 55,
        correctLevel: QRCode.CorrectLevel.M,
    });

    document.getElementById('qr2-{{ $item->id }}').querySelector('img')?.removeAttribute('title');
    @endif

    @endforeach

});
</script>
</body>
</html>