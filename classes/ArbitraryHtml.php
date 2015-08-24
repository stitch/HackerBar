<?php
/**
 * Created by PhpStorm.
 * User: ejonker
 * Date: 3-1-2015
 * Time: 15:26
 */

class ArbitraryHtml {
    public $code;
    public $plugin;

    public function __construct($plugin, $code = ""){
        $this->plugin = $plugin;
        $this->code = $code;
    }
}