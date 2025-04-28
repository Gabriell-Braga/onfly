#!/bin/sh

echo "🚀 Iniciando o container do backend..."

# Verifica se o vendor existe
if [ ! -d "vendor" ]; then
  echo "📦 Pasta vendor não encontrada, rodando composer install..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Esperar o banco MySQL subir
echo "⏳ Aguardando o banco de dados ficar disponível..."
until php artisan migrate:status >/dev/null 2>&1; do
  echo "Banco de dados ainda não disponível. Aguardando..."
  sleep 2
done

echo "✅ Banco de dados disponível."

# Rodar migrate:fresh se DB_REFRESH=true
if [ "$DB_REFRESH" = "true" ]; then
  echo "⏳ Rodando migrate:fresh --seed..."
  php artisan migrate:fresh --seed
else
  echo "✅ Rodando migrate..."
  php artisan migrate
fi

# Inicia o servidor
echo "🌐 Iniciando o servidor Laravel..."
php artisan serve --host=0.0.0.0 --port=8000
