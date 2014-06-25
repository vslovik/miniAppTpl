-- DB
SET NAMES UTF8;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT 'Users';


INSERT INTO `users` VALUES
(1, 'Maria', 'maria@gmail.com'),
(2, 'Cecilia', 'cecilia@gmail.com'),
(3, 'Augusta', 'auguata@gmail.com');
