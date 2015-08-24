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
class statistics extends DatabasePlugin {

    //private $noreload = false; // prevents double usernames when this plugin is instantly in DONE state (where it gets called again)

    public function getName(){
        return "Statistics";
    }

    public function getVersion(){
        return "1.1";
    }

    public function getHelp()
    {
        return "<h3>Accepted commands to start plugin</h3>
                <ul>
                    <li>statistics, stats</li>
                </ul>
                <h3>Functionality</h3>
                <p>Shows statistics from all plugins. </p>
                <h3>Start commands</h3>
                <p>This is a list of all commands that can be used to start this plugin:</p>
                <code>" . implode(", ", $this->getSynonyms()) . "</code>";
    }

    public function run($command)
    {
        // synonym used
        $return = array();
        if (!in_array($command, $this->getSynonyms()) or $command == "") {
            $return[] = new Command("stats","Statistics","s");
            return $return;
        }

        $configuration = new Configuration();
        $return = array();
        $countPlugins = 0;
        if ($command != "") {
            foreach ($configuration->plugins as $plugin) {
                $countPlugins +=1;
                $plugin = new $plugin;
                $return[] = $plugin->getStatistics();
            }
        }

        $return[] = new Value("Number of plugins", $countPlugins);
        $return[] = new FinalCommand("statistics","Refresh!");

        return $return;
    }

    /**
     * @return Array() list of synonyms for this plugin. required method by the plugin interface.
     *
     * If you want to start dynamiccaly, you also give all your dynamic values, such as usernames... or whatver is needed to start the plugin.
     */
    public function getSynonyms(){
        return array("statistics","stats");
    }

}