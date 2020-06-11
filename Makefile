## composer-test-packages: Command to install packages required for testing
composer-test-packages:
	@echo "=============Install packages required for testing============"
	composer require fzaninotto/faker --dev --no-suggest --prefer-stable --optimize-autoloader

## run-tests: Command to run all test cases
run-tests:
	@echo "=============Run all test cases============"
	cd tests && php FilterRun filter_check

## phpunit: Run PHPUnit tests
phpunit:
	composer run test

## help: Command to view help
help: Makefile
	@echo
	@echo "Choose a command to run in Expresspay Refine:"
	@echo
	@sed -n 's/^##//p' $< | column -t -s ':' |  sed -e 's/^/ /'
	@echo
