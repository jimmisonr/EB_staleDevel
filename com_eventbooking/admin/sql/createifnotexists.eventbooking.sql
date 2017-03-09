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
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_id` int(11) NOT NULL DEFAULT '0',
  `event_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)CHARACTER SET `utf8`;
CREATE TABLE IF NOT EXISTS `#__eb_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_type` varchar(50) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `sent_to` tinyint(4) NOT NULL DEFAULT '0',
  `email` varchar(100) DEFAULT '0',
  `subject` varchar(255) DEFAULT NULL,
  `body` text,
  PRIMARY KEY (`id`)
) CHARACTER SET `utf8`;