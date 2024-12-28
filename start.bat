start php -S 0.0.0.0:8080
start wsl -d Ubuntu redis-server

@REM for %%f in (ext/redis/*.php) do start php /ext/redis/%%f