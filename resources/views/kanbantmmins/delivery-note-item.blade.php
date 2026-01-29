{{-- Partial view for a single delivery note item --}}
@php
    $index = $itemIndex;
@endphp

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
                            
                            if(isset($currentNote->qr_code) && !empty($currentNote->qr_code)) {
                                $currentQrCode = $currentNote->qr_code;
                                
                                if(preg_match('/^(.+)(\d)$/', $currentQrCode, $matches)) {
                                    $baseQrCode = $matches[1];
                                    $currentDigit = (int)$matches[2];
                                    
                                    $relatedItems = $allItems->filter(function($item) use ($baseQrCode) {
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
                                        $samePartNumbers = $allItems->where('part_no', $currentPartNo);
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
                            } else {
                                $currentPartNo = $currentNote->part_no ?? '';
                                
                                if(!empty($currentPartNo)) {
                                    $samePartNumbers = $allItems->where('part_no', $currentPartNo);
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
                    // Check if unique_no exists in run_outs table
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