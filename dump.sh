docker run --link recipepro_db_1:db mariadb mysqldump -u root -pexample -h db --databases wordpress > basicdata/load.sql
