<!-- Mobile Camera Barcode Scanner Modal -->
<div x-data="barcodeCameraScanner()"
     @open-barcode-scanner.window="startScanner($event.detail)"
     class="select-none">

    <!-- Modal Overlay -->
    <div x-show="isOpen" x-cloak
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">

        <div class="bg-gray-900 border border-gray-700 rounded-3xl overflow-hidden w-full max-w-sm shadow-2xl relative">
            <!-- Header -->
            <div class="px-5 py-3.5 bg-gray-800 flex items-center justify-between border-b border-gray-700">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h4 class="font-bold text-white text-xs uppercase tracking-wider">Camera Barcode Scanner</h4>
                </div>
                <button type="button" @click="stopScanner()"
                    class="text-gray-400 hover:text-white p-1 rounded-lg text-lg font-bold leading-none cursor-pointer">
                    &times;
                </button>
            </div>

            <!-- Video Viewfinder -->
            <div class="relative bg-black h-72 flex items-center justify-center overflow-hidden">
                <video x-ref="video" class="w-full h-full object-cover" autoplay playsinline muted></video>

                <!-- Scanning Reticle / Viewfinder Frame -->
                <div class="absolute inset-x-8 inset-y-12 border-2 border-emerald-500/80 rounded-2xl pointer-events-none shadow-[0_0_0_9999px_rgba(0,0,0,0.5)]">
                    <!-- Laser Line -->
                    <div class="w-full h-0.5 bg-emerald-400 shadow-[0_0_8px_#10b981] animate-bounce mt-16"></div>
                </div>

                <div x-show="errorMessage" x-cloak
                    class="absolute inset-0 bg-gray-900/90 p-6 flex flex-col items-center justify-center text-center">
                    <svg class="w-10 h-10 text-rose-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-xs text-gray-300 font-medium" x-text="errorMessage"></p>
                    <button type="button" @click="stopScanner()" class="mt-4 px-4 py-2 bg-gray-700 text-white text-xs font-bold rounded-xl">Close</button>
                </div>
            </div>

            <!-- Footer Tips -->
            <div class="p-3 bg-gray-800/90 text-center">
                <p class="text-[11px] text-gray-400">
                    Align barcode inside the green frame to scan automatically.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    function barcodeCameraScanner() {
        return {
            isOpen: false,
            targetInput: null,
            stream: null,
            errorMessage: '',
            scanInterval: null,
            barcodeDetector: null,

            async startScanner(detail) {
                this.targetInput = detail?.target || null;
                this.errorMessage = '';
                this.isOpen = true;

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.errorMessage = 'Camera access is not supported on this browser or connection requires HTTPS.';
                    return;
                }

                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: { ideal: 'environment' } },
                        audio: false
                    });
                    this.$refs.video.srcObject = this.stream;
                    await this.$refs.video.play();

                    // Initialize BarcodeDetector if available
                    if ('BarcodeDetector' in window) {
                        try {
                            const formats = await BarcodeDetector.getSupportedFormats();
                            this.barcodeDetector = new BarcodeDetector({ formats });
                        } catch (e) {
                            this.barcodeDetector = new BarcodeDetector();
                        }
                    }

                    this.startDetecting();
                } catch (err) {
                    console.error('Camera error:', err);
                    this.errorMessage = 'Camera permission denied or camera not found.';
                }
            },

            startDetecting() {
                if (this.scanInterval) clearInterval(this.scanInterval);

                this.scanInterval = setInterval(async () => {
                    if (!this.isOpen || !this.$refs.video || this.$refs.video.readyState !== 4) return;

                    if (this.barcodeDetector) {
                        try {
                            const barcodes = await this.barcodeDetector.detect(this.$refs.video);
                            if (barcodes && barcodes.length > 0) {
                                const code = barcodes[0].rawValue;
                                this.onBarcodeFound(code);
                            }
                        } catch (e) {}
                    }
                }, 250);
            },

            onBarcodeFound(code) {
                if (!code) return;

                // Play haptic feedback if available
                if (navigator.vibrate) {
                    navigator.vibrate(80);
                }

                // Dispatch global event
                window.dispatchEvent(new CustomEvent('barcode-scanned', {
                    detail: {
                        barcode: code,
                        target: this.targetInput
                    }
                }));

                this.stopScanner();
            },

            stopScanner() {
                if (this.scanInterval) {
                    clearInterval(this.scanInterval);
                    this.scanInterval = null;
                }
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                    this.stream = null;
                }
                this.isOpen = false;
            }
        };
    }
</script>
