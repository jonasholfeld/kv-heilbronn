.PHONY: start serve

PORT ?= 8001

start:
	@trap 'kill 0' INT; \
	php -S 127.0.0.1:$(PORT) -t . kirby/router.php & \
	npm run dev

serve:
	npm run build
	git add .
	git commit -m "local changes"
	git push
	ssh jholfeld@alnilam.uberspace.de 'cd /var/www/virtual/jholfeld/kvheilbronn.jholfeld.uber.space/ && git pull && rm -rf site/cache/*/pages'
