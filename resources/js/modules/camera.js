// Camera stream: start, stop, switch, and the iOS/WebKit-safe way of
// showing the stream in the <video> element.

const MODAL_TRANSITION_MS = 220

export const cameraMethods = {
    // Wait until the <video> element is rendered and visible (especially
    // inside the modal) before a stream is attached to it.
    waitForVisibleVideo() {
        return new Promise((resolve) => {
            this.$nextTick(() => {
                requestAnimationFrame(() =>
                    requestAnimationFrame(() => {
                        setTimeout(resolve, this.useModal ? MODAL_TRANSITION_MS : 0)
                    }),
                )
            })
        })
    },

    // Attach a stream so that iOS/WebKit actually paints it:
    // muted + playsinline, a fresh srcObject, then an explicit play().
    async attachStream(stream) {
        const video = this.$refs.video
        if (!video) return

        video.muted = true
        video.playsInline = true
        video.setAttribute('playsinline', '')
        video.setAttribute('webkit-playsinline', '')

        video.srcObject = null
        video.srcObject = stream

        if (video.readyState < 1) {
            await Promise.race([
                new Promise((r) => video.addEventListener('loadedmetadata', r, { once: true })),
                new Promise((r) => setTimeout(r, 1500)),
            ])
        }

        try {
            await video.play()
        } catch (e) {
            console.warn('[TakePicture] video.play() failed:', e)
        }
    },

    // Follow the configured mirror setting if there is one, otherwise
    // mirror the front camera and not the back camera.
    applyMirrorFromCamera() {
        this.mirroredView = this.mirroredViewOverride ?? !this.isBackCamera
    },

    async getCameras() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices()
            this.availableCameras = devices.filter((device) => device.kind === 'videoinput')

            if (this.availableCameras.length > 0 && !this.selectedCameraId) {
                this.selectedCameraId = this.availableCameras[0].deviceId
                this.detectCameraType(this.availableCameras[0])
            }

            return this.availableCameras
        } catch (error) {
            console.error('[TakePicture] Error getting camera devices:', error)
            this.webcamError = this.t('error_unable_to_detect_cameras')
            return []
        }
    },

    detectCameraType(camera) {
        if (!camera) return

        const label = (camera.label || '').toLowerCase()

        this.isBackCamera =
            label.includes('back') ||
            label.includes('rear') ||
            label.includes('environment') ||
            label.includes('0, facing back')

        this.applyMirrorFromCamera()
    },

    syncCameraTypeFromStream(stream) {
        const settings = stream.getVideoTracks()[0]?.getSettings?.()

        if (settings?.facingMode) {
            this.isBackCamera = settings.facingMode === 'environment'
            this.applyMirrorFromCamera()
        }
    },

    buildConstraints() {
        let aspectWidth = 16
        let aspectHeight = 9

        if (this.aspectRatio) {
            const parts = String(this.aspectRatio).split(':')
            if (parts.length === 2) {
                aspectWidth = parseInt(parts[0], 10)
                aspectHeight = parseInt(parts[1], 10)
            }
        }

        const constraints = {
            video: {
                facingMode: this.isMobile ? this.currentFacingMode : 'user',
                width: { ideal: aspectWidth * 120 },
                height: { ideal: aspectHeight * 120 },
            },
            audio: false,
        }

        if (this.selectedCameraId) {
            constraints.video.deviceId = { exact: this.selectedCameraId }
        }

        return constraints
    },

    async startCamera() {
        if (this.isDisabled || this.webcamActive) return

        this.stopCamera()

        await new Promise((r) => setTimeout(r, 120))

        this.initMultipleMode()

        this.webcamError = null

        // Open the capture panel (modal or inline) before asking for the stream.
        if (!this.modalOpen) {
            this.openModal()
        }

        this.webcamActive = true

        await this.waitForVisibleVideo()

        try {
            const stream = await navigator.mediaDevices.getUserMedia(this.buildConstraints())
            this.cameraStream = stream

            await this.attachStream(stream)

            if (this.showCameraSelector) {
                await this.getCameras()
            }

            this.syncCameraTypeFromStream(stream)

            if (this.isMultiple && this.currentShot) {
                await this.$nextTick()
                this.announceShot(this.currentShot)
            }
        } catch (error) {
            console.error('[TakePicture] Error accessing webcam:', error)

            this.stopCamera()
            this.handleWebcamError(error)
        }
    },

    handleWebcamError(error) {
        if (this.isMobile && window.location.protocol !== 'https:') {
            this.webcamError = this.t('error_https_required_mobile')
            return
        }

        const errorKeys = {
            NotAllowedError: 'error_permission_denied',
            PermissionDeniedError: 'error_permission_denied',
            NotFoundError: 'error_no_camera_found',
            DevicesNotFoundError: 'error_no_camera_found',
            NotReadableError: 'error_camera_in_use',
            TrackStartError: 'error_camera_in_use',
            OverconstrainedError: 'error_overconstrained',
            SecurityError: 'error_security',
            AbortError: 'error_abort',
        }

        this.webcamError = this.t(errorKeys[error?.name] ?? 'error_unknown')
    },

    async changeCamera(cameraId) {
        this.selectedCameraId = cameraId

        const camera = this.availableCameras.find((c) => c.deviceId === cameraId)
        this.detectCameraType(camera)

        if (this.webcamActive) {
            this.stopCamera()
            await this.$nextTick()
            this.startCamera()
        }
    },

    stopTracks() {
        if (this.cameraStream) {
            this.cameraStream.getTracks().forEach((track) => track.stop())
            this.cameraStream = null
        }
    },

    stopCamera() {
        this.webcamActive = false
        this.stopTracks()

        const video = this.$refs.video
        if (video) {
            video.pause()
            video.srcObject = null
        }
    },

    toggleMirror() {
        this.mirroredView = !this.mirroredView
    },

    async flipCamera() {
        if (this.availableCameras.length < 2) return

        const currentIndex = this.availableCameras.findIndex(
            (cam) => cam.deviceId === this.selectedCameraId,
        )
        const nextCamera = this.availableCameras[(currentIndex + 1) % this.availableCameras.length]

        this.selectedCameraId = nextCamera.deviceId
        this.detectCameraType(nextCamera)

        this.stopTracks()

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    deviceId: { exact: nextCamera.deviceId },
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                },
                audio: false,
            })
            this.cameraStream = stream

            await this.attachStream(stream)

            this.syncCameraTypeFromStream(stream)
        } catch (error) {
            console.error('[TakePicture] Error flipping camera:', error)
            this.handleWebcamError(error)

            const previous = this.availableCameras[currentIndex]
            if (previous) {
                this.selectedCameraId = previous.deviceId
                this.detectCameraType(previous)
            }
        }
    },
}
