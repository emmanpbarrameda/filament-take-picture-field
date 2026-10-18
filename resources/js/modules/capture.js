// Single-photo capture, preview and image helpers.
export const captureMethods = {
    isBase64Image(value = null) {
        const v = value ?? this.photoData
        return typeof v === 'string' && v.startsWith('data:image/')
    },

    getImageUrl(path) {
        if (!path || typeof path !== 'string') return null

        if (path.startsWith('data:image/')) return path
        if (path.startsWith('http://') || path.startsWith('https://')) return path

        return '/storage/' + path.replace(/^\/+/, '')
    },

    // Draw the current video frame to a canvas, applying max dimensions and
    // mirroring, and return it as a JPEG data URL.
    captureToDataUrl() {
        const video = this.$refs.video
        if (!video || !video.videoWidth || !video.videoHeight) return null

        let targetWidth = video.videoWidth
        let targetHeight = video.videoHeight

        if (this.maxWidth || this.maxHeight) {
            const ratio = video.videoWidth / video.videoHeight

            if (this.maxWidth && this.maxHeight) {
                if (targetWidth > this.maxWidth) {
                    targetWidth = this.maxWidth
                    targetHeight = Math.round(targetWidth / ratio)
                }
                if (targetHeight > this.maxHeight) {
                    targetHeight = this.maxHeight
                    targetWidth = Math.round(targetHeight * ratio)
                }
            } else if (this.maxWidth && targetWidth > this.maxWidth) {
                targetWidth = this.maxWidth
                targetHeight = Math.round(targetWidth / ratio)
            } else if (this.maxHeight && targetHeight > this.maxHeight) {
                targetHeight = this.maxHeight
                targetWidth = Math.round(targetHeight * ratio)
            }
        }

        const canvas = document.createElement('canvas')
        canvas.width = targetWidth
        canvas.height = targetHeight

        const context = canvas.getContext('2d')

        if (this.mirroredView) {
            context.translate(canvas.width, 0)
            context.scale(-1, 1)
        }

        context.drawImage(video, 0, 0, targetWidth, targetHeight)

        return canvas.toDataURL('image/jpeg', this.imageQuality / 100)
    },

    capturePhoto() {
        if (this.isMultiple) return this.captureMultiplePhoto()

        const imageData = this.captureToDataUrl()
        if (!imageData) return

        this.photoData = imageData
        this.stopCamera()
        this.showingPreview = true
    },

    confirmPhoto() {
        if (this.isMultiple) return this.confirmAllPhotos()

        this.showingPreview = false
        this.closeModal()
    },

    retakePhoto() {
        if (this.isMultiple) {
            const key = this.currentShot?.key
            if (key) return this.retakeShot(key)
            return
        }

        this.photoSelected = false
        this.startCamera()
    },

    async retakeInModal() {
        this.showingPreview = false

        if (this.isMultiple) {
            this.photoData = null
            this.capturedShots = {}
            this.ensureShotKeys()
            this.currentShotIndex = 0
        }

        await this.$nextTick()
        this.startCamera()
    },

    clearPhoto() {
        if (this.isMultiple) return this.clearAllPhotos()

        this.photoData = null
        this.photoSelected = false
    },

    handleSinglePreviewClick() {
        if (!this.photoData) return
        if (this.isMultiple && typeof this.photoData === 'object') return

        this.openImage(this.photoData)
    },

    // Open a captured (base64) or stored photo in a new tab.
    openImage(value) {
        if (!value) return

        if (this.isBase64Image(value)) {
            const [header, data] = value.split(',')
            const mime = header.split(':')[1].split(';')[0]
            const bytes = atob(data)
            const buffer = new Uint8Array(bytes.length)

            for (let i = 0; i < bytes.length; i++) buffer[i] = bytes.charCodeAt(i)

            const url = URL.createObjectURL(new Blob([buffer], { type: mime }))
            window.open(url, '_blank')?.focus()
            setTimeout(() => URL.revokeObjectURL(url), 60_000)
            return
        }

        window.open(this.getImageUrl(value), '_blank')?.focus()
    },
}
