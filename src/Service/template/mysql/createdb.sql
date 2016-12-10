
CREATE DATABASE IF NOT EXISTS `${environment.database.name}` CHARACTER SET 'utf8';
GRANT ALL ON `${environment.database.name}`.* TO '${database.mysql.user}'@'%';
USE `${environment.database.name}`;
