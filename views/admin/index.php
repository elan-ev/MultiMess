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

<?
$infobox_content = array(array(
    'kategorie' => _('Hinweise:'),
    'eintrag'   => array(array(
        'icon' => 'icons/16/black/info.png',
        'text' => _('Geben sie eine Liste von Nutzernamen (username) ein, an die eine Nachricht verschickt werden soll. Die Namen können'
        . 'mit Komma, Semikolon, oder whitespaces getrennt sein.Alternativ k&ouml;nnen Sie sich auch hier (zusätzlich) eine'
        . ' bestimmte Rechtegruppe auswählen, an die eine Nachricht verschickt werden soll, sowie Empfänger anhand einer bestimmten Studieninformation.')
    ))
));

$infobox = array('picture' => 'infobox/messages.jpg', 'content' => $infobox_content);
?>
<h1><?=_("Massenachrichten")?></h1>
<p>

</p>
<form action="<?= PluginEngine::getLink('multimess/admin/addressee_lookup/') ?>" method=post>
    <label for="cand_addressee">
        <?=_("Empfänger:")?>
    </label></br></br></br>
    <div style="clear:both" align="center">
        <div style="float:left;width:45%;">
            <textarea id="cand_addressee" name="cand_addressee" rows="15" cols="40" wrap="virtual"><?=(is_array($addressee_list) ? join("\n", array_keys($addressee_list)): '')?></textarea>
        </div>
        <div align="left">
            <input type="checkbox" name="perms[]" value="admin"> Administratoren<br>
            <input type="checkbox" name="perms[]" value="dozent"> Dozenten<br>
            <input type="checkbox" name="dozent_aktiv" value="dozent_aktiv"> aktive Dozenten<br>
            <input type="checkbox" name="perms[]" value="tutor"> Tutoren<br>
            <br>
            <div>
                <span style="vertical-align:top;padding-right:15px;">Studiengang</span>
                <select name="studiengang[]" size="5" multiple>
                    <? foreach($studycourses as $sg) : ?>
                    <option value="<?=$sg['studiengang_id']?>"><?=$sg['name']?></option>
                    <? endforeach ?>
                </select>
            </div>
            <br>
            Empfänger anhand bestimmter Studieninfos hinzufügen:<br>
            <input type="text" name="datafield"  size="40" value=""><br><br>
            <input type="checkbox" name="locked" value="TRUE"> nur an nicht gesperrte Nutzer<br>
            <!-- <input type="checkbox" name="perms[]" value="autor"> Autoren<br> -->
            <br><br>
        </div>
    </div>
    <br>
    <div class="form_submit">
        <?= makeButton('suchen','input') ?>
        <?//= LinkButton::createCancel(_('Abbrechen'), PluginEngine::getLink('opencast/admin/config/')) ?>
    </div>
</form> 