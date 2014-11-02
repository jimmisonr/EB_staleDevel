DROP TABLE IF EXISTS `#__eb_menus`;
CREATE TABLE IF NOT EXISTS `#__eb_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(255) DEFAULT NULL,
  `menu_parent_id` int(11) DEFAULT NULL,
  `menu_view` varchar(255) DEFAULT NULL,
  `menu_layout` varchar(255) DEFAULT NULL,
  `published` tinyint(1) unsigned DEFAULT NULL,
  `ordering` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) CHARACTER SET `utf8`;

--
-- Dumping data for table `jos_eb_menus`
--

INSERT INTO `#__eb_menus` (`id`, `menu_name`, `menu_parent_id`, `menu_view`, `menu_layout`, `published`, `ordering`) VALUES
(1, 'EB_DASHBOARD', 0, 'dashboard', NULL, 1, 1),
(2, 'EB_SETUP', 0, NULL, NULL, 1, 2),
(3, 'EB_CATEGORIES', 2, 'categories', NULL, 1, 1),
(4, 'EB_EVENTS', 2, 'events', NULL, 1, 2),
(5, 'EB_CUSTOM_FIELDS', 2, 'fields', NULL, 1, 5),
(6, 'EB_LOCATIONS', 2, 'locations', NULL, 1, 6),
(7, 'EB_COUPONS', 2, 'coupons', NULL, 1, 4),
(8, 'EB_REGISTRANTS', 0, 'registrants', NULL, 1, 3),
(9, 'EB_PAYMENT_PLUGINS', 0, 'plugins', NULL, 1, 4),
(10, 'EB_EMAIL_MESSAGES', 0, 'message', NULL, 1, 5),
(11, 'EB_TRANSLATION', 0, 'language', NULL, 1, 6),
(12, 'EB_CONFIGURATION', 0, 'configuration', NULL, 1, 7);