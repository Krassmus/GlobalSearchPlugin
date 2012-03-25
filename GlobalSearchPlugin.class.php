<?php

/*
 *  Copyright (c) 2011  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

require_once dirname(__file__).'/classes/Globalsearch.class.php';

class GlobalSearchPlugin extends StudIPPlugin implements SystemPlugin {
    
    protected $maximum_results = 30;
    
    public function __construct() {
        parent::__construct();
        if (false) {
            //Suche ersetzen
            if (Navigation::hasItem("/search")) {
                $nav = Navigation::getItem("/search");
                $nav->setURL(PluginEngine::getURL($this, array(), "search"));
                foreach ($nav->getSubNavigation() as $name => $nav_object) {
                    $nav->removeSubNavigation($name);
                }
                $tab = new AutoNavigation(_("Suche"), PluginEngine::getURL($this, array(), "search"));
                $nav->addSubNavigation('search', $tab);
            }
        } else {
            //Suche erweitern
            if (Navigation::hasItem("/search")) {
                $nav = Navigation::getItem("/search");
                $nav->setURL(PluginEngine::getURL($this, array(), "search"));
                $tab = new AutoNavigation(_("Suche"), PluginEngine::getURL($this, array(), "search"));
                $nav->addSubNavigation('search', $tab);
            }
        }
        
        //Observer definieren:
        NotificationCenter::addObserver($this, "show_user_avatar", "WillDisplaySearchResultItem");
        NotificationCenter::addObserver($this, "show_seminar_avatar", "WillDisplaySearchResultItem");
        NotificationCenter::addObserver($this, "show_document_avatar", "WillDisplaySearchResultItem");
        
        NotificationCenter::addObserver($this, "add_calculator", "LastAlteringOfSearchResults");
        
        NotificationCenter::addObserver($this, "filter_study_areas", "GlobalSearchFilter");
    }
    
    public function show_user_avatar($eventname, $search_item) {
        if ($search_item->type === "user") {
            $search_item->avatar = Avatar::getAvatar($search_item->entry_id)->getURL(Avatar::MEDIUM);
            $user = new User($search_item->entry_id);
            $search_item->presentation = "<cite>".htmlReady($user->motto)."</cite>";
            $search_item->presentation .= "<div>".htmlReady($user->schwerp)."</div>";
            
            $uname = get_username($search_item->entry_id);
            $search_item->tools[] = '<a href="'.URLHelper::getLink("sms_send.php", array('rec_uname' => $uname)).'" title="'._("Nachricht schreiben").'">'.Assets::img("icons/16/blue/mail.png", array('class' => "text-bottom"))."</a>";
            if ($GLOBALS['perm']->have_perm("root")) {
                $search_item->tools[] = '<a href="'.URLHelper::getLink("dispatch.php/admin/user/edit/".$search_item->entry_id).'" title="'._("Benutzerverwaltung").'">'.Assets::img("icons/16/blue/edit.png", array('class' => "text-bottom"))."</a>";
            } else {
                $search_item->tools[] = '<a href="'.URLHelper::getLink("about.php", array('username' => $uname, 'cmd' => "add_user", 'add_uname' => $uname)).'" title="'._("Als Buddy hinzufügen").'">'.Assets::img("icons/16/blue/add/person.png", array('class' => "text-bottom"))."</a>";
            }
        }
    }
    
    public function show_document_avatar($eventname, $search_item) {
        if ($search_item->type === "document") {
            $db = DBManager::get();
            $dokument = $db->query("SELECT name, url FROM dokumente WHERE dokument_id = ".$db->quote($search_item->entry_id))->fetch(PDO::FETCH_ASSOC);
            $extension = strtolower(substr($dokument['name'], strrpos($dokument['name'], ".") + 1));
            if (in_array($extension, array("jpg","jpeg","gif","png","bmp"))) {
                $search_item->avatar = URLHelper::getURL("sendfile.php", array('type' => $dokument['url'] ? 6 : 0, 'file_id' => $search_item->entry_id, 'file_name' => $dokument['name']));
            }
            $search_item->tools[] = '<a href="'.URLHelper::getURL("sendfile.php", array('force_download' => 1, 'type' => $dokument['url'] ? 6 : 0, 'file_id' => $search_item->entry_id, 'file_name' => $name)).'">'.Assets::img("icons/16/blue/download.png")."</a>";
        }
    }
    
    public function show_seminar_avatar($eventname, $search_item) {
        if ($search_item->type === "seminar") {
            $search_item->avatar = CourseAvatar::getAvatar($search_item->entry_id)->getURL(Avatar::MEDIUM);
            $seminar = new Seminar($search_item->entry_id);
            $date = $seminar->getDatesHTML(array('link_to_dates' => false));
            
            $search_item->presentation = "<div>";
            $key = 0;
            foreach ($seminar->getMembers('dozent') as $dozent) {
                if ($key > 0) {
                    $search_item->presentation .= ", ";
                }
                $search_item->presentation .= '<a href="'.URLHelper::getLink("about.php", array('username' => $dozent['username'])).'">'.htmlReady($dozent['Vorname']." ".$dozent['Nachname']).'</a>';
                $key++;
            }
            $search_item->presentation .= "</div>";
            $search_item->presentation .= "<div>";
            $search_item->presentation .= htmlReady(get_semester($search_item->entry_id).". ");
            if ($date) {
                $search_item->presentation .= _("Termine").": ".$date;
            }
            $search_item->presentation .= "</div>";
            
            if ($GLOBALS['perm']->have_perm("admin")) {
                $search_item->tools[] = '<a href="'.URLHelper::getLink("dispatch.php/course/basicdata/view", array('cid' => $search_item->entry_id)).'" title="'._("Grunddaten bearbeiten").'">'.Assets::img("icons/16/blue/edit.png", array('class' => "text-bottom"))."</a>";
                $search_item->tools[] = '<a href="'.URLHelper::getLink("dispatch.php/course/study_areas/show", array('cid' => $search_item->entry_id)).'" title="'._("Studienbereiche festlegen").'">'.Assets::img("icons/16/blue/grouping.png", array('class' => "text-bottom"))."</a>";
                $search_item->tools[] = '<a href="'.URLHelper::getLink("raumzeit.php", array('cid' => $search_item->entry_id)).'" title="'._("Zeiten/Räume").'">'.Assets::img("icons/16/blue/date.png", array('class' => "text-bottom"))."</a>";
                $search_item->tools[] = '<a href="'.URLHelper::getLink("dispatch.php/course/room_requests", array('cid' => $search_item->entry_id)).'" title="'._("Raumanfragen").'">'.Assets::img("icons/16/blue/room_request.png", array('class' => "text-bottom"))."</a>";
            }
        }
    }
    
    public function add_calculator($eventname, $results) {
        //computational goodie
        if (strpos($_SESSION['search_parameter']['search'], "?>") === false && strpos($_SESSION['search_parameter']['search'], ";") === false) {
            preg_match_all("/(\w+)/", $_SESSION['search_parameter']['search'], $matches);
            $forbidden = false;
            $allowed = array(
                "abs", "acos", "acosh", "asin", "asinh", "atan2", "atan", "atanh", "base_convert", 
                "ceil", "cos", "cosh", "exp", "expm1", "floor", "fmod", "lcg_value", "log10", "log1p", 
                "log", "max", "min", "mt_rand", "mt_srand", "pi", "pow", "rand", "round", "sin", "sinh", 
                "sqrt", "srand", "tan", "tanh");
            $x = false;
            $y = false;
            if ($matches[0] && count($matches[0])) {
                foreach ($matches[0] as $match) {
                    if ($match === "x") {
                        $x = true;
                    } elseif ($match === "y") {
                        $y = true;
                        $forbidden = true;
                    } elseif (!is_numeric($match) && !in_array($match, $allowed)) {
                        $forbidden = true;
                    }
                }
            }
            if (!$forbidden && $x === false) {
                //calculate the term
                try {
                    $value = @eval("return ".$_SESSION['search_parameter']['search'].";");
                    if (is_numeric($value)) {
                        $result_object = new stdClass();
                        $result_object->title = $value;
                        array_unshift($results->results, $result_object);
                    }
                } catch (Exception $e) {
                    //do nothing
                };
            } elseif(!$forbidden && $x === true && $y === false) {
                //2-dimensional graph
                $values = array();
                $keys = array();
                $ymin = 0;
                $ymax = 1;
                for ($i = -10; $i <= 10; $i += 0.125) {
                    $value = @eval("return ".preg_replace("/([^\w]?)x([^\w]?)/", "$1 ".$i." $2", $_SESSION['search_parameter']['search']).";");
                    if (is_numeric($value)) {
                        if (is_finite($value)) {
                            if ($value < $ymin) {
                                $ymin = $value;
                            } elseif($value > $ymax) {
                                $ymax = $value;
                            }
                        }
                        $values[] = is_finite($value) ? (string) $value : null;
                        $keys[] = floor($i) === $i ? (string) $i : "";
                    }
                }
                $result_object = new stdClass();
                $result_object->title = "f(x) = ".$_SESSION['search_parameter']['search'];
                $result_object->presentation = 
                    '<canvas id="search_f_x_graph" width="650" height="300"></canvas>'.
                    '<script>
                        var f_x_data = '.json_encode($values).';
                        var line = new RGraph.Line("search_f_x_graph", f_x_data);
                        line.Set("chart.linewidth", 2);
                        //line.Set("chart.ymin", '.$ymin.');
                        //line.Set("chart.ymax", '.$ymax.');
                        line.Set("chart.xaxispos", "'.($ymin < 0 ? "center" : "bottom").'");
                        line.Set("chart.labels", '.json_encode($keys).');
                        line.Set("chart.shadow", true);
                        line.Set("chart.shadow.color", "rgba(0,0,0,0.2)");
                        line.Set("chart.gutter.left", 50);
                        
                        line.Draw();
                    </script>';
                PageLayout::addHeadElement("script", array('src' => $GLOBALS['ABSOLUTE_URI_STUDIP'].$this->getPluginPath()."/assets/RGraph.common.core.js"), '');
                PageLayout::addHeadElement("script", array('src' => $GLOBALS['ABSOLUTE_URI_STUDIP'].$this->getPluginPath()."/assets/RGraph.line.js"), '');
                
                array_unshift($results->results, $result_object);
            }
        }
    }
    
    public function filter_study_areas($eventname, $filter) {
        $study_areas = TreeAbstract::getInstance('StudipSemTree', false);
        $sem_tree_layer = $study_areas->getKids($_SESSION['search_parameter']['study_area'] ? $_SESSION['search_parameter']['study_area'] : "root");
        $db = DBManager::get();
        $sem_tree_items = array();
        if (is_array($sem_tree_layer)) foreach ($sem_tree_layer as $sem_tree_id) {
            $sem_tree_items[] = $db->query(
                "SELECT sem_tree.sem_tree_id, IF(sem_tree.studip_object_id IS NULL, sem_tree.name, Institute.Name) AS name " .
                "FROM sem_tree " .
                    "LEFT JOIN Institute ON (sem_tree.studip_object_id = Institute.Institut_id) " .
                "WHERE sem_tree.sem_tree_id = ".$db->quote($sem_tree_id)." " .
            "")->fetch(PDO::FETCH_ASSOC);
        }
        $parents = $study_areas->getParents($_SESSION['search_parameter']['study_area'] ? $_SESSION['search_parameter']['study_area'] : "root");
        if ($parents) {
            $parents = array_reverse($parents);
        }
        if ($_SESSION['search_parameter']['study_area']) {
            $parents[] = $_SESSION['search_parameter']['study_area'];
        }
        $breadcrumb = array();
        if (is_array($parents)) foreach ($parents as $sem_tree_id) {
            $breadcrumb[] = $db->query(
                "SELECT sem_tree.sem_tree_id, IF(sem_tree.studip_object_id IS NULL, sem_tree.name, Institute.Name) AS name " .
                "FROM sem_tree " .
                    "LEFT JOIN Institute ON (sem_tree.studip_object_id = Institute.Institut_id) " .
                "WHERE sem_tree.sem_tree_id = ".$db->quote($sem_tree_id)." " .
            "")->fetch(PDO::FETCH_ASSOC);
        }
        $template = $this->getTemplate("study_area_filter.php", null);
        $template->set_attribute("sem_tree_items", $sem_tree_items);
        $template->set_attribute("breadcrumb", $breadcrumb);
        $filter->filter[] = array(
            'id' => "study_areas_filter",
            'header' => _("Filtern nach Studienbereich").($_SESSION['search_parameter']['study_area'] ? " ".Assets::img("icons/16/black/exclaim.png", array('class' => "text-bottom")) : ""),
            'content' => $template->render(),
            'open' => Request::get("study_area") ? true : false
        );
    }
    
    public function filter_object_types($eventname, $filter) {
        $template = $this->getTemplate("object_types_filter.php", null);
        $template->set_attribute("sem_tree_items", $sem_tree_items);
        $template->set_attribute("breadcrumb", $breadcrumb);
        $filter->filter[] = array(
            'id' => "study_areas_filter",
            'header' => _("Filtern nach Studienbereich"),
            'content' => $template->render(),
            'open' => Request::get("object_type") ? true : false
        );
    }
    
    public function search_action() {
        $template = $this->getTemplate("suche.php", "with_infobox");
        $db = DBManager::get();
        if (Request::submitted("zuruecksetzen") || !isset($_SESSION['search_parameter'])) {
            $_SESSION['search_parameter'] = array();
        }
        if (Request::get("clear")) {
            unset($_SESSION['search_parameter'][Request::get("clear")]);
        }
        if (Request::submitted("suchen")) {
            $_SESSION['search_parameter']['search'] = Request::get("search");
        }
        if (Request::get("study_area")) {
            $_SESSION['search_parameter']['study_area'] = Request::get("study_area");
        }
        foreach ($_SESSION['search_parameter'] as $key => $parameter) {
            if (!$parameter) {
                unset($_SESSION['search_parameter'][$key]);
            }
        }
        
        if (count($_SESSION['search_parameter'])) {
            if (!$GLOBALS['perm']->have_perm("root")) {
                $range_ids = $db->query("SELECT Seminar_id FROM seminar_user WHERE user_id = ".$db->quote($GLOBALS['user']->id)." ")->fetchAll(PDO::FETCH_COLUMN, 0);
                $range_ids[] = $GLOBALS['user']->id;
            } else {
                $range_ids = array();
            }
            
            //Suche durchführen:
            $time = microtime();
            $searchstring = $_SESSION['search_parameter']['search'];
            $filter = array();
            
            if ($_SESSION['search_parameter']['study_area']) {
                //Auf Studienbereich einschränken:
                $study_areas = TreeAbstract::getInstance('StudipSemTree', false);
                $path = $study_areas->getParents($_SESSION['search_parameter']['study_area']);
                $filter[] = "sem_tree_".implode("_", $path);
            }
            
            $results = Globalsearch::get()->search($searchstring, $range_ids, null, $filter, 0, $this->maximum_results + 1);
            if (count($results) > $this->maximum_results) {
                array_pop($results);
                $template->set_attribute('more', true);
            }
            //Ergebnisse nochmal prozessieren:
            foreach ($results as $key => $result) {
                $result = (object) $result;
                $result->tools = array();
                NotificationCenter::postNotification("WillDisplaySearchResultItem", $result);
                if (!$result->forbidden) {
                    $results[$key] = $result;
                } else {
                    //Wenn Rechte fehlen, sollte das Objekt nicht angezeigt werden.
                    //Der Observer der Notification schreibt dann: $result->forbidden = true;
                    unset($results[$key]);
                }
            }
            
            //post-search modifying:
            $results = (object) array('results' => $results);
            NotificationCenter::postNotification("LastAlteringOfSearchResults", $results);
            $results = (array) $results->results;
            
            $time = microtime() - $time;
            $template->set_attribute("time", $time);
            
            $template->set_attribute("results", $results);
        }
        
        $filter = new stdClass();
        $filter->filter = array();
        NotificationCenter::postNotification("GlobalSearchFilter", $filter);
        
        $template->set_attribute("filter", $filter->filter);
        $template->set_attribute("plugin", $this);
        echo $template->render();
    }
    
    public function load_action() {
        $db = DBManager::get();
        if (!$GLOBALS['perm']->have_perm("root")) {
            $range_ids = $db->query("SELECT Seminar_id FROM seminar_user WHERE user_id = ".$db->quote($GLOBALS['user']->id)." ")->fetchAll(PDO::FETCH_COLUMN, 0);
            $range_ids[] = $GLOBALS['user']->id;
        } else {
            $range_ids = array();
        }
        $searchstring = $_SESSION['search_parameter']['search'];
        $filter = array();

        if ($_SESSION['search_parameter']['study_area']) {
            //Auf Studienbereich einschrï¿½nken:
            $study_areas = TreeAbstract::getInstance('StudipSemTree', false);
            $path = $study_areas->getParents($_SESSION['search_parameter']['study_area']);
            $filter[] = "sem_tree_".implode("_", $path);
        }

        $output = array('more' => 0);
        $results = Globalsearch::get()->search($searchstring, $range_ids, null, $filter, Request::int("offset") * $this->maximum_results, $this->maximum_results + 1);
        if (count($results) > $this->maximum_results) {
            array_pop($results);
            $output['more'] = 1;
        }
        //Ergebnisse nochmal prozessieren:
        foreach ($results as $key => $result) {
            $result = (object) $result;
            $result->tools = array();
            NotificationCenter::postNotification("WillDisplaySearchResultItem", $result);
            if (!$result->forbidden) {
                $results[$key] = $result;
            } else {
                //Wenn Rechte fehlen, sollte das Objekt nicht angezeigt werden.
                //Der Observer der Notification schreibt dann: $result->forbidden = true;
                unset($results[$key]);
            }
            $template = $this->getTemplate("result.php", null);
            $template->set_attribute('result', $result);
            $output['results'][] = studip_utf8encode($template->render());
        }
        
        echo json_encode($output);
    }
    
    public function indexing_action() {
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        //indexiert Veranstaltungen:
        $db = DBManager::get();
        $count = 0;
        $seminare = $db->query(
            "SELECT Seminar_id, VeranstaltungsNummer, Name, Untertitel, Beschreibung, Sonstiges, Ort, visible FROM seminare " .
        "");
        $index = Globalsearch::get();
        while ($seminar = $seminare->fetch(PDO::FETCH_ASSOC)) {
            $searchtext = $seminar['VeranstaltungsNummer']." ".$seminar['Name']." ".$seminar['Untertitel']." ".$seminar['Beschreibung']." ".$seminar['Sonstiges']." ".$seminar['Ort'];
            $sem_tree_ids = $db->query("SELECT sem_tree_id FROM seminar_sem_tree WHERE seminar_id = ".$db->quote($seminar['Seminar_id']))->fetchAll(PDO::FETCH_COLUMN, 0);
            $searchtext .= GlobalSearchPlugin::getStudyareaSearchstring($sem_tree_ids);
            $index->setEntry(
                $seminar['Name'],
                "seminar",
                "details.php?cid=".$seminar['Seminar_id'],
                $seminar['visible'] ? null : $seminar['Seminar_id'],
                $searchtext,
                CourseAvatar::getAvatar($seminar['Seminar_id'])->getImageTag(Avatar::SMALL)." ".htmlReady($seminar['Name']),
                $seminar['Seminar_id']
            );
            $count++;
        }
        $users = $db->query(
            "SELECT user_id, username, Vorname, Nachname, visible FROM auth_user_md5 " .
        "");
        while ($user = $users->fetch(PDO::FETCH_ASSOC)) {
            $searchtext = $user['Vorname']." ".$user['Nachname'];
            $index->setEntry(
                $user['Vorname']." ".$user['Nachname'],
                "user",
                "about.php?username=".$user['username'],
                ($user['visible'] !== "never" ? null : $user['user_id']),
                $searchtext,
                Avatar::getAvatar($user['user_id'])->getImageTag(Avatar::SMALL). " " .htmlReady($user['Vorname'])." ".htmlReady($user['Nachname']),
                $user['user_id']
            );
            $count++;
        }
        $documents = $db->query(
            "SELECT dokument_id, seminar_id, name, description, filename FROM dokumente " .
        "");
        while ($document = $documents->fetch(PDO::FETCH_ASSOC)) {
            $seminar_name = $db->query("SELECT Name FROM seminare WHERE Seminar_id = ".$db->quote($document['seminar_id'])." ")->fetch(PDO::FETCH_COLUMN, 0);
            
            $searchtext = $document['name']." ".$document['description']." ".$document['filename'];
            $sem_tree_ids = $db->query("SELECT sem_tree_id FROM seminar_sem_tree WHERE seminar_id = ".$db->quote($document['seminar_id']))->fetchAll(PDO::FETCH_COLUMN, 0);
            $searchtext .= GlobalSearchPlugin::getStudyareaSearchstring($sem_tree_ids);
            $index->setEntry(
                $document['name'].($seminar_name ? " in ".$seminar_name : ""),
                "document",
                "folder.php?cid=".$document['seminar_id']."&cmd=all&open=".$document['dokument_id']."#anker",
                $document['seminar_id'],
                $searchtext,
                "<strong>".htmlReady($document['filename'])."</strong>: " .
                    htmlReady($document['description']),
                $document['dokument_id']
            );
            $count++;
        }
        
        $resources = $db->query(
            "SELECT resource_id, name, description FROM resources_objects WHERE category_id != '' " .
        "");
        while ($object = $resources->fetch(PDO::FETCH_ASSOC)) {
            $index->setEntry(
                $object['name'],
                "resource",
                "resources.php?show_object=".$object['resource_id']."&view=view_schedule",
                null,
                $object['name']." ".$object['description'],
                htmlReady($object['description']),
                $object['resource_id']
            );
            $count++;
        }
        
        $postings = $db->query("SELECT topic_id, name, description, user_id, Seminar_id FROM px_topics ");
        while ($posting = $postings->fetch(PDO::FETCH_ASSOC)) {
            $posting_content = preg_replace("/\[quote([=\d\w\s]*)\]([\d\w\s]*)\[\/quote\]/", "", $posting['description']);
            
            $posting_content = strip_tags(formatReady(str_replace("\n", " ", $posting_content)));
            
            $searchtext = get_fullname($posting['user_id'])." ".$posting_content;
            $sem_tree_ids = $db->query("SELECT sem_tree_id FROM seminar_sem_tree WHERE seminar_id = ".$db->quote($posting['Seminar_id']))->fetchAll(PDO::FETCH_COLUMN, 0);
            $searchtext .= GlobalSearchPlugin::getStudyareaSearchstring($sem_tree_ids);
            $index->setEntry(
                $posting['name'],
                "posting",
                "forum.php?cid=".$posting['Seminar_id']."&view=tree&open=".$posting['topic_id']."#anker",
                $posting['Seminar_id'],
                $searchtext,
                $posting_content,
                $posting['topic_id']
            );
            $count++;
        }
        
        $result_object = new stdClass();
        $result_object->count = 0;
        NotificationCenter::postNotification("indexing_plugin_items", $result_object);
        $count += $result_object->count;
        
        if ($GLOBALS['IS_CLI']) {
            echo "Index ersellt mit ".$count." Einträgen.";
        } else {
            $template = $this->getTemplate("indexing.php");
            $template->set_attribute("count", $count);
            $template->set_attribute("plugin", $this);
            echo $template->render();
        }
    }
    
    protected function getDisplayName() {
        return _("Suche");
    }
    
    static public function getStudyareaSearchstring($sem_tree_ids, $depth = true) {
        $db = DBManager::get();
        $searchtext = "";
        $study_areas = TreeAbstract::getInstance('StudipSemTree', false);
        foreach ($sem_tree_ids as $sem_tree_id) {
            $study_area_path = $depth ? (array) $study_areas->getParents($sem_tree_id) : array();
            array_unshift($study_area_path, $sem_tree_id);
            foreach ($study_area_path as $key => $study_area_path_part) {
                $path_part = array_splice($study_area_path, 0, $key + 1);
                $searchtext .= " sem_tree_".implode("_", $path_part);
            }
        }
        return $searchtext;
    }

    protected function getTemplate($template_file_name, $layout = "without_infobox") {
        if (!$this->template_factory) {
            $this->template_factory = new Flexi_TemplateFactory(dirname(__file__)."/templates");
        }
        $template = $this->template_factory->open($template_file_name);
        if ($layout) {
            if (method_exists($this, "getDisplayName")) {
                PageLayout::setTitle($this->getDisplayName());
            } else {
                PageLayout::setTitle(get_class($this));
            }
            $template->set_layout($GLOBALS['template_factory']->open($layout === "without_infobox" ? 'layouts/base_without_infobox' : 'layouts/base'));
        }
        return $template;
    }
    
}