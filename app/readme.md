clone repository https://github.com/CIIS-ZCMC/zcmc-erp-server.git

cd zcmc-erp-server

composer install

php artisan migrate

php artisan import:all

php artisan db:seed

php artisan serve

documentation (host:port/api-docs)
