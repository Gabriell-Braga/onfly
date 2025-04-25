#!/bin/bash

if [ "$DB_REFRESH" = true ]; then
  echo "⏳ Rodando migrate:fresh --seed..."
  php artisan migrate:fresh --seed
else
  echo "✅ Rodando migrate..."
  php artisan migrate
fi

# Inicia o servidor PHP (ou outro comando final)
php artisan serve --host=0.0.0.0 --port=8000
