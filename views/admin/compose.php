<?
    /*
     * Copyright (C) 2012 - André Klaßen <andre.klassen@elan-ev.de>
     *
     * This program is free software; you can redistribute it and/or
     * modify it under the terms of the GNU General Public License as
     * published by the Free Software Foundation; either version 2 of
     * the License, or (at your option) any later version.
     */
?>

<?= $this->render_partial('feedback', array('messages' => $flash['messages'])) ?>

<script language="JavaScript">
    jQuery(document).ready(function () {
        var strHref = window.location.href;
        if (strHref.indexOf("addresser") == -1) {
            jQuery('#compose-accordion').accordion({
                active: 2
            });
        } else {
            jQuery('#compose-accordion').accordion({
                active: 1
            });
        }
        jQuery('#layout_content').css('overflow', 'hidden');
    });
</script>


<?
$infobox_content = array(array(
    'kategorie' => _('Hinweise:'),
    'eintrag'   => array(array(
        'icon' => 'icons/16/black/info.png',
        'text' => _('Sie können hier eine Nachricht verfassen. Falls Sie die Empfänger überprüfen und bearbeiten wollen,'
                 . ' klicken Sie auf die entsprechende Feldüberschrift. Einen alternativen Absender können Sie bei Bedarfs auch bestimmen.')
    ))
));
$infobox = array('picture' => 'infobox/messages.jpg', 'content' => $infobox_content);

?>

<h1><?=_("Massenachrichten")?></h1>
<form action="<?= PluginEngine::getLink('multimess/admin/send/') ?>" method=post>
<div id="compose-accordion">
    <h3><?=_("Empfänger")?></h3>
    <div class="admin-accordion-content">
        <? if (count($cand_addressees) > 0) : ?>
        <?=$this->render_partial("admin/_addressees", array('cand_addressees' => $cand_addressees));?>
        <? endif; ?>
     </div>
    <h3><?=_("Absender")?></h3>
    <div class="admin-accordion-content">
        <div align="center">
            <? if(!$flash['cand_addresser']) :?>
                <input type="text" name="addresser" value="" style="width: 95%">
                <input name="addresser_search" type="image" src="<?=Assets::image_path('icons/16/blue/search.png')?>">
            <? else : ?>
                  <?=$this->render_partial("admin/_addresser", array('cand_addresser' => $flash['cand_addresser']));?>
            <? endif; ?>
        </div>
    </div>
    <h3><?=_("Nachrichteninhalt")?></h3>
    <div class="admin-accordion-content">
        <div style="padding: 0 25px">
        <label for="subject" style="text-align: left;"><?=_("Betreff")?>:</label>
        <div align="center"><input type="text" name="subject" value=""style="width: 100%"></div>
        <label for="message" style="text-align: left;width:100%"><?=_("Nachricht")?>:</label>
        <div align="center"><textarea name="message" style="width: 100%" cols="80" rows="10" wrap="virtual"></textarea></div>
        </div>
    </div>
</div>
<div class="form_submit">
<<<<<<< HEAD
    <?= makeButton('absenden','input') ?>
    <a href="<?=PluginEngine::getLink('multimess/admin/index')?>"> <?=  makeButton('abbrechen', 'img') ?></a>
=======
    <div class="button-group">
        <?= \Studip\LinkButton::createCancel(_('Abbrechen'), PluginEngine::getLink('multimess/admin/')) ?>
        <?= \Studip\Button::create(_('Absenden'), 'absenden') ?>
    </div>
>>>>>>> master
</div>
</form>
