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

class spacecams extends plugin {

    public function getName(){
        return "spacecams";
    }

    public function run($command){
        $this->setState("Done"); // 1 button, returns some HTML but nothing more...
        switch ($command){
            case "":
                return array(new Command("spacecams", "SpaceCams","c"));
                break;
            case "spacecams":
                return array(new Page("asd",$this->iFrame('plugins/spacecams/spacecams.html')));
                break;
        }
    }

    public function getSynonyms(){
        return  Array("spacecams");
    }
}