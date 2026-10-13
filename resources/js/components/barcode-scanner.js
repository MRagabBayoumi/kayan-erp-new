/**
 * Kayan ERP — Barcode Camera Scanner Module
 * Live camera barcode detection via Web API with fallback image parsing
 */

let kayanScannerStream = null;
let currentScannerTargetInput = null;
let isKayanScanningActive = false;
let scannerFacingMode = 'environment';
let barcodeDetectorInstance = null;

export function initBarcodeScanner() {
    const modal = document.getElementById('kayanBarcodeScannerModal');
    if (!modal) return;

    // Check BarcodeDetector API support
    if ('BarcodeDetector' in window) {
        try {
            barcodeDetectorInstance = new BarcodeDetector({
                formats: ['ean_13', 'code_128', 'qr_code', 'upc_a', 'upc_e', 'code_39']
            });
        } catch (e) {
            barcodeDetectorInstance = null;
        }
    }

    // Event delegation for opening scanner
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-action="open-barcode-scanner"]');
        if (trigger) {
            e.preventDefault();
            const targetId = trigger.getAttribute('data-target-input') || 'productBarcode';
            openKayanBarcodeScanner(targetId);
        }

        const closeBtn = e.target.closest('[data-action="close-barcode-scanner"]');
        if (closeBtn) {
            e.preventDefault();
            closeKayanBarcodeScanner();
        }

        const switchBtn = e.target.closest('[data-action="switch-scanner-camera"]');
        if (switchBtn) {
            e.preventDefault();
            switchScannerCamera();
        }
    });

    // Close on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeKayanBarcodeScanner();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('open')) {
            closeKayanBarcodeScanner();
        }
    });

    // File input fallback change event
    const fileInput = document.getElementById('barcodeFileCapture');
    if (fileInput) {
        fileInput.addEventListener('change', () => {
            handleBarcodeImageFile(fileInput);
        });
    }

    // Expose window functions for compatibility if needed
    window.openKayanBarcodeScanner = openKayanBarcodeScanner;
    window.closeKayanBarcodeScanner = closeKayanBarcodeScanner;
    window.switchScannerCamera = switchScannerCamera;
}

export async function openKayanBarcodeScanner(targetInputId) {
    currentScannerTargetInput = document.getElementById(targetInputId) || document.getElementById('productBarcode') || document.getElementById('inventorySearchInput');
    const modal = document.getElementById('kayanBarcodeScannerModal');
    const errorNotice = document.getElementById('scannerErrorMessage');

    if (!modal) return;

    if (errorNotice) {
        errorNotice.style.display = 'none';
        errorNotice.textContent = '';
    }

    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');

    await startCameraStream();
}

export function closeKayanBarcodeScanner() {
    const modal = document.getElementById('kayanBarcodeScannerModal');
    if (modal) {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }

    isKayanScanningActive = false;

    if (kayanScannerStream) {
        kayanScannerStream.getTracks().forEach(track => track.stop());
        kayanScannerStream = null;
    }

    const video = document.getElementById('kayanBarcodeVideo');
    if (video) {
        video.srcObject = null;
    }
}

async function startCameraStream() {
    const video = document.getElementById('kayanBarcodeVideo');
    const errorNotice = document.getElementById('scannerErrorMessage');
    const statusBadge = document.getElementById('scannerStatusBadge');

    if (!video) return;

    if (kayanScannerStream) {
        kayanScannerStream.getTracks().forEach(track => track.stop());
    }

    const constraints = {
        video: {
            facingMode: { ideal: scannerFacingMode },
            width: { ideal: 1280 },
            height: { ideal: 720 }
        },
        audio: false
    };

    try {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('متصفحك لا يدعم الوصول المباشر لكاميرا الويب. يرجى استخدام متصفح حديث أو تفعيل اتصال HTTPS.');
        }

        kayanScannerStream = await navigator.mediaDevices.getUserMedia(constraints);
        video.srcObject = kayanScannerStream;
        await video.play();

        isKayanScanningActive = true;
        if (statusBadge) statusBadge.textContent = 'وجّه الكاميرا نحو باركود الصنف';

        requestAnimationFrame(scanVideoFrame);
    } catch (err) {
        isKayanScanningActive = false;
        if (errorNotice) {
            errorNotice.style.display = 'block';
            let msg = 'تعذر تشغيل الكاميرا: ';
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                msg += 'تم رفض إذن الوصول للكاميرا. يرجى السماح بالوصول من إعدادات المتصفح.';
            } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                msg += 'لم يتم العثور على كاميرا متصلة بالجهاز.';
            } else {
                msg += (err.message || 'خطأ غير متوقع. يمكنك استخدام خيار التقاط الصورة أدناه.');
            }
            errorNotice.textContent = msg;
        }
    }
}

