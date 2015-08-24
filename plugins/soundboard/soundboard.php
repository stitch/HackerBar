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


// todo: bug: sounds are not played in safari on iphone.
class soundboard extends Plugin {

    public function __construct()
    {
    }

    public function getName(){
        return "Soundboard";
    }

    public function getVersion(){
        return "1.0";
    }

    // todo: accept approximate soundnames, such as horn, hor, foghorn rn etc...
    public function getHelp()
    {
        return "<h3>Accepted commands to start plugin</h3>
                <ul>
                    <li>soundboard, displays soundboard</li>
                    <li>sound, displays soundboard</li>
                    <li>(sound|soundboard) 'soundname', plays a sound and exits instantly</li>
                </ul>
                <h3>Accepted commands when plugin is started</h3>
                <ul>
                    <li>&lt;soundname&gt;, plays a soundy</li>
                    <li><i>The names of the buttons deviate from the names of the sounds</i></li>
                </ul>
                <h3>Functionality</h3>
                <p>Plays a sound </p>
                <h3>Start commands</h3>
                <p>This is a list of all commands that can be used to start this plugin:</p>
                <code>" . implode(", ", $this->getSynonyms()) . "</code>";
    }
    // Handles the following commands
    // "sound" -> starts the soundboard, then you can choose whatever sound you want to play
    // "sound <soundname>" Only plays a sound, and exits immediately.
    public function run($command)
    {
        // main screen
        if ($command == ""){
            return Array(new command("soundboard", "Soundboard","s"));
        }

        $return = Array();

        $commandWithoutSynonyms = $this->stripSynonyms($command);
        if ($this->startsWith($command, "sound") && !empty($commandWithoutSynonyms)){
            $this->setState("Done");
        }

        //print $commandWithoutSynonyms."wryyyyy";
        if ($commandWithoutSynonyms != "") {
            $sound = "";

            $commandWithoutSynonyms = strtolower($commandWithoutSynonyms);
            switch ($commandWithoutSynonyms){
                case "airhorn" : $sound = "airhorn.mp3"; break;
                case "alarm1" : $sound = "alarm1.mp3"; break;
                case "beeping" : $sound = "beeping.mp3"; break;
                case "belgian waffles" : $sound = "belgian waffles.wav"; break;
                case "bill nye" : $sound = "bill nye.mp3"; break;
                case "bleepyouluigi" : $sound = "bleepyouluigi.mp3"; break;
                case "boringlink" : $sound = "boringlink.mp3"; break;
                case "chew bubblegum" : $sound = "chew bubblegum.mp3"; break;
                case "cookoo" : $sound = "cookoo.mp3"; break;
                case "cricket" : $sound = "cricket.mp3"; break;
                case "dundundun" : $sound = "dundundun.wav"; break;
                case "foghorn" : $sound = "foghorn.mp3"; break;
                case "givemebelgianwaffles" : $sound = "givemebelgianwaffles.mp3"; break;
                case "incomingtransmission" : $sound = "incomingtransmission.mp3"; break;
                case "laserbeam" : $sound = "laserbeam.mp3"; break;
                case "loveangry" : $sound = "loveangry.mp3"; break;
                case "maniacscream" : $sound = "maniacscream.mp3"; break;
                case "nerds" : $sound = "nerds.mp3"; break;
                case "outta here" : $sound = "outta here.mp3"; break;
                case "pacmanstart" : $sound = "pacmanstart.mp3"; break;
                case "record scratch" : $sound = "record scratch.mp3"; break;
                case "rage" : $sound = "stitchRAGE.wav"; break;
                case "rick" : $sound = "rickbegin.mp3"; break;
                case "slide" : $sound = "slide.mp3"; break;
                case "smoking" : $sound = "smoking.mp3"; break;
                case "spinback" : $sound = "spinback.mp3"; break;
                case "start_botsauto" : $sound = "start_botsauto.mp3"; break;
                case "sweeper" : $sound = "sweeper.mp3"; break;
                case "tiptoe" : $sound = "tiptoe.mp3"; break;
                case "watkrijgenwenu" : $sound = "watkrijgenwenu.mp3"; break;
                case "wijzijnervoorjou" : $sound = "stitchwijzijnervoorjouvet.wav"; break;
                case "yesyessf" : $sound = "yesyessf.mp3"; break;
            }

            if ($sound) {
                $return[] = $this->playSound("soundboard/".$sound);
            }
        }

        $return[] = new Hint("PLAY IT FUCKING LOUD!");
        $return[] = new command("airhorn", "Airhorn","a");
        $return[] = new command("alarm1","Alarm","");
        $return[] = new command("beeping","Beeping","b");
        $return[] = new command("belgian waffles","Waffles!","");
        $return[] = new command("bill nye","Bill Nye","");
        $return[] = new command("bleepyouluigi","Luigi","");
        $return[] = new command("boringlink","Boring","");
        $return[] = new command("chew bubblegum","Chew Bubble Gum","c");
        $return[] = new command("cookoo","Cookoo","");
        $return[] = new command("cricket","Cricket","");
        $return[] = new command("dundundun","DUN DUN DUN!","d");
        $return[] = new command("foghorn","Foghorn","f");
        $return[] = new command("givemebelgianwaffles","Give Belgian Waffles","g");
        $return[] = new command("incomingtransmission","Incomming Transmission","i");
        $return[] = new command("laserbeam","Laserbeam","l");
        $return[] = new command("loveangry","I love to be angry","");
        $return[] = new command("maniacscream","Maniac Scream","m");
        $return[] = new command("nerds","Nerds!","n");
        $return[] = new command("outta here","Outta here!","o");
        $return[] = new command("pacmanstart","Pac-Man","p");
        $return[] = new command("record scratch","Record Scratch","r");
        $return[] = new command("rage","RAGEEEE!");
        $return[] = new command("rick","Rick Astley Toms");
        $return[] = new command("slide","Slide","s");
        $return[] = new command("smoking","Smoking can kill you","");
        $return[] = new command("spinback" ,"Spinback","");
        $return[] = new command("start_botsauto","Start Botsauto's","");
        $return[] = new command("sweeper","Sweeper","");
        $return[] = new command("tiptoe","Tiptoe","t");
        $return[] = new command("watkrijgenwenu","Wat krijgen we nu!?","w");
        $return[] = new command("wijzijnervoorjou","WIJ ZIJN ER VOOR JOU!");
        $return[] = new command("yesyessf","YES YES","y");

        $return[] = $this->autoSuggest($return);

        return $return;
    }

    /**
     * @return Array() list of synonyms for this plugin. required method by the plugin interface.
     * The ordering here matters. soundboard will be stripped earlier than sound. Place longer synonyms first.
     */
    public function getSynonyms(){
        return Array("soundboard", "sound");
    }

}