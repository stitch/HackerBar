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

class foghorn extends Plugin {

    public function getName(){
        return "Foghorn";
    }

    // this plugin is so simple, it does not have state. It only has one action: run when there is an input.
    // return array with the possible command(s) and possible GUI Actions.
    public function run($command)
    {
        $this->setState("Done");

        if (in_array($command, $this->getSynonyms())){
            $return = Array();
            $return[] = $this->playSound("foghorn/foghorn.mp3");
            $return[] = new command("foghorn", "FOG HORN");
            return $return;
        }

        return Array(new command("foghorn", "FOG HORN"));
    }

    /**
     * @return Array() list of synonyms for this plugin. required method by the plugin interface.
     */
    public function getSynonyms(){
        return Array("horn", "foghorn", "toot");
    }

}