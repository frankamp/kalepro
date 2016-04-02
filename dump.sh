docker run --link kalepro_db_1:db mariadb mysqldump -u root -pexample -h db --databases wordpress > load.sql
