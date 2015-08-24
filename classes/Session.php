<?php
/**
 * Created by PhpStorm.
 * User: ejonker
 * Date: 3-1-2015
 * Time: 0:34
 *
 * Uses PHP sessions to do magic transactions between requests.
 */

// only one plugin at a time. stored WHAT plugin and it's state. php session management over json tokens.
class Session {

    public function __construct(){
        (!isset($_SESSION["runningPlugin"])) ? $_SESSION["runningPlugin"] = "" : null ;
        (!isset($_SESSION["pluginState"])) ? $_SESSION["pluginState"] = "Done" : "Done" ;
    }

    public function getRunningPlugin(){
        return unserialize($_SESSION["runningPlugin"]);
    }

    public function setRunningPlugin($plugin){
        $_SESSION["runningPlugin"] = serialize($plugin);
        $this->setPluginState($plugin->getState());
    }

    public function getPluginState(){
        return $_SESSION["pluginState"];
    }

    public function setPluginState($state){
        $_SESSION["pluginState"] = $state;
    }

    // we only unset the specific variables for this application.
    public function reset(){
        unset($_SESSION["runningPlugin"]);
        unset($_SESSION["pluginState"]);
    }
}