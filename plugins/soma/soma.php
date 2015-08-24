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
 * Youtube has more types of embeds:
 * Single Track: //www.youtube.com/embed/oWC1MLVOsG0?autoplay=1
 * Playlist: //www.youtube.com/embed/videoseries?list=PLY2sCUR74l_rFHojL1e2qEKhz_qJ6mxYH&autoplay=1
 *
 * handles:
 * "radio" | "webradio" -> starts this plugin
 * "youtube" -> starts this plugin
 * "yt <displayedname>" -> instantly starts with playing a video.
 */
class soma extends plugin {

    public function getName(){
        return "Webradio";
    }

    public function getHelp(){
        return "<h3>Accepted Commands</h3>
                <ul>
                    <li>(soma|radio)</li>
                    <li>(soma|radio) &lt;Station Name&gt;</li>
                </ul>
                <h3>Functionality</h3>
                <p>Let's you listen to SOma</p>
                <p>Example shortcut: yt 80, this starts the video displayed as '80' instantly.</p>
                <h3>Start commands</h3>
                <p>This is a list of all commands that can be used to start this plugin:</p>
                <code>".implode(", ",$this->getSynonyms())."</code>";
    }

    public function run($command){

        if ($command == ""){
            return array(new Command("soma", "Soma Internet Radio","r")); // todo: possible to return objects directly? .. no, it should always be an array that can hold anything.
        }

        // make it more flexible:
        $command = $this->stripSynonyms($command); // rename to removesysnonyms? as ther eis another remove function?

        $return = array();
        // web players version...
        $return[] = new command("http://somafm.com/player/#/now-playing/groovesalad", "Groove Salad", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/lush", "Lush", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/spacestation", "Space Station Soma", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/digitalis", "Digitalis", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/poptron", "PopTron", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/indiepop", "Indie Pop Rocks", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/bagel", "BAGeL Radio", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/7soul", "Seven Inch Soul", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/defcon", "DEF CON Radio", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/thetrip", "The Trip", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/cliqhop", "cliqhop idm", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/dubstep", "Dub Step Beyond", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/earwaves", "Earwaves", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/missioncontrol", "Mission Control", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/sonicuniverse", "Sonic Universe", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/secretagent", "Secret Agent", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/bootliquor", "Boot Liquor", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/covers", "Covers", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/u80s", "Underground 80s", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/illstreet", "Illinois Street Lounge", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/deepspaceone", "Deep Space One", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/dronezone", "Drone Zone", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/suburbsofgoa", "Suburbs of Goa", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/folkfwd", "Folk Forward", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/beatblender", "Beat Blender", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/brfm", "Black Rock FM", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/sf1033", "SF 10-33", "","","","radiostation");
        $return[] = new command("http://somafm.com/player/#/now-playing/doomed", "Doomed", "","","","radiostation");
        // todo: add a mp3-stream version with your own player.


        // just make it easy, this is the *click* interface
        foreach($return as $commandObject){
            if (is_a($commandObject,"Command") and $commandObject->command == $command){
                $return[] = new BackgroundHtml("soma", $this->iFrame($command));
            }
        }

        // this is the "yt displayedName" version
        foreach($return as $commandObject){
            if (is_a($commandObject,"Command") and strtolower($commandObject->displayedName) == strtolower($command)){
                $return[] = new BackgroundHtml("soma", $this->iFrame($commandObject->command));
            }
        }

        $return[] = new Hint("You're listening to soma.fm, donate today for more great music!");

        $return[] = $this->autoSuggest($return);

        return $return;
    }


    public function getSynonyms(){
        return  Array("soma", "radio", "","","","radiostation");
    }

    // todo: implement...
    public function getCssFiles(){
        return new CssFile("soma/soma.css");
    }

    // get javascript files

    // get datatypes...

}