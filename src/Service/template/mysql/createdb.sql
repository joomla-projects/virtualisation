
CREATE DATABASE IF NOT EXISTS `${database.name}` CHARACTER SET 'utf8';
GRANT ALL ON `${database.name}`.* TO '${mysql.user}'@'%';
USE `${database.name}`;
