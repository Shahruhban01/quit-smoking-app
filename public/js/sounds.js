/**
 * Sound Effects Manager
 * Plays audio feedback for user actions
 */

class SoundManager {
    constructor() {
        this.sounds = {
            badgeUnlock: new Audio('assets/sounds/badge-unlock.mp3'),
            coinEarn: new Audio('assets/sounds/coin-earn.mp3'),
            streakRecord: new Audio('assets/sounds/streak-record.mp3')
        };
        
        this.enabled = localStorage.getItem('sound_enabled') !== 'false';
        
        // Preload sounds
        Object.values(this.sounds).forEach(sound => {
            sound.preload = 'auto';
            sound.volume = 0.5;
        });
    }
    
    play(soundName) {
        if (!this.enabled || !this.sounds[soundName]) return;
        
        const sound = this.sounds[soundName];
        sound.currentTime = 0;
        sound.play().catch(err => console.log('Sound play failed:', err));
    }
    
    toggle() {
        this.enabled = !this.enabled;
        localStorage.setItem('sound_enabled', this.enabled);
        return this.enabled;
    }
}

const soundManager = new SoundManager();
