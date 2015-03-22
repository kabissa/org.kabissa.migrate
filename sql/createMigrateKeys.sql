CREATE TABLE IF NOT EXISTS migrate_keys (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  entity varchar(45) DEFAULT NULL,
  original_id int(10) unsigned NOT NULL,
  entity_id int(10) unsigned NOT NULL,
  created_date date DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;