# Makefile dla projektu Laravel z Docker Compose

# Zmienne
DC = docker-compose
APP = app-ai

# Domyślny cel
.PHONY: help
help:
	@echo "Dostępne komendy:"
	@echo "  make up       			- Uruchamia wszystkie usługi w tle"
	@echo "  make down     			- Zatrzymuje i usuwa wszystkie usługi"
	@echo "  make build    			- Buduje obrazy Docker"
	@echo "  make tinker   			- Uruchamia Tinker w kontenerze app-ai"
	@echo "  make migrate  			- Wykonuje migracje bazy danych"
	@echo "  make migrate-rollback  - Wykonuje migracje bazy danych w dół"
	@echo "  make logs     			- Wyświetla logi wszystkich usług"
	@echo "  make shell    			- Otwiera powłokę w kontenerze app-ai"
	@echo "  make install  			- Wykonuje composer install"
	@echo "  make update   			- Wykonuje composer update"
	@echo "  make dump     			- Wykonuje composer dump-autoload"
	@echo "  make clear    			- Czyści pamięć podręczną aplikacji"
	@echo "  make check   			- Sprawdza styl kodu (Pint, tryb testowy)"
	@echo "  make fix      			- Poprawia styl kodu (Pint)"

# Uruchomienie usług
.PHONY: up
up:
	$(DC) up -d

# Zatrzymanie i usunięcie usług
.PHONY: down
down:
	$(DC) down

# Budowanie obrazów
.PHONY: build
build:
	$(DC) up --build -d

# Wykonanie composer install
.PHONY: install
install:
	$(DC) exec $(APP) composer install

# Wykonanie composer update
.PHONY: update
update:
	$(DC) exec $(APP) composer update

# Wykonanie composer dump-autoload
.PHONY: dump
dump:
	$(DC) exec $(APP) composer dump-autoload

# Uruchomienie Tinker
.PHONY: tinker
tinker:
	$(DC) exec $(APP) php artisan tinker

# Wykonanie migracji
.PHONY: migrate
migrate:
	$(DC) exec $(APP) php artisan migrate

# Wykonanie migrate:rollback
.PHONY: migrate-rollback
migrate-rollback:
	$(DC) exec $(APP) php artisan migrate:rollback

# Wykonanie czyszczenia cache
.PHONY: clear
clear:
	$(DC) exec $(APP) php artisan optimize:clear

# Wykonanie serve
.PHONY: serve
serve:
	$(DC) exec $(APP) php artisan serve

# Wyświetlanie logów
.PHONY: logs
logs:
	$(DC) logs -f

# Otwarcie powłoki w kontenerze
.PHONY: shell
shell:
	$(DC) exec $(APP) bash

# Sprawdzanie stylu kodu
.PHONY: check
check:
	$(DC) exec $(APP) vendor/bin/pint --test

# Poprawianie stylu kodu
.PHONY: fix
fix:
	$(DC) exec $(APP) vendor/bin/pint
