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

### Unable to create a signed JWT from the given configuration
docker exec -it symfony_app php bin/console lexik:jwt:generate-keypair --overwrite

### questions
1. what is #[ORM\HasLifecycleCallbacks]?