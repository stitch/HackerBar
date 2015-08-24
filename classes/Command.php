<?php
/**
 * Created by PhpStorm.
 * User: ejonker
 * Date: 3-1-2015
 * Time: 15:00
 */

class Command {
    public $command;
    public $displayedName;
    public $hotkey; // https://en.wikipedia.org/wiki/Access_key
    public $fact1;
    public $fact2;
    public $styles;

    public function __construct($command = "", $displayName = "", $hotkey = "", $fact1 = "", $fact2 = "", $styles = "")
    {
        $this->command = $command;
        $this->displayedName = $displayName;
        $this->hotkey = $hotkey;
        $this->fact1 = $fact1;
        $this->fact2 = $fact2;
        $this->styles = $styles;
    }
}