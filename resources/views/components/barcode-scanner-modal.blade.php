@props([
    'targetInputId' => 'productBarcode',
])

<!-- Barcode Camera Scanner Modal -->
<div id="kayanBarcodeScannerModal" class="erp-modal-overlay" style="z-index: 1200;">
    <div class="erp-modal-container" style="max-width: 480px; width: 95%;">
        <div class="erp-modal-header" style="background: var(--bg-surface-alt);">
            <div class="erp-modal-title">
                <x-icon name="camera" size="18" />
                <span>مسح الباركود بالكاميرا</span>
            </div>
            <button type="button" class="erp-modal-close" onclick="closeKayanBarcodeScanner()" title="إغلاق">✕</button>
        </div>

        <div class="erp-modal-body" style="padding: 16px; text-align: center;">
            <!-- Camera Viewport Box -->
            <div id="scannerViewport" style="position: relative; width: 100%; height: 260px; background: #0f172a; border-radius: var(--border-radius); overflow: hidden; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; box-shadow: inset 0 0 20px rgba(0,0,0,0.6);">
                <!-- Live Video Feed -->
                <video id="kayanBarcodeVideo" playsinline autoplay muted style="width: 100%; height: 100%; object-fit: cover;"></video>

                <!-- Scanning Aim Reticle & Laser -->
                <div id="scannerReticle" style="position: absolute; width: 75%; height: 60%; border: 2px dashed rgba(255,255,255,0.7); border-radius: 12px; pointer-events: none; box-shadow: 0 0 0 9999px rgba(0,0,0,0.45); display: flex; align-items: center; justify-content: center;">
                    <div class="scanner-laser" style="width: 90%; height: 2px; background: #ef4444; box-shadow: 0 0 8px #ef4444; position: absolute; animation: scanLaser 1.8s infinite ease-in-out;"></div>
                </div>

                <!-- Scanner Status / Hint Overlay -->
                <div id="scannerStatusBadge" style="position: absolute; bottom: 10px; background: rgba(0,0,0,0.75); color: #fff; font-size: 11px; padding: 4px 10px; border-radius: 20px; backdrop-filter: blur(4px);">
                    وجّه الكاميرا نحو باركود الصنف
                </div>
            </div>

            <!-- Permission or Error Notice -->
            <div id="scannerErrorMessage" style="display: none; padding: 10px 14px; background: var(--danger-soft); color: var(--danger-text); border-radius: var(--border-radius-sm); font-size: 12px; margin-bottom: 12px; text-align: right;">
            </div>

            <!-- Controls and Fallback Options -->
            <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                <button type="button" id="btnSwitchCamera" onclick="switchScannerCamera()" class="erp-btn erp-btn-secondary" style="font-size: 12px;">
                    <x-icon name="arrow-left-right" size="14" />
                    <span>تبديل الكاميرا</span>
                </button>

                <!-- Native Camera Snap Fallback for Devices without live BarcodeDetector -->
                <label class="erp-btn erp-btn-secondary" style="cursor: pointer; font-size: 12px;">
                    <x-icon name="camera" size="14" />
                    <span>التقاط صورة للباركود</span>
                    <input type="file" id="barcodeFileCapture" accept="image/*" capture="environment" onchange="handleBarcodeImageFile(this)" style="display: none;">
                </label>
            </div>
        </div>

        <div class="erp-modal-footer" style="justify-content: space-between;">
            <span style="font-size: 11px; color: var(--text-muted);">
                يدعم EAN-13, Code-128, QR, UPC
            </span>
            <button type="button" onclick="closeKayanBarcodeScanner()" class="erp-btn erp-btn-secondary">إلغاء</button>
        </div>
    </div>
</div>

<style>
@keyframes scanLaser {
    0% { top: 15%; opacity: 0.8; }
    50% { top: 85%; opacity: 1; }
    100% { top: 15%; opacity: 0.8; }
}
.erp-scanner-active-border {
    border-color: #22c55e !important;
    box-shadow: 0 0 16px rgba(34, 197, 94, 0.8) !important;
}
</style>

