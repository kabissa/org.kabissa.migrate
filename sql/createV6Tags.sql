CREATE TABLE IF NOT EXISTS `v6_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Tag ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of Tag.',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional verbose description of the tag.',
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional parent id for this tag.',
  `is_selectable` tinyint(4) DEFAULT '1',
  `is_reserved` tinyint(4) DEFAULT '0',
  `is_tagset` tinyint(4) DEFAULT '0',
  `used_for` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_date` datetime DEFAULT NULL COMMENT 'Date and time that tag was created.',
  `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this tag',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UI_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=36 ;

--
-- Gegevens worden uitgevoerd voor tabel `v6_tag`
--

INSERT INTO `v6_tag` (`id`, `name`, `description`, `parent_id`, `is_selectable`, `is_reserved`, `is_tagset`, `used_for`, `created_date`, `created_id`) VALUES
  (6, 'salesforce', 'Contacts migrated to CiviCRM from Salesforce in 2007', 28, 1, 1, 0, 'civicrm_contact', NULL, NULL),
  (7, 'all-trainers', 'People originally on the ttgo all-trainers list', 28, 1, 1, 0, 'civicrm_contact', NULL, NULL),
  (8, 'bainbridgepotluckinvitees', NULL, 28, 1, 1, 0, 'civicrm_contact', NULL, NULL),
  (9, 'globalgiving', NULL, 28, 1, 1, 0, 'civicrm_contact', NULL, NULL),
  (11, 'Photo Competition 2011', 'Participants in the Photo Competition 2011', 28, 1, 0, 0, 'civicrm_contact', NULL, NULL),
  (12, '2011fundraisinground1', 'Tobias email to first round of potential donors - people who gave last year', 28, 1, 1, 0, 'civicrm_contact', NULL, NULL),
  (13, 'connectmembers2011', 'Was a member of connect group as of dec 15 2011', 28, 1, 1, 0, 'civicrm_contact', NULL, NULL),
  (14, 'trainersmembers2011', 'Was a member of ict trainers group as of dec 15 2011', 28, 1, 0, 0, 'civicrm_contact', NULL, NULL),
  (15, 'peerlearningmembers2011', 'Was a member of ICT Peer Learning group as of dec 15 2011', 28, 1, 1, 0, 'civicrm_contact', NULL, NULL),
  (16, 'kabissanewsmembers2011', 'Was a member of kabissa news group as of dec 15 2011', 28, 1, 1, 0, 'civicrm_contact', NULL, NULL),
  (20, 'LEAD Fundraising', NULL, 25, 1, 0, 0, 'civicrm_contact', '2012-09-11 17:40:05', 6179),
  (21, 'LEAD Partner', NULL, 25, 1, 0, 0, 'civicrm_contact', '2012-09-11 22:28:55', 6179),
  (22, 'Trust Badges Meeting Abuja November 2012', 'Participants in the Trust Badges Meeting in Abuja Nov 2012', NULL, 1, 1, 0, 'civicrm_contact', '2012-11-10 15:01:41', NULL),
  (24, 'BADGES', 'Container tag for trust badges', NULL, 1, 1, 0, 'civicrm_contact', '2012-12-31 17:26:44', 6179),
  (25, 'LEAD', 'Container Tag for all LEAD tags', NULL, 1, 1, 0, 'civicrm_contact', '2013-01-08 06:17:31', 6179),
  (26, 'LEAD Consulting Client', NULL, 25, 1, 0, 0, 'civicrm_contact', '2013-01-08 06:18:32', 6179),
  (27, 'LEAD Domain Registration Client', NULL, 25, 1, 0, 0, 'civicrm_contact', '2013-01-08 13:04:48', 6179),
  (28, 'Z ARCHIVE', 'Container for old tags no longer being added to', NULL, 1, 1, 0, 'civicrm_contact', '2013-01-08 13:13:39', 6179),
  (29, 'LEAD Organization Directory', 'Contact with organization to add to the directory', 25, 1, 1, 0, 'civicrm_contact', '2013-01-08 13:21:37', 6179),
  (30, 'trust', NULL, NULL, 1, 0, 0, 'civicrm_contact', '2013-02-17 10:40:00', NULL),
  (31, 'bainbridgebrainstorm2013', 'invite to brainstorm meeting on bainbridge', NULL, 1, 0, 0, 'civicrm_contact', '2013-03-22 13:58:05', 6179),
  (32, '2014stakeholdersurvey', NULL, NULL, 1, 0, 0, 'civicrm_contact', '2013-11-20 14:09:38', 6179),
  (33, 'africaroundtable20140319', 'Africa Roundtable with Global Giving 19 March 2014', NULL, 1, 0, 0, 'civicrm_contact', '2014-03-25 12:10:29', 6179),
  (34, 'Registrant to Fall 2014 Webinar Series Engaging Blogging for Afr', NULL, NULL, 1, 0, 0, 'civicrm_contact', '2014-10-13 17:19:28', 7),
  (35, 'ToBeSpamChecked', 'tag created by Erik Hommel to identify newsletter group members that are spam suspect', NULL, 1, 0, 0, 'civicrm_contact', '2014-10-30 16:24:07', 7);

