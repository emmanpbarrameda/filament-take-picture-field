// Multiple-shot mode: a sequence of required photos, each with a key.
export const multiShotMethods = {
    initMultipleMode() {
        if (!this.isMultiple) return

        if (this.photoData && typeof this.photoData === 'object' && !Array.isArray(this.photoData)) {
            this.capturedShots = { ...this.photoData }
        }

        this.ensureShotKeys()
        this.syncPhotoDataFromShots()
    },

    ensureShotKeys() {
        this.shotSequence.forEach((shot) => {
            if (shot?.key && typeof this.capturedShots[shot.key] === 'undefined') {
                this.capturedShots[shot.key] = null
            }
        })
    },

    syncPhotoDataFromShots() {
        if (!this.isMultiple) return

        this.photoData = this.hasAnyCapturedShot ? { ...this.capturedShots } : null
    },

    findShotIndex(key) {
        return this.shotSequence.findIndex((s) => s?.key === key)
    },

    findFirstMissingIndex() {
        const index = this.shotSequence.findIndex((s) => s?.key && !this.capturedShots?.[s.key])
        return index === -1 ? 0 : index
    },

    setShot(key, value) {
        this.capturedShots = { ...this.capturedShots, [key]: value }
    },

    async captureMultiplePhoto() {
        const imageData = this.captureToDataUrl()
        if (!imageData) return

        if (this.retakeSingle && this.retakeKey) {
            const idx = this.findShotIndex(this.retakeKey)
            if (idx !== -1) this.currentShotIndex = idx
        }

        const shotKey = this.retakeSingle && this.retakeKey ? this.retakeKey : this.currentShot?.key
        if (!shotKey) return

        this.setShot(shotKey, imageData)
        this.syncPhotoDataFromShots()

        // Retaking one photo: go straight back to the review grid.
        if (this.retakeSingle) {
            this.retakeSingle = false
            this.retakeKey = null

            await this.$nextTick()
            this.showingPreview = true
            this.stopCamera()
            return
        }

        // Otherwise move on to the next photo that is still missing.
        const nextIndex = this.shotSequence.findIndex(
            (s, idx) => idx > this.currentShotIndex && s?.key && !this.capturedShots?.[s.key],
        )

        if (nextIndex !== -1) {
            this.currentShotIndex = nextIndex
            this.announceShot(this.shotSequence[nextIndex])
            return
        }

        await this.$nextTick()
        this.showingPreview = true
        this.stopCamera()
    },

    retakeShot(shotKey) {
        const index = this.findShotIndex(shotKey)
        if (index === -1) return

        if (!this.modalOpen) this.openModal()

        this.retakeSingle = true
        this.retakeKey = shotKey
        this.currentShotIndex = index

        this.setShot(shotKey, null)
        this.syncPhotoDataFromShots()

        this.showingPreview = false
        this.startCamera()
    },

    // Camera button on a tile in the saved gallery.
    captureShotFromGallery(shotKey) {
        if (this.photoData?.[shotKey]) return this.retakeShot(shotKey)

        const idx = this.findShotIndex(shotKey)
        if (idx !== -1) this.currentShotIndex = idx

        this.retakeSingle = false
        this.retakeKey = null
        this.showingPreview = false

        if (!this.modalOpen) this.openModal()
        this.startCamera()
    },

    // Clicking a tile in the review grid inside the capture panel.
    selectShotFromReview(shotKey) {
        if (!shotKey) return

        const hasShot = !!this.capturedShots?.[shotKey]
        const idx = this.findShotIndex(shotKey)
        if (idx !== -1) this.currentShotIndex = idx

        this.showingPreview = false

        if (hasShot) {
            this.retakeSingle = true
            this.retakeKey = shotKey
            this.setShot(shotKey, null)
            this.photoData = { ...this.capturedShots }
        } else {
            this.retakeSingle = false
            this.retakeKey = null
        }

        this.startCamera()
    },

    removeShot(shotKey) {
        this.setShot(shotKey, null)
        this.ensureShotKeys()
        this.syncPhotoDataFromShots()
    },

    confirmAllPhotos() {
        this.cancelSpeech()
        this.photoData = { ...this.capturedShots }
        this.showingPreview = false
        this.closeModal()
    },

    clearAllPhotos() {
        this.capturedShots = {}
        this.photoData = null
        this.currentShotIndex = 0
        this.showingPreview = false
    },
}
