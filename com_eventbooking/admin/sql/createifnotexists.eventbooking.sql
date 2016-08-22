CREATE TABLE IF NOT EXISTS `#__eb_discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `event_ids` tinytext,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `from_date` datetime DEFAULT NULL,
  `to_date` datetime DEFAULT NULL,
  `times` int(11) NOT NULL DEFAULT '0',
  `used` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_discount_events` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `discount_id` INT NOT NULL DEFAULT '0',
  `event_id` INT NOT NULL DEFAULT '0',
  PRIMARY KEY(`id`)
)CHARACTER SET `utf8`;