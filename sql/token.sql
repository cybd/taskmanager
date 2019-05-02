CREATE TABLE `token` (
  `id` INT(11) AUTO_INCREMENT,
  `userId` INT(11) NOT NULL,
  `token` CHAR(40) NOT NULL,
  `expireAt` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY(`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;