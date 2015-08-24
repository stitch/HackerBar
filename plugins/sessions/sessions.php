<?php
/**
 * Created by PhpStorm.
 * User: ejonker
 * Date: 2-1-2015
 * Time: 21:25
 * HOE ga ik met 1 regel input dit doen? Is dat wenselijk?
 * HOE geef ik suggesties voor wat moet worden ingevoerd (wat maakt de homepage)
 * HOE geef ik alle info voor op de homepage? Wordt dit via de sync methode? Of iets anders?
 */

// todo: add identicons https://github.com/yzalis/Identicon/
// this is purely a proof of concept. Multi sessions are possible and they should be available in the hackerbar.
// switching between plugins / sessoins and states should happen at hackerbar level, not at plugin level.
class sessions extends DatabasePlugin {

    public function getName(){
        return "session";
    }

    public function getVersion(){
        return "1.0";
    }

    public function getHelp()
    {
        return "<h3>Accepted commands to start plugin</h3>
                <ul>
                    <li>session</li>
                </ul>
                <h3>Functionality</h3>
                <p>Makes it possible to switch between PHP sessions, so to have mutliple transactions running simultaneously</p>
                <h3>Start commands</h3>
                <p>This is a list of all commands that can be used to start this plugin:</p>
                <code>" . implode(", ", $this->getSynonyms()) . "</code>";
    }

    public function run($command)
    {
        // todo: make it possible to switch session without starting this plugin (setting it to memory).
        // this all should be done on a higher level, that of hackerbar, not on the plugin level.
        // we want to switch between sessions at ALL times, and that is not possible when other plugins are running since this functionality can then not be reached.
        // or you should ALWAYS run this plugin before anything else. That is do-able. And a hack :) And this is hackerbar, so that is fine.

        // perform instant switching without starting the plugin, thats not how this works. default = empty. then command.
        if ($this->startsWith($command,"switch")){
            $tmpCommand = trim($this->removeFromStart($command, "switch"));
            $this->switchSession($tmpCommand); // this already started it...
            return array();
        }


        $return = array();

        // start the plugin when the right synonym is called, and it's not started yet
        if (in_array($command, $this->getSynonyms()) && $this->getState() == $this->defaultState) {
            $this->setState("Started!");
        }

        // return a default for when the plugin is not started
        if ($this->getState() == $this->defaultState){
            $return[] = new Command("session","Switch Session <br>curr: ".session_id());
            return $return;
        }
        // the plugin is started

        // add the default session
        if (!$this->isSession(session_id())){
            $this->saveSession(session_id());
        }

        // handle "kill <sessionid>"
        if ($this->startsWith($command, "kill")){
            $tmpCommand = $this->removeFromStart($command, "kill");
            if ($this->isSession($tmpCommand)){
                $this->destroySession($tmpCommand);
            }
        }

        // handle new <sessionid>
        // if the input is unknown, create a new session and switch to that one :)
        $firstWord =  substr($command, 0, (strpos($command, ' ') ? strpos($command, ' ') : strlen($command)));
        if (!$this->isSession($command) && !in_array($firstWord,["kill", "switch","add","new","inspect","debug","session"])){
            $this->saveSession($command);
        }

        // handle <sessionid>
        // switch to a session, this means this plugin is closed, because the session was changed. session with session is special because of plugin name.
        if ($this->isSession($command) && session_id() != $command && $command != "session"){
            $this->switchSession($command);
        }

        // listing current sessions
        $sessions = $this->getSessions();
        $return[] = new Command(session_id(),"Current session:  ".session_id()." <br />size: ".strlen(print_r($_SESSION, 1)));
        foreach($sessions as $session){
            $return[] = new Command($session["id"],"Switch to: <br />".$session["id"]);
        }


        // default actions
        $return[] = new Hint("Type a session to switch, a new name for a new session or kill <session> to quit one.");

        // get current plugin info, during this plugin, it will probably be session... // be able to ask some current stats of a stored object... (hard?)
        // these values might not be set with new sessions...
        if (isset($_SESSION["runningPlugin"])) {
            $runningPlugin = unserialize($_SESSION["runningPlugin"]);
            if ($runningPlugin) {
                $return[] = new Value("Running plugin", $runningPlugin->getName());
            }
        }
        if (isset($_SESSION["pluginState"])) {
            $return[] = new Value("Plugin state:", $_SESSION["pluginState"]);
        }

        return $return;
    }

    function isSession($sessionId){
        return $this->DB->query("select 1 from session where id = %s",$sessionId);
    }

    // you need to store the entire sesison in th edb, otherwise you have to switch between them to show what's in them..
    function getSessions(){
        return $this->DB->query("select * from session");
    }

    // todo: add valudation:  a-z, A-Z, 0-9 and '-,' and NOT EMPTY!
    function saveSession($sessionId) {
        if (!$this->isValidSessionId($sessionId)) return false;
        $this->DB->insert("session", array(
            "id" => $sessionId,
            "created" => DB::sqleval("NOW()")));
    }

    function isValidSessionId($sessionId)
    {
        if (strlen($sessionId) < 1){
            return false;
        }
        return (preg_match('/^[a-zA-Z0-9-,]+$/', $sessionId) === 1) ? true : false;
    }

    function destroySession($sessionId){
        $currentSessionId = session_id(); // switch back to this one after deleting it, unless this is the one being destroyed, then switch to the oldest one, if there is none, then generate one.

        // when the session is the current session
        if ($sessionId == $currentSessionId){
            session_destroy();
            $this->DB->query("delete from session where id = %s",$sessionId);
            $newSessionId = $this->getOldestSession();
            if (!$newSessionId){
                session_id("default");
            } else {
                session_id($newSessionId);
            }
            session_start();
            return true;
        }

        // added for readability
        if ($sessionId != $currentSessionId){
            $this->switchSession($sessionId);
            session_destroy();
            $this->DB->query("delete from session where id = %s",$sessionId);
            session_id($currentSessionId);
            session_start();
            return true;
        }

    }

    function getOldestSession(){
        return $this->DB->queryFirstField("select id from session having MIN(created)");

    }

    function switchSession($session){
        if (!$this->isSession($session)) return;
        session_write_close();
        session_id($session);
        session_start();
    }

    /**
     * @return Array() list of synonyms for this plugin. required method by the plugin interface.
     *
     * If you want to start dynamiccaly, you also give all your dynamic values, such as usernames... or whatver is needed to start the plugin.
     */
    public function getSynonyms(){
        return array("session","switch");
    }


}