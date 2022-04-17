To create and populate sample database "sakila", run the following:

docker-compose exec <database-service> mysql -u<mysql-user> -p<mysql-password> -e"SOURCE <path-to-sql-file>"