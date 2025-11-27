/**
 * Main Application Entry Point
 */

let dashboard;

document.addEventListener('DOMContentLoaded', () => {
    // Initialize app
    const loadingScreen = document.getElementById('loading-screen');
    
    setTimeout(() => {
        // Remove 'active' class to trigger fade-out
        loadingScreen.classList.remove('active');
        
        // After animation completes, display correct screen
        setTimeout(() => {
            if (AuthManager.isAuthenticated()) {
                showDashboard();
            } else {
                showAuthScreen();
            }
        }, 500); // Match CSS transition duration
        
    }, 2000); // 2 second delay for "INITIALIZING" effect
    
    // Auth event listeners
    setupAuthListeners();
    
    // Sound toggle
    const soundToggle = document.getElementById('sound-toggle');
    if (soundToggle) {
        soundToggle.addEventListener('click', () => {
            const enabled = soundManager.toggle();
            soundToggle.textContent = enabled ? '🔊' : '🔇';
        });
    }
    
    // Logout
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to logout?')) {
                AuthManager.logout();
            }
        });
    }
});

function showAuthScreen() {
    document.getElementById('auth-screen').classList.add('active');
}

async function showDashboard() {
    document.getElementById('dashboard-screen').classList.add('active');
    dashboard = new Dashboard();
    await dashboard.load();
}

function setupAuthListeners() {
    // Toggle between login/register
    document.getElementById('show-register').addEventListener('click', () => {
        document.getElementById('login-form').classList.remove('active');
        document.getElementById('register-form').classList.add('active');
    });
    
    document.getElementById('show-login').addEventListener('click', () => {
        document.getElementById('register-form').classList.remove('active');
        document.getElementById('login-form').classList.add('active');
    });
    
    // Login
    document.getElementById('login-btn').addEventListener('click', async () => {
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        
        if (!email || !password) {
            showError('Please enter email and password');
            return;
        }
        
        try {
            await AuthManager.handleLogin(email, password);
            window.location.reload();
        } catch (error) {
            showError('Login failed: ' + error.message);
        }
    });
    
    // Register
    document.getElementById('register-btn').addEventListener('click', async () => {
        const formData = {
            username: document.getElementById('reg-username').value,
            email: document.getElementById('reg-email').value,
            password: document.getElementById('reg-password').value,
            quit_date: document.getElementById('reg-quit-date').value,
            cigarettes_per_day: document.getElementById('reg-cigs-per-day').value,
            cost_per_pack: document.getElementById('reg-cost-per-pack').value
        };
        
        // Validation
        if (!formData.username || !formData.email || !formData.password || 
            !formData.quit_date || !formData.cigarettes_per_day) {
            showError('Please fill in all required fields');
            return;
        }
        
        try {
            await AuthManager.handleRegister(formData);
            alert('Registration successful! Please login.');
            document.getElementById('show-login').click();
        } catch (error) {
            showError('Registration failed: ' + error.message);
        }
    });
    
    // Enter key support
    document.getElementById('login-password').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            document.getElementById('login-btn').click();
        }
    });
    
    document.getElementById('reg-cost-per-pack').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            document.getElementById('register-btn').click();
        }
    });
}

function showError(message) {
    const errorDiv = document.getElementById('auth-error');
    errorDiv.textContent = message;
    errorDiv.classList.add('show');
    setTimeout(() => errorDiv.classList.remove('show'), 5000);
}
