<?php
/**
 * Created by PhpStorm.
 * User: ejonker
 * Date: 13-1-2015
 * Time: 22:42
 */

// to not polute a normal plugin with all kinds of database crap, this is an extension which does have this crap.
class DatabasePlugin extends plugin {
    protected $DB = null;

    public function __construct(){
        $this->DB = new DB();
    }

    // default database queries you need everywhere
    // you need to have a DB object for this.
    protected function isAccount($accountName){
        return $this->DB->queryFirstField("select 1 from  `account` where name = %s", $accountName);
    }

    protected function isProductCode($command){
        return $this->DB->queryFirstField("SELECT 1 FROM `productcode` where code = %s", $command);
    }

    // should be: getAccountNames... (or getAccountKeys to be more DB abstract)
    protected function getAccounts(){
        return $this->DB->queryFirstColumn("SELECT name FROM account ORDER BY name ASC");
    }

    protected function isTransaction($transactionId){
        return $this->DB->queryFirstField("select 1 FROM `transaction` where id = %i",$transactionId);
    }

    // the following is a nice rule: even though the product is not listed, the product can still be
    // scanned: suppose that someone found a leftover product, then you still want to sell it.
    // the danger is that the products in the shop are not updated, because it still works.
    protected function isProduct($command){
        return $this->DB->queryFirstField("SELECT 1 FROM `product` where name = %s LIMIT 1",$command);
    }

    protected function logDepositMutation($account, $amount, $reason)
    {
        $this->DB->insert("depositmutation", array(
            "account" => $account,
            "cents" => $amount,
            "reason" => $reason,
            "datetime" => DB::sqleval("NOW()")));
    }

    // this helps with "sort-of" products such as:
    /* Coca Cola (33cl)
     * Coca Cola
     * cocacola
     * coca
     * cacola
     * la (if there are no other producs that have "la". etc...
     * because this is so flexible, make sure this is called AFTER you check this is an account.
     * Do not "just" implement these... as the first one that has a valid "starting thing" them will be called.
     * */
    protected function looksLikeASingleProductOrProductCode($command){
        $possibleNames = $this->DB->queryFirstColumn("SELECT name FROM `product` where LOWER(name) LIKE LOWER(%ss)", $command);
        //print_r($possibleNames);
        if (count($possibleNames) == 1){
            return $possibleNames[0];
        }
        // do not search product codes... yet... note that productcodes contain numbers. so numbers == 1 is 1 .
        $possibleNames = $this->DB->queryFirstColumn("SELECT product FROM `productcode` where LOWER(code) LIKE LOWER(%ss)", $command);
        if (count($possibleNames) == 1){
            return $possibleNames[0];
        }

        return false;
    }

    /**
     * For account stitch, anything that looks like stitch. it uses the first thing it can find. So watch the suggestions.
     * this is used at accounts.
     */
    protected function looksLikeAnAccount($command){
        $possibleAccounts = $this->DB->queryFirstColumn("SELECT name FROM `account` where LOWER(name) LIKE LOWER(%ss)", $command);
        if (count($possibleAccounts) == 1){
            return $possibleAccounts[0];
        }
        return false;
    }

}