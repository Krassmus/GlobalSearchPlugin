<?php

interface StudIPIndex {
    /**
     * @param entry_id : string MD5 hash_id of item
     * @param type : string for type of item i.e. "seminar" for Seminar_id, "user", etc. . Plugins can provide their own type if wanted
     * @param title : string of Title
     * @param url_of_item : url you should usually get to the item if you click on a searchresult
     * @param range_id : has it got any range_id as Seminar_id or user_id? Important for rights
     * @param indexstring : string in that is going to be search (with title). Should be a list of useful words
     * @param presentation : string of HTML that should be displayed as the search result
     */
	public function setEntry($entry_id, $type, $title, $url_of_item, $range_id, $indexstring, $presentation);
	public function deleteEntry($entry_id, $type);
	/**
	 * returns an array of associated arrays as the result in order of relevance
	 */
	public function search($searchterm, $range_ids = array(), $type = null, $filter = array(), $offset = 0, $limit = null);
}

class StudIPMysqlIndex implements StudIPIndex {
	
	public function setEntry($entry_id, $type, $title, $url_of_item, $range_id, $indexstring, $presentation) {
		if (!$range_id) {
            $range_id = null;
        }
		$db = DBManager::get();
        $statement = $db->prepare(
			"INSERT IGNORE INTO searchindex " .
			"SET entry_id = :entry_id, " .
				"title = :title, " .
				"type = :type, " .
				"url = :url, " .
                "range_id = :range_id, " .
				"indexstring = :indexstring, " .
				"presentation = :presentation " .
			"ON DUPLICATE KEY UPDATE " .
				"title = :title, " .
				"url = :url, " .
                "range_id = :range_id, " .
				"indexstring = :indexstring, " .
				"presentation = :presentation " .
		"");
		return (bool) $statement->execute(array(
			'entry_id' => $entry_id,
			'title' => $title,
			'type' => $type,
			'url' => $url_of_item,
			'range_id' => $range_id,
			'indexstring' => $indexstring,
			'presentation' => $presentation
		));
	}
	
	public function deleteEntry($entry_id, $type) {
		$db = DBManager::get();
		$statement = $db->prepare(
			"DELETE FROM searchindex " .
			"WHERE entry_id = :entry_id " .
				"AND type = :type " .
		"");
		return (bool) $statement->execute(array(
			'entry_id' => $entry_id, 
			'type' => $type
		));
	}
    
    public function search($searchterm, $range_ids = array(), $type = null, $filter = array(), $offset = 0, $limit = null) {
        $db = DBManager::get();
        if (strpos($searchterm, " ") === false) {
            $searchterm = "+".$searchterm;
        }
        if (count($filter)) {
            $searchterm .= " +".implode(" +", $filter);
        }
        if (!$GLOBALS['perm']->have_perm("root")) {
            $permission_filter = (is_array($range_ids) && count($range_ids))
                    ? "AND (range_id IS NULL OR range_id IN (:range_ids)) "
                    : "AND range_id IS NULL ";
        }
        $statement = $db->prepare(
            "SELECT * " .
            "FROM searchindex " .
            "WHERE " .
                "MATCH (title, indexstring) AGAINST (:searchterm IN BOOLEAN MODE) " .
                ($type ? "AND type = :type " : "") .
                $permission_filter .
            "ORDER BY MATCH (title, indexstring) AGAINST (:searchterm IN BOOLEAN MODE) DESC " .
            ($offset || $limit !== null ? "LIMIT ".(int)$offset." ".($limit !== null ? ", ".(int)$limit : "") : "") .
        "");
        $args = array('searchterm' => $searchterm);
        if ($type) {
            $args['type'] = $type;
        }
        if (!$GLOBALS['perm']->have_perm("root") && is_array($range_ids) && count($range_ids)) {
            $args['range_ids'] = $range_ids;
        }
        $statement->execute($args);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}

$GLOBALS['GLOBALSEARCH_INDEX'] = "Mysql";

class Globalsearch {
    protected $index = null;
    static protected $instance = null;
	
	static public function get() {
        if (self::$instance === null) {
            self::$instance = new Globalsearch();
        }
		return self::$instance;
	}
    
    public function __construct() {
        $indextype = "StudIP".$GLOBALS['GLOBALSEARCH_INDEX']."Index";
        $tmp_index = new $indextype();
        if (is_a($tmp_index, "StudIPIndex")) {
            $this->index = $tmp_index;
        } else {
            throw new Exception($indextype." is not derived from interface StudIPIndex.");
        }
    }
	
	public function setEntry($title, $type, $url, $range_id, $indexstring, $presentation, $entry_id = null) {
		$entry_id || $entry_id = md5($type."__".$url);
		$this->index->setEntry($entry_id, $type, $title, $url, $range_id, $indexstring, $presentation);
	}
	
	public function search($term, $range_ids = array(), $type = null, $filter = array(), $offset = 0, $limit = null) {
		return $this->index->search($term, $range_ids, $type, $filter, $offset, $limit);
	}
}