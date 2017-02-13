all:
	cd front/; \
	npm install

	cd back/; \
	docker-compose up --no-deps -d php-fpm database; \
	docker exec -ti sandstone-php /bin/bash -c "composer update"; \
	docker exec -ti sandstone-database /bin/bash -c "mysql -u root -proot -e 'create database sandstone;'"; \
	docker exec -ti sandstone-php /bin/bash -c "bin/console orm:schema-tool:create"; \
	docker-compose up -d
