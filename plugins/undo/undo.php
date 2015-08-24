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
// keyvalues. en

class undo extends DatabasePlugin {

    protected $DB = null;
    private $transaction = 0;
    private $sure = "no";

    // probably input database anywah here?
    public function __construct()
    {
        $this->DB = new DB();
    }

    public function getName(){
        return "undo";
    }

    public function getVersion(){
        return "1.0";
    }

    public function getHelp()
    {
        return "<h3>Accepted commands to start plugin</h3>
                <ul>
                    <li>undo &lt;transactionid&gt;, undoes the transaction with transactionid (untested)</li>
                    <li>undolast, undoes the last transaction and exits</li>
                </ul>
                <h3>Accepted commands when running</h3>
                <ul>
                    <li>&lt;transactionid&gt;, undoes the transaction with transactionid</li>
                </ul>
                <h3>Functionality</h3>
                <p>Undoes transactions.</p>
                <h3>Start commands</h3>
                <p>This is a list of all commands that can be used to start this plugin:</p>
                <code>" . implode(", ", $this->getSynonyms()) . "</code>";
    }


    public function run($command)
    {
        $return = array();

        // reset the plugin when not started
        if ($command == "no"){
            $this->clear();
        }

        if (in_array($command, $this->getSynonyms())){
            $this->setState("party!");
        }

        // default output, on click / synonym
        if ($this->getState() == $this->defaultState) {
            $return[] = new command("undo", "Undo Transaction","u");
            $return[] = $this->getSuggestions();
            return $return;
        }




        // a transaction is given, but its not verified that it should be deleted, ask a question.
        if ($this->isTransaction($command) && $this->sure == "no"){
            $this->transaction = $command;

            $recentTransaction = $this->DB->queryFirstRow("SELECT * from transactionhistory  where id = %i LIMIT 1", $this->transaction);
            //print_r($recentTransaction);
            $return[] = new Value("Involved",$recentTransaction["accounts"]);
            $return[] = new Value("Products",$recentTransaction["products"]);
            $return[] = new Value("When",$this->timeAgo($recentTransaction["datetime"]));
            $return[] = new Value("Total Amount",$this->asCurrency($recentTransaction["price"]));

            $return[] = new FinalCommand("yes","UNDO TRANSACTION");
            // are you sure?
        }

        // there is a transaction
        if ($command == "yes" && $this->transaction > 0){
            $this->sure = "yes";
        }


        if ($this->sure == "yes" && $this->transaction > 0) {
            $this->undo();
            $return[] = $this->playSound("undo/assak.mp3");
        }


        $return[] = $this->getRecentTransactionCommands();
        $return[] = new Hint("You have one week to undo.");

        return $return;

    }

    public function getRecentTransactionCommands(){
        $commands = array();

        // you can undo to one week from now...
        $recentTransactions = $this->DB->query("SELECT * from transactionhistory WHERE `datetime` between date_sub(now(),INTERVAL 1 WEEK) and now()");

        foreach($recentTransactions as $transaction){
            $users = ($transaction["nraccounts"] == 1) ? $transaction["accounts"] : $transaction["nraccounts"]." accounts";
            $products = ($transaction["nrproducts"] == 1) ? $transaction["products"] : $transaction["nrproducts"]." products";
            $commands[] = new Command($transaction["id"],$this->timeAgo($transaction["datetime"])."<br />".$users."<br />".$products,"","&euro; ".number_format($transaction["price"] / 100, 2), "#".$transaction["id"]);
        }

        return $commands;
    }

    /**
     * A transaction is undone in:
     * - delete transaction
     * - delete sold product
     * - delete payshare
     * - it increases the amount of money of all involved accounts
     * - it stores a deposit mutation (it does NOT delete the old one)
     */
    private function undo(){

        if (!$this->isTransaction($this->transaction)){
            return false;
        }

        $transaction = $this->transaction;
        // make sure that all database data is consistent during this set of operations.
        $this->DB->startTransaction();

        // retrieve the amount of money that should be given back to the account(s)
        // then give it back, also log this as a new depositmutation. You get the price you payed for it then.
        $payshares = $this->DB->query("SELECT account, amount FROM `payshare` where `transaction` = %i", $this->transaction);
        foreach($payshares as $payshare){
            $this->DB->query("UPDATE account SET deposit = deposit + %i WHERE name = %s", $payshare["amount"], $payshare["account"]);
            $this->DB->insert("depositmutation", array(
                "account" => $payshare["account"],
                "cents" => $payshare["amount"],
                "reason" => "undo of transaction ".$this->transaction,
                "datetime" => DB::sqleval("NOW()")));
        }

        // reset the stock of the sold products.
        $restocks = $this->DB->query("SELECT product, amount FROM `sold product` where `transaction` = %i", $this->transaction);
        foreach($restocks as $restock){
            $this->DB->query("UPDATE product SET stock = stock + %i WHERE name = %s", $restock["amount"], $restock["product"]);
        }

        // delete transaction from related tables, then delete the transaction table
        $this->DB->query("DELETE from `unlisted product` WHERE `transaction` = %i", $this->transaction);
        $this->DB->query("DELETE from `sold product` WHERE `transaction` = %i", $this->transaction);
        $this->DB->query("DELETE from `payshare` WHERE `transaction` = %i", $this->transaction);
        $this->DB->query("DELETE from `transaction` WHERE `id` = %i", $this->transaction);

        $this->clear();
        // finish the transaction
        $this->DB->commit();

        return true;
    }

    private function clear(){
        $this->sure = "no";
        $this->transaction = 0;
    }

    function timeAgo($time_ago){
        $cur_time 	= time();
        $time_elapsed 	= $cur_time - strtotime($time_ago);
        $seconds 	= $time_elapsed ;
        $minutes 	= round($time_elapsed / 60 );
        $hours 		= round($time_elapsed / 3600);
        $days 		= round($time_elapsed / 86400 );
        $weeks 		= round($time_elapsed / 604800);
        $months 	= round($time_elapsed / 2600640 );
        $years 		= round($time_elapsed / 31207680 );

        if($seconds <= 60){
            return "$seconds sec. ago";
        }

        else if($minutes <=60){
            return ($minutes==1) ? "one minute ago" : "$minutes min. ago";
        }

        else if($hours <=24){
            return ($hours==1) ? "an hour ago" : "$hours hours ago";
        }

        else if($days <= 7){
            if($days==1){
                return "yesterday";
            }else{
                return "$days days ago";
            }
        }

        else if($weeks <= 4.3){
            if($weeks==1){
                return "a week ago";
            }else{
                return "$weeks weeks ago";
            }
        }

        else if($months <=12){
            if($months==1){
                return "a month ago";
            }else{
                return "$months months ago";
            }
        }

        else{
            if($years==1){
                return "one year ago";
            }else{
                return "$years years ago";
            }
        }
    }


    /**
     * @return Array() list of synonyms for this plugin. required method by the plugin interface.
     * // there will be a shitload of sysnonyms that can start this plugin, namely:
     * - All productgroups (names)
     * - All productcodes
     * - All products (names)
     * - "shop"
     */
    public function getSynonyms(){
        //return Array("shop");

        $synonyms = Array("undolast", "undo");
        return $synonyms;

    }
}