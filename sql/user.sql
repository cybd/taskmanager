CREATE TABLE `user` (
  `id` INT(11) AUTO_INCREMENT,
  `email` VARCHAR(64) NOT NULL,
  `password` VARCHAR(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;