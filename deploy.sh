#!/bin/bash
set -euo pipefail

DOMAIN="${1:-aldara.ivema.es}"
echo "=== Desplegando HospedaCheck en: $DOMAIN (vía Plesk reverse proxy) ==="

echo "=== 1. Instalando Docker ==="
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com | sh
    sudo usermod -aG docker $USER
    echo "Reinicia sesión y ejecuta el script de nuevo."
    exit 0
fi

echo "=== 2. Configurando .env ==="
if [ ! -f backend/.env ]; then
    cp backend/.env.example backend/.env
    echo "IMPORTANTE: Edita backend/.env:"
    echo "  APP_ENV=production"
    echo "  APP_DEBUG=false"
    echo "  DB_PASSWORD= (cambiar)"
    echo "  REDIS_PASSWORD= (cambiar)"
    echo "  APP_URL=https://$DOMAIN"
    echo ""
    read -p "Pulsa Enter cuando hayas editado backend/.env..."
fi

echo "=== 3. Arrancando servicios ==="
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d

echo "=== 4. Migrando base de datos ==="
sleep 5
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T app php artisan migrate --seed --force || true
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T app php artisan key:generate --force || true
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T app php artisan storage:link || true

echo "=== 5. Optimizando Laravel ==="
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T app php artisan config:cache || true
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T app php artisan route:cache || true
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T app php artisan view:cache || true
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T app php artisan livewire:publish --assets --force || true

echo ""
echo "=== 6. Sync backend a Plesk (setup híbrido) ==="
echo "Si tienes el backend servido por Plesk, ejecuta manualmente:"
echo "  rsync -a --delete --exclude={'vendor/','node_modules/','.env','.git','storage/'} \\"
echo "    /opt/aldara/backend/ /var/www/vhosts/ivema.es/aldara/"
echo "  cd /var/www/vhosts/ivema.es/aldara"
echo "  php artisan livewire:publish --assets --force"
echo "  php artisan config:cache"
echo "  php artisan route:cache"
echo "  php artisan view:cache"

echo ""
echo "=== ¡Desplegado! ==="
echo "Docker Nginx escucha en: http://localhost:8080"
echo ""
echo "Ahora configura Plesk:"
echo "  1. Entra en Plesk → Websites & Domains → $DOMAIN"
echo "  2. Hosting Settings → Nginx serving → Proxy Mode (o Apache + Nginx)"
echo "  3. Añade: Settings → Reverse Proxy → http://localhost:8080"
echo "  4. Activa SSL con Let's Encrypt desde Plesk"
echo ""
echo "Tu web quedará en: https://$DOMAIN"
