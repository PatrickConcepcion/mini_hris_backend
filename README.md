# Mini HRIS Backend

A Laravel 12-based mini Human Resource Information System (HRIS) backend focused on employee data management with JWT authentication and RESTful API design.

## Features

- **JWT Authentication**: Secure API authentication with automatic token rotation
- **Employee Management**: Full CRUD operations for employee records
- **Search & Filtering**: Advanced search and filtering capabilities
- **RESTful API**: Versioned API endpoints (`/api/v1/`)
- **Security**: Enhanced security with JWT token rotation on every request
- **Logging**: Comprehensive logging for all operations
- **Testing**: PHPUnit test suite with feature and unit tests

## Security Features

### JWT Token Rotation
This application implements automatic JWT token rotation for enhanced security:
- New token generated on every authenticated request
- Same expiry time maintained to prevent premature logouts
- Prevents token replay attacks
- Frontend must extract rotated tokens from response headers

## Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & npm (for frontend assets)

### Installation

1. **Clone and setup**:
   ```bash
   git clone <repository-url>
   cd mini_hris
   composer run setup
   ```

2. **Environment configuration**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   # Configure your database and JWT_SECRET in .env
   ```

3. **Run migrations**:
   ```bash
   php artisan migrate
   ```

### Development

Start the development environment:
```bash
composer run dev
```

This runs:
- Laravel server (`php artisan serve`)
- Queue worker (`php artisan queue:listen`)
- Log monitoring (`php artisan pail`)
- Vite dev server for frontend assets

### Testing

Run the test suite:
```bash
php artisan test
```

## API Documentation

### Authentication Endpoints
- `POST /api/v1/auth/login` - User login
- `POST /api/v1/auth/refresh` - Refresh JWT token
- `POST /api/v1/auth/logout` - User logout

### Employee Endpoints
- `GET /api/v1/employees` - List employees (with search/filter)
- `POST /api/v1/employees` - Create employee
- `GET /api/v1/employees/{id}` - Get employee details
- `PUT /api/v1/employees/{id}` - Update employee
- `DELETE /api/v1/employees/{id}` - Delete employee

### Query Parameters
- `?search=` - Search by name, email, or employee number
- `?gender=` - Filter by gender
- `?page=` - Pagination page
- `?per_page=` - Items per page

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/V1/          # API controllers
│   ├── Middleware/                  # Custom middleware (RotateToken)
│   └── Requests/                    # Form request validation
├── Models/                          # Eloquent models
└── Services/                        # Business logic services

database/
├── factories/                       # Model factories
├── migrations/                      # Database migrations
└── seeders/                         # Database seeders

routes/
└── api.php                          # API route definitions

tests/
├── Feature/Api/V1/                  # Feature tests
└── Unit/                           # Unit tests
```

## Key Technologies

- **Laravel 12**: PHP framework
- **JWT Auth**: PHPOpenSourceSaver/JWTAuth for authentication
- **Eloquent ORM**: Database interactions with UUID primary keys
- **PHPUnit**: Testing framework
- **Vite**: Frontend asset compilation
- **Tailwind CSS**: Utility-first CSS framework (frontend)

## Development Guidelines

- Follow PSR-12 coding standards
- Use Form Request classes for validation
- Implement business logic in Services/Actions
- Write tests for new features
- Use UUIDs for all model primary keys
- Log all important operations

## Contributing

1. Follow the established coding standards
2. Write tests for new features
3. Update documentation as needed
4. Ensure all tests pass before submitting

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## JWT Authentication (API)

- Install package: `composer require php-open-source-saver/jwt-auth`
- Publish config (optional here): `php artisan vendor:publish --provider="PHPOpenSourceSaver\\JWTAuth\\Providers\\LaravelServiceProvider" --tag=config`
- Generate secret: `php artisan jwt:secret` (writes `JWT_SECRET` to `.env`)
- API guard is configured in `config/auth.php: 'api' => ['driver' => 'jwt']`
- Middleware aliases for `jwt.auth` and `jwt.refresh` are auto-registered in `bootstrap/app.php` when the package is installed.
- API routes are enabled at `routes/api.php`; example protected route: `GET /api/me` (uses `auth:api`).
