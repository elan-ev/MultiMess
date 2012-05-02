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

<div style="margin: 0 10px;">
    <table width="100%" cellspacing="0" cellpadding="2" border="0">
        <? foreach($cand_addresser as $addr) : ?>
        <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
            <td align="center">
                <input type="radio" name="addresser" value="<?=$addr['user_id']?>">
            </td>
            <td width="30px">
                <a href="<?= URLHelper::getLink('about.php?username=' . $addr['username']) ?>">
                    <?= Avatar::getAvatar($addr['user_id'])->getImageTag(Avatar::SMALL) ?>
                </a>
            </td>
            <td width="100%">
                <a href="<?= URLHelper::getLink('about.php?username=' . $addr['username']) ?>">
                    <?= htmlReady($addr['Nachname'].','.$addr['Vorname'].' ('.$addr['perms'].')') ?>
                </a>
            </td>
        </tr>
        <? endforeach ?>
    </table>
</div>