export async function switchScannerCamera() {
    scannerFacingMode = (scannerFacingMode === 'environment') ? 'user' : 'environment';
    await startCameraStream();
}

async function scanVideoFrame() {
    if (!isKayanScanningActive) return;

    const video = document.getElementById('kayanBarcodeVideo');
    if (!video || video.readyState !== video.HAVE_ENOUGH_DATA) {
        requestAnimationFrame(scanVideoFrame);
        return;
    }

    if (barcodeDetectorInstance) {
        try {
            const barcodes = await barcodeDetectorInstance.detect(video);
            if (barcodes.length > 0) {
                const detectedCode = barcodes[0].rawValue;
                handleBarcodeDetected(detectedCode);
                return;
            }
        } catch (e) {
            // Frame detection error, continue next frame
        }
    }

    if (isKayanScanningActive) {
        requestAnimationFrame(scanVideoFrame);
    }
}

function handleBarcodeDetected(code) {
    if (!code) return;

    isKayanScanningActive = false;
    playScannerBeep();

    const reticle = document.getElementById('scannerReticle');
    if (reticle) reticle.classList.add('erp-scanner-active-border');

    const statusBadge = document.getElementById('scannerStatusBadge');
    if (statusBadge) statusBadge.textContent = 'تم المسح بنجاح: ' + code;

    setTimeout(() => {
        if (currentScannerTargetInput) {
            currentScannerTargetInput.value = code;
            currentScannerTargetInput.dispatchEvent(new Event('input', { bubbles: true }));
            currentScannerTargetInput.dispatchEvent(new Event('change', { bubbles: true }));

            // If the target input is inside a search form, auto submit
            if (currentScannerTargetInput.form && currentScannerTargetInput.type === 'search') {
                currentScannerTargetInput.form.submit();
            }
        }

        closeKayanBarcodeScanner();
        if (reticle) reticle.classList.remove('erp-scanner-active-border');
    }, 450);
}

function playScannerBeep() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(1200, audioCtx.currentTime);
        gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.15);

        osc.connect(gain);
        gain.connect(audioCtx.destination);

        osc.start();
        osc.stop(audioCtx.currentTime + 0.15);
    } catch (e) {}
}

export async function handleBarcodeImageFile(fileInput) {
    if (!fileInput.files || fileInput.files.length === 0) return;
    const file = fileInput.files[0];

    const statusBadge = document.getElementById('scannerStatusBadge');
    if (statusBadge) statusBadge.textContent = 'جاري تحليل الصورة...';

    const img = new Image();
    img.src = URL.createObjectURL(file);
    img.onload = async () => {
        if (barcodeDetectorInstance) {
            try {
                const barcodes = await barcodeDetectorInstance.detect(img);
                if (barcodes.length > 0) {
                    handleBarcodeDetected(barcodes[0].rawValue);
                    return;
                }
            } catch (e) {}
        }

        const errorNotice = document.getElementById('scannerErrorMessage');
        if (errorNotice) {
            errorNotice.style.display = 'block';
            errorNotice.textContent = 'تعذر قراءة باركود واضح من الصورة. يرجى تجربة صورة أوضح أو إدخال الرقم يدوياً.';
        }
        if (statusBadge) statusBadge.textContent = 'فشل التعرف على الباركود';
    };
}
