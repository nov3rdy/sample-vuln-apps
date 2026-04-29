.PHONY: start stop restart seed reset test logs health clean

# ─── Docker lifecycle ────────────────────────────────────────────────
start: ## Build and start containers
	docker compose up -d --build

stop: ## Stop and remove containers
	docker compose down

restart: stop start ## Full rebuild
	@echo "Waiting for DB..."
	@sleep 3
	@$(MAKE) seed

# ─── Database ────────────────────────────────────────────────────────
seed: ## Populate database with seed data
	@echo "Seeding database..."
	node src/db/seed.js

reset: ## Wipe and reseed database
	@echo "Resetting database..."
	node src/db/seed.js

# ─── Development ─────────────────────────────────────────────────────
test: ## Run Jest tests
	pnpm test

logs: ## Tail app logs
	docker compose logs -f app

health: ## Check if API is responding
	@curl -sf http://localhost:60011/bookmarks > /dev/null && echo "API is up at http://localhost:60011" || echo "API is not responding"

# ─── Cleanup ─────────────────────────────────────────────────────────
clean: ## Remove containers, volumes, and node_modules
	docker compose down -v
	rm -rf node_modules

# ─── Help ────────────────────────────────────────────────────────────
help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-12s\033[0m %s\n", $$1, $$2}'
