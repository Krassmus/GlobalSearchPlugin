<style>
    #study_areas_filter_content ul.breadcrumb > li {
        display: inline-block;
        background: url('<?= Assets::image_path("icons/16/grey/arr_1right.png")?>') no-repeat;
        padding-left: 20px;
    }
    #study_areas_filter_content ul.breadcrumb > li:first-child {
        background: none no-repeat;
        padding-left: 0px;
    }
</style>
<ul class="breadcrumb">
    <? foreach ($breadcrumb as $sem_tree_item) : ?>
    <li>
        <a href="<?= URLHelper::getLink("?", ($sem_tree_item['sem_tree_id'] ? array("study_area" => $sem_tree_item['sem_tree_id']) : array('clear' => "study_area"))) ?>"><?= !$sem_tree_item['sem_tree_id'] ? _("Alle Studienbereiche") : htmlReady($sem_tree_item['name']) ?></a>
    </li>
    <? endforeach ?>
</ul>
<ul>
    <? foreach ($sem_tree_items as $sem_tree_item) : ?>
    <li>
        <a href="<?= URLHelper::getLink("?", array("study_area" => $sem_tree_item['sem_tree_id'])) ?>"><?= htmlReady($sem_tree_item['name']) ?></a>
    </li>
    <? endforeach ?>
</ul>