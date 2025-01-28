start php -S localhost:8080
start wsl -d Ubuntu redis-server

@REM for %%f in (ext/redis/*.php) do start php /ext/redis/%%f
