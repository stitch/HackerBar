<?php
/**
 * Created by PhpStorm.
 * User: ejonker
 * Date: 2-1-2015
 * Time: 22:13
 */

class Plugin {
    public $state = "Not Started";
    public $defaultState = "Not Started"; // why u no static?

    public function getName(){
        return "Default Plugin.";
    }

    public function getVersion(){
        return "1.0";
    }

    public function getAuthor(){
        return "Elger 'Stitch' Jonker";
    }


    public function reset(){
        $this->$state = "Not Started";
    }

    public function abort(){
        $this->reset();
    }

    public function getSynonyms(){
        return Array();
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }

    public function isState($state){
        return $this->state == $state;
    }

    public function run($command){
        return;
    }

    public function isFamiliar(){
        return false;
    }

    public function stripSynonyms($command)
    {
        // remove all synonyms from the start, note that this is a very sloppy function
        $synonyms = $this->getSynonyms();
        foreach ($synonyms as $synonym) {
            $newCommand = preg_replace('/^' . preg_quote($synonym, '/') . '/', '', $command);
            if ($newCommand != $command) {
                return trim($newCommand);
            } // return on the first match, less sloppier.
        }
        return $command; // nothing happened.
    }

    //https://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
    // due to the lack of scalars, this is php 5.
    protected function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    // more lack of scalars
    protected function removeFromStart($haystack, $needle){
        $string = preg_replace('/^' . preg_quote($needle, '/') . '/', '', $haystack);
        return trim($string);
    }

    protected function array_removevalue($array, $value){
        if(($key = array_search($value, $array)) !== false) {
            unset($array[$key]);
        }
        return $array;
    }

    // $soundFileName = "pluginname/soundfilename"
    protected function playSound($soundFileName){

        if (is_file("plugins/".$soundFileName)){
            $soundHtml = file_get_contents("plugins/sound.html");
            $soundHtml = str_replace("{SOUNDFILE}", "plugins/".$soundFileName, $soundHtml);
            return new ArbitraryHTML("sound", $soundHtml);
        }
        return null;
    }

    // get a list of values and arrays (somethings) regardng the performance of the plugin
    public function getStatistics(){
        return array();
    }

    // support suggestions in the input field.
    // returns array with Suggestion objects
    public function getSuggestions(){
        $synonyms = $this->getSynonyms();
        $suggestions = array();
        foreach ($synonyms as $synonym){
            $suggestions[] = new Suggestion($synonym,$synonym);
        }
        return $suggestions;
    }

    // getOverallCommands()// not depending on state... for closing the shop, abort, print etc...??


    public function getHelp(){
        return "No help defined. +1 to hate.";
    }

    protected function asCurrency($value){
        return "&euro;".number_format($value / 100 , 2);
    }

    // ambiguous return.
    // todo: things that sstart with ,<number> are not cast properly yet...
    protected function isAmount($something){
        $amount = (int)$something;
        if (strpos($something, ",") or strpos($something, ".")) {
            $something = str_replace(",",".",$something); // php and .
            $amount = bcmul($something, 100); // naive start
        }

        if (is_integer((int)$amount)){
            return $amount;
        } else {
            return false;
        }
    }

    // include pluginname / whatever file.
    protected function getCssFiles(){
        return array();
    }

    protected function autoSuggest($objectArray){

        $suggestions = array();
        foreach ($objectArray as $something){
            // get some suggestions from the existing commands... this is very dumb
            // todo: the displaynames should be displayed as suggestions, not the actual commands... yet that crashes...
            if (is_a($something,"Command")){
                $suggestions[] = new Suggestion($something->command,$something->command);
            }
        }
        return $suggestions;
    }


    protected function iFrame($src){
        $html = file_get_contents("plugins/iframe.html");
        $html = str_replace("{IFRAMESRC}", $src, $html);
        return $html;
    }

}