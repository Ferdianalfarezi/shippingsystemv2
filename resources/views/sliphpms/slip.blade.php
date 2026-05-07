{{-- resources/views/sliphpms/slip.blade.php --}}
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400&display=swap" rel="stylesheet">
<style>
@font-face { font-family:"CR"; src:url('/fonts/heisei.otf') format('opentype'); }

*{box-sizing:border-box;margin:0;padding:0;}
html,body{background:#ccc;}
.frame{background:#fff;width:1330px;height:449px;position:relative;overflow:hidden;margin:0 auto 8px;}

/* All lines */
.l{position:absolute;border-top:2px solid #000;height:0;transform-origin:0 0;}

/* All text */
.t{position:absolute;color:#000;font-family:"CR",monospace;font-size:11px;font-weight:400;letter-spacing:.05em;}
.ta-r{text-align:right;}
.ta-l{text-align:left;}
.lh{line-height:10px;}
.h-slip {font-family:"Inter",sans-serif;font-size:24px;font-weight:300;letter-spacing:.06em;}
.h-title{font-family:"Inter",sans-serif;font-size:18px;font-weight:300;letter-spacing:.06em;text-align:right;}

/* Barcode */
.barcode-wrap{position:absolute;display:flex;flex-direction:column;align-items:flex-start;}
.barcode-wrap svg{display:block;width:100%;height:45px;max-width:250px;}
.barcode-txt{font-size:9px;letter-spacing:.12em;color:#000;margin-top:1px;}

/* ── VERTICAL LINES ── (all rotated 90deg, width = visual height) */
.L1  {left:502px; top:-3px; width:449px; transform:rotate(90deg);}
.L2  {left:957px; top:0;    width:449px; transform:rotate(90deg);}
.L3  {left:978px; top:49px; width:181px; transform:rotate(90deg);}
.L55 {left:937px; top:17px; width:271px; transform:rotate(90deg);}
.L56 {left:524px; top:47px; width:242px; transform:rotate(90deg);}
.L97 {left:480px; top:18px; width:332px; transform:rotate(90deg);}
.L98 {left:13px;  top:49px; width:302px; transform:rotate(90deg);}
.L99 {left:120px; top:18px; width:61px;  transform:rotate(90deg);}
.La  {left:165px; top:18px; width:31px;  transform:rotate(90deg);}
.Lb  {left:310px; top:18px; width:31px;  transform:rotate(90deg);}
.Lc  {left:354px; top:18px; width:31px;  transform:rotate(90deg);}
.Ld  {left:397px; top:18px; width:31px;  transform:rotate(90deg);}
.Le  {left:428px; top:18px; width:31px;  transform:rotate(90deg);}
.Lf  {left:390px; top:50px; width:121px; transform:rotate(90deg);}
.Lg  {left:356px; top:169px;width:60px;  transform:rotate(90deg);}
.Lh  {left:292px; top:50px; width:121px; transform:rotate(90deg);}
.Li  {left:176px; top:79px; width:31px;  transform:rotate(90deg);}
.Lj  {left:106px; top:199px;width:31px;  transform:rotate(90deg);}
.Lk  {left:60px;  top:260px;width:30px;  transform:rotate(90deg);}
.Ll  {left:160px; top:231px;width:119px; transform:rotate(90deg);}
.Lm  {left:216px; top:230px;width:90px;  transform:rotate(90deg);}
.Ln  {left:76px;  top:279px;width:70px;  transform:rotate(90deg);}
.Lo  {left:248px; top:259px;width:31px;  transform:rotate(90deg);}
.Lp  {left:349px; top:259px;width:31px;  transform:rotate(90deg);}
.Lq  {left:93px;  top:260px;width:30px;  transform:rotate(90deg);}
.Lr  {left:127px; top:260px;width:30px;  transform:rotate(90deg);}
.Ls  {left:382px; top:260px;width:90px;  transform:rotate(90deg);}
/* Col1 ticks 10px */
.T1a{left:123px;top:219px;width:10px;transform:rotate(90deg);}
.T1b{left:140px;top:219px;width:10px;transform:rotate(90deg);}
.T1c{left:156px;top:219px;width:10px;transform:rotate(90deg);}
.T1d{left:173px;top:219px;width:10px;transform:rotate(90deg);}
.T1e{left:190px;top:219px;width:10px;transform:rotate(90deg);}
.T1f{left:207px;top:219px;width:10px;transform:rotate(90deg);}
.T1g{left:223px;top:219px;width:10px;transform:rotate(90deg);}
.T1h{left:240px;top:219px;width:10px;transform:rotate(90deg);}
.T1i{left:256px;top:219px;width:10px;transform:rotate(90deg);}
.T1j{left:273px;top:219px;width:10px;transform:rotate(90deg);}
.T1k{left:290px;top:219px;width:10px;transform:rotate(90deg);}
.T1l{left:306px;top:219px;width:10px;transform:rotate(90deg);}
.T1m{left:323px;top:219px;width:10px;transform:rotate(90deg);}
.T1n{left:340px;top:219px;width:10px;transform:rotate(90deg);}
.T1o{left:143px;top:279px;width:10px;transform:rotate(90deg);}
.T1p{left:110px;top:279px;width:10px;transform:rotate(90deg);}
.T1q{left:93px; top:309px;width:10px;transform:rotate(90deg);}
.T1r{left:93px; top:338px;width:10px;transform:rotate(90deg);}
.T1s{left:110px;top:338px;width:10px;transform:rotate(90deg);}
.T1t{left:126px;top:338px;width:10px;transform:rotate(90deg);}
.T1u{left:143px;top:338px;width:10px;transform:rotate(90deg);}
.T1v{left:143px;top:309px;width:10px;transform:rotate(90deg);}
.T1w{left:126px;top:309px;width:10px;transform:rotate(90deg);}
.T1x{left:110px;top:309px;width:10px;transform:rotate(90deg);}
.T1y{left:233px;top:309px;width:10px;transform:rotate(90deg);}
.T1z{left:250px;top:309px;width:10px;transform:rotate(90deg);}
.T2a{left:266px;top:309px;width:10px;transform:rotate(90deg);}
.T2b{left:283px;top:309px;width:10px;transform:rotate(90deg);}
.T2c{left:300px;top:309px;width:10px;transform:rotate(90deg);}
.T2d{left:316px;top:309px;width:10px;transform:rotate(90deg);}
.T2e{left:333px;top:309px;width:10px;transform:rotate(90deg);}
.T2f{left:346px;top:309px;width:10px;transform:rotate(90deg);}
.T2g{left:363px;top:309px;width:10px;transform:rotate(90deg);}
.T2h{left:332px;top:279px;width:10px;transform:rotate(90deg);}
.T2i{left:315px;top:279px;width:10px;transform:rotate(90deg);}
.T2j{left:299px;top:279px;width:10px;transform:rotate(90deg);}
.T2k{left:281px;top:279px;width:10px;transform:rotate(90deg);}
.T2l{left:265px;top:279px;width:10px;transform:rotate(90deg);}
.T2m{left:397px;top:279px;width:10px;transform:rotate(90deg);}
.T2n{left:413px;top:279px;width:10px;transform:rotate(90deg);}
.T2o{left:430px;top:279px;width:10px;transform:rotate(90deg);}
.T2p{left:447px;top:279px;width:10px;transform:rotate(90deg);}
.T2q{left:463px;top:279px;width:10px;transform:rotate(90deg);}
/* Col2 verticals */
.M1{left:632px;top:48px; width:31px;transform:rotate(90deg);}
.M2{left:631px;top:108px;width:31px;transform:rotate(90deg);}
.M3{left:619px;top:199px;width:31px;transform:rotate(90deg);}
.M4{left:685px;top:17px; width:31px;transform:rotate(90deg);}
.M5{left:619px;top:257px;width:31px;transform:rotate(90deg);}
.M6{left:585px;top:258px;width:31px;transform:rotate(90deg);}
.M7{left:652px;top:257px;width:31px;transform:rotate(90deg);}
.M8{left:818px;top:258px;width:31px;transform:rotate(90deg);}
.M9{left:737px;top:18px;width:92px;transform:rotate(90deg);}
.M10{left:852px;top:200px;width:31px;transform:rotate(90deg);}
.Ma{left:772px;top:140px;width:60px;transform:rotate(90deg);}
.Mb{left:685px;top:218px;width:71px;transform:rotate(90deg);}
.Mc{left:752px;top:218px;width:71px;transform:rotate(90deg);}
/* Col2 ticks 11px */
.T3a{left:835px;top:218px;width:11px;transform:rotate(90deg);}
.T3b{left:819px;top:218px;width:11px;transform:rotate(90deg);}
.T3c{left:802px;top:218px;width:11px;transform:rotate(90deg);}
.T3d{left:785px;top:218px;width:11px;transform:rotate(90deg);}
.T3e{left:768px;top:218px;width:11px;transform:rotate(90deg);}
.T3f{left:735px;top:218px;width:11px;transform:rotate(90deg);}
.T3g{left:718px;top:218px;width:11px;transform:rotate(90deg);}
.T3h{left:702px;top:218px;width:11px;transform:rotate(90deg);}
.T3i{left:669px;top:218px;width:11px;transform:rotate(90deg);}
.T3j{left:652px;top:218px;width:11px;transform:rotate(90deg);}
.T3k{left:635px;top:218px;width:11px;transform:rotate(90deg);}
.T3l{left:602px;top:277px;width:11px;transform:rotate(90deg);}
.T3m{left:635px;top:277px;width:11px;transform:rotate(90deg);}
.T3n{left:669px;top:277px;width:11px;transform:rotate(90deg);}
.T3o{left:702px;top:277px;width:11px;transform:rotate(90deg);}
.T3p{left:718px;top:277px;width:11px;transform:rotate(90deg);}
.T3q{left:735px;top:277px;width:11px;transform:rotate(90deg);}
.T3r{left:835px;top:277px;width:11px;transform:rotate(90deg);}
.T3s{left:852px;top:277px;width:11px;transform:rotate(90deg);}
.T3t{left:869px;top:277px;width:11px;transform:rotate(90deg);}
.T3u{left:885px;top:277px;width:11px;transform:rotate(90deg);}
.T3v{left:902px;top:277px;width:11px;transform:rotate(90deg);}
.T3w{left:919px;top:277px;width:11px;transform:rotate(90deg);}
/* Col3 verticals */
.N1{left:1309px;top:17px; width:212px;transform:rotate(90deg);}
.N2{left:1184px;top:49px; width:60px; transform:rotate(90deg);}
.N3{left:1084px;top:48px; width:31px; transform:rotate(90deg);}
.N4{left:1127px;top:17px; width:31px; transform:rotate(90deg);}
.N5{left:1077px;top:17px; width:31px; transform:rotate(90deg);}
.N6{left:1077px;top:138px;width:31px; transform:rotate(90deg);}
.N7{left:1020px;top:198px;width:31px; transform:rotate(90deg);}

/* Col3 ticks 10px */
.T4a{left:1037px;top:219px;width:10px;transform:rotate(90deg);}
.T4b{left:1070px;top:219px;width:10px;transform:rotate(90deg);}
.T4c{left:1104px;top:219px;width:10px;transform:rotate(90deg);}
.T4d{left:1209px;top:219px;width:10px;transform:rotate(90deg);}
.T4e{left:1225px;top:219px;width:10px;transform:rotate(90deg);}
.T4f{left:1242px;top:219px;width:10px;transform:rotate(90deg);}
.T4g{left:1259px;top:219px;width:10px;transform:rotate(90deg);}
.T4h{left:1275px;top:219px;width:10px;transform:rotate(90deg);}
.T4i{left:1292px;top:219px;width:10px;transform:rotate(90deg);}
.T4j{left:1094px;top:159px;width:10px;transform:rotate(90deg);}
.T4k{left:1110px;top:159px;width:10px;transform:rotate(90deg);}
.T4l{left:1127px;top:159px;width:10px;transform:rotate(90deg);}
.T4m{left:1144px;top:159px;width:10px;transform:rotate(90deg);}
.T4n{left:1160px;top:159px;width:10px;transform:rotate(90deg);}
.T4o{left:1177px;top:159px;width:10px;transform:rotate(90deg);}
.T4p{left:1194px;top:159px;width:10px;transform:rotate(90deg);}
.T4q{left:1210px;top:159px;width:10px;transform:rotate(90deg);}
.T4r{left:1227px;top:159px;width:10px;transform:rotate(90deg);}
.T4s{left:1244px;top:159px;width:10px;transform:rotate(90deg);}
.T4t{left:1261px;top:159px;width:10px;transform:rotate(90deg);}
.T4u{left:1277px;top:159px;width:10px;transform:rotate(90deg);}
.T4v{left:1294px;top:159px;width:10px;transform:rotate(90deg);}

/* ── HORIZONTAL LINES ── */
.H1 {left:1076px; top:16px; width:233px;}
.H2 {left:977px; top:49px; width:333px;}
.H3 {left:978px; top:79px; width:331px;}
.H4 {left:978px; top:109px;width:331px;}
.H5 {left:978px; top:139px;width:331px;}
.H6 {left:978px; top:169px;width:331px;}
.H7 {left:978px; top:199px;width:331px;}
.H8 {left:978px; top:229px;width:331px;}
.H9 {left:524px; top:47px; width:413px;}
.H10{left:524px; top:79px; width:213px;}
.H11{left:524px; top:109px;width:413px;}
.H12{left:524px; top:139px;width:413px;}
.H13{left:524px; top:169px;width:413px;}
.H14{left:524px; top:199px;width:413px;}
.H15{left:524px; top:229px;width:413px;}
.H16{left:524px; top:259px;width:413px;}
.H17{left:524px; top:289px;width:413px;}
.H18{left:685px; top:17px; width:252px;}
.H19{left:736px; top:93px; width:201px;}
.H20{left:12px;  top:49px; width:468px;}
.H21{left:12px;  top:79px; width:468px;}
.H22{left:13px;  top:109px;width:468px;}
.H23{left:12px;  top:139px;width:468px;}
.H24{left:13px;  top:169px;width:468px;}
.H25{left:12px;  top:199px;width:468px;}
.H26{left:12px;  top:229px;width:468px;}
.H27{left:13px;  top:259px;width:468px;}
.H28{left:13px;  top:289px;width:468px;}
.H29{left:14px;  top:319px;width:368px;}
.H30{left:12px;  top:350px;width:468px;}
.H31{left:118px; top:17px; width:362px;}

@media print {
    @page {
        margin: 0;
        padding: 0;
        size: 1330px 449px;
    }
    html, body {
        background: #fff;
        margin: 0;
        padding: 0;
    }
    .frame {
        page-break-after: always;
        page-break-inside: avoid;
        transform: none !important;
        margin: 0 !important;
        margin-bottom: 0 !important;
    }
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .t {
        font-weight: 700 !important;
        color: #000 !important;
        font-size: 12px !important;
        letter-spacing: 0 !important;
    }
    .l {
        border-top: 2.5px solid #000 !important;
    }
    .barcode-txt {
        font-weight: 700 !important;
        color: #000 !important;
    }

    /* ── Header paksa ukuran pt agar tidak ikut scale printer ── */
    .h-slip {
        font-size: 18pt !important;   /* setara ~24px tapi tidak di-scale */
        font-weight: 900 !important;
        color: #000 !important;
        letter-spacing: .06em !important;
    }
    .h-title {
        font-size: 13pt !important;   /* setara ~18px tapi tidak di-scale */
        font-weight: 700 !important;
        color: #000 !important;
        letter-spacing: .06em !important;
    }
}
</style>
</head>
<body>

@php
$lines = [
  'L1','L2',
  'L3','L55','L56','L97','L98','L99','La','Lb','Lc','Ld','Le','Lf','Lg','Lh','Li','Lj','Lk','Ll','Lm','Ln','Lo','Lp','Lq','Lr','Ls',
  'T1a','T1b','T1c','T1d','T1e','T1f','T1g','T1h','T1i','T1j','T1k','T1l','T1m','T1n','T1o','T1p','T1q','T1r','T1s','T1t','T1u','T1v','T1w','T1x','T1y','T1z',
  'T2a','T2b','T2c','T2d','T2e','T2f','T2g','T2h','T2i','T2j','T2k','T2l','T2m','T2n','T2o','T2p','T2q',
  'M1','M2','M3','M4','M5','M6','M7','M8','M9','M10','Ma','Mb','Mc',
  'T3a','T3b','T3c','T3d','T3e','T3f','T3g','T3h','T3i','T3j','T3k','T3l','T3m','T3n','T3o','T3p','T3q','T3r','T3s','T3t','T3u','T3v','T3w',
  'N1','N2','N3','N4','N5','N6','N7','N8','N9','Na','Nb',
  'T4a','T4b','T4c','T4d','T4e','T4f','T4g','T4h','T4i','T4j','T4k','T4l','T4m','T4n','T4o','T4p','T4q','T4r','T4s','T4t','T4u','T4v',
  'H1','H2','H3','H4','H5','H6','H7','H8','H9','H10','H11','H12','H13','H14','H15','H16','H17','H18','H19',
  'H20','H21','H22','H23','H24','H25','H26','H27','H28','H29','H30','H31',
];
@endphp

@forelse ($sliphpms as $s)
@php
    $dt = $s->datetime ? \Carbon\Carbon::parse($s->datetime) : null;

    $barcodeGen   = new \Picqer\Barcode\BarcodeGeneratorSVG();
    $barcodeValue = substr($s->di_no, 2) . '-00';
    $barcodeSvg   = $barcodeGen->getBarcode(
        $barcodeValue,
        \Picqer\Barcode\BarcodeGeneratorSVG::TYPE_CODE_128,
        2,  // widthFactor
        45  // height
    );
@endphp
<div class="frame">

  {{-- Lines --}}
  @foreach ($lines as $c)<div class="l {{ $c }}"></div>@endforeach

  {{-- HEADERS --}}
  <div class="t h-slip no-scale"  style="left:37px;top:4px; font-weight:bold;">SLIP</div>
<div class="t h-slip no-scale"  style="left:535px;top:3px; font-weight:bold;">RECEIPT</div>
<div class="t h-slip no-scale"  style="left:980px;top:3px; font-weight:bold;">COPY</div>
<div class="t h-title no-scale" style="left:192px;top:393px;width:284px;">HPM<br>Honda Prospect Motor</div>
<div class="t h-title no-scale" style="left:651px;top:395px;width:284px;">HPM</div>
<div class="t h-title no-scale" style="left:645px;top:420px;width:284px; white-space: nowrap;">TO BE RETURNED TO SUPPLIER</div>
<div class="t h-title no-scale" style="left:1212px;top:393px;width:99px;">HPM<br>SUPPLIER</div>

  {{-- COL 1 LABELS --}}
  <div class="t ta-r" style="left:17px;top:27px;width:76px;">(ORDER)</div>
  <div class="t ta-r" style="left:3px;top:47px;width:93px;">LOCATION CD</div>
  <div class="t ta-r" style="left:120px;top:16px;width:93px;">SLIP NO</div>
  <div class="t ta-r" style="left:65px;top:48px;width:93px;">NAME</div>
  <div class="t ta-r" style="left:386px;top:47px;width:93px;">FROM SUP ADR</div>
  <div class="t ta-r" style="left:268px;top:47px;width:93px;">SHIP TO CD</div>
  <div class="t ta-r" style="left:257px;top:18px;width:93px;">LOC.C</div>
  <div class="t ta-r" style="left:302px;top:17px;width:93px;">MS/SP</div>
  <div class="t ta-r" style="left:332px;top:17px;width:93px;">HNS</div>
  <div class="t ta-r" style="left:375px;top:18px;width:93px;">SPNO</div>
  <div class="t ta-r" style="left:-21px;top:77px;width:93px;">PARTS NO</div>
  <div class="t ta-r" style="left:166px;top:77px;width:93px;">PARTS COLOR</div>
  <div class="t ta-r" style="left:268px;top:78px;width:93px;">PLAN CODE</div>
  <div class="t ta-r" style="left:373px;top:78px;width:93px;">SUPPLY ADR</div>
  <div class="t ta-r" style="left:-3px;top:107px;width:93px;">PARTS NAME</div>
  <div class="t ta-r" style="left:239px;top:107px;width:93px;">DC NO</div>
  <div class="t ta-r" style="left:357px;top:107px;width:93px;">RCV TYPE</div>
  <div class="t ta-r" style="left:-13px;top:137px;width:93px;">KD LOT NO</div>
  <div class="t ta-r" style="left:289px;top:138px;width:93px;">PARTS WEIGHT</div>
  <div class="t ta-r" style="left:367px;top:137px;width:93px;">CONTAINER</div>
  <div class="t ta-r" style="left:-2px;top:167px;width:141px;">PRODUCTION SEQ NO</div>
  <div class="t ta-r" style="left:353px;top:166px;width:93px;">INV CATEGORY</div>
  <div class="t ta-r" style="left:-54px;top:198px;width:141px;">INVOICE NO</div>
  <div class="t ta-r" style="left:332px;top:196px;width:93px;">SP ORD NO</div>
  <div class="t ta-r" style="left:-94px;top:228px;width:141px;">DATE</div>
  <div class="t ta-l lh" style="left:165px;top:231px;">TIME</div>
  <div class="t ta-l lh" style="left:223px;top:231px;">QTY</div>
  <div class="t ta-r" style="left:359px;top:228px;width:93px;">PACKING</div>
  <div class="t ta-l lh" style="left:17px;top:263px;">REC<br>DATE</div>
  <div class="t ta-l lh" style="left:221px;top:263px;">REC<br>QTY</div>
  <div class="t ta-l lh" style="left:354px;top:263px;">QC<br>QTY</div>
  <div class="t ta-l lh" style="left:17px;top:293px;">EXCISE<br>(%)</div>
  <div class="t ta-r" style="left:376px;top:288px;width:93px;">APPROVED BY</div>
  <div class="t ta-l lh" style="left:165px;top:293px;">DUTY</div>
  <div class="t ta-l lh" style="left:18px;top:323px;">SALE<br>(%)</div>
  <div class="t ta-r" style="left:352px;top:329px;width:93px;">(QC)</div>
  <div class="t ta-l lh" style="left:165px;top:322px;">REMARKS</div>

  {{-- COL 1 VALUES --}}
  <div class="t" style="left:168px;top:28px;">{{ substr($s->di_no, 2) . '  -  00' }}</div>
  <div class="t" style="left:15px;top:60px;">{{ $s->from }}&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;00</div>
  <div class="t" style="left:15px;top:90px;">{{ $s->part_no }}</div>
  <div class="t" style="left:15px;top:120px;">{{ $s->part_name }}</div>
  <div class="t" style="left:295px;top:59px;">{{ $s->to }}</div>
  <div class="t" style="left:393px;top:89px;">{{ $s->supply_address }}</div>
  <div class="t" style="left:393px;top:118px;">{{ $s->rcv_type }}</div>
  <div class="t" style="left:15px;top:149px;">HPM&nbsp;&nbsp;&nbsp;{{ $s->kd_lot_no }}</div>
  <div class="t" style="left:295px;top:120px;">{{ $s->part_color }}</div>
  <div class="t" style="left:360px;top:180px;">{{ $s->inv_cat }}</div>
  <div class="t" style="left:295px;top:90px;">HPM&nbsp;&nbsp;{{ $s->ps_code }}</div>
  <div class="t" style="left:17px;top:178px;">{{ $s->prod_seq }}</div>
  <div class="t" style="left:50px;top:240px;">{{ $dt?->format('d/m/Y') }}</div>
  <div class="t" style="left:170px;top:240px;">{{ $dt?->format('H:i') }}</div>
  <div class="t" style="left:316px;top:240px;">{{ $s->qty }}</div>
  <div class="t" style="left:370px;top:30px;">{{ $s->ms_id }}</div>
  <div class="t" style="left:330px;top:30px;">{{ $s->loc }}</div>
  <div class="t" style="left:410px;top:30px;">{{ $s->hns }}</div>

  {{-- BARCODE COL 1 (SLIP) --}}
  <div class="barcode-wrap" style="left:120px; top:362px; width:250px;">
    {!! $barcodeSvg !!}
    <div class="barcode-txt">{{ $barcodeValue }}</div>
  </div>

  {{-- COL 2 LABELS --}}
  <div class="t ta-r" style="left:515px;top:47px;width:93px;">LOCATION CD</div>
  <div class="t ta-r" style="left:698px;top:17px;width:93px;">SLIP NO</div>
  <div class="t ta-r" style="left:675px;top:45px;width:93px;">SIGN</div>
  <div class="t ta-r" style="left:610px;top:47px;width:93px;">SHIP TO CD</div>
  <div class="t ta-r" style="left:716px;top:91px;width:93px;">PLAN CODE:</div>
  <div class="t ta-r" style="left:493px;top:77px;width:93px;">PARTS NO</div>
  <div class="t ta-r" style="left:515px;top:107px;width:93px;">PARTS COLOR</div>
  <div class="t ta-r" style="left:617px;top:107px;width:93px;">PARTS NAME</div>
  <div class="t ta-r" style="left:498px;top:137px;width:93px;">KD LOT NO</div>
  <div class="t ta-r" style="left:770px;top:138px;width:93px;">PARTS WEIGHT</div>
  <div class="t ta-r" style="left:509px;top:167px;width:141px;">PRODUCTION SEQ NO</div>
  <div class="t ta-r" style="left:772px;top:168px;width:93px;">INV CATEGORY</div>
  <div class="t ta-r" style="left:460px;top:198px;width:142px;">INVOICE NO</div>
  <div class="t ta-r" style="left:417px;top:228px;width:141px;">DATE</div>
  <div class="t ta-l lh" style="left:690px;top:235px;">TIME</div>
  <div class="t ta-l lh" style="left:759px;top:235px;">QTY</div>
  <div class="t ta-l lh" style="left:529px;top:261px;">REC<br>DATE</div>
  <div class="t ta-l lh" style="left:761px;top:262px;">REC<br>QTY</div>

  {{-- COL 2 VALUES --}}
  <div class="t" style="left:745px;top:28px;">{{ substr($s->di_no, 2) . '  -  00' }}</div>
  <div class="t" style="left:815px;top:91px;">HPM&nbsp;&nbsp;{{ $s->ps_code }}</div>
  <div class="t" style="left:638px;top:60px;">{{ $s->to }}</div>
  <div class="t" style="left:527px;top:60px;">{{ $s->from }}&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;00</div>
  <div class="t" style="left:527px;top:90px;">{{ $s->part_no }}</div>
  <div class="t" style="left:636px;top:120px;">{{ $s->part_name }}</div>
  <div class="t" style="left:527px;top:149px;">HPM&nbsp;&nbsp;&nbsp;{{ $s->kd_lot_no }}</div>
  <div class="t" style="left:527px;top:179px;">{{ $s->prod_seq }}</div>
  <div class="t" style="left:777px;top:180px;">{{ $s->inv_cat }}</div>
  <div class="t" style="left:565px;top:237px;">{{ $dt?->format('d/m/Y') }}</div>
  <div class="t" style="left:700px;top:240px;">{{ $dt?->format('H:i') }}</div>
  <div class="t" style="left:910px;top:240px;">{{ $s->qty }}</div>

  {{-- BARCODE COL 2 (RECEIPT) --}}
  <div class="barcode-wrap" style="left:610px; top:310px; width:250px;">
    {!! $barcodeSvg !!}
    <div class="barcode-txt">{{ $barcodeValue }}</div>
  </div>

  {{-- COL 3 LABELS --}}
  <div class="t ta-r" style="left:967px;top:48px;width:93px;">LOCATION CD</div>
  <div class="t ta-r" style="left:1060px;top:48px;width:93px;">SHIP TO CD</div>
  <div class="t ta-r" style="left:1090px;top:15px;width:93px;">SLIP NO</div>
  <div class="t ta-r" style="left:1162px;top:47px;width:93px;">PLAN CODE</div>
  <div class="t ta-r" style="left:945px;top:77px;width:93px;">PARTS NO</div>
  <div class="t ta-r" style="left:1173px;top:77px;width:93px;">PARTS COLOR</div>
  <div class="t ta-r" style="left:963px;top:107px;width:93px;">PARTS NAME</div>
  <div class="t ta-r" style="left:915px;top:137px;width:141px;">INVOICE NO</div>
  <div class="t ta-r" style="left:873px;top:167px;width:141px;">DATE</div>
  <div class="t ta-l lh" style="left:1129px;top:173px;">TIME</div>
  <div class="t ta-l lh" style="left:1201px;top:173px;">QTY</div>
  <div class="t ta-l lh" style="left:985px;top:204px;">REC<br>DATE</div>
  <div class="t ta-l lh" style="left:1130px;top:204px;">REC<br>QTY</div>

  {{-- COL 3 VALUES --}}
  <div class="t" style="left:1137px;top:28px;">{{ substr($s->di_no, 2) . '  -  00' }}</div>
  <div class="t" style="left:980px;top:60px;">{{ $s->from }}&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;00</div>
  <div class="t" style="left:1090px;top:60px;">{{ $s->to }}</div>
  <div class="t" style="left:980px;top:90px;">{{ $s->part_no }}</div>
  <div class="t" style="left:983px;top:120px;">{{ $s->part_name }}</div>
  <div class="t" style="left:1190px;top:60px;">HPM&nbsp;&nbsp;{{ $s->ps_code }}</div>
  <div class="t" style="left:983px;top:180px;">{{ $dt?->format('d/m/Y') }}</div>
  <div class="t" style="left:1140px;top:180px;">{{ $dt?->format('H:i') }}</div>
  <div class="t" style="left:1280px;top:175px;">{{ $s->qty }}</div>

  {{-- BARCODE COL 3 (COPY) --}}
  <div class="barcode-wrap" style="left:1058px; top:270px; width:250px;">
    {!! $barcodeSvg !!}
    <div class="barcode-txt">{{ $barcodeValue }}</div>
  </div>

</div>
@empty
<p style="padding:24px;font-family:sans-serif;color:#555;">Tidak ada data slip.</p>
@endforelse

<script>
(function(){
  function scale(){
    var s = Math.min(window.innerWidth / 1330, 1);
    document.querySelectorAll('.frame').forEach(function(el){
      el.style.transform      = 'scale(' + s + ')';
      el.style.transformOrigin = 'top left';
      el.style.marginBottom   = ((449 * s) - 449 + 8) + 'px';
    });
  }

  // Sebelum print: reset semua transform supaya ukuran asli 1330x449 terbaca oleh @page
  window.addEventListener('beforeprint', function(){
    document.querySelectorAll('.frame').forEach(function(el){
      el.style.transform      = 'none';
      el.style.transformOrigin = 'top left';
      el.style.marginBottom   = '0';
    });
  });

  // Setelah print: kembalikan scaling untuk tampilan browser
  window.addEventListener('afterprint', function(){
    scale();
  });

  window.addEventListener('load',   scale);
  window.addEventListener('resize', scale);
})();
</script>
</body>
</html>