/**
 * Authentication State Management
 */

class AuthManager {
    static isAuthenticated() {
        return !!localStorage.getItem('auth_token');
    }
    
    static saveToken(token) {
        localStorage.setItem('auth_token', token);
    }
    
    static logout() {
        localStorage.removeItem('auth_token');
        window.location.reload();
    }
    
    static async handleLogin(email, password) {
        try {
            const response = await API.login(email, password);
            this.saveToken(response.token);
            return true;
        } catch (error) {
            throw error;
        }
    }
    
    static async handleRegister(formData) {
        try {
            await API.register(formData);
            return true;
        } catch (error) {
            throw error;
        }
    }
}
