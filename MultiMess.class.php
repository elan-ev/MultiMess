<?php
/*
 * MultiMess.class.php - MultiMess a BulkMail-Plugin for Stud.IP
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

require_once 'vendor/trails/trails.php';


class MultiMess extends StudipPlugin implements SystemPlugin
{
    /**
     * Initialize a new instance of the plugin.
     */
    function __construct()
    {
        parent::__construct();

        global $SessSemName, $perm;
        
        if($perm->have_perm('admin')) {
            //.. now the subnavi
            $main = new Navigation(_("Massennachrichten"));

            $main->setURL(PluginEngine::getURL('multimess/admin/index'));
    
            /*
            $config = new Navigation('OC Einstellungen');
            $config->setURL(PluginEngine::getURL('opencast/admin/config'));
            $main->addSubNavigation('oc-config', $config);
            */
            
            Navigation::addItem('/start/multimess', $main);
            //Navigation::addItem('/admin/config/oc-config', $config);
            
        }
        /*
         $style_attributes = array(
            'rel'   => 'stylesheet',
            'href'  => $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'] . $this->getPluginPath() . '/stylesheets/oc.css');
         PageLayout::addHeadElement('link',  array_merge($style_attributes, array()));

         $script_attributes = array(
            'src'   => $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'] . $this->getPluginPath() . '/javascripts/application.js');
         PageLayout::addHeadElement('script', $script_attributes, '');
         */
   
    }    

    /**
     * This method dispatches all actions.
     *
     * @param string   part of the dispatch path that was not consumed
     */
    function perform($unconsumed_path)
    {
        $trails_root = $this->getPluginPath();
        $dispatcher = new Trails_Dispatcher($trails_root, NULL, NULL);
        $dispatcher->dispatch($unconsumed_path);
    }
}
