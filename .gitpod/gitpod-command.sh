# Backend
ddev start -y

ddev exec php artisan key:generate

# bash .gitpod/import-db.sh
# bash .gitpod/import-files.sh

# Seed DB
ddev exec php artisan migrate --seed
ddev exec php artisan jwt:secret

# Frontend
# nvm install 16
# nvm use 16
# npm run hot
# ddev exec npm run dev
