# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Canalizador is a Laravel 12 application for automating YouTube video optimization and content repurposing. It uses AI-driven insights to generate shorts, update metadata, and process videos in bulk.

## Architecture

The codebase follows Domain-Driven Design with a hexagonal architecture pattern.

### Domain Modules (src/)

Each module follows the same three-layer structure:

- **Domain/**: Entities, Value Objects, Repository interfaces, Exceptions
- **Application/**: Use Cases (application services)
- **Infrastructure/**: Repository implementations, HTTP Controllers, external service integrations

Current modules:
- `Avatar` - AI-generated avatar management
- `Channel` - YouTube channel sync and AI-powered metadata optimization
- `Clip` - Video clip generation, download, and composition (event-driven)
- `Image` - Image storage and management
- `Script` - Video script generation and management
- `Shared` - Cross-cutting concerns (Clock, HttpClient, Value Objects, Domain Events)
- `Video` - Video creation, content retrieval, publishing to YouTube
- `VideoLegacy` - Legacy video processing features

### Laravel App Layer (app/)

Contains Laravel-specific code:
- `Services/` - Google OAuth client and token management
- `Http/Middleware/` - API token validation, Google token verification
- `Providers/AppServiceProvider.php` - Dependency injection bindings (central wiring of domain/infrastructure)

### Key Integration Points

- **YouTube Data API** - Channel sync, video publishing
- **YouTube Analytics API** - Performance metrics
- **OpenAI/Sora** - Video generation, script generation, metadata generation
- **Prism PHP** - AI model integration abstraction

## Code Style

PSR-12 with these notable additions:
- Short array syntax
- Aligned binary operators (single space minimal)
- Ordered alphabetical imports
- Trailing commas in multiline arrays
- Single quotes for strings

## Testing

Tests run with SQLite in-memory database. CI uses MySQL 8.0.

Test suites:
- `tests/Unit/` - Unit tests
- `tests/Feature/` - Feature/integration tests

## API Endpoints

All API routes require `api.token` middleware. Most video/channel operations also require `EnsureGoogleToken` middleware for YouTube OAuth.

Key endpoints:
- `POST /api/avatars` - Create AI avatar
- `POST /api/videos/create` - Create video (triggers async clip generation via events)
- `GET /api/videos/{videoId}/content` - Retrieve generated video content
- `POST /api/videos/publish` - Publish video to YouTube
- `PUT /api/channels/{channelId}/sync` - Sync channel from YouTube
- `PUT /api/channels/{channelId}/update-with-ai` - Update channel metadata with AI

## Namespace Mapping

- `App\` → `app/`
- `Canalizador\` → `src/`
- `Tests\` → `tests/`
