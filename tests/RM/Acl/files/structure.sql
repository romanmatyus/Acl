SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `acl`;
CREATE TABLE `acl` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`resource` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
	`privilege` varchar(100) COLLATE utf8_unicode_ci NULL,
	`type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
	`access` tinyint(1) DEFAULT 0 NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `acl_user`;
CREATE TABLE `acl_user` (
	`acl_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	KEY `acl_id` (`acl_id`),
	KEY `user_id` (`user_id`),
	CONSTRAINT `acl_user_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `acl_user_ibfk_3` FOREIGN KEY (`acl_id`) REFERENCES `acl` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `acl_role`;
CREATE TABLE `acl_role` (
	`acl_id` int(11) NOT NULL,
	`role_id` int(11) NOT NULL,
	KEY `acl_id` (`acl_id`),
	KEY `role_id` (`role_id`),
	CONSTRAINT `acl_role_ibfk_4` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `acl_role_ibfk_3` FOREIGN KEY (`acl_id`) REFERENCES `acl` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(20) COLLATE utf8_bin NOT NULL,
	`parent` int(11) DEFAULT NULL,
	PRIMARY KEY (`id`),
	KEY `parent` (`parent`),
	CONSTRAINT `role_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `acl_relation`;
CREATE TABLE `acl_relation` (
	`acl_id` int(11) NOT NULL,
	`name` varchar(20) COLLATE utf8_bin NOT NULL,
	KEY `acl_id` (`acl_id`),
	CONSTRAINT `acl_ibfk_1` FOREIGN KEY (`acl_id`) REFERENCES `acl` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(30) COLLATE utf8mb4_bin NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role` (
	`user_id` int(11) NOT NULL,
	`role_id` int(11) NOT NULL,
	KEY `user_id` (`user_id`),
	KEY `role_id` (`role_id`),
	CONSTRAINT `user_role_ibfk_4` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `user_role_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `book`;
CREATE TABLE `book` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(100) COLLATE utf8mb4_bin NOT NULL,
	`author` int(11) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `author` (`author`),
	CONSTRAINT `book_ibfk_3` FOREIGN KEY (`author`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `book_resource`;
CREATE TABLE `book_resource` (
	`acl_id` int(11) NOT NULL,
	`resource_id` int(11) NOT NULL,
	KEY `acl_id` (`acl_id`),
	KEY `resource_id` (`resource_id`),
	CONSTRAINT `book_resource_ibfk_4` FOREIGN KEY (`resource_id`) REFERENCES `book` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `book_resource_ibfk_3` FOREIGN KEY (`acl_id`) REFERENCES `acl` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `user_book`;
CREATE TABLE `user_book` (
	`user_id` int(11) NOT NULL,
	`book_id` int(11) NOT NULL,
	KEY `user_id` (`user_id`),
	KEY `book_id` (`book_id`),
	CONSTRAINT `user_book_ibfk_4` FOREIGN KEY (`book_id`) REFERENCES `book` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `user_book_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
