<?php
/*
 * MultiMessModel.php - MultiMess DB-abstraction
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Klaßen <andre.klassen@elan-ev.de>
 * @copyright   2012 ELAN e.V. <http://www.elan-ev.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
    class MultiMessModel {

        static function adressee_search($query,$values=array(), $locked='', $cand_addressees = array() ) {
            $stmt = DBManager::get()->prepare($query.$locked);
            $stmt->execute($values);
            while($cand_addressee = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cand_addressees[$cand_addressee['username']] = $cand_addressee;
                $cand_addressees[$cand_addressee['username']]['selected'] = true;
            }

            return $cand_addressees;
        }

        static function adresser_search($cand_addresser) {
            $pdo = DBManager::get();
            $searchstring = $pdo->quote('%' . $cand_addresser . '%');
            $stmt = $pdo->query("SELECT *"
                . " FROM auth_user_md5"
                . " LEFT JOIN user_info USING (user_id)"
                . " WHERE perms NOT IN('root', 'admin')"
                . " AND (username LIKE $searchstring OR Vorname LIKE $searchstring"
                . " OR Nachname LIKE $searchstring)"
                . " LIMIT 500");

            while ($data = $stmt->fetch()) {
                $results[$data['username']] = $data;
            }

            return $results;
        }

    }

?>