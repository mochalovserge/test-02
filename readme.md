## Задание Игра "Электрик"

Для установки склонировать репозиторий. Перейти в каталог проекта и от root
выполнить слудующие команды:

`#docker-compose up`
после установки образов и запуска контейнеров необходимо
поменять права доступа к <project dir>/web/var/*
 - `#chmod -R 777 <project dir>/web/var/logs`
 - `#chmod -R 777 <project dir>/web/var/session`
 - `#chmod -R 777 <project dir>/web/var/cache`
 
Затем запустить миграции через контейнер:
`#docker-compose exec php bash`

затем
`root@319f0f4827a5:/var/www/html# php bin/console doctrine:migration:migrate`

адрес для запуска http://localhost/
