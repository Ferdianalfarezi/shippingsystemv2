<!-- Advertisement Overlay -->
<div id="adOverlay" style="display: none;">
    <div class="ad-container" id="adContent">
        <!-- Content will be injected here -->
    </div>
    
    <style>
        #adOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: #000000;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        #adOverlay .ad-container {
            width: 100vw;
            height: 100vh;
            position: relative;
        }
        
        #adOverlay .ad-container img {
            width: 100vw;
            height: 100vh;
            object-fit: cover;
        }
        
        #adOverlay .ad-container video {
            width: 100vw;
            height: 100vh;
            object-fit: cover;
        }
    </style>
</div>  

<script>
(function() {
    // Storage key untuk tracking iklan yang sudah tampil hari ini
    const SHOWN_ADS_KEY = 'shown_ads_v2';
    const CHECK_INTERVAL = 30000; // Check setiap 30 detik
    
    // Get today's date string
    function getTodayKey() {
        const now = new Date();
        return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    }
    
    // Get shown ads for today
    function getShownAds() {
        const data = localStorage.getItem(SHOWN_ADS_KEY);
        if (!data) return { date: getTodayKey(), ads: {} };
        
        const parsed = JSON.parse(data);
        
        // Reset jika tanggal berbeda
        if (parsed.date !== getTodayKey()) {
            return { date: getTodayKey(), ads: {} };
        }
        
        return parsed;
    }
    
    // Mark ad as shown dengan start_time sebagai identifier
    // Jadi kalau start_time diubah, iklan akan tampil lagi
    function markAdShown(adId, startTime) {
        const shownAds = getShownAds();
        shownAds.ads[adId] = startTime; // simpan start_time yang sudah ditampilkan
        localStorage.setItem(SHOWN_ADS_KEY, JSON.stringify(shownAds));
    }
    
    // Check if ad already shown today dengan start_time yang sama
    function isAdShownToday(adId, startTime) {
        const shownAds = getShownAds();
        // Cek apakah ID ada DAN start_time sama
        return shownAds.ads[adId] === startTime;
    }
    
    // Show advertisement
function showAd(ad) {
    const overlay = document.getElementById('adOverlay');
    const content = document.getElementById('adContent');
    
    // Set content based on type
    if (ad.type === 'image') {
        content.innerHTML = `<img src="${ad.file_url}" alt="${ad.title}">`;
    } else {
        content.innerHTML = `<video src="${ad.file_url}" autoplay muted playsinline></video>`;
    }
    
    // Show overlay
    overlay.style.display = 'flex';
    
    // Start countdown (hidden, just for timing)
    let remaining = ad.duration;
    
    const countdownInterval = setInterval(function() {
        remaining--;
        
        if (remaining <= 0) {
            clearInterval(countdownInterval);
            hideAd();
            markAdShown(ad.id, ad.start_time);
        }
    }, 1000);
    
    // For video, also listen for video end
    if (ad.type === 'video') {
        const video = content.querySelector('video');
        if (video) {
            video.onended = function() {
                clearInterval(countdownInterval);
                hideAd();
                markAdShown(ad.id, ad.start_time);
            };
        }
    }
}
    
    // Hide advertisement
    function hideAd() {
        const overlay = document.getElementById('adOverlay');
        const content = document.getElementById('adContent');
        
        overlay.style.display = 'none';
        content.innerHTML = '';
    }
    
    // Check for current advertisement
    function checkForAd() {
        fetch('/api/advertisements/current')
            .then(response => response.json())
            .then(data => {
                if (data.show && data.ad) {
                    // Check if not already shown today dengan start_time yang sama
                    if (!isAdShownToday(data.ad.id, data.ad.start_time)) {
                        showAd(data.ad);
                    }
                }
            })
            .catch(error => {
                console.log('Ad check error:', error);
            });
    }
    
    // Initial check
    checkForAd();
    
    // Periodic check
    setInterval(checkForAd, CHECK_INTERVAL);
})();
</script>