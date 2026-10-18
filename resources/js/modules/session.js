// The capture session: opening and closing the capture panel (shown as a
// modal, or inline when useModal(false)), cancelling, and auto start.
//
// "modalOpen" means "a capture session is open". In inline mode the panel is
// rendered in place instead of in a modal, but the flow is exactly the same.

export const sessionMethods = {
    openModal() {
        this.initMultipleMode()

        this.modalSnapshot = this.isMultiple
            ? JSON.parse(JSON.stringify(this.capturedShots || {}))
            : this.photoData

        this.modalOpen = true

        if (this.useModal) document.body.classList.add('overflow-hidden')
    },

    // Close and throw away anything captured in this session.
    cancelModal() {
        this.cancelSpeech()

        if (this.isMultiple) {
            this.capturedShots = this.modalSnapshot ? { ...this.modalSnapshot } : {}
            this.ensureShotKeys()
            this.currentShotIndex = this.findFirstMissingIndex()
            this.showingPreview = false
            this.syncPhotoDataFromShots()
        } else {
            this.photoData = this.modalSnapshot ?? null
            this.photoSelected = !!this.photoData
            this.showingPreview = false
        }

        this.closeModal()
    },

    // Close and keep what was captured.
    closeModal() {
        this.cancelSpeech()
        this.modalOpen = false
        this.showingPreview = false

        if (this.useModal) document.body.classList.remove('overflow-hidden')

        this.stopCamera()
        this.modalSnapshot = null
    },

    onEscape() {
        if (this.modalOpen) {
            this.cancelModal()
        } else {
            this.stopCamera()
        }
    },

    onBackdropClick() {
        if (this.isMultiple && this.showingPreview) return

        this.closeModal()
    },

    checkVisibilityAndAutoStart() {
        if (this.initialized || this.isDisabled || this.photoData) return

        const el = this.$el
        if (!el) return

        const rect = el.getBoundingClientRect()
        const isVisible =
            rect.width > 0 && rect.height > 0 && window.getComputedStyle(el).display !== 'none'

        if (isVisible && this.autoStart) {
            this.initialized = true
            this.stopVisibilityObserver()
            this.$nextTick(() => this.startCamera())
        }
    },

    startVisibilityObserver() {
        this.stopVisibilityObserver()

        this.visibilityObserver = new MutationObserver(() => this.checkVisibilityAndAutoStart())
        this.visibilityObserver.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class', 'style'],
        })
    },

    stopVisibilityObserver() {
        this.visibilityObserver?.disconnect()
        this.visibilityObserver = null
    },
}
