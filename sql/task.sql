CREATE TABLE `task2` (
  `id` INT(11) AUTO_INCREMENT,
  `title` VARCHAR(64) NOT NULL,
  `userId` INT(11) NOT NULL,
  `status` TINYINT NOT NULL,
  `priority` TINYINT NOT NULL,
  `dueDate` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`dueDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;