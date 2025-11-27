/**
 * Terminal Sound Effects V2.0
 */

class SoundManager {
    constructor() {
        // Create audio context for beep sounds
        this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        this.enabled = localStorage.getItem('sound_enabled') !== 'false';
    }
    
    // Terminal beep
    beep(frequency = 800, duration = 100) {
        if (!this.enabled) return;
        
        const oscillator = this.audioContext.createOscillator();
        const gainNode = this.audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(this.audioContext.destination);
        
        oscillator.frequency.value = frequency;
        oscillator.type = 'square';
        
        gainNode.gain.setValueAtTime(0.1, this.audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + duration / 1000);
        
        oscillator.start(this.audioContext.currentTime);
        oscillator.stop(this.audioContext.currentTime + duration / 1000);
    }
    
    // Success sound
    success() {
        this.beep(1000, 80);
        setTimeout(() => this.beep(1200, 80), 100);
        setTimeout(() => this.beep(1500, 150), 200);
    }
    
    // Badge unlock
    badgeUnlock() {
        for (let i = 0; i < 5; i++) {
            setTimeout(() => this.beep(500 + i * 200, 100), i * 80);
        }
    }
    
    // Coin earn
    coinEarn() {
        this.beep(1200, 50);
        setTimeout(() => this.beep(1500, 50), 50);
    }
    
    // Error sound
    error() {
        this.beep(200, 200);
    }
    
    // Click sound
    click() {
        this.beep(600, 30);
    }
    
    toggle() {
        this.enabled = !this.enabled;
        localStorage.setItem('sound_enabled', this.enabled);
        return this.enabled;
    }
    
    // Keyboard typing sound
    keystroke() {
        this.beep(400 + Math.random() * 200, 20);
    }
}

const soundManager = new SoundManager();

// Add click sounds to all buttons
document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', (e) => {
        if (e.target.matches('button, .cyber-btn, .link-text')) {
            soundManager.click();
        }
    });
    
    // Typing sounds for inputs
    document.addEventListener('keydown', (e) => {
        if (e.target.matches('input, textarea')) {
            soundManager.keystroke();
        }
    });
});
