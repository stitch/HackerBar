<?php
/**
 * Created by PhpStorm.
 * User: ejonker
 * Date: 2-1-2015
 * Time: 22:03
 * Er is maar 1 module die tegelijkertijd aan het runnen is. Dus de inputhandler mag je daarin helpen op het hoogste niveau
 * maar daarna moet de module zelf dingen gaan teruggeven en verwerken. Dus afhankelijk van de runnende plugin kunnen we iets doen.
 * Of een plugin runt halen we uit??...
 *
 * Uit een plugin krijg je terug: een lijst met dto's van verschillende typen. De GUI mag uitmaken waar e.e.a. komt te staan.
 * Typen beschikbaar zijn afgeleid van het type input (het is nu mogelijk om het in te voeren). Er zijn verschllende smaakjes denk ik.
 */
//print session_id();
session_id("asdf");
session_start(); // supporting sessions over ajax requests (using "tokens")

// this is what you should edit.
require_once("configuration.php");

require_once("classes/inputHandler.php");
require_once("classes/Session.php");
require_once("classes/meekrodb.2.3.class.php");
require_once("plugins/Plugin.php");
require_once("plugins/DatabasePlugin.php"); // that has database functions and a pending connection

// ui
require_once("classes/Command.php"); // a simple command, no specialties.
require_once("classes/AccountCommand.php"); // this is information about an account
require_once("classes/InvolvedAccountCommand.php"); // in some plugins an operation takes place on these accounts
require_once("classes/FinalCommand.php");
require_once("classes/ArbitraryHtml.php");
require_once("classes/BackgroundHtml.php");
require_once("classes/Value.php");
require_once("classes/Suggestion.php");
require_once("classes/ChartDataset.php");
require_once("classes/Dataset.php");
require_once("classes/Hint.php");
require_once("classes/Page.php");
require_once("classes/Image.php");


// store
require_once("classes/Product.php");
require_once("classes/ShoppingCart.php");
require_once("classes/ProductCommand.php"); // a simple command, no specialties.



$HackerBar = new HackerBar();
$output = $HackerBar->handleCommand((isset($_POST["input"]) ? $_POST["input"] :""));
print json_encode($output, JSON_PRETTY_PRINT);

// The HackerBar only returns JSON. The client should be doing something to interpret it.
// The only thing returned is JSON of a (subtype) of input. This can be arranged however the client desires it.

class HackerBar {
    private $configuration = null; // object, list of configured plugins, applications and database settings
    private $loadedPlugins = null; // array, list of all available plugins
    private $inputHandler = null; // object, determines what module to start
    private $session = null; // object, preserves some variables across requests

    /**
     * IN: a command, text, can be a lot of things, determined by the running plugin what it is.
     * OUT: an array of "actions" or other display stuff that the front-end should be able to render (with the use of plugins)
     */
    function __construct()
    {
        $this->configuration = new Configuration();
        $this->session = new Session(); // supporting sessions over json using post parameters.
        $this->loadedPlugins = $this->loadPlugins();
        $this->inputHandler = new inputHandler($this->loadedPlugins);
    }

    public function handleCommand($input = ""){
        //$input = strtolower(trim($input)); // everything in the system is lowercase. Also complete sentences asked elsewhere.
        $input = trim($input); // all lowercase is a misfeature and makes the system weird.

        // store all commands, whatever it may be
        $this->storeCommand($input);

        $commands = Array();

        // this is usually run on first load
        // usually run when aborting the current operation
        if ($input == "" || $input == "abort"){
            $this->session->reset();
            return $this->getHomeScreen();
        }


        $plugin = $this->session->getRunningPlugin();

        // check if this plugin is allowed to be used.
        if (!empty($plugin)) {
            // prevent execution of unloaded plugins (in essense, some files)
            if (!$this->isConfiguredPlugin($plugin)) {
                print "Tried to run a plugin that is not loaded of type ".htmlentities(get_class ($plugin));
                $this->session->reset();
                return $this->getHomeScreen();
            }
        }

        $returned = Array();
        if (!empty($plugin)){
            // perform the next step of the plugin...
            if ($this->session->getPluginState() != "Done")
            {
                // handle the input by the plugin
                $output = $plugin->run($input); // many commands, outputs and junk can be returned
                $returned = $this->sortPluginOutput(array(),$output);

                // update the plugin and store it's state for the next input.
                $this->session->setRunningPlugin($plugin);
            }

            // todo: zoeken enzo hier implementeren... tenzij de plugin dit zelf al doet natuurlijk...

            // this is the wizard / multiple step done. this resets the homescreen just as with the singe one command below.
            if ($this->session->getPluginState() == "Done") {
                $homeScreenCommands = $this->getHomeScreen();
                $returned["Command"] = Array(); // clear all commands, since it is done. Do the rest.
                $returned = $this->sortPluginOutput($homeScreenCommands,$returned);
                $this->session->reset(); // done!
            }


        } else {

            // if there is not a plugin started, then start one in a new session
            $plugin = $this->inputHandler->parse($input);
            // would get_class also just give the Plugin back?
            if (in_array(get_parent_class($plugin), Array("Plugin","DatabasePlugin"))) {
                // plugin d-etermined, so running it.
                $output = $plugin->run($input);
                $returned = $this->sortPluginOutput(array(), $output);
                $this->session->setRunningPlugin($plugin);
            } else {
                // show best alternative results, more commands that "could be meant"...
                // now just return the home screen if no plugin was found.
                $output = $this->searchCommands($input); // array of plugins
                $returned = $this->sortPluginOutput(array(), $output);
                $returned["Command"][] =  new Command("SearchResult", "SearchResult");
                //return $this->getHomeScreen();
            }

            // if the plugin is started AND done at the same time, just return with the homescreen instead of
            // whatever the command has to offer. The command author should take care that the state "Done" means
            // something and can be experienced as reliable. (this is a single-thing-command on the homepage's Done).. what is the wizard done?
            if ($this->session->getPluginState() == "Done") {
                $homeScreenCommands = $this->getHomeScreen();
                $returned["Command"] = Array(); // clear all commands, since it is done. Do the rest.
                $returned = $this->sortPluginOutput($homeScreenCommands,$returned);
                $this->session->reset(); // done!
                //print "Done! - ".$this->session->getPluginState();
                //$this->session->setPluginState("Not Started");
            }

        }

        $returned["sessionId"] =  session_id();
        $returned["session"] =  print_r($_SESSION,1);
        $returned["input"] =  htmlentities($input);

        return $returned;
    }

