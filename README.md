# Canalizador

**Canalizador** is a Laravel 12 application for automating YouTube content production and optimization using artificial intelligence. It programmatically generates videos, scripts, voices, and shorts through a REST API.

---

## Features

- **AI video generation** — Integration with Google Veo and OpenAI Sora to create clips from prompts
- **Voice cloning** — Voice synthesis and cloning with ElevenLabs (speech-to-speech)
- **Script generation** — GPT-powered scripts for video content
- **Smart fragmentation** — Splits long videos into optimized shorts and publishes them to YouTube
- **Channel sync** — Syncs YouTube channel metadata and enhances it with AI
- **Audio transcription** — Extracts and transcribes audio from downloaded videos
- **News & weather** — Downloads news and fetches weather forecasts (AEMET) as video input
- **Event queue** — Async processing with RabbitMQ (clip generation, composition, publishing)

---

## Architecture

**Domain-Driven Design (DDD)** with hexagonal architecture. Domain code lives in `src/`, separate from Laravel (`app/`).

```
src/
├── Shared/           # Cross-cutting abstractions (EventBus, Clock, HttpClient)
├── VideoProduction/  # Avatar, Clip, Image, News, Script, Video, Voice, Weather
└── YouTube/          # Channel, Metric, Transcription, Video
```

Each module follows the same structure: `Domain/` → `Application/` → `Infrastructure/`.

---

## Requirements

| Tool           | Version |
|----------------|---------|
| PHP            | ^8.5    |
| Composer       | ^2      |
| Docker         | 20+     |
| Docker Compose | v2+     |

**Required external APIs:**

| Service            | Used for                                  |
|--------------------|-------------------------------------------|
| Google YouTube API | Publishing, downloading, and syncing videos |
| Google Veo API     | AI video generation                       |
| OpenAI             | Scripts, metadata, and fragmentation      |
| ElevenLabs         | Voice cloning and generation              |
| AEMET (optional)   | Weather forecasts                         |

---

## Installation

### 1. Clone the repository

```bash
git clone <repo-url> canalizador
cd canalizador
```

### 2. Configure environment variables

```bash
cp .env.example .env
```

Edit `.env` and fill in the external API credentials:

```dotenv
# Database (use the docker-compose values)
DB_HOST=10.7.0.6
DB_DATABASE=canalizador
DB_USERNAME=root
DB_PASSWORD=your_password

# RabbitMQ (use the docker-compose values)
RABBITMQ_HOST=10.7.0.11

# OpenAI
OPENAI_API_KEY=sk-...

# Google Veo
GOOGLE_VEO_API_KEY=...

# ElevenLabs
ELEVENLABS_API_KEY=...
```

### 3. Run the setup script

```bash
bash docker/commands/setup.sh
```

This script handles everything: builds images, starts containers, installs dependencies, waits for MySQL and RabbitMQ to be ready, runs migrations, and declares queues. It prints a health check on completion.

### 4. Create a user and get the API token

```bash
docker exec -it php_canalizador php artisan tinker
```

```php
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
]);
echo $user->generateApiToken();
```

All API requests require the token in the `Authorization: Bearer <token>` header.

---

## API Reference

All endpoints require the header:
```
Authorization: Bearer <your-api-token>
```

Endpoints marked with `*` additionally require Google OAuth configured.

### Video production

| Method | Route                               | Description                  |
|--------|-------------------------------------|------------------------------|
| POST   | `/api/videos/create`                | Create an AI-generated video |
| GET    | `/api/videos/{videoId}/content` *   | Retrieve generated content   |
| POST   | `/api/videos/{videoId}/apply-voice` | Apply a voice to a video     |

### Voice

| Method | Route                  | Description                  |
|--------|------------------------|------------------------------|
| POST   | `/api/voice/clone`     | Clone a voice from audio     |
| POST   | `/api/voice/generate`  | Generate audio from text     |

### YouTube *

| Method | Route                                            | Description                        |
|--------|--------------------------------------------------|------------------------------------|
| POST   | `/api/youtube/videos/publish`                    | Publish a video to YouTube         |
| POST   | `/api/youtube/channels/{channelId}/download-latest` | Download the latest channel video |
| POST   | `/api/youtube/videos/fragment-and-publish`       | Fragment and publish as shorts     |
| POST   | `/api/youtube/videos/smart-fragment-and-publish` | Smart shorts fragmentation         |
| PUT    | `/api/channels/{channelId}/sync`                 | Sync channel with YouTube          |
| PUT    | `/api/channels/{channelId}/update-with-ai`       | Update channel metadata with AI    |

### Utilities

| Method | Route                | Description             |
|--------|----------------------|-------------------------|
| GET    | `/api/weather`       | Get weather forecasts   |
| POST   | `/api/news/download` | Download news           |

---

## Useful Commands

The `docker/commands/` directory includes wrappers to run commands inside the PHP container:

```bash
# Equivalent to: docker exec -it php_canalizador php artisan <cmd>
bash docker/commands/artisan.sh migrate
bash docker/commands/artisan.sh tinker

# Equivalent to: docker exec -it php_canalizador composer <cmd>
bash docker/commands/composer.sh install
bash docker/commands/composer.sh test
```

## Code Style & Static Analysis

```bash
# Format code
composer cs-fix

# Static analysis with PHPStan
vendor/bin/phpstan analyse
```

PSR-12 with alphabetically ordered imports, single quotes, and trailing commas in multiline arrays.

---

## Docker Services

| Service     | Port  | Description                              |
|-------------|-------|------------------------------------------|
| Nginx       | 80    | Web server                               |
| PHP-FPM     | 9000  | PHP processor                            |
| MySQL 8     | 3306  | Database                                 |
| Redis       | 6379  | Cache and sessions                       |
| RabbitMQ    | 5672  | Message queue                            |
| RabbitMQ UI | 15672 | Management panel (guest/guest)           |
| Worker      | —     | Queue processor (starts automatically)   |

---

## License

MIT
