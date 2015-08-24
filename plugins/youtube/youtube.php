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
 * "yt" -> starts this plugin
 * "youtube" -> starts this plugin
 * "yt <displayedname>" -> instantly starts with playing a video.
 */
class youtube extends plugin {

    public function getName(){
        return "YouTube";
    }

    public function getHelp(){
        return "<h3>Accepted Commands</h3>
                <ul>
                    <li>(youtube|yt)</li>
                    <li>(youtube|yt) &lt;Button Name&gt;</li>
                </ul>
                <h3>Functionality</h3>
                <p>When either youtube or yt is given, a list of possible videos is showed.<br>
                A shortcut is to supply the name of the button to start the video instantly.</p>
                <p>Example shortcut: yt 80, this starts the video displayed as '80' instantly.</p>
                <h2>Start commands</h2>
                <p>This is a list of all commands that can be used to start this plugin:</p>
                <code>".implode(", ",$this->getSynonyms())."</code>";
    }

    public function run($command){

        if ($command == ""){
            return array(new Command("youtube", "YouTube","y"));
        }

        // make it more flexible:
        $command = $this->stripSynonyms($command); // rename to removesysnonyms? as ther eis another remove function?

        $return = array();
        $return[] = new command("dQw4w9WgXcQ?list=RDdQw4w9WgXcQ&autoplay=1", "80");
        $return[] = new command("QpDn4-Na5co?autoplay=1", "Lazerhawk");
        $return[] = new command("GpQvN0q8jUo?autoplay=1", "Dream");
        $return[] = new command("videoseries?list=PLCA03AA2B7DC1FF86&autoplay=1", "Hardcore","h");
        $return[] = new command("videoseries?list=PL080059B5CE9F4C19&autoplay=1", "Ganja", "g");
        $return[] = new command("videoseries?list=PLnwrplJgKXnfHeCNG6rhfeDF4wWRKe-ER&autoplay=1", "TV", "g");
        $return[] = new command("videoseries?list=UUFKeJVmqdApqOS8onQWznfA&autoplay=1", "TAS", "g");
        $return[] = new command("videoseries?list=UUZ0GmK_b1fhmIKMFAYz1pUw&autoplay=1", "Rave Radio", "g");
        $return[] = new command("videoseries?list=PL0E6B11D6AB01BED4&autoplay=1", "Trololo", "t");

        // bug in bar, it does not send commands with an ampersand when clicking.
        if (strpos($command,"list=") and !strpos($command,"autoplay")){
            $command .= "&autoplay=1";
        }

        // just make it easy, this is the *click* interface
        foreach($return as $commandObject){
            if (is_a($commandObject,"Command") and $commandObject->command == $command){
                $return[] = new BackgroundHtml("youtube",$this->iFrame("http://www.youtube.com/embed/".$command));
            }
        }

        // this is the "yt displayedName" version
        foreach($return as $commandObject){
            if (is_a($commandObject,"Command") and strtolower($commandObject->displayedName) == strtolower($command)){
                $return[] = new BackgroundHtml("youtube",$this->iFrame("http://www.youtube.com/embed/".$commandObject->command),"youtube");
            }
        }

        $return[] = $this->autoSuggest($return);

        return $return;
    }

    // maybe inverse this: ask if you can handle it. Why? you don't know all id's, but you do know the format.
    public function getSynonyms(){
        return  Array("youtube", "yt"); // destroys videos with yt in the key.
    }

    // is youtube URL...
}