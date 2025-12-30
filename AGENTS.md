# Canalizador Project Rules

## Arquitectura y Estructura

### Domain-Driven Design (DDD)
- **Estructura de capas**: El proyecto sigue DDD con tres capas claramente separadas:
  - `Domain/`: Entidades, Value Objects, Interfaces de Repositorios, Excepciones de Dominio
  - `Application/`: Use Cases, DTOs (Request/Response), Mappers
  - `Infrastructure/`: Implementaciones concretas (Repositorios, Controllers, Servicios externos)

- **Regla de dependencias**: 
  - Domain NO depende de Application ni Infrastructure
  - Application depende solo de Domain
  - Infrastructure depende de Domain y Application
  - Nunca importar clases de Infrastructure en Domain o Application

### Naming Conventions

#### Namespaces
- Domain: `Canalizador\{Module}\Domain\{Layer}\{Name}`
- Application: `Canalizador\{Module}\Application\{Layer}\{Name}`
- Infrastructure: `Canalizador\{Module}\Infrastructure\{Layer}\{Name}`

#### Clases
- **Entidades**: Nombres en singular, sin sufijos (`Video`, `Script`)
- **Value Objects**: Nombres descriptivos (`VideoId`, `PublishOptions`, `Title`)
- **Repositorios (Domain)**: Interfaces con nombre descriptivo (`VideoRepository`, `VideoPublisher`)
- **Repositorios (Infrastructure)**: Implementaciones con prefijo de tecnología (`YoutubeVideoPublisher`, `EloquentScriptRepository`)
- **Use Cases**: Verbos en infinitivo (`PublishVideo`, `GetYoutubeVideo`)
- **Controllers**: Sufijo `Controller` (`PublishVideoController`)
- **Mappers**: Sufijo `Mapper` (`PublishVideoRequestMapper`)
- **Services**: Sufijo descriptivo (`GoogleClientService`, `VideoFileValidator`)

#### Métodos
- **Getters en Value Objects**: Nombre del atributo sin prefijo (`value()`, `id()`)
- **Métodos de transformación**: Prefijo `with` para inmutabilidad (`withVideoLocalPath()`)
- **Métodos de estado**: Verbos descriptivos (`markAsCompleted()`)

## Principios SOLID

### Single Responsibility Principle (SRP)
- Cada clase debe tener UNA única razón para cambiar
- Si una clase hace más de una cosa, extraer responsabilidades a servicios dedicados
- Ejemplo: `YoutubeVideoPublisher` delega a `VideoFileValidator`, `VideoMetadataExtractor`, `YouTubeVideoBuilder`, etc.

### Open/Closed Principle (OCP)
- Abierto para extensión, cerrado para modificación
- Usar interfaces y abstracciones para permitir nuevas implementaciones sin modificar código existente
- Ejemplo: `VideoPublisher` permite agregar `TikTokVideoPublisher` sin modificar `PublishVideo`

### Liskov Substitution Principle (LSP)
- Las implementaciones deben ser completamente intercambiables con sus interfaces
- No violar contratos definidos en interfaces

### Interface Segregation Principle (ISP)
- Interfaces pequeñas y específicas, no interfaces "gordas"
- Los clientes no deben depender de métodos que no usan
- Ejemplo: `FileSystem` tiene métodos específicos, no un método genérico "doEverything"

### Dependency Inversion Principle (DIP)
- **NUNCA instanciar clases concretas directamente** (excepto Value Objects y DTOs)
- Depender siempre de abstracciones (interfaces)
- Inyectar dependencias por constructor
- Usar Factories para crear objetos complejos cuando sea necesario
- Ejemplo: `YouTubeServiceFactory` en lugar de `new Google_Service_YouTube($client)`

## Value Objects

### Características
- **Siempre `readonly class`** con `declare(strict_types=1)`
- Propiedades `public readonly` o `private readonly` con getters
- Validación en el constructor
- Inmutables: métodos de transformación retornan nueva instancia (`with*()`)
- Método `value()` para obtener valor primitivo cuando sea necesario

### Ejemplo:
```php
final readonly class PublishOptions
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public array $tags = [],
        public string $privacyStatus = 'private',
    ) {
        // Validación en constructor
        if (!in_array($privacyStatus, ['private', 'unlisted', 'public'], true)) {
            throw new \InvalidArgumentException("Invalid privacy status: {$privacyStatus}");
        }
    }
}
```

## Entidades

### Características
- **Siempre `final readonly class`**
- Compuestas por Value Objects, no tipos primitivos
- Métodos de transformación retornan nueva instancia (inmutabilidad)
- Método `toArray()` para serialización cuando sea necesario

### Ejemplo:
```php
final readonly class Video
{
    public function __construct(
        private VideoId $id,
        private Title $title,
        // ...
    ) {}

    public function withVideoLocalPath(LocalPath $videoLocalPath): self
    {
        return new self(/* ... */);
    }
}
```

## Dependency Injection

### Reglas
- **Todas las dependencias se inyectan por constructor**
- Usar `private readonly` para propiedades inyectadas
- **NUNCA usar `new` para servicios, repositorios, o factories** (solo Value Objects y DTOs)
- Registrar bindings en `AppServiceProvider` para interfaces → implementaciones

### Ejemplo correcto:
```php
public function __construct(
    private readonly GoogleClientService $googleClientService,
    private readonly VideoFileValidator $videoFileValidator,
    private readonly YouTubeServiceFactory $youtubeServiceFactory
) {}
```

### Ejemplo incorrecto:
```php
// ❌ NUNCA hacer esto
$service = new Google_Service_YouTube($client);

// ✅ Usar factory inyectado
$service = $this->youtubeServiceFactory->create($client);
```

