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

/**
 * Returns some datasets that are based on database views
 */
// todo; work with named datasets, these can be dumped in some sort of (jquery?) datagrid. datasetObject(name, data)
class about extends Plugin {

    //private $noreload = false; // prevents double usernames when this plugin is instantly in DONE state (where it gets called again)

    public function getName(){
        return "HackerBar";
    }

    public function getVersion(){
        return "1.0";
    }

    public function getHelp()
    {
        return "<h3 id='help_HackerBar'>HackerBar</h3>
                <p>The bar (forcibly) trusted by 10's of people worldwide!</p>
                <h3 id='help_Functionality'>Functionality</h3>
                <p>Shows basic information about the HackerBar software. </p>
                <h3 id='help_Start_commands'>Start commands</h3>
                <p>This is a list of all commands that can be used to start this plugin:</p>
                <code>" . implode(", ", $this->getSynonyms()) . "</code>";
    }

    // no commands... only help and a direct command.
    public function run($command)
    {
        if (in_array($command, $this->getSynonyms())){
            $return = array();
            $return[] = new Page($this->getHelp());
            ob_start();
            phpinfo(1);
            $phpModules = ob_get_clean();
            $return[] = new Page("<h3 id='PHP General'>PHP General</h3>".$phpModules);
            ob_start();
            phpinfo(4);
            $phpModules = ob_get_clean();
            $return[] = new Page("<h3 id='PHP Configuration'>PHP Configuration</h3>".$phpModules);
            ob_start();
            phpinfo(8);
            $phpModules = ob_get_clean();
            $return[] = new Page("<h3 id='PHP Modules'>PHP Modules</h3>".$phpModules);
            ob_start();
            phpinfo(16);
            $phpModules = ob_get_clean();
            $return[] = new Page("<h3 id='PHP Environment'>PHP Environment</h3>".$phpModules);
            ob_start();
            phpinfo(32);
            $phpModules = ob_get_clean();
            $return[] = new Page("<h3 id='PHP Variables'>PHP Variables</h3>".$phpModules);
            return $return;
        }
        return array();
    }

    /**
     * @return Array() list of synonyms for this plugin. required method by the plugin interface.
     *
     * If you want to start dynamiccaly, you also give all your dynamic values, such as usernames... or whatver is needed to start the plugin.
     */
    public function getSynonyms(){
        return array("HackerBar","about", "configuration", "debug");
    }

}