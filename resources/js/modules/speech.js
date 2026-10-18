// Text-to-speech instructions for multi-shot capture.

const hasSpeech = () => typeof window !== 'undefined' && 'speechSynthesis' in window

export const speechMethods = {
    speak(text) {
        if (!this.enableTts || !text) return

        if (!hasSpeech()) {
            console.warn('[TakePicture] Text-to-speech not supported')
            return
        }

        window.speechSynthesis.cancel()

        const utterance = new SpeechSynthesisUtterance(text)
        utterance.lang = this.ttsLang
        utterance.rate = 0.9
        utterance.pitch = 1.0

        utterance.onstart = () => {
            this.isSpeaking = true
        }
        utterance.onend = () => {
            this.isSpeaking = false
        }

        window.speechSynthesis.speak(utterance)
    },

    cancelSpeech() {
        if (hasSpeech()) window.speechSynthesis.cancel()
        this.isSpeaking = false
    },

    announceShot(shot) {
        if (!shot || (!shot.instruction && !shot.label)) return

        this.speak(shot.instruction || this.t('take_picture_of', { label: shot.label }))
    },
}
