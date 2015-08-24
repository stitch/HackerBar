<?php
/**
 * Created by PhpStorm.
 * User: ejonker
 * Date: 10-1-2015
 * Time: 14:14
 */

class ProductCommand extends Command {

    public $price;
    public $stock;

    public function __construct($systemName = "", $displayName = "", $hotkey = "", $price = "", $stock = "")
    {
        $this->price = $price;
        $this->stock = $stock;
        parent::__construct($systemName, $displayName, $hotkey);
    }
}