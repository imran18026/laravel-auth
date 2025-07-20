# Laravel JWT Authentication System

A complete JWT authentication solution for Laravel with email verification, password reset, and OAuth integration.

## Features

- JWT Authentication
- Email Verification
- Password Reset with Token Invalidation
- OAuth Social Login (Google, Facebook, etc.)
- SMS Verification (Stub Implementation)
- Automatic Token Invalidation on Password Change
- Comprehensive Test Suite
- Pre-configured Test Users

## Requirements

- PHP 8.2+
- Laravel 10+
- MySQL 5.7+ / PostgreSQL / SQLite
- Composer

## Installation

### 1. Clone the repository
```bash
git clone your-repo-url.git
cd your-project
```

### 2. Install dependencies
```bash
composer install
```

### 3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file with your database credentials:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_jwt
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Configure JWT
```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

### 5. Run migrations and seed test users
```bash
php artisan migrate --seed
```

### 6. Configure Mail (for email verification)
Set in `.env`:
```ini
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS=no-reply@example.com
```

## Running the Application

Start the development server:
```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

## API Endpoints

| Method | Endpoint                     | Description                          |
|--------|------------------------------|--------------------------------------|
| POST   | /api/v1/auth/register        | Register new user                    |
| POST   | /api/v1/auth/login           | Login user                           |
| POST   | /api/v1/auth/logout          | Logout user                          |
| POST   | /api/v1/auth/refresh         | Refresh JWT token                    |
| GET    | /api/v1/auth/me              | Get authenticated user info          |
| POST   | /api/v1/auth/forgot-password | Send password reset link             |
| POST   | /api/v1/auth/reset-password  | Reset password                       |
| POST   | /api/v1/auth/verify-email    | Verify email address                 |
| POST   | /api/v1/auth/resend-email    | Resend verification email            |
| POST   | /api/v1/auth/send-sms-code   | Send SMS verification code           |
| POST   | /api/v1/auth/verify-sms      | Verify SMS code                      |
| GET    | /api/v1/auth/oauth/{provider}/redirect | OAuth redirect           |
| GET    | /api/v1/auth/oauth/{provider}/callback | OAuth callback          |

## Test Users

Pre-seeded users for testing:

| Email                 | Password  | Verified | Role       |
|-----------------------|-----------|----------|------------|
| admin@example.com     | admin123  | Yes      | Admin      |
| user@example.com      | password  | Yes      | Regular    |
| unverified@example.com| password  | No       | Unverified |
| phone@example.com     | password  | Yes      | With phone |

## Testing

Run the test suite:
```bash
php artisan test
```

Test coverage includes:
- Authentication flows
- Email verification
- Password reset
- Token invalidation
- OAuth integration
- Protected routes

## Security Features

- Automatic token invalidation on password change
- Email verification required for protected routes
- JWT token expiration (60 minutes by default)
- CSRF protection
- Rate limiting (configured in Laravel)

## Postman Collection

Import the included `postman_collection.json` to test all endpoints with pre-configured examples.

## Environment Variables

Key environment variables to configure:

| Variable            | Description                          |
|---------------------|--------------------------------------|
| JWT_SECRET          | JWT signing key                      |
| JWT_TTL             | Token lifetime in minutes (default: 60) |
| MAIL_*              | Email configuration                  |
| DB_*                | Database configuration               |
| GOOGLE_CLIENT_ID    | Google OAuth client ID               |
| GOOGLE_CLIENT_SECRET| Google OAuth client secret           |

## Deployment

For production deployment:

1. Set `APP_ENV=production` in `.env`
2. Configure proper mail settings
3. Set up HTTPS
4. Configure proper CORS settings
5. Set up queue workers for emails

## Troubleshooting

**Token not invalidating after password change?**
- Ensure you're using the latest token after password reset
- Verify the JWT blacklist is enabled in `config/jwt.php`

**Email verification not working?**
- Check mail configuration in `.env`
- Verify queue workers are running if using queues

**OAuth not working?**
- Ensure you've registered your app with the provider
- Verify callback URLs are correctly configured

## License

MIT
# laravel-auth
# laravel-auth
# laravel-auth
