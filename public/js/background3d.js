/**
 * MATRIX RAIN EFFECT V2.0
 * Pure Canvas 2D - Better Performance than Three.js
 */

class MatrixRain {
    constructor() {
        this.canvas = document.getElementById('bg-canvas');
        if (!this.canvas) {
            console.warn('Canvas not found');
            return;
        }
        
        this.ctx = this.canvas.getContext('2d');
        this.width = window.innerWidth;
        this.height = window.innerHeight;
        
        this.canvas.width = this.width;
        this.canvas.height = this.height;
        
        // Matrix characters
        this.chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$%^&*()_+-=[]{}|;:,.<>?/~`';
        this.charArray = this.chars.split('');
        
        this.fontSize = 14;
        this.columns = Math.floor(this.width / this.fontSize);
        
        // Each column has random Y position
        this.drops = [];
        for (let i = 0; i < this.columns; i++) {
            this.drops[i] = Math.random() * -100;
        }
        
        this.init();
    }
    
    init() {
        window.addEventListener('resize', () => this.handleResize());
        this.animate();
    }
    
    handleResize() {
        this.width = window.innerWidth;
        this.height = window.innerHeight;
        this.canvas.width = this.width;
        this.canvas.height = this.height;
        this.columns = Math.floor(this.width / this.fontSize);
        this.drops = [];
        for (let i = 0; i < this.columns; i++) {
            this.drops[i] = Math.random() * -100;
        }
    }
    
    draw() {
        // Black background with transparency for trail effect
        this.ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
        this.ctx.fillRect(0, 0, this.width, this.height);
        
        this.ctx.font = `${this.fontSize}px monospace`;
        
        for (let i = 0; i < this.drops.length; i++) {
            // Random character
            const char = this.charArray[Math.floor(Math.random() * this.charArray.length)];
            
            // Different shades of green
            const y = this.drops[i] * this.fontSize;
            
            // Brightest (head of drop)
            if (y > 0 && y < this.height) {
                this.ctx.fillStyle = '#0f0'; // Bright green
                this.ctx.fillText(char, i * this.fontSize, y);
                
                // Add glow effect to head
                this.ctx.shadowBlur = 20;
                this.ctx.shadowColor = '#0f0';
                this.ctx.fillText(char, i * this.fontSize, y);
                this.ctx.shadowBlur = 0;
            }
            
            // Dimmer trail
            if (y - this.fontSize > 0 && y - this.fontSize < this.height) {
                this.ctx.fillStyle = '#0a0'; // Medium green
                this.ctx.fillText(
                    this.charArray[Math.floor(Math.random() * this.charArray.length)],
                    i * this.fontSize,
                    y - this.fontSize
                );
            }
            
            // Dimmest trail
            if (y - this.fontSize * 2 > 0 && y - this.fontSize * 2 < this.height) {
                this.ctx.fillStyle = '#050'; // Dark green
                this.ctx.fillText(
                    this.charArray[Math.floor(Math.random() * this.charArray.length)],
                    i * this.fontSize,
                    y - this.fontSize * 2
                );
            }
            
            // Move drop down
            this.drops[i]++;
            
            // Reset drop randomly
            if (y > this.height && Math.random() > 0.975) {
                this.drops[i] = 0;
            }
        }
    }
    
    animate() {
        this.draw();
        requestAnimationFrame(() => this.animate());
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    new MatrixRain();
});