## Use Cases

### Estructura
- Clase `final readonly`
- Constructor recibe repositorios y servicios necesarios
- Método `execute()` recibe Request DTO y retorna Response DTO
- Documentar excepciones con `@throws`

### Ejemplo:
```php
final readonly class PublishVideo
{
    public function __construct(
        private VideoRepository $videoRepository,
        private VideoPublisherFactory $videoPublisherFactory
    ) {}

    /**
     * @throws VideoGenerationFailed
     */
    public function execute(PublishVideoRequest $request): PublishVideoResponse
    {
        // Lógica del caso de uso
    }
}
```

## Controllers

### Responsabilidades
- Solo orquestación: recibir request, llamar al Use Case, retornar response
- Validación delegada a Mappers
- Manejo de errores HTTP (status codes)
- **NO lógica de negocio**

### Estructura
```php
final class PublishVideoController
{
    public function __construct(
        private readonly PublishVideo $publishVideo,
        private readonly PublishVideoRequestMapper $mapper
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $useCaseRequest = $this->mapper->map($request);
        $response = $this->publishVideo->execute($useCaseRequest);
        return response()->json($response->toArray());
    }
}
```

## Mappers

### Responsabilidades
- Validar datos del Request HTTP
- Mapear Request HTTP → Request DTO del Use Case
- Extraer y transformar datos según sea necesario

### Ejemplo:
```php
final class PublishVideoRequestMapper
{
    public function map(Request $request): PublishVideoRequest
    {
        $validated = $request->validate([
            'video_id' => 'required|string',
            'platform' => 'required|in:youtube',
            // ...
        ]);

        return new PublishVideoRequest(
            videoId: $validated['video_id'],
            platform: $validated['platform'],
            options: $this->extractOptions($validated)
        );
    }
}
```

## Excepciones de Dominio

### Uso
- Crear excepciones específicas del dominio en `Domain/Exceptions/`
- Usar factory methods cuando sea apropiado
- Ejemplo: `VideoGenerationFailed::apiError($message)`

### Naming
- Nombres descriptivos que indiquen el problema
- Sufijo `Exception` o nombre descriptivo (`VideoGenerationFailed`, `VideoNotFound`)

## Servicios Externos (Google APIs, OpenAI, etc.)

### Patrones
- **Abstraer en interfaces** en Domain cuando sea posible
- Implementaciones en Infrastructure
- Usar Factories para crear clientes de APIs externas
- Centralizar creación de clientes (ej: `GoogleClientService`)

### Ejemplo:
```php
// Domain
interface VideoGenerator
{
    public function generate(/* ... */): string;
}

// Infrastructure
final class SoraVideoGenerator implements VideoGenerator
{
    public function __construct(
        private readonly HttpClient $httpClient,
        private readonly SoraConfig $config
    ) {}
}
```

## Testing

### Estructura
- Tests unitarios para lógica de dominio (Value Objects, Entidades)
- Tests de integración para Use Cases
- Mocks de interfaces, no de implementaciones concretas

## Código PHP Específico

### Declaraciones
- **Siempre** `declare(strict_types=1);` al inicio de cada archivo
- Usar tipos estrictos en todos los métodos

### Propiedades
- Preferir `readonly` cuando sea posible
- Usar property promotion en constructores: `public function __construct(private readonly Type $property) {}`

### Arrays
- Usar sintaxis corta `[]` en lugar de `array()`
- Tipar arrays cuando sea posible: `array<string>`, `array<int, string>`

### Strings
- Usar heredoc/nowdoc para strings largos (prompts, configuraciones)
- Preferir interpolación de strings cuando sea legible

## Laravel Integration

### Configuración
- Variables de entorno en `.env` y `.env.example`
- Configuración en `config/` usando `config()` helper
- Bindings de servicios en `AppServiceProvider`

### Middleware
- Middleware para autenticación/autorización
- Guardar estado en sesión cuando sea necesario (`session(['key' => $value])`)

### Routes
- API routes en `routes/api.php`
- Web routes en `routes/web.php`
- Usar middleware groups cuando sea apropiado

## Comentarios y Documentación

### PHPDoc
- **SOLO usar `@throws`** para documentar excepciones que pueden ser lanzadas
- **SOLO usar `@var`** cuando falta contexto (por ejemplo, en foreach o variables sin tipo explícito)
- **NO usar `@param`** si los parámetros ya están tipados en la firma del método
- **NO usar `@return`** si el tipo de retorno ya está especificado en la firma del método
- **NO usar PHPDoc redundante** que solo repite información ya presente en el código
- **NO usar comentarios de código simples (`//` o `/* */`)**
- El código debe ser autoexplicativo sin necesidad de comentarios

## Git y Commits

### Commits
- Commits atómicos: un cambio lógico por commit
- Mensajes descriptivos en español
- No hacer commits de trabajo en progreso (`WIP`, `fix`, etc.) a menos que sea necesario

## Performance

### Optimizaciones
- Lazy loading cuando sea apropiado
- Caché para datos que no cambian frecuentemente
- Validación temprana para evitar procesamiento innecesario

## Seguridad

### Validación
- Validar TODOS los inputs del usuario
- Usar Value Objects para validación de dominio
- Sanitizar datos antes de persistir

### Autenticación
- Usar middleware para proteger rutas
- Tokens almacenados de forma segura
- Refresh tokens cuando sea necesario

## Build y Test Commands

- Instalar dependencias: `composer install`
- Ejecutar tests: `php artisan test`
- Ejecutar linter: `./vendor/bin/phpstan analyse`
- Ejecutar fixer: `./vendor/bin/php-cs-fixer fix`
- Ejecutar todos los checks antes de commit: `composer test && composer lint`
