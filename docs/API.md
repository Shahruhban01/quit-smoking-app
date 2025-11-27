# API Documentation

## Base URL
```
http://localhost/quit-smoking-app/backend/api
```

## Authentication

All protected endpoints require JWT token:
```
Authorization: Bearer <your_jwt_token>
```

---

## Endpoints

### POST /register.php
Create new user account.

**Request Body:**
```
{
  "username": "hackerman",
  "email": "hack@quit.app",
  "password": "password123",
  "quit_date": "2025-11-01",
  "cigarettes_per_day": 20,
  "cost_per_pack": 12.00,
  "country": "US",
  "timezone": "America/New_York"
}
```

**Response (201):**
```
{
  "success": true,
  "message": "Registration successful",
  "user_id": 1
}
```

---

### POST /login.php
Authenticate user and receive JWT.

**Request Body:**
```
{
  "email": "hack@quit.app",
  "password": "password123"
}
```

**Response (200):**
```
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "user_id": 1,
    "username": "hackerman",
    "email": "hack@quit.app"
  }
}
```

---

### GET /dashboard.php
Get user dashboard data. **Requires auth.**

**Response (200):**
```
{
  "user": {
    "username": "hackerman",
    "quit_date": "2025-11-01",
    "total_days_quit": 26,
    "current_streak": 26,
    "best_streak": 26,
    "total_coins": 180,
    "badges_unlocked": 3,
    "money_saved": 156.00,
    "sound_enabled": true,
    "checked_in_today": false
  },
  "next_badge": {
    "name": "Platinum Champion",
    "requirement_value": 30,
    "coin_reward": 500
  },
  "recent_logs": [...]
}
```

---

### POST /checkin.php
Log daily smoke-free check-in. **Requires auth.**

**Request Body:**
```
{
  "date": "2025-11-27",
  "note": "Feeling strong today!"
}
```

**Response (200):**
```
{
  "success": true,
  "message": "Check-in successful!",
  "current_streak": 27,
  "coins_earned": 10,
  "new_badges": []
}
```

---

### POST /relapse.php
Log smoking relapse. **Requires auth.**

**Request Body:**
```
{
  "date": "2025-11-27",
  "cigarettes_smoked": 3,
  "note": "Bad day at work"
}
```

**Response (200):**
```
{
  "success": true,
  "message": "Relapse logged. Your streak has been reset.",
  "punishment": {
    "streak_reset": true,
    "coins_deducted": 50,
    "new_total_coins": 130
  },
  "encouragement": "Don't give up! Your best streak was 27 days - you can beat it!"
}
```

---

### GET /badges.php
Get all badges with unlock status. **Requires auth.**

**Response (200):**
```
{
  "badges": [
    {
      "badge_id": 1,
      "badge_key": "day_1",
      "name": "First Victory",
      "description": "Completed your first smoke-free day",
      "requirement_value": 1,
      "coin_reward": 10,
      "unlocked": 1,
      "unlocked_at": "2025-11-02 10:23:45"
    },
    ...
  ]
}
```

---

### GET /coins.php
Get coin transaction history. **Requires auth.**

**Query Parameters:**
- `limit` (optional): Number of transactions (default: 50, max: 100)

**Response (200):**
```
{
  "transactions": [
    {
      "transaction_id": 15,
      "amount": 10,
      "reason": "daily_checkin",
      "reference_id": "2025-11-27",
      "created_at": "2025-11-27 04:00:00"
    },
    ...
  ]
}
```

---

## Error Responses

### 400 Bad Request
```
{
  "error": "Missing required field: email"
}
```

### 401 Unauthorized
```
{
  "error": "Invalid token"
}
```

### 409 Conflict
```
{
  "error": "Username or email already exists"
}
```

### 500 Internal Server Error
```
{
  "error": "Database error: Connection failed"
}
```