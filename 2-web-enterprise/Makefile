.PHONY: start stop restart seed reset test logs health clean install

# ─── Docker lifecycle ────────────────────────────────────────────────
start: ## Build and start containers
	docker compose up -d --build

stop: ## Stop and remove containers
	docker compose down

restart: stop start ## Full rebuild
	@echo "Waiting for DB..."
	@sleep 5
	@$(MAKE) seed

# ─── Database ────────────────────────────────────────────────────────
seed: ## Populate database with seed data
	@echo "Seeding database..."
	docker compose exec -T app php db/seed.php

reset: ## Wipe and reseed database
	@echo "Resetting database..."
	docker compose exec -T db mysql -uroot -proot -e "DROP DATABASE IF EXISTS companyhub; CREATE DATABASE companyhub;"
	docker compose exec -T db sh -c "mysql -uroot -proot companyhub < /docker-entrypoint-initdb.d/01-init.sql"
	$(MAKE) seed

# ─── Development ─────────────────────────────────────────────────────
install: ## Install Composer dependencies inside the app container
	docker compose exec -T app composer install --no-security-blocking

test: ## Run PHPUnit tests
	docker compose exec -T app vendor/bin/phpunit --colors=always

logs: ## Tail web + app logs
	docker compose logs -f web app

health: ## Check if app is responding
	@curl -sf http://localhost:60012/login > /dev/null && echo "App is up at http://localhost:60012" || echo "App is not responding"

# ─── Cleanup ─────────────────────────────────────────────────────────
clean: ## Remove containers, volumes, and vendor
	docker compose down -v
	rm -rf vendor

# ─── Help ────────────────────────────────────────────────────────────
help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-12s\033[0m %s\n", $$1, $$2}'
