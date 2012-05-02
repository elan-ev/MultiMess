<?php
/*
 * admin.php - MultiMess - admin controller
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

require_once 'app/controllers/authenticated_controller.php';
require_once $this->trails_root.'/models/MultiMessModel.php';
require_once $this->trails_root.'/models/MultiMessBulkMail.php';
require_once 'app/models/studycourse.php';
require_once 'lib/classes/UserLookup.class.php';
require_once 'lib/classes/SemesterData.class.php';

class AdminController extends AuthenticatedController
{
    /**
     * Common code for all actions: set default layout and page title.
     */
    function before_filter(&$action, &$args)
    {
        $this->flash = Trails_Flash::instance();
            // set default layout
            $layout = $GLOBALS['template_factory']->open('layouts/base');
            $this->set_layout($layout);
    }
    
    /**
     * This is the default action of this controller.
     */
    function index_action()
    {
        if (isset($this->flash['message'])) {
            $this->message = $this->flash['message'];
        }

        $this->studycourses = StudycourseModel::getStudyCourses();


    }

    function addressee_lookup_action()
    {

        $sem = new SemesterData();
        $this_sem = $sem->getCurrentSemesterData();
        $next_sem =    $sem->getNextSemesterData();


        if (Request::get('locked')) $locked = "AND aum.locked = 0";
        else $locked = NULL;




        if(Request::submitted('suchen')) {
            $cand_addressee_list= Request::get('cand_addressee');
            // (1) check textfield
            if(trim($cand_addressee_list)) {
                $cand_addressees = preg_split("/[\s,;]+/",$cand_addressee_list, -1, PREG_SPLIT_NO_EMPTY);
                $query = "SELECT * FROM auth_user_md5 aum WHERE username IN( ? )";
                $values = array($cand_addressees);
                $this->cand_addresses = MultiMessModel::adressee_search($query, $values, $locked, $this->cand_addresses);
            }
            // (2) check perms
            if($perms = Request::getArray('perms')) {
                $query = "SELECT * FROM auth_user_md5 aum WHERE perms IN( ? )";
                $values = array($perms);
                $this->cand_addresses = MultiMessModel::adressee_search($query, $values, $locked, $this->cand_addresses);
            }
            // (3) check for active lecturers
            if(Request::submitted('dozent_aktiv')) {
                $query = "SELECT * FROM auth_user_md5 aum LEFT JOIN  seminar_user AS su USING (user_id)"
                    . " LEFT JOIN seminare AS s USING ( Seminar_id ) WHERE s.start_time IN (?,?) AND su.status='dozent'";
                $values = array($this_sem['beginn'], $next_sem['beginn']);
                $this->cand_addresses = MultiMessModel::adressee_search($query, $values, $locked, $this->cand_addresses);
            }
            // (4) check studiengang
            if($cand_sg = Request::getArray('studiengang')) {
                foreach($cand_sg as $sg) {
                    $lookup = new UserLookup;
                    $lookup->setFilter('fach',$sg);
                    $sg_users[] = $lookup->execute();
                }
                if(is_array($sg_users)) {
                    foreach($sg_users as $user) {
                        $query = "SELECT * FROM auth_user_md5 aum WHERE user_id IN( ? )";
                        $values = array($user);
                        $this->cand_addresses = MultiMessModel::adressee_search($query, $values, $locked, $this->cand_addresses);
                    }
                }
            }
            // (5)...
            if($study_info = Request::get('datafield')) {
                $study_info = sprintf('%%%s%%',implode('%',explode(' ',$study_info)));
                $query = "SELECT * FROM auth_user_md5 AS aum LEFT JOIN datafields_entries AS de ON ( aum.user_id = de.range_id )"
                       . "LEFT JOIN datafields AS df USING ( datafield_id ) WHERE df.name='Studieninfo' AND de.content LIKE '?'";
                $values = array($study_info);
                $this->cand_addresses = MultiMessModel::adressee_search($query, $values, $locked, $this->cand_addresses);
            }


            if(sizeof($this->cand_addresses) > 0) {
                $this->flash['messages'] = array('success' => sprintf(_("Es wurden %s Nutzer gefunden"), sizeof($this->cand_addresses)));
                $this->flash['cand_adresses'] = $this->cand_addresses;
                $this->redirect(PluginEngine::getLink('multimess/admin/compose'));
            } else {
                $this->flash['messages'] = array('info' => _("Es wurden keine Nutzer gefunden"));
                $this->redirect(PluginEngine::getLink('multimess/admin/index'));
            }

        } else {
            $this->flash['messages'] = array('info' => _("Bitte geben Sie ein Suchkriterium ein"));
            $this->redirect(PluginEngine::getLink('multimess/admin/index'));


        }


    }

    function compose_action(){
        if (isset($this->flash['message'])) {
            $this->message = $this->flash['message'];
        }
        $this->cand_addresses = $this->flash['cand_adresses'];

    }

    /**
     * send_action
     */
    function send_action()
    {
        //take care of addressees
        $query = "SELECT * FROM auth_user_md5 aum WHERE user_id IN( ? )";
        $values = array(Request::getArray('addressees'));

        $this->flash['cand_adresses'] = MultiMessModel::adressee_search($query, $values);
        if(Request::submitted('addresser_search')) {
            $cand_addresser = MultiMessModel::adresser_search(Request::get('addresser'));
            $this->flash['cand_addresser'] = array_diff_assoc($cand_addresser, $this->flash['cand_adresses']);
            $this->redirect(PluginEngine::getLink('multimess/admin/compose',array('addresser' => true)));
        } else if (Request::submitted('absenden')){
            if(Request::submitted('subject') && Request::submitted('message')) {
                $subject = Request::get('subject');
                $message = Request::get('message');
            } else {
                $this->flash['messages'] = array('info' => _("Bitte geben Sie ein Betreff und Nachricht ein."));
                $this->redirect(PluginEngine::getLink('multimess/admin/compose'));
            }


            if(Request::submitted('addresser')) {
                $addresser = Request::get('addresser');
            } else {
                $addresser = '____%system%____';
            }

            $bm = new MultiMessBulkMail();
            foreach($this->flash['cand_adresses'] as $addresse) {
                $bm->sendingEmail($addresse['user_id'], $addresser, $message, $subject, md5(uniqid("rockandroll")));
            }
            $bm->bulkSend();

            $this->flash['messages'] = array('success' => sprintf(_("Es wurde eine Nachricht an %s Empfänger geschickt."), sizeof($this->flash['cand_adresses'])));


            $this->redirect(PluginEngine::getLink('multimess/admin/index'));


        }

    }
}
