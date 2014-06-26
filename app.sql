use app;

SET NAMES UTF8;

CREATE TABLE `countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `country_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`country_id`),
  CONSTRAINT `cities_fk_country_id` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `city_id` int(10) unsigned NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY(`username`),
  UNIQUE KEY(`email`),
  CONSTRAINT `users_fk_city_id` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `text` varchar(255) DEFAULT NULL,
  `created` datetime,
  PRIMARY KEY (`id`),
  KEY (`user_id`),
  KEY (`created`),
  CONSTRAINT `messages_fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT 'Messages';


INSERT INTO `countries` VALUES (1, 'Russia');
INSERT INTO `countries` VALUES (2, 'Italy');
INSERT INTO `countries` VALUES (3, 'Germany');
INSERT INTO `countries` VALUES (4, 'France');
INSERT INTO `countries` VALUES (5, 'Spain');

INSERT INTO `cities` VALUES (1, 'Moscow', 1);

-- pass - md5 of stex, md5 of petr
INSERT INTO `users` VALUES
(1, 'Ivan', 'Ivanov', 'Ivan', 'ivan@mail.ru', '1', '2f7d85d5a9dd8988479a0bfd109575ed'),
(2, 'Petr', 'Petrov', 'Petr', 'petr@mail.ru', '1', '2f0714f5365318775c8f50d720a307dc')
;
