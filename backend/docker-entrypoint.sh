#!/bin/bash
set -e

# Instalar dependências se vendor não existir
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "Instalando dependências do Composer..."
    composer install --no-interaction --optimize-autoloader
fi

# Copiar .env se não existir
if [ ! -f ".env" ]; then
    echo "Criando arquivo .env..."
    cp .env.example .env
fi

# Gerar APP_KEY se estiver vazio
if ! grep -q "^APP_KEY=base64" .env; then
    echo "Gerando APP_KEY..."
    php artisan key:generate
fi

# Gerar JWT_SECRET se não existir
if ! grep -q "^JWT_SECRET=" .env; then
    echo "Gerando JWT_SECRET..."
    php artisan jwt:secret --force
fi

# Aguardar banco de dados estar disponível
echo "Aguardando banco de dados..."
until php -r "try { new PDO('sqlsrv:server=db,1433;Database=master', 'sa', 'SenhaFametro123!'); } catch (Exception \$e) { exit(1); } exit(0);" 2>/dev/null; do
    sleep 2
done

# Rodar migrations
php artisan migrate --force

# Rodar seeders
php artisan db:seed --force

# Iniciar servidor
echo "Iniciando Laravel..."
exec php artisan serve --host=0.0.0.0 --port=8000
