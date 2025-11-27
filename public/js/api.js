/**
 * API Communication Layer
 * All backend communication goes through this module
 */

const API_BASE_URL = '../backend/api'; // Adjust to your backend path

class API {
    static async request(endpoint, options = {}) {
        const token = localStorage.getItem('auth_token');
        
        const config = {
            method: options.method || 'GET',
            headers: {
                'Content-Type': 'application/json',
                ...(token && { 'Authorization': `Bearer ${token}` })
            },
            ...options
        };
        
        if (options.body) {
            config.body = JSON.stringify(options.body);
        }
        
        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
            
            // Check if response is empty
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error(`Server returned non-JSON response (${response.status}). Check PHP error logs.`);
            }
            
            const text = await response.text();
            
            // Check if response is empty
            if (!text || text.trim() === '') {
                throw new Error('Server returned empty response. Check PHP error logs.');
            }
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                console.error('JSON Parse Error:', text);
                throw new Error('Invalid JSON from server: ' + text.substring(0, 100));
            }
            
            if (!response.ok) {
                throw new Error(data.error || `Request failed (${response.status})`);
            }
            
            return data;
            
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
    
    // Rest of the methods stay the same...
    static async register(userData) {
        return this.request('/register.php', {
            method: 'POST',
            body: userData
        });
    }
    
    static async login(email, password) {
        return this.request('/login.php', {
            method: 'POST',
            body: { email, password }
        });
    }
    
    // Dashboard
    static async getDashboard() {
        return this.request('/dashboard.php');
    }
    
    // Check-in
    static async checkIn(date, note = null) {
        return this.request('/checkin.php', {
            method: 'POST',
            body: { date, note }
        });
    }
    
    // Relapse
    static async logRelapse(date, cigarettes_smoked, note = null) {
        return this.request('/relapse.php', {
            method: 'POST',
            body: { date, cigarettes_smoked, note }
        });
    }
    
    // Badges
    static async getBadges() {
        return this.request('/badges.php');
    }
    
    // Coins
    static async getCoinHistory(limit = 50) {
        return this.request(`/coins.php?limit=${limit}`);
    }
}
