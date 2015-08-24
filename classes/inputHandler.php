<?php
/**
 * Created by PhpStorm.
 * User: Elger
 * Date: 2-1-2015
 * Time: 21:18
 */

// there are two modes of input: lines and commands. A command is parsed, while a line is given back to something.
class inputHandler
{
    private $plugins = Array();

    function __construct(Array $plugins, $input = "")
    {
        $this->plugins = $plugins;
        return;
    }

    // this unravels input and sends it to a plugin
    // deposit [amount [username]]
    // account [username]
    // stock [productcode [amount]
    // buy [productcode [,productcode+] [account]
    // new
    // report
    // unbuy

    // supported shortcuts
    // account.name --> account
    // productcode.code --> buy
    // transaction.id --> undobuy
    function parse($input) {

        // the first word of the input is checked for what should be done.
        // everything else is then given to the plugin that is called dynamically.
        $words = array();
        $words = explode(' ', $input);
        $firstWord = $words[0]; // you check in the first word, because that is a command most of the time. More words is more fancy.
       //print_r($words);

        // check if the command is available in all plugins, run the first one
        foreach ($this->plugins as $plugin)
        {
            if (in_array($firstWord,$plugin->getSynonyms())){
                return $plugin;
            }

            if (in_array($input,$plugin->getSynonyms())){
                return $plugin;
            }
        }

        // if there is no command that fits the a synonym, searches are supported.
        // we ask every plugin if the input is familiar. If so, we give the input to the plugin via another method. // todo: remove this?
        foreach ($this->plugins as $plugin)
        {
            if ($plugin->isFamiliar($firstWord)){
                return $plugin; // todo: support multiple familiar plugins
            } elseif (($plugin->isFamiliar($input))){
                return $plugin;
            }
        }

        // since switch statements cannot hold functions:
        switch ($firstWord){
            case "" :


        }
    }


}