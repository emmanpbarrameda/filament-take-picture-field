// Alpine component for the TakePicture form field.
import { cameraMethods } from '../modules/camera.js'
import { captureMethods } from '../modules/capture.js'
import { multiShotMethods } from '../modules/multi-shot.js'
import { sessionMethods } from '../modules/session.js'
import { speechMethods } from '../modules/speech.js'

const MOBILE_USER_AGENT = /iPhone|iPad|iPod|Android/i

export default function takePicture({ state, config = {}, i18n = {} }) {
    return {
        // Livewire-entangled field state: a base64 data URL / stored path,
        // or (multiple mode) an object of shot key => data URL / path.
        photoData: state,

        i18n,

        // Field configuration (from TakePicture::getJsConfig()).
        aspectRatio: config.aspectRatio ?? '16:9',
        imageQuality: config.imageQuality ?? 90,
        maxWidth: config.maxWidth ?? null,
        maxHeight: config.maxHeight ?? null,
        autoStart: !!config.autoStart,
        useModal: config.useModal !== false,
        showCameraSelector: config.showCameraSelector !== false,
        isDisabled: !!config.isDisabled,
        mirroredViewOverride: config.mirroredView ?? null,
        isMultiple: !!config.isMultiple,
        shotSequence: Array.isArray(config.shotSequence) ? config.shotSequence : [],
        requireAllShots: config.requireAllShots !== false,
        enableTts: !!config.enableTts,
        ttsLang: config.ttsLang ?? 'en-US',

        // Camera.
        webcamActive: false,
        webcamError: null,
        cameraStream: null,
        availableCameras: [],
        selectedCameraId: null,
        currentFacingMode: 'environment',
        isBackCamera: false,
        mirroredView: config.mirroredView ?? false,
        isMobile: MOBILE_USER_AGENT.test(navigator.userAgent),

        // Capture session.
        modalOpen: false,
        modalSnapshot: null,
        showingPreview: false,
        photoSelected: false,
        isHovering: false,
        initialized: false,
        visibilityObserver: null,

        // Multiple mode.
        currentShotIndex: 0,
        capturedShots: {},
        retakeSingle: false,
        retakeKey: null,

        // Speech.
        isSpeaking: false,

        get currentShot() {
            return this.shotSequence[this.currentShotIndex] || null
        },

        get completedShotsCount() {
            return Object.keys(this.capturedShots).filter((k) => this.capturedShots[k]).length
        },

        get allShotsCompleted() {
            if (!this.requireAllShots) return true
            return this.shotSequence.every((shot) => this.capturedShots?.[shot.key])
        },

        get hasAnyCapturedShot() {
            return Object.values(this.capturedShots || {}).some((v) => !!v)
        },

        init() {
            this.initMultipleMode()
            this.checkVisibilityAndAutoStart()

            if (window.location.protocol !== 'https:' && this.isMobile) {
                this.webcamError = this.t('error_https_required_mobile')
            }

            if (!this.photoData) {
                if (!this.useModal) {
                    this.startCamera()
                } else if (this.autoStart && !this.initialized) {
                    this.startVisibilityObserver()
                    this.checkVisibilityAndAutoStart()
                    setTimeout(() => this.checkVisibilityAndAutoStart(), 100)
                    setTimeout(() => this.checkVisibilityAndAutoStart(), 500)
                }
            } else if (!this.isBase64Image()) {
                this.photoSelected = true
            }

            if (this.showCameraSelector) {
                this.getCameras()
            }
        },

        // Called by Alpine when the field is removed from the page
        // (e.g. navigating away): turn the camera off and clean up.
        destroy() {
            this.stopVisibilityObserver()
            this.cancelSpeech()
            this.stopCamera()

            if (this.modalOpen && this.useModal) {
                document.body.classList.remove('overflow-hidden')
            }
        },

        // Translate a key from TakePicture::getJsTranslations(),
        // filling in {placeholders}.
        t(key, replacements = {}) {
            let text = this.i18n?.[key] ?? key

            for (const [name, value] of Object.entries(replacements)) {
                text = text.split(`{${name}}`).join(value ?? '')
            }

            return text
        },

        ...speechMethods,
        ...cameraMethods,
        ...captureMethods,
        ...multiShotMethods,
        ...sessionMethods,
    }
}
