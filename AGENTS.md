# AGENTS.md - Instrucciones para asistentes AI

## Stack
- PHP 8.3, Laravel 11, Blade + Livewire + Tailwind, MySQL 8, Redis
- Flutter 3.x para app móvil
- Docker para desarrollo

## Convenciones

### Backend
- **Estructura por dominios**: `app/Domains/<Domain>/` con Controllers, Models, Services, Policies, Requests
- **Multi-tenant**: Todos los modelos multi-tenant usan `BelongsToTenant` trait + Global Scope
- **Servicios**: Inyectar dependencias en constructor, usar repositorios solo si aportan valor
- **DTOs/Requests**: Usar Form Requests para validación de entrada
- **Políticas**: Una Policy por modelo, autorizar con `$this->authorize()`
- **Eventos**: Usar eventos para desacoplar flujos (CheckinCompleted, ReservationCreated, etc.)
- **Colas**: Tareas pesadas en jobs con `ShouldQueue`
- **Tests**: Tests unitarios para servicios, feature tests para API

### Base de datos
- Migraciones con nombres descriptivos y prefijo numérico
- Índices en foreign keys y campos de búsqueda frecuente
- `softDeletes()` donde tenga sentido
- Cifrado con `->cast('encrypted')` para datos sensibles

### API
- Versionada a partir de `/api/v1`
- Envelope estándar: `{ data, message, status, meta }`
- Rate limiting: 100 req/min general, 5 req/min login
- Paginación: `?page=1&per_page=15&sort=-created_at`

### Frontend
- Componentes Livewire para interactividad del panel
- Formulario público check-in en Blade standalone (máxima ligereza)
- Alpine.js para interacciones ligeras

### App Móvil (Flutter)
- Arquitectura feature-based con providers
- API Client con Dio + interceptors
- Token persistente con flutter_secure_storage
- Modo offline: cola local con Hive (Fase 3)

### Testing
- `php artisan test` para backend
- `flutter test` para móvil

### Comandos útiles
- `php artisan migrate --seed` - reset completa BD
- `php artisan checkin:sync-integrations` - sincronizar OTAs
- `php artisan checkin:ses-retry-failed` - reintentar SES fallidos
- `php artisan horizon` - monitor de colas

### Próximas implementaciones (Fase 2/3)
- Conectores reales Booking/Airbnb (cuando se disponga de acceso)
- Modo auto SES (cuando se verifique API)
- OCR para documentos
- Modo offline app móvil
- 2FA
- SMS/WhatsApp notificaciones
