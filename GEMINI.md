# AI Agents - Mini HRIS Backend

This repository contains coding guidelines and instructions for AI assistants working on the Mini HRIS Backend project.

## Primary Instructions

All AI coding assistants should reference the comprehensive instructions in:

**`.github/copilot-instructions.md`**

This file contains:
- Project architecture overview
- Critical workflows and commands
- Coding standards and principles (SOLID, DRY)
- Laravel-specific conventions
- API design patterns
- Security guidelines
- Testing requirements
- AI behavior guidelines

## Project Overview

**Mini HRIS Backend** is a Laravel 12-based Human Resource Information System that manages employee data with JWT authentication and RESTful API design.

### Key Technologies
- Laravel 12 (PHP Framework)
- JWT Authentication
- MySQL/PostgreSQL Database
- RESTful API (`/api/v1/` endpoints)
- Vite (Frontend build tool)

### Core Features
- Employee CRUD operations
- User authentication and authorization
- Search and filtering capabilities
- Comprehensive logging and error handling

## For AI Assistants

When working on this codebase:

1. **Read the full instructions** in `.github/copilot-instructions.md`
2. **Follow SOLID and DRY principles** for maintainable code
3. **Use UUIDs** for all model primary keys (`HasUuids` trait)
4. **Implement comprehensive tests** for new features
5. **Maintain API consistency** with proper response formats
6. **Prioritize security** with proper validation and authentication

## Quick Reference

- **Setup**: `composer run setup`
- **Development**: `composer run dev`
- **Testing**: `php artisan test`
- **Models**: Use UUIDs, define relationships clearly
- **Controllers**: Thin controllers, business logic in services
- **Validation**: Form Request classes in `app/Http/Requests/`

For detailed guidelines, see `.github/copilot-instructions.md`.