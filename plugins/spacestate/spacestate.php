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

require_once 'Requests.php';

class spacestate extends Plugin {

    public function getName(){
        return "Spacestate";
    }

    public function getVersion(){
        return "1.1";
    }

    public function __construct(){
        Requests::register_autoloader();
    }


    public function getHelp()
    {
        return "<h3>Accepted commands to start plugin</h3>
                <ul>
                    <li>spacestsate</li>
                </ul>
                <h3>Functionality</h3>
                <p>Alters spacestate, compattible with stitch's spacestate, but easilly adjustable to yours </p>
                <h3>Start commands</h3>
                <p>This is a list of all commands that can be used to start this plugin:</p>
                <code>" . implode(", ", $this->getSynonyms()) . "</code>";
    }

    public function run($command)
    {

        // todo: dynamic open or closed in the command button. no: because that happens on every request / abort. that is not funny.
        $return = array();

        // start the plugin when the right synonym is called, and it's not started yet
        if (in_array($command, $this->getSynonyms()) && $this->getState() == $this->defaultState) {
            $this->setState("Started!");
        }

        // return a default for when the plugin is not started
        if ($this->getState() == $this->defaultState){
            $return[] = new Command("spacestate","Space State","s");
            return $return;
        }

        // the plugin is started

        if ($command == "open"){
            $this->openSpace();
        } elseif ($command == "close") {
            $this->closeSpace();
        }

        $return[] = new Command("open","Open the space","o");
        $return[] = new Command("close","Close the space","c");

        // SO XSS! WOW!
        $return[] = new Page($this->iFrame("http://www.awesomespace.nl/state/")); // wat deed ik ookalweer?
        // get a webpage,just like in the iframe stuff... so better make that a default function
        //$page =


        return $return;
    }

    /**
     * @return Array() list of synonyms for this plugin. required method by the plugin interface.
     *
     * If you want to start dynamiccaly, you also give all your dynamic values, such as usernames... or whatver is needed to start the plugin.
     */
    public function getSynonyms(){
        return array("spacestate");
    }

    // key=this+is+awesome%21&message=Your+new+MOTD+here%21&state=1&submit=Open+the+space
    // http://awesomespace.nl/state/index.php
    private function openSpace(){

        $data = array('key' => '', 'state' => 1, 'message' => 'opened by HackerBar', 'submit' => "Open the space");
        $response = Requests::post('http://awesomespace.nl/state/index.php', array(), $data);
        return $response->body;
        // ignore the response body... since we are slackers...
    }

    private function closeSpace(){

        $data = array('key' => '',  'state' => 0, 'message' => 'opened by HackerBar', 'submit' => "Open the space");
        $response = Requests::post('http://awesomespace.nl/state/index.php', array(), $data);
        return $response->body;
        // ignore the response body... since we are slackers...
    }

}