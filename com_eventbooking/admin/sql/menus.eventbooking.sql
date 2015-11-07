DROP TABLE IF EXISTS `#__eb_menus`;
CREATE TABLE IF NOT EXISTS `#__eb_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(255) DEFAULT NULL,
  `menu_parent_id` int(11) DEFAULT NULL,
  `menu_link` varchar(255) DEFAULT NULL,
  `published` tinyint(1) unsigned DEFAULT NULL,
  `ordering` int(11) DEFAULT NULL,
  `menu_class` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `#__eb_menus`
--

INSERT INTO `#__eb_menus` (`id`, `menu_name`, `menu_parent_id`, `menu_link`, `published`, `ordering`, `menu_class`) VALUES
(1, 'EB_DASHBOARD', 0, 'index.php?option=com_eventbooking&view=dashboard', 1, 1, 'home'),

(2, 'EB_SETUP', 0, NULL, 1, 2, 'list-view'),
(3, 'EB_CATEGORIES', 2, 'index.php?option=com_eventbooking&view=categories', 1, 1, 'folder-open'),
(4, 'EB_EVENTS', 2, 'index.php?option=com_eventbooking&view=events', 1, 2, 'calendar'),
(5, 'EB_CUSTOM_FIELDS', 2, 'index.php?option=com_eventbooking&view=fields', 1, 3, 'list'),
(6, 'EB_LOCATIONS', 2, 'index.php?option=com_eventbooking&view=locations', 1, 4, 'location'),
(7, 'EB_COUNTRIES', 2, 'index.php?option=com_eventbooking&view=countries', 1, 5, 'flag'),
(8, 'EB_STATES', 2, 'index.php?option=com_eventbooking&view=states', 1, 6, 'book'),

(9, 'EB_REGISTRANTS', 0, 'index.php?option=com_eventbooking&view=registrants', 1, 3, 'user'),

(10, 'EB_COUPONS', 0, NULL, 1, 4, 'tags'),
(11, 'EB_COUPONS', 10, 'index.php?option=com_eventbooking&view=coupons', 1, 1, 'tags'),
(12, 'EB_IMPORT', 10, 'index.php?option=com_eventbooking&view=coupon&layout=import', 1, 2, 'upload'),
(13, 'EB_EXPORT', 10, 'index.php?option=com_eventbooking&task=coupon.export', 1, 3, 'download'),
(14, 'EB_BATCH', 10, 'index.php?option=com_eventbooking&view=coupon&layout=batch', 1, 4, 'list'),

(15, 'EB_PAYMENT_PLUGINS', 0, 'index.php?option=com_eventbooking&view=plugins', 1, 5, 'wrench'),
(16, 'EB_EMAIL_MESSAGES', 0, 'index.php?option=com_eventbooking&view=message', 1, 6, 'envelope'),
(17, 'EB_TRANSLATION', 0, 'index.php?option=com_eventbooking&view=language', 1, 7, 'flag'),
(18, 'EB_CONFIGURATION', 0, 'index.php?option=com_eventbooking&view=configuration', 1, 8, 'cog'),
(19, 'EB_TOOLS', 0, NULL, 1, 9, 'tools'),
(20, 'EB_PURGE_URLS', 19, 'index.php?option=com_eventbooking&task=reset_urls', 1, 1, 'refresh'),
(21, 'EB_FIX_DATABASE', 19, 'index.php?option=com_eventbooking&task=upgrade', 1, 2, 'ok'),
(22, 'EB_SHARE_TRANSLATION', 19, 'index.php?option=com_eventbooking&task=share_translation', 1, 3, 'heart');