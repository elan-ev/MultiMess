<?php
use Studip\Button, Studip\LinkButton;
?>
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
            Empfänger anhand bestimmter Studieninfos hinzufügen:<br>
            <input type="text" name="datafield"  size="40" value=""><br><br>
            <input type="checkbox" name="locked" value="TRUE"> nur an nicht gesperrte Nutzer<br>
            <!-- <input type="checkbox" name="perms[]" value="autor"> Autoren<br> -->
            <br><br>
        </div>
    </div>
    <br>
    <div class="form_submit">
        <!-- <div class="button-group"> -->
        <?//= \Studip\LinkButton::createCancel(_('Abbrechen'), PluginEngine::getLink('multimess/admin/')) ?>
        <?= \Studip\Button::create(_('Suchen'), 'suchen') ?>
        <!-- </div> -->
    </div>
</form>