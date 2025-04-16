@php
    $isDisabled = $field->isDisabled();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            photoData: $wire.entangle('{{ $getStatePath() }}'),
            photoSelected: false,
            webcamActive: false,
            webcamError: null,
            cameraStream: null,
            availableCameras: [],
            selectedCameraId: null,
            modalOpen: false,
            aspectRatio: '{{ $getAspect() }}',
            imageQuality: {{ $getImageQuality() }},
            mirroredView: true,
            isDisabled: {{ json_encode($isDisabled) }},
            urlPrefix: '{{ $getImageUrlPrefix() }}',
            isMobile: /iPhone|iPad|iPod|Android/i.test(navigator.userAgent),
            currentFacingMode: 'environment',
            
            getImageUrl(path) {
                if (!path) return null;
                if (path.startsWith('data:image/')) return path;

                //prepend the URL prefix if it's a path
                if (!path.startsWith('http://') && !path.startsWith('https://')) {
                    return this.urlPrefix + path;
                }
                return path;
            },

            async getCameras() {
                try {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    this.availableCameras = devices.filter(device => device.kind === 'videoinput');
                    
                    if (this.availableCameras.length > 0 && !this.selectedCameraId) {
                        //default cam
                        this.selectedCameraId = this.availableCameras[0].deviceId;
                    }
                    
                    return this.availableCameras;
                } catch (error) {
                    console.error('Error getting camera devices:', error);
                    this.webcamError = '{{ __('Unable to detect available cameras') }}';
                    return [];
                }
            },
            
            async initWebcam() {
                if (this.isDisabled) return;
                this.webcamActive = true;
                this.webcamError = null;
                
                //aspect ratio
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
                        height: { ideal: aspectHeight * 120 }
                    },
                    audio: false
                };
                
                //camera is selected, use deviceId
                if (this.selectedCameraId) {
                    constraints.video.deviceId = { exact: this.selectedCameraId };
                }
                
                try {
                    const stream = await navigator.mediaDevices.getUserMedia(constraints);
                    this.cameraStream = stream;
                    this.$refs.video.srcObject = stream;
                    
                    if ({{ $getShowCameraSelector() ? 'true' : 'false' }}) {
                        await this.getCameras();
                    }
                    
                    if ({{ $getUseModal() ? 'true' : 'false' }} && !this.modalOpen) {
                        this.openModal();
                    }
                } catch (error) {
                    console.error('Error accessing webcam:', error);
                    this.handleWebcamError(error);
                }
            },
            
            handleWebcamError(error) {

                //mobile error
                if (/iPhone|iPad|iPod|Android/i.test(navigator.userAgent)) {
                    if (window.location.protocol !== 'https:') {
                        this.webcamError = '{{ __('Camera access requires HTTPS on mobile devices') }}';
                        return;
                    }
                }

                switch (error.name) {
                    case 'NotAllowedError':
                    case 'PermissionDeniedError':
                        this.webcamError = '{{ __('Permission denied. Please allow camera access') }}';
                        break;
                    case 'NotFoundError':
                    case 'DevicesNotFoundError':
                        this.webcamError = '{{ __('No available or connected camera found') }}';
                        break;
                    case 'NotReadableError':
                    case 'TrackStartError':
                        this.webcamError = '{{ __('The camera is in use by another application or cannot be accessed') }}';
                        break;
                    case 'OverconstrainedError':
                        this.webcamError = '{{ __('Could not meet the requested camera constraints') }}';
                        break;
                    case 'SecurityError':
                        this.webcamError = '{{ __('Access blocked for security reasons. Use HTTPS or a trusted browser') }}';
                        break;
                    case 'AbortError':
                        this.webcamError = '{{ __('The camera access operation was canceled') }}';
                        break;
                    default:
                        this.webcamError = '{{ __('An unknown error occurred while trying to open the camera') }}';
                }
            },
            
            async changeCamera(cameraId) {
                this.selectedCameraId = cameraId;
                if (this.webcamActive) {
                    this.stopCamera();
                    await this.$nextTick();
                    this.initWebcam();
                }
            },
            
            capturePhoto() {
                const video = this.$refs.video;
                const canvas = document.createElement('canvas');
                
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const context = canvas.getContext('2d');
                
                if (this.mirroredView) {
                    context.translate(canvas.width, 0);
                    context.scale(-1, 1);
                }
                
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const quality = this.imageQuality / 100;
                this.photoData = canvas.toDataURL('image/jpeg', quality);
                
                //after capture, stop the cam
                this.stopCamera();
                
                if (this.modalOpen) {
                    this.closeModal();
                }
            },
            
            usePhoto() {
                this.photoSelected = true;
            },
            
            retakePhoto() {
                this.photoSelected = false;
                this.initWebcam();
            },
            
            stopCamera() {
                this.webcamActive = false;
                if (this.cameraStream) {
                    this.cameraStream.getTracks().forEach(track => track.stop());
                    this.cameraStream = null;
                }
            },
            
            toggleCamera() {
                if (this.webcamActive) {
                    this.stopCamera();
                } else {
                    this.initWebcam();
                }
            },
            
            toggleMirror() {
                this.mirroredView = !this.mirroredView;
            },

            flipCamera() {
                //if multiple mobile cams, enable
                if (this.availableCameras.length < 2) return;
                
                const currentIndex = this.availableCameras.findIndex(
                    cam => cam.deviceId === this.selectedCameraId
                );
                
                const nextIndex = (currentIndex + 1) % this.availableCameras.length;
                const nextCamera = this.availableCameras[nextIndex];
                this.selectedCameraId = nextCamera.deviceId;
                
                const constraints = {
                    video: {
                        deviceId: { exact: nextCamera.deviceId },
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                };

                if (this.cameraStream) {
                    this.cameraStream.getTracks().forEach(track => {
                        track.stop();
                    });
                    this.cameraStream = null;
                }

                navigator.mediaDevices.getUserMedia(constraints)
                    .then(stream => {
                        this.cameraStream = stream;
                        if (this.$refs.video) {
                            this.$refs.video.srcObject = stream;
                        }
                        this.currentFacingMode = nextCamera.label.toLowerCase().includes('back') 
                            ? 'environment' 
                            : 'user';
                    })
                    .catch(error => {
                        console.error('Error flipping camera:', error);
                        this.handleWebcamError(error);
                        this.selectedCameraId = this.availableCameras[currentIndex].deviceId;
                    });
            },

            handlePreviewClick() {
                if (!this.photoData) return;
                
                //preview click
                if (this.photoData.startsWith('data:image/')) {

                    //for blob
                    const byteString = atob(this.photoData.split(',')[1]);
                    const mimeString = this.photoData.split(',')[0].split(':')[1].split(';')[0];
                    const ab = new ArrayBuffer(byteString.length);
                    const ia = new Uint8Array(ab);
                    
                    for (let i = 0; i < byteString.length; i++) {
                        ia[i] = byteString.charCodeAt(i);
                    }
                    
                    const blob = new Blob([ab], { type: mimeString });
                    const url = URL.createObjectURL(blob);
                    
                    window.open(url, '_blank').focus();
                    return;
                }
                
                //for url
                window.open(this.getImageUrl(this.photoData), '_blank');
            },
            
            isBase64Image() {
                return this.photoData && this.photoData.startsWith('data:image/');
            },
            
            clearPhoto() {
                this.photoData = null;
                this.photoSelected = false;
            },
            
            openModal() {
                this.modalOpen = true;
                document.body.classList.add('overflow-hidden');
            },
            
            closeModal() {
                this.modalOpen = false;
                document.body.classList.remove('overflow-hidden');
                this.stopCamera();
            }
        }"
        x-init="() => { 

            if (window.location.protocol !== 'https:' && /iPhone|iPad|iPod|Android/i.test(navigator.userAgent)) {
                webcamError = '{{ __('Camera access requires HTTPS on mobile devices') }}';
            }

            if (!photoData) { 
                if ({{ $getUseModal() ? 'false' : 'true' }}) {
                    initWebcam(); 
                }
            } else if (!isBase64Image()) { 
                photoSelected = true; 
            }
            
            if ({{ $getShowCameraSelector() ? 'true' : 'false' }}) {
                getCameras();
            }
        }"
        @keydown.escape.window="if (modalOpen) { closeModal(); } else { stopCamera(); }"
        class="flex flex-col space-y-4"
    >
        <!-- preview thumbnail -->
        <div class="flex items-center space-x-4">
            
            <div class="relative w-20 h-20">

                <!-- photo-preview available -->
                <template x-if="photoData">
                    <div class="relative w-20 h-20 rounded-lg overflow-hidden bg-gray-100 shadow-sm border border-gray-300"
                        :class="{'cursor-default': {{ json_encode($isDisabled) }}, 'cursor-pointer hover:shadow-md': !{{ json_encode($isDisabled) }}}"
                    >
                        <!-- get url -->
                        <img :src="photoData ? getImageUrl(photoData) : ''" class="w-full h-full object-cover">
                        
                        <!-- edit Button (visible only when not disabled) -->
                        <div class="absolute bottom-0 right-0 p-1 bg-gray-800 bg-opacity-70 rounded-tl" 
                            x-show="!{{ json_encode($isDisabled) }}"
                            @click.stop="initWebcam()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                        </div>
                        
                        <!-- preview button -->
                        <div class="absolute top-0 left-0 p-1 bg-gray-800 bg-opacity-70 rounded-br">
                            <button 
                                type="button"
                                @click.stop="handlePreviewClick()"
                                class="block"
                                x-show="photoData"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>

                    </div>
                </template>
                
                <!-- take photo button -->
                <template x-if="!photoData">
                    <button 
                        type="button"
                        @click="!{{ json_encode($isDisabled) }} && initWebcam()"
                        class="w-24 h-24 rounded-lg border border-dashed border-gray-400 flex flex-col items-center justify-center bg-gray-50 hover:bg-gray-100 cursor-pointer transition-colors"
                        :class="{'cursor-default pointer-events-none opacity-70': {{ json_encode($isDisabled) }}, 'cursor-pointer': !{{ json_encode($isDisabled) }}}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="mt-1 text-xs text-gray-600">{{ __('Take Photo') }}</span>
                    </button>
                </template>
                
                <!-- clear button -->
                <template x-if="photoData && !{{ json_encode($isDisabled) }}">
                    <button 
                        type="button" 
                        @click.stop="clearPhoto()" 
                        class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full shadow-sm hover:bg-red-600 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </template>
                
            </div>
            
            <!-- help text -->
            @if (!$isDisabled)
                <div class="text-sm ml-4">
                    <p class="text-gray-700 font-medium mb-1">{{ $getLabel() }}</p>
                    <p class="text-gray-500 text-xs">{{ __('Click to capture a new photo') }}</p>
                </div>
            @endif
        </div>
        
        <!-- display error message, when accessing the camera -->
        <template x-if="webcamError && !modalOpen">
            <div class="text-red-500 bg-red-50 py-2 px-3 rounded text-sm">
                <span x-text="webcamError"></span>
            </div>
        </template>

        <!-- field to store the captured picture -->
        <input type="hidden" {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}">
        
        <!-- MODAL -->
        <template x-teleport="body">
            <div 
                x-show="modalOpen" 
                @click.self="closeModal()"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 bg-black bg-opacity-75 flex items-center justify-center p-4"
                style="display: none;"
            >
                <div 
                    @click.stop
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg overflow-hidden"
                >
                    <!-- MODAL HEADER -->
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Take Photo') }}
                        </h3>
                        <button 
                            type="button" 
                            @click="closeModal()"
                            class="text-gray-400 hover:text-gray-500"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- MODAL BODY -->
                    <div class="p-4">
                        <!-- CAMERA VIEW  -->
                        <div class="relative bg-black rounded-lg overflow-hidden mb-4">
                            <!-- PRVIEW -->
                            <template x-if="webcamActive && !webcamError">
                                <div class="aspect-video flex items-center justify-center">
                                    <video 
                                        x-ref="video" 
                                        autoplay 
                                        playsinline
                                        :style="mirroredView ? 'transform: scaleX(-1);' : ''"
                                        class="max-w-full max-h-[60vh] object-contain"
                                    ></video>
                                </div>
                            </template>
                            
                            <!-- ERROR -->
                            <template x-if="webcamError">
                                <div class="aspect-video bg-gray-800 flex flex-col items-center justify-center text-center p-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span class="text-white text-lg font-medium" x-text="webcamError"></span>

                                    <button
                                        type="button"
                                        @click="webcamError = null; initWebcam()"
                                        class="mt-4 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700"
                                    >
                                        {{ __('Try Again') }}
                                    </button>
                                    
                                </div>
                            </template>
                            
                            <!-- TAKE PHOTO BUTTON -->
                            <template x-if="webcamActive && !webcamError">
                                <div class="absolute bottom-4 left-0 right-0 flex justify-center">
                                    <button
                                        type="button"
                                        @click="capturePhoto()"
                                        class="w-16 h-16 rounded-full bg-primary-600 hover:bg-primary-700 border-4 border-white flex items-center justify-center shadow-lg"
                                        title="{{ __('Take Photo') }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            
                            <!-- MIRROR -->
                            <template x-if="webcamActive && !webcamError">
                                <div class="absolute top-4 right-4">
                                    <button
                                        type="button"
                                        @click="toggleMirror()"
                                        class="w-10 h-10 rounded-full bg-black bg-opacity-50 text-white flex items-center justify-center"
                                        :title="mirroredView ? disableMirrorText : enableMirrorText"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                    </button>
                                </div>
                            </template>

                            <!-- FLIP on MOBILE ONLY -->
                            <template x-if="webcamActive && !webcamError && isMobile">
                                <div class="absolute top-4 left-4">
                                    <button
                                        type="button"
                                        @click="flipCamera()"
                                        class="w-10 h-10 rounded-full bg-black bg-opacity-50 text-white flex items-center justify-center"
                                        title="{{ __('Flip Camera') }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                                        </svg>
                                    </button>
                                </div>
                            </template>


                        </div>
                        
                        <!-- CAMERA SELECTOR DROPDOWN -->
                        <template x-if="{{ $getShowCameraSelector() ? 'true' : 'false' }} && availableCameras.length > 1">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ __('Select Camera') }}
                                </label>
                                <select
                                    x-model="selectedCameraId"
                                    @change="changeCamera($event.target.value)"
                                    class="block w-full bg-white text-gray-700 border-gray-300 rounded-md shadow-sm
                                        dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700
                                        focus:border-primary-500 focus:ring-primary-500"
                                >
                                    <template x-for="(camera, index) in availableCameras" :key="camera.deviceId">
                                        <option 
                                            :value="camera.deviceId" 
                                            class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-300"
                                            x-text="`Camera ${index + 1} (${camera.label || 'Unnamed Camera'})`"
                                        ></option>
                                    </template>
                                </select>
                            </div>
                        </template>
                        
                        <!-- ACTION BUTTONS -->
                        <div class="flex justify-end space-x-3">
                            <button
                                type="button"
                                @click="closeModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600"
                            >
                                {{ __('Cancel') }}
                            </button>
                            
                            <!-- TAKE PHOTO -->
                            <button
                                type="button"
                                @click="capturePhoto()"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                            >
                                {{ __('Take Photo') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-dynamic-component>