# Examples

start-db: ## Start the database
	docker-compose up -d db
	docker-compose run --rm netcat

morphism: ## Populate the schema for the database
morphism: start-db
	docker-compose run --rm morphism diff /config/morphism.yml --apply-changes=yes

seed: ## Seed the data for the database
seed: start-db
	docker-compose run --rm sprout seed the_schema -v

seed-group: ## Seed the data in a group for a database
seed-group: start-db
	docker-compose run --rm sprout seed --group=core -v

chop: ## Truncate the data in the database
chop: start-db
	docker-compose run --rm sprout chop the_schema:country -v

chop-group: ## Truncate the data in the database
chop-group: start-db
	docker-compose run --rm sprout chop --group=core -v

dump: ## Dump the data from a table
dump: start-db
	docker-compose run --rm sprout dump the_schema -v

dump-group: ## Dump the data from a table
dump-group: start-db
	docker-compose run --rm sprout dump --group=core the_schema -v
