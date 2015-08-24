<?php
/**
 * Created by PhpStorm.
 * User: ejonker
 * Date: 11-1-2015
 * Time: 18:54
 */

class Value {
    public $name = "";
    public $value = "";

    public function __construct($name, $value){
        $this->name = $name;
        $this->value = $value;
    }
}