<script>
    let kayanScannerStream = null;
    let kayanScannerActive = false;
    let currentFacingMode = 'environment';
    let currentTargetInputId = '{{ $targetInputId }}';
    let barcodeDetectorInstance = null;
    let scannerScanInterval = null;

    // Check BarcodeDetector support
    if ('BarcodeDetector' in window) {
        try {
            barcodeDetectorInstance = new BarcodeDetector({
                formats: ['ean_13', 'ean_8', 'code_128', 'code_39', 'code_93', 'qr_code', 'upc_a', 'upc_e']
            });
        } catch (e) {
            console.warn('BarcodeDetector format init notice:', e);
            barcodeDetectorInstance = new BarcodeDetector();
        }
    }

    function playBeepSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.value = 880; // A5 note
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.12);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.12);
        } catch (e) {}
    }

    async function openKayanBarcodeScanner(targetInputId) {
        if (targetInputId) {
            currentTargetInputId = targetInputId;
        }

        const modal = document.getElementById('kayanBarcodeScannerModal');
        const errBox = document.getElementById('scannerErrorMessage');
        if (errBox) errBox.style.display = 'none';

        if (modal) {
            modal.classList.add('open');
            modal.style.display = 'flex';
        }

        await startCameraStream();
    }

    async function startCameraStream() {
        stopCameraStream();

        const video = document.getElementById('kayanBarcodeVideo');
        const errBox = document.getElementById('scannerErrorMessage');
        const badge = document.getElementById('scannerStatusBadge');

        try {
            const constraints = {
                video: {
                    facingMode: currentFacingMode,
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            };

            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            kayanScannerStream = stream;
            video.srcObject = stream;
            video.setAttribute('playsinline', true);
            await video.play();

            kayanScannerActive = true;
            if (badge) badge.innerText = 'وجّه الكاميرا نحو الباركود';

            startBarcodeScanningLoop();
        } catch (err) {
            console.error('Camera access error:', err);
            if (errBox) {
                errBox.style.display = 'block';
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    errBox.innerHTML = '<strong>تعذر فتح الكاميرا:</strong> يرجى السماح للمتصفح بصلاحية الكاميرا، أو استخدام زر "التقاط صورة للباركود" أدناه.';
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    errBox.innerHTML = '<strong>لم يتم العثور على كاميرا:</strong> يرجى التأكد من اتصال الكاميرا أو استخدام صورة الباركود.';
                } else {
                    errBox.innerHTML = '<strong>ملاحظة:</strong> تتطلب الكاميرا اتصالاً آمناً (HTTPS أو localhost). يمكنك استخدام زر التقاط صورة للباركود مباشرة.';
                }
            }
            if (badge) badge.innerText = 'تعذر تشغيل البث المباشر';
        }
    }

    function startBarcodeScanningLoop() {
        if (scannerScanInterval) clearInterval(scannerScanInterval);

        const video = document.getElementById('kayanBarcodeVideo');
        const reticle = document.getElementById('scannerReticle');

        scannerScanInterval = setInterval(async () => {
            if (!kayanScannerActive || !video || video.readyState < 2) return;

            if (barcodeDetectorInstance) {
                try {
                    const barcodes = await barcodeDetectorInstance.detect(video);
                    if (barcodes && barcodes.length > 0) {
                        const code = barcodes[0].rawValue;
                        handleBarcodeDetected(code);
                    }
                } catch (e) {
                    // detection loop tick
                }
            }
        }, 180);
    }

    function handleBarcodeDetected(codeValue) {
        if (!codeValue) return;

        playBeepSound();

        const reticle = document.getElementById('scannerReticle');
        if (reticle) reticle.classList.add('erp-scanner-active-border');

        const badge = document.getElementById('scannerStatusBadge');
        if (badge) {
            badge.innerText = `تم المسح بنجاح: ${codeValue}`;
            badge.style.background = 'rgba(34, 197, 94, 0.9)';
        }

        const input = document.getElementById(currentTargetInputId);
        if (input) {
            input.value = codeValue;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        setTimeout(() => {
            closeKayanBarcodeScanner();
            if (input) {
                input.focus();
                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 350);
    }

    async function handleBarcodeImageFile(input) {
        if (!input.files || input.files.length === 0) return;
        const file = input.files[0];

        try {
            const imgBitmap = await createImageBitmap(file);
            if (barcodeDetectorInstance) {
                const barcodes = await barcodeDetectorInstance.detect(imgBitmap);
                if (barcodes && barcodes.length > 0) {
                    handleBarcodeDetected(barcodes[0].rawValue);
                    return;
                }
            }
            alert('تم التقاط الصورة، ولكن لم يتم التعرف على باركود واضح. يرجى إعادة المحاولة بإضاءة واضحة أو إدخاله يدوياً.');
        } catch (e) {
            console.error('File scan error:', e);
            alert('تعذر قراءة الباركود من الصورة الملتقطة.');
        }
    }

    function switchScannerCamera() {
        currentFacingMode = (currentFacingMode === 'environment') ? 'user' : 'environment';
        startCameraStream();
    }

    function stopCameraStream() {
        kayanScannerActive = false;
        if (scannerScanInterval) {
            clearInterval(scannerScanInterval);
            scannerScanInterval = null;
        }

        if (kayanScannerStream) {
            kayanScannerStream.getTracks().forEach(track => track.stop());
            kayanScannerStream = null;
        }

        const video = document.getElementById('kayanBarcodeVideo');
        if (video) video.srcObject = null;

        const reticle = document.getElementById('scannerReticle');
        if (reticle) reticle.classList.remove('erp-scanner-active-border');
    }

    function closeKayanBarcodeScanner() {
        stopCameraStream();
        const modal = document.getElementById('kayanBarcodeScannerModal');
        if (modal) {
            modal.classList.remove('open');
            modal.style.display = 'none';
        }
    }

    document.getElementById('kayanBarcodeScannerModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeKayanBarcodeScanner();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeKayanBarcodeScanner();
        }
    });
</script>
