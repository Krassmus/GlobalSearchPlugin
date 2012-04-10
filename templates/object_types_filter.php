<? 
$object_types = array(
    'seminar' => _("Veranstaltungen"),
    'user' => _("Nutzer"),
    'resource' => _("Raum / Ressource"),
    'document' => _("Dokument"),
    'posting' => _("Forumbeiträge")
);
?>
<div style="text-align: center; margin: 5px;">
    <a href="<?= URLHelper::getLink("?", array('clear' => "type")) ?>"><?= _("alle") ?></a><br>
    <? foreach ($object_types as $type => $type_text) : ?>
    <a href="<?= URLHelper::getLink("?", array('select_type' => $type)) ?>">
        <? if ($_SESSION['search_parameter']['type'] === $type) : ?>
        <span style="font-weight: bold;">
        <? endif ?>
        <?= htmlReady($type_text) ?>
        <? if ($_SESSION['search_parameter']['type'] === $type) : ?>
        <?= Assets::img("icons/16/blue/accept.png", array('class' => "text-bottom")) ?>
        </span>
        <? endif ?>
    </a><br>
    <? endforeach ?>
</div>