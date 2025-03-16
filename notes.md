symfony server:start
http://127.0.0.1:8000

JWT authentication

create an entity:
php bin/console make:user

password hasher
php bin/console security:hash-password

check routes
php bin/console debug:router

### create database
docker exec -it symfony_app bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

### entity changes?
php bin/console make:migration
php bin/console doctrine:migrations:migrate

### Unable to create a signed JWT from the given configuration
docker exec -it symfony_app php bin/console lexik:jwt:generate-keypair --overwrite

### manually check the logs
docker exec -it symfony_app tail -f var/log/dev.log

### trigger command
php bin/console app:test-mailer

### check pending messages
php bin/console messenger:consume async -vv
flush -> php bin/console messenger:reset
### questions
1. what is #[ORM\HasLifecycleCallbacks]?

### check if doctrine detects your entities
- php bin/console doctrine:mapping:info

### Redis
docker exec -it redis redis-cli
KEYS *