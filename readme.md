symfony server:start
http://127.0.0.1:8000

JWT authentication

create an entity:
php bin/console make:user

password hasher
php bin/console security:hash-password

check routes
php bin/console debug:router