# CheckIn SaaS

Plataforma multi-tenant SaaS para registro de viajeros en alojamientos turísticos en España, con envío a SES.Hospedajes.

## Stack

- **Backend:** PHP 8.3 / Laravel 11
- **Frontend:** Blade + Livewire + Tailwind CSS
- **App Móvil:** Flutter 3.x (Android + iOS)
- **BD:** MySQL 8
- **Cache/Colas:** Redis
- **Infra:** Docker + Nginx + PHP-FPM

## Requisitos

- Docker & Docker Compose
- PHP 8.3+ (para desarrollo local)
- Composer
- Node.js 20+ (para assets)

## Instalación Rápida (Docker)

```bash
# 1. Clonar repositorio
git clone <repo> && cd checkin

# 2. Copiar .env
cp .env.example .env

# 3. Iniciar contenedores
docker-compose up -d

# 4. Instalar dependencias
docker exec checkin-app composer install
docker exec checkin-app php artisan key:generate
docker exec checkin-app php artisan storage:link

# 5. Migrar y seedear
docker exec checkin-app php artisan migrate --seed

# 6. Compilar assets (si no se usa Vite dev server)
docker exec checkin-app npm install && npm run build

# 7. Acceder
# Web: http://localhost:8080
# Mailpit: http://localhost:8025
```

## Instalación Local

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## App Móvil

```bash
cd mobile
flutter pub get
flutter run
```

Configurar URL de API en `lib/core/api/api_client.dart` o mediante `--dart-define=API_URL=https://tu-dominio.com/api/v1`.

## Estructura del Proyecto

```
checkin/
├── backend/          # Laravel 11
│   ├── app/
│   │   ├── Domains/  # Módulos: Auth, Tenant, Property, Reservation, Guest, Checkin, Integration, Compliance, Billing
│   │   ├── Models/   # Modelos globales (User, AuditLog)
│   │   ├── Traits/   # BelongsToTenant, HasUuid, Auditable
│   │   └── Http/     # Controllers, Middleware, Requests
│   ├── config/       # Configuración: tenant.php, ses.php, integrations.php, checkin.php
│   ├── database/     # Migraciones y seeders
│   ├── resources/    # Vistas Blade
│   └── routes/       # web.php, api.php
├── mobile/           # Flutter app
│   └── lib/
│       ├── core/     # API client, almacenamiento, utilidades
│       └── features/ # Auth, Home, Reservations, Checkin, Tenants
├── docker/           # Dockerfiles y configs
└── docker-compose.yml
```

## API REST

Documentación completa: `http://localhost:8080/api/documentation` (con Swagger UI, requiere instalar paquete).

### Endpoints principales

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/v1/auth/login` | Login |
| GET | `/api/v1/auth/me` | Perfil |
| GET | `/api/v1/tenants` | Listar tenants |
| GET/POST | `/api/v1/properties` | CRUD alojamientos |
| GET/POST | `/api/v1/reservations` | CRUD reservas |
| POST | `/api/v1/reservations/{id}/send-checkin` | Enviar enlace check-in |
| GET/POST | `/api/v1/guests` | CRUD huéspedes |
| GET/POST | `/api/v1/checkins` | Gestión check-ins |
| POST | `/api/v1/checkins/{id}/verify` | Verificar check-in |
| GET/POST | `/api/v1/integrations` | Integraciones |
| POST | `/api/v1/integrations/ics/import` | Importar ICS |
| GET/POST | `/api/v1/ses/submissions` | Envíos SES |

## Licencia

Propietaria. Todos los derechos reservados.
