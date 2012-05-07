#!/usr/bin/php -q
<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

require_once dirname(__FILE__) . '/studip_cli_env.inc.php';

require_once 'lib/plugins/core/StudIPPlugin.class.php';
if (file_exists(dirname(__FILE__) . '/../public/plugins_packages/data-quest/GlobalSearchPlugin/GlobalSearchPlugin.class.php')) {
    include_once dirname(__FILE__) . '/../public/plugins_packages/data-quest/GlobalSearchPlugin/GlobalSearchPlugin.class.php';
} else {
    echo _("Fehler: Plugin zum Importieren ist nicht installiert oder am falschen Ort.");
    exit;
}

$GLOBALS['IS_CLI'] = true;

echo sprintf(_("Um %s faengt ein neuer Durchlauf des Importscripts an."), date("H:i:s")._(" Uhr am ").date("j.n.Y"))."\n";

$plugin = new GlobalSearchPlugin();
$plugin->indexing_action();
echo "\n";

echo sprintf(_("Um %s hoert der Durchlauf des Importscripts auf."), date("H:i:s")._(" Uhr am ").date("j.n.Y"))."\n";