    private function getHomeScreen(){
        $mergedOutput = Array();
        foreach ($this->loadedPlugins as $plugin){
            $output = $plugin->run("");
            $mergedOutput = $this->sortPluginOutput($mergedOutput, $output);
        }
        
        $mergedOutput["sessionId"] =  session_id();
        return $mergedOutput;
    }


    /**
     * Plugins always return an _array_ with different groups of things.
     * For example:
     * [command][] = OBJECT
     * [usercommand][] = OBJECT
     * [shoppingcart][] = OBJECT // normally 1 shoppingcart... we can reduce this at the end.
     *
     * // the output now becomes native to the type used in the system. So only known objects are returned.
     * // probably should be recursive. then we can just give some objects somewhere and it will work out fine.
     */
    private function sortPluginOutput($sorted, $junk){
        foreach($junk as $key => $junkitem){
            if (is_object($junkitem)) {
                $sorted[get_class($junkitem)][] = $junkitem;
            } else if (is_array($junkitem)){
                // just suppose 1 level deep... no recursion
                foreach ($junkitem as $nestedjunkitem){
                    if (is_object($nestedjunkitem)) {
                        $sorted[get_class($nestedjunkitem)][] = $nestedjunkitem;
                    }
                }
            } else {
                $sorted[$key] = $junkitem; // string values etc, or just overwrite something
            }
        }
        return $sorted;
    }


    private function loadPlugins(){
        $loadedPlugins = Array();

        foreach($this->configuration->plugins as $pluginName){
            $fileName = "plugins/".$pluginName."/".$pluginName.".php";
            if (file_exists($fileName)){
                require_once($fileName);
            } else {
                // LOG The plugin X is specified, but could not be found. Ignoring.
            }

            $loadedPlugins[] = new $pluginName(); // do DI here... but PHP-DI requires include(world), so no... we do it less efficiently.
        }
        return $loadedPlugins;
    }

    private function searchCommands($input) {
        $foundCommands = Array();

        // find plugins matching by synonym
        foreach($this->loadedPlugins as $plugin){
            $synonyms = $plugin->getSynonyms();
            $synonymlist = implode(" ", $synonyms);
            if (stripos($synonymlist,$input) !== false) {
                $commands = $plugin->run("");
                $foundCommands = $this->sortPluginOutput($foundCommands, $commands);
                continue;
            }
        }

        // find commands that are handled by plugins by familiarity (such as usernames, products)

        return $foundCommands;
    }

    private function isConfiguredPlugin($plugin){
        foreach($this->loadedPlugins as $loadedPlugin){
            // == for type comparison, === for instance comparison.
            if (get_class($loadedPlugin) == get_class($plugin)) {
                //print get_class($loadedPlugin)."  is a ".get_class($plugin);
                return true;
            }
        }
        return false;
    }


    // shoudl this be done by a plugin? like the session plugin?
    private function storeCommand($command)
    {
        DB::insert("command log", array(
            "command" => $command,
            "session" => session_id(),
            "ip" => DB::sqleval("INET6_ATON(\"".$_SERVER['REMOTE_ADDR']."\")"), // ignoring proxies, HTTP_X_FORWARDED_FOR
            "datetime" => DB::sqleval("NOW()")));
    }
}