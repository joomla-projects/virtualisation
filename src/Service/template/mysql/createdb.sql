
CREATE DATABASE IF NOT EXISTS `${mysql.name}` CHARACTER SET 'utf8';
GRANT ALL ON `${mysql.name}`.* TO '${mysql.user}'@'%';
USE `${mysql.name}`;
SET sql_mode = 'ALLOW_INVALID_DATES';
