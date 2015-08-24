<?php
/**
 * Created by PhpStorm.
 * User: ejonker
 * Date: 8-1-2015
 * Time: 19:48
 */

// achievement unlocked, programming a shoppingcart in 1 session, without ever running the class and it works.
class ShoppingCart {

    private $DB = null;
    private $contents = Array(); // array of Product.

    public function __construct(){
        $this->DB = new DB();
    }

    public function addProductByName($productName){
        if (!$this->isProductByName($productName))
            return false;

        if ($this->containsProductByName($productName)){
            return $this->incrementAmount($productName);
        } else {
            $product = new Product();
            $productInfo  = $this->DB->queryFirstRow("SELECT * FROM `product` where name = %s",$productName); // casting?
            $product->name = $productInfo["name"];
            $product->amount = 1; // this is not stock, this is the amount of products in the cart. stock is stock.
            $product->individualPrice = $productInfo["price"];
            $product->accumulatedPrice = $productInfo["price"];
            $product->group = $productInfo["group"];
            $product->stock = $productInfo["stock"];
            $product->description = $productInfo["description"];
            $product->unlisted = false;

            $this->contents[] = $product;
        }
        return true;
    }

    public function addOne($productName){
        $this->addProductByName($productName);
    }

    public function addProductByCode($productCode){
        $productName = $this->DB->queryFirstField("SELECT product FROM `productcode` where code = %s", $productCode);
        $this->addProductByName($productName);
    }

    private function isProductByName($productName){
        return $this->DB->queryFirstColumn("SELECT 1 FROM `product` where name = %s",$productName);
    }

    public function addUnlisted($amount){
        $product = new Product();
        $product->name = "Unlisted Product";
        $product->amount = 1; // this is not stock, this is the amount of products in the cart. stock is stock.
        $product->individualPrice = $amount;
        $product->accumulatedPrice = $amount;
        $product->group = "unlisted";
        $product->stock = "-inf";
        $product->description = "Unlisted product";
        $product->unlisted = true;
        $this->contents[] = $product;
    }

    public function containsUnlistedProduct(){
        foreach ($this->contents as $product){
            if ($product->unlisted) {
                return true;
            }
        }
        return false;
    }

    // added lowercase comparison: to sell both Nuts and nuts and NuTs
    public function containsProductByName($productName){
        foreach ($this->contents as $product){
            if (strtolower($product->name) == strtolower($productName)) {
                return true;
            }
        }
        return false;
    }

    // added lowercase comparison: to sell both Nuts and nuts and NuTs
    private function incrementAmount($productName){
        foreach ($this->contents as $product){
            if (strtolower($product->name) == strtolower($productName)) {
                $product->amount++;
                $product->accumulatedPrice = $product->amount * $product->individualPrice;
                return true;
            }
        }
        return false;
    }

    // input some of your customized nightmare here...
    public function getTotalAmount(){
        $total = 0;
        foreach ($this->contents as $product){
            $total += $product->accumulatedPrice;
        }
        return $total;
    }

    public function clear(){
        $this->contents = Array();
    }

    public function isEmpty(){
        return (empty($this->contents));
    }

    public function removeOne($productName){
        if (!$this->isProductByName($productName))
            return false;

        foreach ($this->contents as $key => $product){
            if ($product->name == $productName) {
                $product->amount--;
                $product->accumulatedPrice = $product->amount * $product->individualPrice;

                // remove empty products.
                if ($product->amount <= 0){
                    unset($this->contents[$key]);
                    $this->contents = array_values($this->contents); // for the ones that iterate sequentially
                }
            }
        }
    }

    public function removeAll($productName){
        if (!$this->isProductByName($productName))
            return false;

        foreach ($this->contents as $key => $product) {
            if ($product->name == $productName) {
                unset($this->contents[$key]);
                $this->contents = array_values($this->contents); // for the ones that iterate sequentially
            }
        }
        return true;
    }

    /**
     *
     */
    public function getContents(){
        return $this->contents;
    }

    public function getListedContentsOnly(){
        $listed = array();
        foreach ($this->contents as $product){
            if (!$product->unlisted) {
                $listed[] = $product;
            }
        }
        return $listed;
    }

    public function getUnlistedContentsOnly(){
        $unlisted = array();
        foreach ($this->contents as $product){
            if ($product->unlisted) {
                $unlisted[] = $product;
            }
        }
        return $unlisted;
    }

    // tostring??? is this too magical? will this cause problems when printing it / handling it?
    public function getAsString(){
        $string = "";
        foreach ($this->contents as $productInfo){
            $string .= "".$productInfo->amount."x ".$productInfo->name." for ".$productInfo->individualPrice.", totalling ".$productInfo->accumulatedPrice." ";
        }
        return $string;
    }
}
