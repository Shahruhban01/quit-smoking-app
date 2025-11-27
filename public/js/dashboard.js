/**
 * Dashboard UI Logic
 * Handles rendering and interactions on the main dashboard
 */

class Dashboard {
    constructor() {
        this.data = null;
    }
    
    async load() {
        try {
            this.data = await API.getDashboard();
            this.render();
        } catch (error) {
            console.error('Dashboard load error:', error);
            alert('Failed to load dashboard');
        }
    }
    
    render() {
        const user = this.data.user;
        
        // Update header
        document.getElementById('coin-counter').querySelector('.coin-amount').textContent = user.total_coins;
        
        // Update streak card
        document.getElementById('current-streak').textContent = user.current_streak;
        document.getElementById('best-streak').textContent = user.best_streak;
        document.getElementById('total-days').textContent = user.total_days_quit;
        
        // Update money saved
        document.getElementById('money-saved').textContent = user.money_saved.toFixed(2);
        
        // Render check-in UI
        this.renderCheckIn(user.checked_in_today);
        
        // Render next badge progress
        this.renderNextBadge();
        
        // Load badges
        this.loadBadges();
    }
    
    renderCheckIn(checkedIn) {
        const container = document.getElementById('checkin-content');
        
        if (checkedIn) {
            container.innerHTML = `
                <div class="checkin-status">
                    <div class="checkmark">✓</div>
                    <p>You've checked in today!</p>
                    <button id="log-relapse-btn" class="cyber-btn danger">Log Relapse</button>
                </div>
            `;
            
            document.getElementById('log-relapse-btn').addEventListener('click', () => {
                this.showRelapseModal();
            });
        } else {
            container.innerHTML = `
                <p>Did you stay smoke-free today?</p>
                <button id="checkin-yes-btn" class="cyber-btn primary checkin-btn-large">
                    YES - I'm Smoke Free!
                </button>
                <button id="checkin-relapse-btn" class="cyber-btn danger" style="margin-top: 1rem;">
                    No, I smoked
                </button>
            `;
            
            document.getElementById('checkin-yes-btn').addEventListener('click', () => {
                this.handleCheckIn();
            });
            
            document.getElementById('checkin-relapse-btn').addEventListener('click', () => {
                this.showRelapseModal();
            });
        }
    }
    
    renderNextBadge() {
        const container = document.getElementById('next-badge-info');
        const nextBadge = this.data.next_badge;
        
        if (nextBadge) {
            const progress = (this.data.user.current_streak / nextBadge.requirement_value) * 100;
            
            container.innerHTML = `
                <p><strong>${nextBadge.name}</strong></p>
                <p>${nextBadge.requirement_value} day streak</p>
                <p class="coin-reward">+${nextBadge.coin_reward} ⬡</p>
            `;
            
            document.getElementById('badge-progress').style.width = `${Math.min(progress, 100)}%`;
        } else {
            container.innerHTML = `<p>All badges unlocked!</p>`;
            document.getElementById('badge-progress').style.width = '100%';
        }
    }
    
    async handleCheckIn() {
        try {
            const response = await API.checkIn(new Date().toISOString().split('T')[0]);
            
            // Animate coin gain
            this.animateCoinGain(response.coins_earned);
            soundManager.play('coinEarn');
            
            // Show badge unlocks
            if (response.new_badges.length > 0) {
                for (const badge of response.new_badges) {
                    await this.showBadgeUnlock(badge);
                }
            }
            
            // Reload dashboard
            await this.load();
            
        } catch (error) {
            alert('Check-in failed: ' + error.message);
        }
    }
    
    showRelapseModal() {
        const modal = document.getElementById('relapse-modal');
        modal.classList.add('active');
        
        document.getElementById('confirm-relapse').onclick = async () => {
            const cigs = parseInt(document.getElementById('relapse-cigs').value) || 1;
            const note = document.getElementById('relapse-note').value;
            
            try {
                await API.logRelapse(new Date().toISOString().split('T')[0], cigs, note);
                
                // Animate streak shatter
                document.getElementById('current-streak').classList.add('shatter');
                
                modal.classList.remove('active');
                await this.load();
                
            } catch (error) {
                alert('Failed to log relapse: ' + error.message);
            }
        };
        
        document.getElementById('cancel-relapse').onclick = () => {
            modal.classList.remove('active');
        };
    }
    
    async showBadgeUnlock(badge) {
        return new Promise((resolve) => {
            const overlay = document.getElementById('badge-unlock-overlay');
            overlay.querySelector('.badge-icon-large').textContent = '🏆';
            overlay.querySelector('.unlock-title').textContent = badge.name;
            overlay.querySelector('.unlock-description').textContent = badge.description;
            overlay.querySelector('.coin-reward span').textContent = badge.coin_reward;
            
            overlay.classList.add('active');
            soundManager.play('badgeUnlock');
            
            setTimeout(() => {
                overlay.classList.remove('active');
                resolve();
            }, 3000);
        });
    }
    
    animateCoinGain(amount) {
        const coinElement = document.getElementById('coin-counter').querySelector('.coin-amount');
        coinElement.classList.add('gain');
        setTimeout(() => coinElement.classList.remove('gain'), 500);
    }
    
    async loadBadges() {
        try {
            const response = await API.getBadges();
            const container = document.getElementById('badges-container');
            
            container.innerHTML = response.badges.map(badge => `
                <div class="badge-item ${badge.unlocked ? 'unlocked' : 'locked'}">
                    <div class="badge-icon">🏆</div>
                    <div class="badge-name">${badge.name}</div>
                    <div class="badge-requirement">${badge.requirement_value} days</div>
                </div>
            `).join('');
            
            document.getElementById('badge-count').textContent = 
                `${response.badges.filter(b => b.unlocked).length}/${response.badges.length}`;
                
        } catch (error) {
            console.error('Failed to load badges:', error);
        }
    }
}
