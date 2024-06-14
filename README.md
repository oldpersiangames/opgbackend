# Deploy

- apt-get install certbot -y && certbot certonly --standalone --agree-tos --register-unsafely-without-email --key-type rsa -d backend.oldpersiangames.org && certbot renew --dry-run
- get docker-compose.yml and put in /opt/opgbackend/docker-compose.yml (remove build at app and uncomment image at app)
- copy .env file kenare docker-compose.yml, set mysql host to "db", and set user pass, add APP_KEY, also:
    SANCTUM_STATEFUL_DOMAINS=dash.oldpersiangames.org
    SESSION_DOMAIN=.oldpersiangames.org
    FRONTEND_URL=https://dash.oldpersiangames.org
    also TELEGRAM_TOKEN=bot_token and OWNER_TG_ID=yourid
    also TELEGRAM_API and TELEGRAM_HASH for your python bot
    also OPG_KEY_HASH
- docker compose pull && docker compose up --build -d
- git clone https://${BACKUP_TOKEN}@github.com/oldpersiangames/opg-backups /opgactions/opg-backups && cd /opgactions/opg-backups && git config user.email alihardanc@gmail.com && git config user.name alihardan
- docker compose exec app bash -c "mysql -h db -u opg -p123456 opg < /opgactions/opg-backups/opgbackend.sql"
- docker compose exec app bash -c "chown -R application:application /opgactions"
- docker compose exec app bash -c "php artisan config:cache && php artisan route:cache && php artisan optimize"
