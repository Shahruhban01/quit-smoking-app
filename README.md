# 🚭 QUIT SMOKING CHALLENGES - Hacker Edition V2.0

<div align="center">

![Version](https://img.shields.io/badge/version-2.0-00ff41)
![License](https://img.shields.io/badge/license-MIT-00ffff)
![Status](https://img.shields.io/badge/status-active-00ff41)

**A cyberpunk-themed web application to help you quit smoking through gamification, challenges, and community support.**

[Demo](#-demo) • [Features](#-features) • [Installation](#-installation) • [Documentation](#-documentation) • [Contributing](#-contributing)

</div>

---

## 🎯 Overview

**Quit Smoking Challenges** is a full-stack web application that transforms your quit-smoking journey into an immersive hacker-themed experience. Track your progress, earn coins, unlock badges, and build streaks in a visually stunning cyberpunk interface complete with Matrix rain effects and terminal-style interactions.

### Why This Project?

- **Gamification Works**: Turn quitting into a game with rewards, streaks, and achievements
- **Visual Motivation**: Cyberpunk aesthetic makes tracking progress exciting
- **Data-Driven**: See money saved, days smoke-free, and health improvements
- **Privacy-Focused**: Self-hosted solution - your data stays with you
- **Open Source**: Free forever, community-driven development

---

## ✨ Features

### 🎮 Gamification System
- **Daily Check-ins**: Log your smoke-free days and earn coins
- **Streak Tracking**: Build consecutive smoke-free day streaks
- **Badge System**: Unlock 9+ achievements (1 day, 7 days, 30 days, etc.)
- **Coin Rewards**: Earn virtual currency for milestones
- **Progress Bars**: Visual feedback for next badge/milestone

### 💀 Hacker-Themed UI
- **Matrix Rain Background**: Animated falling characters effect
- **CRT Monitor Effects**: Scanlines, screen flicker, vignette
- **Glitch Animations**: Text and UI element glitches
- **Terminal Sounds**: Authentic beep sounds for actions
- **Neon Glow Effects**: Cyberpunk color palette (green, cyan, pink)

### 📊 Analytics & Tracking
- **Current Streak**: Days in a row without smoking
- **Best Streak**: Your personal record
- **Total Days Quit**: Lifetime smoke-free days
- **Money Saved**: Calculate savings based on cigarette cost
- **Relapse Logging**: Honest tracking with optional notes

### 🔒 Secure Backend
- **JWT Authentication**: Stateless token-based auth
- **PDO Prepared Statements**: SQL injection prevention
- **Password Hashing**: Bcrypt with PHP's `password_hash()`
- **Input Validation**: Sanitization on all user inputs

---

## 🚀 Quick Start

### Prerequisites

- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 8.0 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Optional**: Composer (for JWT library)

### Installation

1. **Clone the repository**
```
git clone https://github.com/shahruhban01/quit-smoking-app.git
cd quit-smoking-app
```

2. **Configure database**
```
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE quit_smoking_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

3. **Import schema**
```
mysql -u root -p quit_smoking_app < database/schema.sql
```

4. **Configure credentials**
```
# Edit backend/config/db.php
nano backend/config/db.php

# Update these values:
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'quit_smoking_app');
define('DB_USER', 'root');
define('DB_PASS', '');  # Your password
```

5. **Install JWT library (optional but recommended)**
```
cd backend
composer require firebase/php-jwt
```

6. **Set permissions**
```
chmod 755 backend/sessions
chmod 644 backend/config/db.php
```

7. **Access the app**
```
http://localhost/quit-smoking-app/public/
```

---

## 📁 Project Structure

```
quit-smoking-app/
├── public/                 # Frontend files (document root)
│   ├── index.html         # Main SPA
│   ├── css/
│   │   ├── variables.css  # Design tokens
│   │   ├── hacker-theme-v2.css  # Main styles
│   │   └── components.css # Component styles
│   ├── js/
│   │   ├── app.js         # Application entry point
│   │   ├── api.js         # API communication layer
│   │   ├── auth.js        # Authentication manager
│   │   ├── dashboard.js   # Dashboard logic
│   │   ├── background3d-v2.js  # Matrix rain effect
│   │   ├── sounds-v2.js   # Terminal sound effects
│   │   └── utils.js       # Utility functions
│   └── assets/
│       └── sounds/        # Audio files
├── backend/               # PHP backend
│   ├── config/
│   │   └── db.php        # Database connection
│   ├── includes/
│   │   ├── functions.php # Helper functions
│   │   └── jwt_helper.php # JWT authentication
│   ├── api/              # RESTful endpoints
│   │   ├── register.php  # POST /api/register
│   │   ├── login.php     # POST /api/login
│   │   ├── dashboard.php # GET /api/dashboard
│   │   ├── checkin.php   # POST /api/checkin
│   │   ├── relapse.php   # POST /api/relapse
│   │   ├── badges.php    # GET /api/badges
│   │   └── coins.php     # GET /api/coins/history
│   └── sessions/         # Session storage
├── database/
│   ├── schema.sql        # Database structure
│   └── seed.sql          # Sample data
├── docs/                 # Documentation
│   ├── API.md           # API reference
│   ├── SETUP.md         # Detailed setup guide
│   └── CONTRIBUTING.md  # Contribution guidelines
├── LICENSE              # MIT License
└── README.md           # This file
```

---

## 🎨 Screenshots

### Login Screen
```
 ██████╗ ██╗   ██╗██╗████████╗
██╔═══██╗██║   ██║██║╚══██╔══╝
██║   ██║██║   ██║██║   ██║   
██║▄▄ ██║██║   ██║██║   ██║   
╚██████╔╝╚██████╔╝██║   ██║   
 ╚══▀▀═╝  ╚═════╝ ╚═╝   ╚═╝   
SMOKING // V2.0 CYBERPUNK
```

### Dashboard Features
- Real-time streak counter with neon glow
- Animated progress bars with data streams
- Badge grid with unlock animations
- Money saved calculator
- Daily check-in terminal

---

## 🛠️ Technology Stack

### Frontend
- **HTML5**: Semantic structure
- **CSS3**: Custom properties, animations, flexbox, grid
- **Vanilla JavaScript**: No framework dependencies
- **Canvas API**: Matrix rain background effect

### Backend
- **PHP 8+**: Modern PHP with type hints
- **MySQL/MariaDB**: Relational database
- **PDO**: Database abstraction layer
- **JWT**: JSON Web Tokens for authentication

### Development Tools
- **Composer**: PHP dependency management
- **Git**: Version control

---

## 📖 API Documentation

See [API.md](docs/API.md) for complete API reference.

### Authentication
All protected endpoints require a JWT token in the `Authorization` header:
```
Authorization: Bearer <token>
```

### Key Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register.php` | Create new user account |
| POST | `/api/login.php` | Authenticate and get JWT |
| GET | `/api/dashboard.php` | Get user stats and data |
| POST | `/api/checkin.php` | Log smoke-free day |
| POST | `/api/relapse.php` | Log relapse incident |
| GET | `/api/badges.php` | Get all badges (locked/unlocked) |
| GET | `/api/coins.php` | Get coin transaction history |

---

## 🎯 Gamification Logic

### Coin System
- **Daily Check-in**: +10 coins
- **Streak Milestones**:
  - 7 days: +100 coins
  - 30 days: +500 coins
  - 90 days: +1500 coins
  - 365 days: +10000 coins
- **Badge Unlocks**: +10 to +2000 coins depending on badge
- **Relapse Penalty**: -50 coins

### Badge Progression
1. **First Victory** (1 day) - +10 coins
2. **Bronze Warrior** (3 days) - +30 coins
3. **Silver Shield** (7 days) - +100 coins
4. **Gold Guardian** (14 days) - +200 coins
5. **Platinum Champion** (30 days) - +500 coins
6. **Diamond Defender** (60 days) - +1000 coins
7. **Master of Freedom** (90 days) - +2000 coins
8. **Legend Status** (180 days) - +5000 coins
9. **Ultimate Victory** (365 days) - +10000 coins

---

## 🔧 Configuration

### Database Settings
Edit `backend/config/db.php`:
```
define('DB_HOST', '127.0.0.1');  // Use IP instead of localhost
define('DB_NAME', 'quit_smoking_app');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password
```

### JWT Secret
Edit `backend/includes/jwt_helper.php`:
```
define('JWT_SECRET', 'your-secure-random-secret-here');
define('JWT_EXPIRATION', 86400 * 7); // 7 days
```

### Coin Rewards
Edit `backend/includes/functions.php`:
```
define('COINS_DAILY_CHECKIN', 10);
define('COINS_RELAPSE_PENALTY', -50);

$STREAK_MILESTONES = [
    7 => 100,
    30 => 500,
    // Add more...
];
```

---

## 🐛 Troubleshooting

### Database Connection Failed
- **Solution**: Change `localhost` to `127.0.0.1` in `db.php`
- **Reason**: PHP PDO socket file location mismatch

### CORS Errors
- **Solution**: Ensure `Access-Control-Allow-Origin: *` header in all API files
- **Production**: Replace `*` with your actual domain

### Three.js Not Loading
- **Solution**: Use CDN link or download `three.min.js` to `public/lib/`
- **Alternative**: Use Matrix rain (no Three.js dependency)

### Sessions Not Working
- **Solution**: Create `backend/sessions/` directory with write permissions
```
mkdir backend/sessions
chmod 755 backend/sessions
```

---

## 🤝 Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](docs/CONTRIBUTING.md) for details.

### Development Setup
1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

### Code Style
- **PHP**: PSR-12 coding standard
- **JavaScript**: ESLint with Standard config
- **CSS**: BEM methodology

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🌟 Acknowledgments

- Inspired by **The Matrix** movie trilogy
- UI design influenced by **Cyberpunk 2077**
- Terminal effects from **classic hacker culture**
- Health data from **CDC Smoking & Tobacco Use**

---

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/shahruhban01/quit-smoking-app/issues)
- **Discussions**: [GitHub Discussions](https://github.com/shahruhban01/quit-smoking-app/discussions)
- **Email**: ethicalcodex.00@example.com

---

## 🗺️ Roadmap

- [ ] Mobile app (React Native)
- [ ] Social features (friends, leaderboards)
- [ ] Health metrics integration
- [ ] Email/SMS reminders
- [ ] Multi-language support
- [ ] Dark/Light theme toggle
- [ ] PWA support for offline access
- [ ] Data export (CSV, JSON)

---

<div align="center">

**Made with 💚 by [Ruhban Abdullah](https://developerruhban.com)**

If this project helped you quit smoking, consider starring ⭐ the repo!

</div>
