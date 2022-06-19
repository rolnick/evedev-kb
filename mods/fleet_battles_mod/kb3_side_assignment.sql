CREATE TABLE `kb3_side_assignment` (
  `system_id` int(11) NOT NULL,
  `timestamp_start` datetime NOT NULL,
  `timestamp_end` datetime NOT NULL,
  `entity_id` bigint(20) NOT NULL,
  `entity_type` ENUM('corp', 'ally') NOT NULL,
  `side` ENUM('a', 'e') NOT NULL,
  PRIMARY KEY  (`system_id`, `timestamp_start`, `timestamp_end`, `entity_id`, `entity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;