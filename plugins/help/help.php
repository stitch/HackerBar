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

// invoking the help commands of other plugins... interesting... how to get the other plugins?
class help extends plugin {

    public function getName(){
        return "Help";
    }

    public function getVersion(){
        return "1.0";
    }

    public function getHelp(){
        return "<h3>Accepted Commands to start the plugin</h3>
                <ul>
                    <li>help &lt;pluginname&gt;, shows the help for a certain plugin. eg: 'help shop'</li>
                    <li>help, shows a list of plugins where help is (should be) available</li>
                </ul>
                <h3>Accepted Commands when running the plugin</h3>
                <ul>
                    <li>&lt;pluginname&gt;</li>
                </ul>
                <h3>Functionality</h3>
                <p>Displays help information on a certain plugin. This help information includes ways to start the plugin,
                available commands when running the plugin and what the functionality is of the plugin.</p>
                <h3>Start commands</h3>
                <p>This is a list of all commands that can be used to start this plugin:</p>
                <code>".implode(", ",$this->getSynonyms())."</code>";
    }

    public function run($command){

        $configuration = new Configuration();
        $return = array();

        if ($command != "") {
            foreach($configuration->plugins as $plugin){
                $plugin = new $plugin;
                $return[] = new Command("help ".$plugin->getName(),"HELP: <br/>".$plugin->getName());
            }
            $return[] = new Hint("Help: select functionality where you need help...");
            $return[] = $this->autoSuggest($return);

            // make it possible to ask help about help.
            if (strtolower($command) == "help help"){
                $command = "help";
            } else {
                $command = $this->stripSynonyms($command);
            }

        } else {
            $return[] = new Command("help", "HELP! HELP ME!", "h");
        }


        //if ($this->startsWith($command, "help ")){
        //    $command = substr($command,5);
        //}

        if ($this->isPluginName($command)){
            $plugin = $this->getPluginByName($command);
            if (is_a($plugin,"Plugin")){
                $html = file_get_contents("plugins/help/help.html");
                $html = str_replace("{PLUGINNAME}", $plugin->getName(), $html);
                $html = str_replace("{PLUGINVERSION}", $plugin->getVersion(), $html);
                $html = str_replace("{PLUGINAUTHOR}", $plugin->getAuthor(), $html);
                $html = str_replace("{PLUGINHELP}", $plugin->getHelp(), $html);
                $return[] = new Page($html);
            }
        }

        return $return;

    }

    private function isPluginName($command){
        $configuration = new Configuration();
        foreach($configuration->plugins as $plugin) {
            $plugin = new $plugin;
            if (strtolower($plugin->getName()) == strtolower($command)){
                return true;
            }
        }
        return false;
    }

    private function getPluginByName($command){
        $configuration = new Configuration();
        foreach($configuration->plugins as $plugin) {
            $plugin = new $plugin;
            if (strtolower($plugin->getName()) == strtolower($command)){
                return $plugin;
            }
        }
        return false;
    }

    public function getSynonyms(){
        return  Array("help", "fuuuuuuuu", "fuuuuuuu", "fuuuuuu", "fuuuuu", "fuuuu", "fuuu", "fuu", "fu");
    }
}