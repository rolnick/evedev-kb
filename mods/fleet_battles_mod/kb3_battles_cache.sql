-- Host: localhost
-- Generation Time: Aug, 8 2012 at 08:34 PM
-- Server version: 5.0.44
-- PHP Version: 5.2.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

-- 
-- Table structure for table `kb3_battles_cache`
-- 

CREATE TABLE `kb3_battles_cache` (
  `battle_id` int(11) NOT NULL auto_increment,
  `kll_id` int(11) NOT NULL,
  `killisk` bigint(20) NOT NULL,
  `lossisk` bigint(20) NOT NULL,
  `efficiency` float NOT NULL,
  `bar` tinyblob NOT NULL,
  `kills` int(11) NOT NULL,
  `losses` int(11) NOT NULL,
  `involved` int(11) NOT NULL,
  `system` varchar(100) NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `ownersInvolved` int(10) NOT NULL,
  `ownersInvolved` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`battle_id`),
  KEY `start_end` (`end`,`start`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1



CREATE TABLE `kb3_battles_owner_pilots` (
    `battle_id` int unsigned NOT NULL,
    `plt_id` int unsigned NOT NULL,
    PRIMARY KEY (`battle_id`, `plt_id`)
) ENGINE=InnoDB"

