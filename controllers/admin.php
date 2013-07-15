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
            Navigation::activateItem('/messaging/multimess');
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
        $this->studydegrees = StudycourseModel::getStudyDegrees();


    }

    function addressee_lookup_action()
    {

        $sem      = new SemesterData();
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
                $this->cand_addressees = MultiMessModel::adressee_search($query, $values, $locked, $this->cand_addressees);
            }
            // (2) check perms
            if($perms = Request::getArray('perms')) {
                $query = "SELECT * FROM auth_user_md5 aum WHERE perms IN( ? )";
                $values = array($perms);
                $this->cand_addressees = MultiMessModel::adressee_search($query, $values, $locked, $this->cand_addressees);
            }
            // (3) check for active lecturers
            if(Request::submitted('dozent_aktiv')) {
                $query = "SELECT * FROM auth_user_md5 aum LEFT JOIN  seminar_user AS su USING (user_id)"
                    . " LEFT JOIN seminare AS s USING ( Seminar_id ) WHERE s.start_time IN (?,?) AND su.status='dozent' AND aum.perms = 'dozent'";
                $values = array($this_sem['beginn'], $next_sem['beginn']);
                $this->cand_addressees = MultiMessModel::adressee_search($query, $values, $locked, $this->cand_addressees);
            }
            // (4) check studiengang
            if($cand_sg = Request::getArray('studiengang')) {

                $lookup = new UserLookup;
                $lookup->setFilter('fach',$cand_sg);
                $sg_users[] = $lookup->execute();
              
                if(count($sg_users['0'])) {
                    foreach($sg_users as $user) {
                        $query = "SELECT * FROM auth_user_md5 aum WHERE user_id IN( ? )";
                        $values = array($user);
                        $this->cand_addressees = MultiMessModel::adressee_search($query, $values, $locked, $this->cand_addressees);
                    }
                }
            }
            
            // (5) check abschluss
            if($cand_abs = Request::getArray('abschluss')) {
                
                $lookup = new UserLookup;
                $lookup->setFilter('abschluss',$cand_abs);
                $abs_users[] = $lookup->execute();

                
                if(count($abs_users['0'])) {                    
                    foreach($abs_users as $user) {
                        $query = "SELECT * FROM auth_user_md5 aum WHERE user_id IN( ? )";
                        $values = array($user);
                        $this->cand_addressees = MultiMessModel::adressee_search($query, $values, $locked, $this->cv);
                    }
                
                }
            }
            
            
            //
            // (6)... (we don't need this in the moment)
            // if($study_info = Request::get('datafield')) {
            //                 $study_info = sprintf('%%%s%%',implode('%',explode(' ',$study_info)));
            //                 $query = "SELECT * FROM auth_user_md5 AS aum LEFT JOIN datafields_entries AS de ON ( aum.user_id = de.range_id )"
            //                        . "LEFT JOIN datafields AS df USING ( datafield_id ) WHERE df.name='Studieninfo' AND de.content LIKE '?'";
            //                 $values = array($study_info);
            //                 $this->cand_addressees = MultiMessModel::adressee_search($query, $values, $locked, $this->cand_addressees);
            //             }

            if(sizeof($this->cand_addressees) > 0) {
                $this->flash['messages'] = array('success' => sprintf(_("Es wurden %s Nutzer gefunden"), sizeof($this->cand_addressees)));
                $this->flash['cand_addressees'] = $this->cand_addressees;
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
        $this->cand_addressees = $this->flash['cand_addressees'];

    }

    /**
     * send_action
     */
    function send_action()
    {
        //take care of addressees

        $addressees = Request::getArray('addressees');
        if(empty($addressees)) {
            $this->flash['messages'] = array('info' => _("Sie haben keine(n) Empfänger angegeben."));
            $this->redirect(PluginEngine::getLink('multimess/admin/index'));
        } else {
            $query = "SELECT * FROM auth_user_md5 aum WHERE user_id IN( ? )";
            $values = array($addressees);
            $this->flash['cand_addressees'] = MultiMessModel::adressee_search($query, $values);
            if(Request::submitted('addresser_search')) {
                $cand_addresser = MultiMessModel::adresser_search(Request::get('addresser'));
                $this->flash['cand_addresser'] = array_diff_assoc($cand_addresser, $this->flash['cand_addressees']);
                if (sizeof($this->flash['cand_addresser']) == 0) {
                    $this->flash['cand_addresser'] = false;
                    $this->flash['messages'] = array('info' => _("Es wurde kein Nutzer gefunden. Bitte beachten Sie, dass ein Empfänger nicht als Absender angegeben werden kann."));
                } else {
                    $this->flash['messages'] = array('success' => sprintf(_("Es wurden %s Nutzer gefunden."), sizeof($this->flash['cand_addresser'])));
                }
                $this->redirect(PluginEngine::getLink('multimess/admin/compose',array('addresser' => true)));
            } else if (Request::submitted('absenden') && sizeof($this->flash['cand_addressees']) != 0){
                if(Request::submitted('subject') && Request::submitted('message')) {
                    $subject = Request::get('subject');
                    $message = Request::get('message');
                } else {
                    $this->flash['messages'] = array('info' => _("Bitte geben Sie ein Betreff und Nachricht ein."));
                    $this->redirect(PluginEngine::getLink('multimess/admin/compose'));
                }


                if(Request::get('addresser')) {
                    $addresser = Request::get('addresser');

                } else {
                    $addresser = "____%system%____";
                }

                $bm = new MultiMessBulkMail();

                $cand_addressees = $this->flash['cand_addressees'];
                if(count($cand_addressees)) {
                    $bm->insert_message(addslashes($message), array_keys($cand_addressees), $addresser, time(), '', '','',$subject,TRUE);
                }
                $bm->bulkSend();
                $this->flash['messages'] = array('success' => sprintf(_("Es wurde eine Nachricht an %s Empfänger geschickt."), sizeof($this->flash['cand_addressees'])));
                $this->redirect(PluginEngine::getLink('multimess/admin/index'));
            }
        }

    }
}
