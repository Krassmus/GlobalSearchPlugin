<?php

class InstallSearchindex extends DBMigration {
    
    function up() {
        $db = DBManager::get();
        $db->exec("
            CREATE TABLE IF NOT EXISTS `searchindex` (
                `entry_id` varchar(32) NOT NULL,
                `title` varchar(256) NOT NULL,
                `type` varchar(64) NOT NULL,
                `url` varchar(1024) NOT NULL,
                `range_id` varchar(32) DEFAULT NULL,
                `indexstring` text NOT NULL,
                `presentation` text NOT NULL,
                PRIMARY KEY (`entry_id`, `type`),
                FULLTEXT KEY `title_indexstring` (`title`,`indexstring`)
            ) ENGINE=MyISAM;
        ");
    }
    
    function down() {
        DBManager::get()->exec("DROP TABLE IF EXISTS `searchindex` ");
    }
}