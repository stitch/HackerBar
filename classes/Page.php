<?php
/**
 * Created by PhpStorm.
 * User: Ejonker
 * Date: 15-1-2015
 * Time: 21:34
 */

class Page {
    public $contents = "";

    public function __construct($contents){
        $this->contents = $contents;
    }
}