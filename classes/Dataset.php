<?php
/**
 * Created by PhpStorm.
 * User: ejonker
 * Date: 12-1-2015
 * Time: 20:13
 */

class Dataset {
    public $data = array(array());
    public $name = "";

    public function __construct($name, $data){
        $this->name = $name;
        $this->data = $data;
    }
}