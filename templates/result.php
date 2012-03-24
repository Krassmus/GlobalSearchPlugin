<?php

/*
 *  Copyright (c) 2011  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */
?>
<li>
    <? if ($result->avatar) : ?>
    <div class="avatar" style="background-image: url('<?= htmlReady($result->avatar) ?>');"></div>
    <? endif ?>
    <? if (is_array($result->tools) && count($result->tools)) : ?>
    <div class="tools"><?= implode(" ", $result->tools) ?></div>
    <? endif ?>
    <div class="header">
        <a href="<?= URLHelper::getLink($result->url) ?>">
            <? if ($result->icon) : ?>
            <img src="<?= htmlReady($result->icon) ?>">
            <? else : 
            switch ($result->type) {
                case "seminar":
                    echo Assets::img("icons/16/blue/seminar.png", array('class' => "text-bottom", 'title' => _("Veranstaltung")));
                    break;
                case "user":
                    echo Assets::img("icons/16/blue/person.png", array('class' => "text-bottom", 'title' => _("Person")));
                    break;
                case "document":
                    echo Assets::img("icons/16/blue/file.png", array('class' => "text-bottom", 'title' => _("Dokument")));
                    break;
                case "resource":
                    echo Assets::img("icons/16/blue/resources.png", array('class' => "text-bottom", 'title' => _("Raum/Ressource")));
                    break;
                case "posting":
                    echo Assets::img("icons/16/blue/forum.png", array('class' => "text-bottom", 'title' => _("Forenbeitrag")));
                    break;
            }
            endif ?>
            <?= htmlReady($result->title) ?>
        </a>
    </div>
    <div><?= $result->presentation ?></div>
    <div style="clear: both;"></div>
</li>