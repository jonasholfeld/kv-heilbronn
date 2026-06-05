.PHONY: start

start:
	@trap 'kill 0' INT; \
	php -S 127.0.0.1:8000 -t . kirby/router.php & \
	npm run dev
