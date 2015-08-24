<?php
/**
 * Created by PhpStorm.
 * User: ejonker
 * Date: 24-4-2015
 * Time: 15:02
 */
class Image  {
    public $href = "";
    public $alt = "";

    public function __construct($href, $alt){
        $this->href = $href;
        $this->alt = $alt;
    }
}