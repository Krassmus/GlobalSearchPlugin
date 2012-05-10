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
<div style="text-align: center; margin-left: auto; margin-right: auto; margin-bottom: 10px;">
    <form action="?" method="GET">
        <input type="text" name="search" id="search" value="<?= $_SESSION['search_parameter']['search'] ? htmlReady($_SESSION['search_parameter']['search']) : "" ?>" class="bottom">
        <?= new Studip\Button(_("suchen"), array('title' => _("Suche starten"), 'name' => "suchen")) ?>
        <?= new Studip\Button("zurücksetzen", array('title' => _("Suchwort und alle Filter zurücksetzen"), 'name' => "zuruecksetzen")) ?>
    </form>
</div>

<? $open_tab = false ?>
<div id="search_filter">
    <? foreach ($filter as $filter_array) : ?>
    <? if ($filter_array['open']) { 
        $open_tab = $filter_array['id']; 
    } ?>
    <h3 id="<?= htmlReady($filter_array['id']) ?>"><?= $filter_array['header'] ?></h3>
    <div id="<?= htmlReady($filter_array['id']) ?>_content">
        <?= $filter_array['content'] ?>
    </div>
    <? endforeach ?>
</div>
<script>
jQuery(function () {
    jQuery('#search_filter').accordion({
        collapsible: true,
        active: <?= $open_tab ? "'#".$open_tab."'" : "false" ?>
    });
});
</script>

<style>
    ul#searchresults {
        margin: 0px;
        padding: 0px;
        list-style-type: none;
    }
    ul#searchresults > li {
        clear: both;
        margin: 10px;
        margin-left: 0px;
        margin-right: 0px;
        padding: 5px;
        background-color: white;
        border: thin solid #bbbbee;
        box-shadow: inset 0 0 10px 10px #f8f6ff;
    }
    ul#searchresults > li.selected {
        background-color: #f5f5ff;
        box-shadow: inset 0 0 10px 10px #e8e6ff;
        border: thin solid #5555aa;
    }
    ul#searchresults > li.more, ul#searchresults > li.loading {
        text-align: center;
    }
    ul#searchresults > li > div.tools {
        float: right;
        opacity: 0.3;
    }
    ul#searchresults > li > div.tools:hover, ul#searchresults > li.selected > div.tools {
        opacity: 1;
    }
    ul#searchresults > li > div.avatar {
        width: 50px;
        height: 50px;
        background-size: 100%;
        background-position: center center;
        background-repeat: no-repeat;
        float: left;
        margin-right: 5px;
    }
    ul#searchresults > li > div.header {
        font-weight: bold;
        font-size: 1.1em;
    }
</style>

<ul id="searchresults">
<? if (is_array($results)) foreach ($results as $result) : ?>
    <?= $this->render_partial("result.php", array('result' => $result)) ?>
<? endforeach; ?>
<? if ($more) : ?>
    <li class="more">
        ...
    </li>
<? endif ?>
</ul>

<br>

<? if ($time) : ?>
    <hr>
    <?= sprintf(_("Die Suche dauerte %s Millisekunden."),$time) ?>
<? endif ?>

<script>
jQuery(function () {
    jQuery("#search").focus();
    jQuery("ul#searchresults > li").live("click", function (event, element) {
        if (!jQuery(event.target).is("a *, a")) {
            //Wenn nicht auf ein Link geklickt wurde
            jQuery(this).toggleClass('selected');
            event.stopImmediatePropagation();
        }
    });
});
var offset = 0;
jQuery(window.document).bind('scroll', function (event) {
    if ((jQuery(window).scrollTop() + jQuery(window).height() > jQuery(window.document).height() - 500)
            && (jQuery("#searchresults > li.more").length > 0)) {
        //nachladen
        jQuery("#searchresults > li.more").removeClass("more").addClass("loading");
        jQuery.ajax({
            url: "<?= PluginEngine::getURL($plugin, array(), "load")?>",
            data: {
                'offset': offset + 1
            },
            dataType: "json",
            success: function (response) {
                jQuery("#searchresults > li.loading").remove();
                offset++;
                jQuery.each(response.results, function (index, element) {
                    jQuery("#searchresults").append(jQuery(element));
                });
                if (response.more) {
                    jQuery("#searchresults").append(jQuery('<li class="more">...</li>'));
                }
            }
        });
    }
});
</script>

<? 
$infobox = array(
    'picture' => $GLOBALS['ABSOLUTE_URI_STUDIP'].$plugin->getPluginPath()."/img/glasses.png",
    'content' => array(
        array(
            'kategorie' => _("Information"),
            'eintrag' => array(
                array(
                    'icon' => "icons/16/black/info", 
                    'text' => _("Suchen Sie nach Veranstaltungen, Personen, Räumen, Dateien, Forenpostings.")
                ),
                array(
                    'icon' => "icons/16/black/info", 
                    'text' => _("Nutzen Sie die Filtermöglichkeiten oben, um schneller ans Ziel zu kommen. So können Sie alle Veranstaltungen eines Studienbereiches finden.")
                )
            )
        ),
        array(
            'kategorie' => _("Aktionen"),
            'eintrag' => array(
                array(
                    'icon' => "",
                    'text' => _("Wenn Sie mehrere Suchergebnisse durchgehen wollen, klicken Sie auf diese, um sie zu markieren. Öffnen Sie jedes abzuarbeitende Ergebnis in einem neuen Tab und arbeiten Sie so die Liste am elegantesten ab.")
                ),
                $GLOBALS['perm']->have_perm("root")
                ? array(
                    'icon' => "icons/16/black/refresh", 
                    'text' => sprintf(_("%sErstellen%s Sie den Suchindex neu."), '<a href="'.PluginEngine::getLink($plugin, array(), "indexing").'">', '</a>')
                )
                : null
            )
        )
    )
);