use monitoramento_agua;

CREATE TABLE IF NOT EXISTS `user` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `drink_counter` int DEFAULT 0,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `drink` (
  `id_drink` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `drink_ml` float NOT NULL,
  `drink_datetime` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_drink`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE drink
	add FOREIGN KEY (id_user)
	REFERENCES `user`(id_user)
  ON DELETE CASCADE;
	
