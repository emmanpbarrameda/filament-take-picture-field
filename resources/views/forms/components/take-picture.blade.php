@php
    $isDisabled = $field->isDisabled();
    $isMultiple = $field->getMultipleMode();
    $shotSequence = $field->getShotSequence();
    $requireAllShots = $field->getRequireAllShots();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div wire:key="{{ $getStatePath() }}-{{ $isDisabled ? 'disabled' : 'enabled' }}" x-data="{
        photoData: $wire.entangle('{{ $getStatePath() }}'),
        photoSelected: false,
        webcamActive: false,
        webcamError: null,
        cameraStream: null,
        availableCameras: [],
        selectedCameraId: null,
        modalOpen: false,
        showingPreview: false,
        aspectRatio: '{{ $getAspect() }}',
        imageQuality: {{ $getImageQuality() }},
        maxWidth: {{ $getCaptureMaxWidth() ?? 'null' }},
        maxHeight: {{ $getCaptureMaxHeight() ?? 'null' }},
        autoStart: {{ $getAutoStart() ? 'true' : 'false' }},
        mirroredView: false,
        isBackCamera: false,
        isDisabled: {{ $isDisabled ? 'true' : 'false' }},
        isMobile: /iPhone|iPad|iPod|Android/i.test(navigator.userAgent),
        currentFacingMode: 'environment',
        componentId: '{{ $getId() }}',
        initialized: false,
        isHovering: false,
    
        isMultiple: {{ $isMultiple ? 'true' : 'false' }},
        shotSequence: @js($shotSequence),
        requireAllShots: {{ $requireAllShots ? 'true' : 'false' }},
        currentShotIndex: 0,
        capturedShots: {},
        retakeOnlyKey: null,
        retakeSingle: false,
        retakeKey: null,
        modalSnapshot: null,
    
        enableTTS: {{ $field->getEnableTTS() ? 'true' : 'false' }},
        ttsLang: '{{ $field->getTTSLang() }}',
        isSpeaking: false,

        init() {
            this.initMultipleMode();
            this.checkVisibilityAndAutoStart();
        },
    
        speak(text) {
            if (!this.enableTTS || !text) return;
            if (!('speechSynthesis' in window)) {
                console.warn('Text-to-speech not supported');
                return;
            }
            
            window.speechSynthesis.cancel();
            
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = this.ttsLang;
            utterance.rate = 0.9;
            utterance.pitch = 1.0;
            
            utterance.onstart = () => { this.isSpeaking = true; };
            utterance.onend = () => { this.isSpeaking = false; };
            
            window.speechSynthesis.speak(utterance);
        },

        initMultipleMode() {
            if (!this.isMultiple) return;
    
            if (this.photoData && typeof this.photoData === 'object' && !Array.isArray(this.photoData)) {
                this.capturedShots = { ...this.photoData };
            }
    
            this.ensureShotKeys();
            this.syncPhotoDataFromShots();
        },
    
        get currentShot() {
            return this.shotSequence?.[this.currentShotIndex] || null;
        },
    
        get completedShotsCount() {
            return Object.keys(this.capturedShots).filter(k => this.capturedShots[k]).length;
        },
    
        get allShotsCompleted() {
            if (!this.requireAllShots) return true;
            return (this.shotSequence || []).every(shot => this.capturedShots?.[shot.key]);
        },
    
        get hasAnyCapturedShot() {
            return Object.values(this.capturedShots || {}).some(v => !!v);
        },
    
        syncPhotoDataFromShots() {
            if (!this.isMultiple) return;
            if (!this.hasAnyCapturedShot) {
                this.photoData = null;
                return;
            }
            this.photoData = { ...this.capturedShots };
        },
    
        ensureShotKeys() {
            (this.shotSequence || []).forEach(shot => {
                if (shot?.key && typeof this.capturedShots[shot.key] === 'undefined') {
                    this.capturedShots[shot.key] = null;
                }
            });
        },
    
        getImageUrl(path) {
            if (!path) return null;
    
            if (typeof path !== 'string') return null;
    
            if (path.startsWith('data:image/')) return path;
            if (path.startsWith('http://') || path.startsWith('https://')) return path;
    
            const cleanPath = path.replace(/^\/+/, '');
            return '/storage/' + cleanPath;
        },
    
        async getCameras() {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                this.availableCameras = devices.filter(device => device.kind === 'videoinput');
    
                if (this.availableCameras.length > 0 && !this.selectedCameraId) {
                    this.selectedCameraId = this.availableCameras[0].deviceId;
                    this.detectCameraType(this.availableCameras[0]);
                }
    
                return this.availableCameras;
            } catch (error) {
                console.error('Error getting camera devices:', error);
                this.webcamError = '{{ __('filament-take-picture-field::take-picture-field.error_unable_to_detect_cameras') }}';
                return [];
            }
        },
    
        detectCameraType(camera) {
            if (!camera) return;
    
            const label = (camera.label || '').toLowerCase();
    
            this.isBackCamera = label.includes('back') ||
                label.includes('rear') ||
                label.includes('environment') ||
                label.includes('0, facing back');
    
            this.mirroredView = !this.isBackCamera;
        },
    
        async startCamera() {
            if (this.isDisabled) return;

            if (this.webcamActive) return;

            this.stopCamera();

            await new Promise(r => setTimeout(r, 120));

            this.initMultipleMode();

            this.webcamActive = true;
            this.webcamError = null;

            await this.$nextTick();

            let aspectWidth = 16;
            let aspectHeight = 9;

            if (this.aspectRatio) {
                const parts = this.aspectRatio.split(':');
                if (parts.length === 2) {
                    aspectWidth = parseInt(parts[0]);
                    aspectHeight = parseInt(parts[1]);
                }
            }

            const constraints = {
                video: {
                    facingMode: this.isMobile ? this.currentFacingMode : 'user',
                    width: { ideal: aspectWidth * 120 },
                    height: { ideal: aspectHeight * 120 },
                },
                audio: false
            };

            if (this.selectedCameraId) {
                constraints.video.deviceId = { exact: this.selectedCameraId };
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                this.cameraStream = stream;

                await this.$nextTick();
                if (this.$refs.video) this.$refs.video.srcObject = stream;

                if ({{ $getShowCameraSelector() ? 'true' : 'false' }}) {
                    await this.getCameras();
                }

                const videoTrack = stream.getVideoTracks()[0];
                if (videoTrack) {
                    const settings = videoTrack.getSettings();
                    if (settings.facingMode) {
                        this.isBackCamera = settings.facingMode === 'environment';
                        this.mirroredView = !this.isBackCamera;
                    }
                }

                if ({{ $getUseModal() ? 'true' : 'false' }} && !this.modalOpen) {
                    this.openModal();
                }

                if (this.isMultiple && this.currentShot) {
                    await this.$nextTick();
                    this.speak(
                        this.currentShot.instruction ||
                        '{{ __('filament-take-picture-field::take-picture-field.take_picture_of', ['label' => '__LABEL__']) }}'
                            .replace('__LABEL__', this.currentShot.label)
                    );
                }

            } catch (error) {
                console.error('Error accessing webcam:', error);

                this.stopCamera();
                this.webcamActive = false;

                this.handleWebcamError(error);
            }

        },
    
        handleWebcamError(error) {
            if (/iPhone|iPad|iPod|Android/i.test(navigator.userAgent)) {
                if (window.location.protocol !== 'https:') {
                    this.webcamError = '{{ __('filament-take-picture-field::take-picture-field.error_https_required_mobile') }}';
                    return;
                }
            }
    
            switch (error.name) {
                case 'NotAllowedError':
                case 'PermissionDeniedError':
                    this.webcamError = '{{ __('filament-take-picture-field::take-picture-field.error_permission_denied') }}';
                    break;
    
                case 'NotFoundError':
                case 'DevicesNotFoundError':
                    this.webcamError = '{{ __('filament-take-picture-field::take-picture-field.error_no_camera_found') }}';
                    break;
    
                case 'NotReadableError':
                case 'TrackStartError':
                    this.webcamError = '{{ __('filament-take-picture-field::take-picture-field.error_camera_in_use') }}';
                    break;
    
                case 'OverconstrainedError':
                    this.webcamError = '{{ __('filament-take-picture-field::take-picture-field.error_overconstrained') }}';
                    break;
    
                case 'SecurityError':
                    this.webcamError = '{{ __('filament-take-picture-field::take-picture-field.error_security') }}';
                    break;
    
                case 'AbortError':
                    this.webcamError = '{{ __('filament-take-picture-field::take-picture-field.error_abort') }}';
                    break;
    
                default:
                    this.webcamError = '{{ __('filament-take-picture-field::take-picture-field.error_unknown') }}';
            }
        },
    
        async changeCamera(cameraId) {
            this.selectedCameraId = cameraId;
    
            const camera = this.availableCameras.find(c => c.deviceId === cameraId);
            this.detectCameraType(camera);
    
            if (this.webcamActive) {
                this.stopCamera();
                await this.$nextTick();
                this.startCamera();
            }
        },
    
        captureToDataUrl() {
            const video = this.$refs.video;
            if (!video || !video.videoWidth || !video.videoHeight) return null;
    
            const canvas = document.createElement('canvas');
    
            let targetWidth = video.videoWidth;
            let targetHeight = video.videoHeight;
    
            if (this.maxWidth || this.maxHeight) {
                const aspectRatio = video.videoWidth / video.videoHeight;
    
                if (this.maxWidth && this.maxHeight) {
                    if (targetWidth > this.maxWidth) {
                        targetWidth = this.maxWidth;
                        targetHeight = Math.round(targetWidth / aspectRatio);
                    }
                    if (targetHeight > this.maxHeight) {
                        targetHeight = this.maxHeight;
                        targetWidth = Math.round(targetHeight * aspectRatio);
                    }
                } else if (this.maxWidth && targetWidth > this.maxWidth) {
                    targetWidth = this.maxWidth;
                    targetHeight = Math.round(targetWidth / aspectRatio);
                } else if (this.maxHeight && targetHeight > this.maxHeight) {
                    targetHeight = this.maxHeight;
                    targetWidth = Math.round(targetHeight * aspectRatio);
                }
            }
    
            canvas.width = targetWidth;
            canvas.height = targetHeight;
    
            const context = canvas.getContext('2d');
    
            if (this.mirroredView) {
                context.translate(canvas.width, 0);
                context.scale(-1, 1);
            }
    
            context.drawImage(video, 0, 0, targetWidth, targetHeight);
    
            const quality = this.imageQuality / 100;
            return canvas.toDataURL('image/jpeg', quality);
        },
    
        capturePhoto() {
            if (this.isMultiple) return this.captureMultiplePhoto();
    
            const imageData = this.captureToDataUrl();
            if (!imageData) return;
    
            this.photoData = imageData;
            this.stopCamera();
            this.showingPreview = true;
        },
    
        async captureMultiplePhoto() {
            const imageData = this.captureToDataUrl();
            if (!imageData) return;
    
            if (this.retakeSingle && this.retakeKey) {
                const idx = (this.shotSequence || []).findIndex(s => s?.key === this.retakeKey);
                if (idx !== -1) this.currentShotIndex = idx;
            }
    
            const shotKey = (this.retakeSingle && this.retakeKey) ?
                this.retakeKey :
                this.currentShot?.key;

            if (!shotKey) return;
    
            this.capturedShots = {
                ...this.capturedShots,
                [shotKey]: imageData
            };    
            this.syncPhotoDataFromShots();
    
            if (this.retakeSingle) {
                this.retakeSingle = false;
                this.retakeKey = null;
                this.syncPhotoDataFromShots();

                await this.$nextTick();
                this.showingPreview = true;
                this.stopCamera();
                return;
            }
    
            const nextIndex = (this.shotSequence || []).findIndex((s, idx) =>
                idx > this.currentShotIndex && s?.key && !this.capturedShots?.[s.key]
            );
    
            if (nextIndex !== -1) {
                this.currentShotIndex = nextIndex;

                const nextShot = this.shotSequence[nextIndex];
                if (nextShot?.instruction || nextShot?.label) {
                    this.speak(
                        nextShot.instruction ||
                        '{{ __('filament-take-picture-field::take-picture-field.take_picture_of', ['label' => '__LABEL__']) }}'
                            .replace('__LABEL__', nextShot.label)
                    );
                }

                return;
            }
            await this.$nextTick();
            this.showingPreview = true;
            this.stopCamera();
        },
    
        retakeShot(shotKey) {
            const index = (this.shotSequence || []).findIndex(s => s.key === shotKey);
            if (index === -1) return;
    
            if ({{ $getUseModal() ? 'true' : 'false' }} && !this.modalOpen) {
                this.openModal();
            }
    
            this.retakeSingle = true;
            this.retakeKey = shotKey;
            this.currentShotIndex = index;
    
            this.capturedShots = {
                ...this.capturedShots,
                [shotKey]: null
            };
            this.syncPhotoDataFromShots();
    
            this.showingPreview = false;
            this.startCamera();
        },
    
        confirmPhoto() {
            if (this.isMultiple) return this.confirmAllPhotos();
    
            this.showingPreview = false;
            this.closeModal();
        },
    
        confirmAllPhotos() {
            window.speechSynthesis.cancel();
            this.photoData = { ...this.capturedShots };
            this.showingPreview = false;
            this.closeModal();
        },
    
        retakePhoto() {
            if (this.isMultiple) {
                const key = this.currentShot?.key;
                if (key) return this.retakeShot(key);
                return;
            }
    
            this.photoSelected = false;
            this.startCamera();
        },
    
        stopCamera() {
            this.webcamActive = false;
            if (this.cameraStream) {
                this.cameraStream.getTracks().forEach(track => track.stop());
                this.cameraStream = null;
            }
        },
    
        toggleMirror() {
            this.mirroredView = !this.mirroredView;
        },
    
        async flipCamera() {
            if (this.availableCameras.length < 2) return;
    
            const currentIndex = this.availableCameras.findIndex(
                cam => cam.deviceId === this.selectedCameraId
            );
    
            const nextIndex = (currentIndex + 1) % this.availableCameras.length;
            const nextCamera = this.availableCameras[nextIndex];
            this.selectedCameraId = nextCamera.deviceId;
    
            this.detectCameraType(nextCamera);
    
            const constraints = {
                video: {
                    deviceId: { exact: nextCamera.deviceId },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            };
    
            if (this.cameraStream) {
                this.cameraStream.getTracks().forEach(track => track.stop());
                this.cameraStream = null;
            }
    
            try {
                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                this.cameraStream = stream;
    
                if (this.$refs.video) {
                    this.$refs.video.srcObject = stream;
                }
    
                const videoTrack = stream.getVideoTracks()[0];
                if (videoTrack) {
                    const settings = videoTrack.getSettings();
                    if (settings.facingMode) {
                        this.isBackCamera = settings.facingMode === 'environment';
                        this.mirroredView = !this.isBackCamera;
                    }
                }
            } catch (error) {
                console.error('Error flipping camera:', error);
                this.handleWebcamError(error);
                this.selectedCameraId = this.availableCameras[currentIndex].deviceId;
                this.detectCameraType(this.availableCameras[currentIndex]);
            }
        },
    
        async retakeInModal() {
            this.showingPreview = false;
            if (this.isMultiple) {
                this.photoData = null;
                this.capturedShots = {};
                (this.shotSequence || []).forEach(shot => {
                    if (shot?.key) this.capturedShots[shot.key] = null;
                });
                this.currentShotIndex = 0;
            }
    
            await this.$nextTick();
            this.startCamera();
        },
    
        handleSinglePreviewClick() {
            if (!this.photoData) return;
            if (this.isMultiple && typeof this.photoData === 'object') return;
            this.handleMultipleModePreviewClick(this.photoData);
        },
    
        handleMultipleModePreviewClick(p) {
            if (!p) return;
            if (typeof p === 'string' && p.startsWith('data:image/')) {
                const byteString = atob(p.split(',')[1]);
                const mimeString = p.split(',')[0].split(':')[1].split(';')[0];
    
                const ab = new ArrayBuffer(byteString.length);
                const ia = new Uint8Array(ab);
                for (let i = 0; i < byteString.length; i++) ia[i] = byteString.charCodeAt(i);
    
                const blob = new Blob([ab], { type: mimeString });
                const url = URL.createObjectURL(blob);
    
                window.open(url, '_blank')?.focus();
                return;
            }
            window.open(this.getImageUrl(p), '_blank')?.focus();
        },
    
        isBase64Image(value = null) {
            const v = value ?? this.photoData;
            return (typeof v === 'string') && v.startsWith('data:image/');
        },
    
        clearPhoto() {
            if (this.isMultiple) return this.clearAllPhotos();
    
            this.photoData = null;
            this.photoSelected = false;
        },
    
        clearAllPhotos() {
            this.capturedShots = {};
            this.photoData = null;
            this.currentShotIndex = 0;
            this.showingPreview = false;
        },
    
        findFirstMissingIndex() {
            const seq = this.shotSequence || [];
            for (let i = 0; i < seq.length; i++) {
                const key = seq[i]?.key;
                if (key && !this.capturedShots?.[key]) return i;
            }
            return 0;
        },
    
        openModal() {
            this.initMultipleMode();
    
            this.modalSnapshot = this.isMultiple ?
                JSON.parse(JSON.stringify(this.capturedShots || {})) :
                this.photoData;
    
            this.modalOpen = true;
            document.body.classList.add('overflow-hidden');
        },
    
        cancelModal() {
            window.speechSynthesis.cancel();
            if (this.isMultiple) {
                this.capturedShots = this.modalSnapshot ? { ...this.modalSnapshot } : {};
                this.ensureShotKeys();
                this.currentShotIndex = this.findFirstMissingIndex();
                this.showingPreview = false;
                this.syncPhotoDataFromShots();
            } else {
                this.photoData = this.modalSnapshot ?? null;
                this.photoSelected = !!this.photoData;
                this.showingPreview = false;
            }
            this.closeModal();
        },
    
        closeModal() {
            window.speechSynthesis.cancel();
            this.modalOpen = false;
            this.showingPreview = false;
            document.body.classList.remove('overflow-hidden');
            this.stopCamera();
            this.modalSnapshot = null;
        },
    
        checkVisibilityAndAutoStart() {
            if (this.initialized || this.isDisabled || this.photoData) return;
    
            const el = this.$el;
            if (!el) return;
    
            const rect = el.getBoundingClientRect();
            const isVisible = rect.width > 0 && rect.height > 0 && window.getComputedStyle(el).display !== 'none';
    
            if (isVisible && this.autoStart) {
                this.initialized = true;
                this.$nextTick(() => {
                    this.startCamera();
                });
            }
        },
    
    }"
        x-init="() => {
            if (window.location.protocol !== 'https:' && /iPhone|iPad|iPod|Android/i.test(navigator.userAgent)) {
                webcamError = '{{ __('filament-take-picture-field::take-picture-field.error_https_required_mobile') }}';
            }
        
            if (!photoData) {
                if ({{ $getUseModal() ? 'false' : 'true' }}) {
                    startCamera();
                } else if (autoStart) {
                    const observer = new MutationObserver(() => {
                        checkVisibilityAndAutoStart();
                    });
        
                    observer.observe(document.body, {
                        childList: true,
                        subtree: true,
                        attributes: true,
                        attributeFilter: ['class', 'style']
                    });
        
                    checkVisibilityAndAutoStart();
                    setTimeout(() => checkVisibilityAndAutoStart(), 100);
                    setTimeout(() => checkVisibilityAndAutoStart(), 500);
                }
            } else if (!isBase64Image()) {
                photoSelected = true;
            }
        
            if ({{ $getShowCameraSelector() ? 'true' : 'false' }}) {
                getCameras();
            }
        
            if (isMultiple) {
                initMultipleMode();
            }
        }" @keydown.escape.window="if (modalOpen) { closeModal(); } else { stopCamera(); }"
        class="w-full">


        {{--! MARK: Main Container --}}
        <div class="w-full">

            {{-- MARK: Empty State (Single) --}}
            <template x-if="!photoData && !isMultiple">
                <button type="button" @click="!isDisabled && startCamera()" :disabled="isDisabled"
                    class="
                        relative w-full rounded-lg border-2 border-dashed
                        bg-white shadow-sm
                        border-gray-300 hover:border-primary-500 hover:bg-gray-50
                        dark:bg-white/5 dark:border-white/10 dark:hover:border-primary-400 dark:hover:bg-white/10
                        ring-1 ring-transparent dark:ring-white/10
                        transition-all duration-200
                        focus:outline-none focus:ring-2 focus:ring-primary-600/30 dark:focus:ring-primary-400/30"
                    
                    :class="{
                        'cursor-not-allowed opacity-50': isDisabled,
                        'cursor-pointer': !isDisabled,
                    }">

                    <div class="flex flex-col items-center justify-center py-8 px-4">
                        <div class="mb-3 rounded-full p-3 bg-gray-100 dark:bg-white/10">
                            <x-filament::icon icon="heroicon-o-camera" class="h-6 w-6 text-gray-500 dark:text-gray-200" />
                        </div>

                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ __('filament-take-picture-field::take-picture-field.click_to_capture') }}
                        </p>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                            {{ __('filament-take-picture-field::take-picture-field.opens_camera_hint') }}
                        </p>
                    </div>
                    
                </button>
            </template>

            {{-- Empty State (Multiple) --}}
            <template x-if="!photoData && isMultiple">
                <button type="button" @click="!isDisabled && startCamera()" :disabled="isDisabled"
                    class="
                        relative w-full rounded-lg border-2 border-dashed
                        bg-white shadow-sm
                        border-gray-300 hover:border-primary-500 hover:bg-gray-50
                        dark:bg-white/5 dark:border-white/10 dark:hover:border-primary-400 dark:hover:bg-white/10
                        ring-1 ring-transparent dark:ring-white/10
                        transition-all duration-200
                        focus:outline-none focus:ring-2 focus:ring-primary-600/30 dark:focus:ring-primary-400/30"
                    
                    :class="{
                        'cursor-not-allowed opacity-50': isDisabled,
                        'cursor-pointer': !isDisabled,
                    }">

                    <div class="flex flex-col items-center justify-center py-8 px-4">
                        <div class="mb-3 rounded-full p-3 bg-gray-100 dark:bg-white/10">
                            <x-filament::icon icon="heroicon-o-camera" class="h-6 w-6 text-gray-500 dark:text-gray-200" />
                        </div>

                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ __('filament-take-picture-field::take-picture-field.capture_device_photos') }}
                        </p>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-300"
                            x-text="'{{ __('filament-take-picture-field::take-picture-field.photos_required', ['count' => '__COUNT__']) }}'.replace('__COUNT__', shotSequence?.length || 0)">
                        </p>
                    </div>
                    
                </button>
            </template>


            {{-- MARK: Photo preview (Single) --}}
            <template x-if="photoData && !isMultiple">
                <div class="relative w-full rounded-lg bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 shadow-sm overflow-hidden"
                    @mouseenter="isHovering = true" @mouseleave="isHovering = false">
                    
                    <div class="flex items-center gap-4 p-3">

                        {{-- Thumbnail --}}
                        <div
                            class="relative flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 ring-1 ring-gray-950/5 dark:ring-white/10">
                            <img :src="photoData ? getImageUrl(photoData) : ''" class="w-full h-full object-cover"
                                alt="Captured photo">
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate">
                                {{ __('filament-take-picture-field::take-picture-field.captured_photo') }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                <span
                                    x-text="isBase64Image() ? '{{ __('filament-take-picture-field::take-picture-field.new_capture') }}' : '{{ __('filament-take-picture-field::take-picture-field.saved_photo') }}'">
                                </span>
                            </p>
                        </div>

                        {{-- Action buttons --}}
                        <div class="flex items-center gap-1">
                            <!-- Preview -->
                            <button type="button" @click.stop="handleSinglePreviewClick()"
                                class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                title="{{ __('filament-take-picture-field::take-picture-field.view_full_size') }}">
                                <x-filament::icon icon="heroicon-m-eye" class="h-5 w-5" />
                            </button>

                            <!-- Retake -->
                            <button x-show="!isDisabled" type="button" @click.stop="startCamera()"
                                class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                title="{{ __('filament-take-picture-field::take-picture-field.retake_photo') }}">
                                <x-filament::icon icon="heroicon-m-camera" class="h-5 w-5" />
                            </button>

                            <!-- Delete -->
                            <button x-show="!isDisabled" type="button" @click.stop="clearPhoto()"
                                class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-500/10 transition-colors"
                                title="{{ __('filament-take-picture-field::take-picture-field.remove_photo') }}">
                                <x-filament::icon icon="heroicon-m-trash" class="h-5 w-5" />
                            </button>
                        </div>

                    </div>
                </div>
            </template>

            {{-- MARK: Photo preview (Multiple) --}}
            <template x-if="photoData && isMultiple && typeof photoData === 'object'">
                <div
                    class="w-full rounded-lg bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 shadow-sm p-3">

                    {{-- Header --}}
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate">
                                {{ __('filament-take-picture-field::take-picture-field.device_photos') }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"
                                x-text="`${completedShotsCount}/${shotSequence?.length || 0} captured`">
                            </p>
                        </div>
                    </div>

                    {{-- MARK: Grid Gallery --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                        <template x-for="shot in shotSequence" :key="shot.key">
                            <div
                                class="group relative aspect-video rounded-lg overflow-hidden
                            bg-gray-100 dark:bg-gray-800
                            ring-1 ring-gray-950/5 dark:ring-white/10">

                                <!-- Image -->
                                <img x-show="photoData?.[shot.key]"
                                    :src="photoData?.[shot.key] ? getImageUrl(photoData[shot.key]) : ''"
                                    class="w-full h-full object-cover cursor-pointer" alt=""
                                    @click.stop="handleMultipleModePreviewClick(photoData?.[shot.key])" />

                                <!-- Placeholder -->
                                <div x-show="!photoData?.[shot.key]" class="flex items-center justify-center h-full">
                                    <x-filament::icon icon="heroicon-o-camera"
                                        class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                                </div>

                                <!-- Label -->
                                <div class="absolute bottom-0 left-0 right-0 bg-black/50 px-1.5 py-1">
                                    <p class="text-[10px] leading-none text-white truncate"
                                        x-text="shot.label || shot.key">
                                    </p>
                                </div>

                                <!-- Tile Top-Right Actions -->
                                <div x-show="!isDisabled"
                                    class="absolute top-1 right-1 flex gap-1 opacity-0 group-hover:opacity-100 transition">
                                    
                                    <!-- Preview (per image) -->
                                    <button type="button" x-show="photoData?.[shot.key]"
                                        @click.stop="handleMultipleModePreviewClick(photoData?.[shot.key])"
                                        class="flex items-center justify-center w-7 h-7 rounded-md bg-white/90 hover:bg-white text-gray-700 shadow"
                                        title="{{ __('filament-take-picture-field::take-picture-field.view_full_size') }}">
                                        <x-filament::icon icon="heroicon-m-eye" class="h-5 w-5" />
                                    </button>

                                    <!-- Retake -->
                                    <button type="button"
                                        @click.stop="
                                            if (photoData?.[shot.key]) {
                                                retakeShot(shot.key);
                                                return;
                                            }

                                            const idx = (shotSequence || []).findIndex(s => s.key === shot.key);
                                            if (idx !== -1) currentShotIndex = idx;

                                            retakeSingle = false;
                                            retakeKey = null;
                                            showingPreview = false;

                                            if ({{ $getUseModal() ? 'true' : 'false' }} && !modalOpen) openModal();
                                            startCamera();
                                        "
                                        class="flex items-center justify-center w-7 h-7 rounded-md bg-white/90 hover:bg-white text-gray-700 shadow"
                                        :title="photoData?.[shot.key]
                                            ? '{{ __('filament-take-picture-field::take-picture-field.retake') }}'
                                            : '{{ __('filament-take-picture-field::take-picture-field.capture') }}'">
                                        <x-filament::icon icon="heroicon-m-camera" class="h-4 w-4" />
                                    </button>

                                    <!-- Delete -->
                                    <button type="button" x-show="photoData?.[shot.key]"
                                        @click.stop="
                                            capturedShots = { ...capturedShots, [shot.key]: null };
                                            ensureShotKeys();
                                            syncPhotoDataFromShots();
                                        "
                                        class="flex items-center justify-center w-7 h-7 rounded-md bg-white/90 hover:bg-white text-danger-600 shadow"
                                        title="{{ __('filament-take-picture-field::take-picture-field.delete') }}">
                                        <x-filament::icon icon="heroicon-m-trash" class="h-4 w-4" />
                                    </button>
                                </div>

                            </div>
                        </template>
                    </div>

                    {{-- Footer Grid Gallery --}}
                    <div x-show="!isDisabled" class="flex justify-end gap-2 mt-3">
                        <button type="button" @click="clearAllPhotos()"
                            class="px-3 py-2 text-xs rounded-lg border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5">
                            {{ __('filament-take-picture-field::take-picture-field.clear_all') }}
                        </button>
                    </div>

                </div>
            </template>

        </div>

        {{-- MARK: Error Message --}}
        <template x-if="webcamError && !modalOpen">
            <div
                class="mt-2 rounded-lg bg-danger-50 dark:bg-danger-400/10 p-3 text-sm text-danger-600 dark:text-danger-400">
                <div class="flex items-start gap-2">
                    <x-filament::icon icon="heroicon-m-exclamation-circle" class="h-5 w-5 flex-shrink-0" />
                    <span x-text="webcamError"></span>
                </div>
            </div>
        </template>


        {{-- Get state path --}}
        <input type="hidden" {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}">


        {{-- MARK: Modal --}}
        <template x-teleport="body">

            <div x-cloak x-show="modalOpen" @click.self="if (!(isMultiple && showingPreview)) { closeModal() }"
                @keydown.escape.window="if (modalOpen) { cancelModal(); } else { stopCamera(); }"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-950/50 p-4"
                style="display: none;">
                <div @click.stop x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    class="w-full max-w-xl rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10">
                    
                    {{-- Modal header --}}
                    <div class="border-b border-gray-200 dark:border-white/10 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                                <span x-show="!isMultiple">
                                    {{ __('filament-take-picture-field::take-picture-field.take_photo') }}
                                </span>

                                <span
                                    x-show="isMultiple && !showingPreview"
                                    x-text="currentShot?.label || '{{ __('filament-take-picture-field::take-picture-field.take_photo') }}'">
                                </span>

                                <span x-show="isMultiple && showingPreview">{{ __('filament-take-picture-field::take-picture-field.review_photos') }}</span>
                            </h3>
                        </div>

                        {{-- Progress (Multiple) --}}
                        <div x-show="isMultiple && !showingPreview" x-cloak class="mt-3">
                            <div
                                class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                                <span x-text="`Shot ${currentShotIndex + 1} of ${shotSequence.length}`"></span>
                                <span x-text="`${completedShotsCount} captured`"></span>
                            </div>
                            <div class="flex gap-1">
                                <template x-for="(shot, idx) in shotSequence" :key="shot.key">
                                    <div class="h-1.5 flex-1 rounded-full transition-colors duration-200" :class="capturedShots?.[shot.key] ? 'bg-primary-600 dark:bg-primary-400' : (idx === currentShotIndex ? 'bg-primary-500 dark:bg-primary-500' : 'bg-gray-300 dark:bg-white/10')">
                                    </div>
                                </template>
                            </div>
                            <p x-show="currentShot?.instruction" class="mt-2 text-xs text-gray-500 dark:text-gray-400" x-text="currentShot?.instruction"></p>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6">
                        <div class="relative rounded-lg overflow-hidden bg-gray-950 mb-4">

                            <!-- Video preview (camera active) -->
                            <div x-show="webcamActive && !webcamError"
                                class="aspect-video flex items-center justify-center">
                                <video x-ref="video" autoplay playsinline
                                    :style="mirroredView ? 'transform: scaleX(-1);' : ''"
                                    class="max-w-full max-h-[60vh] object-contain"></video>
                            </div>

                            <!-- Preview in modal - (Multiple) -->
                            <div x-show="isMultiple && showingPreview" class="space-y-4 p-3">
                                <p class="text-center font-medium text-gray-900 dark:text-white">{{ __('filament-take-picture-field::take-picture-field.review_your_photos') }}</p>

                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                                    <template x-for="shot in shotSequence" :key="shot.key">
                                        <div class="group relative aspect-video rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 ring-1 ring-gray-950/5 dark:ring-white/10 cursor-pointer"
                                            @click="const key = shot?.key; if (!key) return; const hasShot = !!capturedShots?.[key]; const idx = (shotSequence || []).findIndex(s => s.key === key); if (idx !== -1) currentShotIndex = idx; showingPreview = false; if (hasShot) { retakeSingle = true; retakeKey = key; capturedShots[key] = null; photoData = { ...capturedShots }; } else { retakeSingle = false; retakeKey = null; } startCamera();">

                                            <img x-show="capturedShots?.[shot.key]"
                                                :src="capturedShots?.[shot.key] ? getImageUrl(capturedShots[shot.key]) : ''"
                                                class="w-full h-full object-cover" alt="">

                                            <div x-show="!capturedShots?.[shot.key]"
                                                class="flex items-center justify-center h-full">
                                                <x-filament::icon icon="heroicon-o-camera"
                                                    class="h-5 w-5 text-gray-400" />
                                            </div>

                                            <div
                                                class="absolute inset-0 bg-black/0 group-hover:bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <x-filament::icon icon="heroicon-m-camera"
                                                    class="h-6 w-6 text-white" />
                                            </div>

                                            <div class="absolute bottom-0 left-0 right-0 bg-black/50 px-1.5 py-1">
                                                <p class="text-[10px] leading-none text-white truncate text-center"
                                                    x-text="shot.label || shot.key"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <p x-show="requireAllShots && !allShotsCompleted"
                                    class="text-center text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('filament-take-picture-field::take-picture-field.missing_required_photos') }}
                                </p>
                            </div>

                            <!-- Preview in modal after capture - (Single Mode) -->
                            <template x-if="showingPreview && photoData && !webcamActive && !isMultiple">
                                <div class="aspect-video flex items-center justify-center bg-gray-900">
                                    <img :src="getImageUrl(photoData)" class="max-w-full max-h-[60vh] object-contain"
                                        alt="Captured preview">
                                </div>
                            </template>

                            <!-- Error display -->
                            <div x-show="webcamError"
                                class="aspect-video bg-gray-900 flex flex-col items-center justify-center text-center p-6">
                                <x-filament::icon icon="heroicon-o-exclamation-triangle"
                                    class="h-12 w-12 text-danger-500 mb-4" />
                                <span class="text-white text-lg font-medium" x-text="webcamError"></span>

                                <x-filament::button color="primary" size="sm" class="mt-4"
                                    @click="webcamError = null; startCamera()">
                                    {{ __('filament-take-picture-field::take-picture-field.try_again') }}
                                </x-filament::button>
                            </div>

                            <!-- Capture button overlay -->
                            <div x-show="webcamActive && !webcamError && !showingPreview"
                                class="absolute bottom-4 left-0 right-0 flex justify-center">
                                <button type="button" @click="capturePhoto()"
                                    class="w-16 h-16 rounded-full bg-primary-600 hover:bg-primary-500 border-4 border-white flex items-center justify-center shadow-lg transition-colors"
                                    title="{{ __('filament-take-picture-field::take-picture-field.take_photo') }}">
                                    <x-filament::icon icon="heroicon-s-camera" class="h-8 w-8 text-white" />
                                </button>
                            </div>

                            <!-- Mirror toggle -->
                            <div x-show="webcamActive && !webcamError && !showingPreview"
                                class="absolute top-4 right-4">
                                <button type="button" @click="toggleMirror()"
                                    class="w-10 h-10 rounded-full bg-gray-900/70 text-white flex items-center justify-center hover:bg-gray-900/90 transition-colors"
                                    :class="{ 'ring-2 ring-primary-500': mirroredView }"
                                    :title="mirroredView
                                        ? '{{ __('filament-take-picture-field::take-picture-field.disable_mirror') }}'
                                        : '{{ __('filament-take-picture-field::take-picture-field.enable_mirror') }}'">
                                    <x-filament::icon icon="heroicon-o-arrows-right-left" class="h-5 w-5" />
                                </button>
                            </div>

                            <!-- Flip camera -->
                            <div x-show="webcamActive && !webcamError && isMobile && availableCameras.length > 1 && !showingPreview"
                                class="absolute top-4 left-4">
                                <button type="button" @click="flipCamera()"
                                    class="w-10 h-10 rounded-full bg-gray-900/70 text-white flex items-center justify-center hover:bg-gray-900/90 transition-colors"
                                    title="{{ __('filament-take-picture-field::take-picture-field.switch_camera') }}">
                                    <x-filament::icon icon="heroicon-o-arrow-path" class="h-5 w-5" />
                                </button>
                            </div>

                            <!-- Camera type indicator (front/back) -->
                            <div x-show="webcamActive && !webcamError && !showingPreview"
                                class="absolute bottom-4 left-4">
                                <span class="px-2 py-1 rounded-full bg-gray-900/70 text-white text-xs">
                                    <span
                                        x-text="isBackCamera 
                                            ? '{{ __('filament-take-picture-field::take-picture-field.back_camera') }}' 
                                            : '{{ __('filament-take-picture-field::take-picture-field.front_camera') }}'"></span>
                                </span>
                            </div>
                        </div>

                        {{-- MARK: Camera dropdown selector (desktop only) --}}
                        <div x-show="{{ $getShowCameraSelector() ? 'true' : 'false' }} && !isMobile && !showingPreview && webcamActive"
                            x-cloak class="mb-4">
                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {{ __('filament-take-picture-field::take-picture-field.select_camera') }}
                            </label>

                            <select x-model="selectedCameraId" @change="changeCamera($event.target.value)"
                                :disabled="availableCameras.length <= 1"
                                class="
                                    block w-full rounded-lg border-none
                                    bg-white text-gray-950
                                    py-2 pe-10 ps-3 text-sm
                                    shadow-sm ring-1 ring-inset ring-gray-950/10
                                    focus:ring-2 focus:ring-inset focus:ring-primary-600/40
                                    disabled:opacity-60 disabled:cursor-not-allowed
                                    dark:bg-white/5 dark:text-white dark:ring-white/10
                                    dark:focus:ring-primary-400/40
                                ">
                                <template x-for="(camera, index) in availableCameras" :key="camera.deviceId">
                                    <option :value="camera.deviceId"
                                        class="bg-white text-gray-950 dark:bg-gray-900 dark:text-white"
                                        x-text="camera.label ? camera.label : `Camera ${index + 1}`"></option>
                                </template>
                            </select>

                            <p x-show="availableCameras.length <= 1"
                                class="mt-2 text-xs text-gray-500 dark:text-gray-300">
                                {{ __('filament-take-picture-field::take-picture-field.only_one_camera_detected') }}
                            </p>
                        </div>


                        {{-- MARK: Action buttons --}}
                        <div class="flex justify-end gap-3">
                            <x-filament::button x-show="!showingPreview" color="gray" @click="cancelModal()">
                                {{ __('filament-take-picture-field::take-picture-field.cancel') }}
                            </x-filament::button>

                            <x-filament::button x-show="showingPreview" color="gray" @click="retakeInModal()"
                                x-text="isMultiple 
                                    ? '{{ __('filament-take-picture-field::take-picture-field.retake_all') }}' 
                                    : '{{ __('filament-take-picture-field::take-picture-field.retake') }}'"
                                >
                            </x-filament::button>

                            <x-filament::button x-show="showingPreview && !isMultiple" color="primary"
                                @click="confirmPhoto()">
                                {{ __('filament-take-picture-field::take-picture-field.use_photo') }}
                            </x-filament::button>

                            <x-filament::button x-show="isMultiple && showingPreview" color="primary"
                                @click="confirmAllPhotos()" x-bind:disabled="!allShotsCompleted">
                                {{ __('filament-take-picture-field::take-picture-field.use_all_photos') }}
                            </x-filament::button>
                        </div>

                    </div>

                </div>

            </div>
        </template>

    </div>
</x-dynamic-component>