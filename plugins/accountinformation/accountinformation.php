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

/**
 * Simple plugin that delivers a list of accounts.
 * When an account is given as input, some information about this account is shown.
 *
 * The funny thing about this plugin: it results a list of accounts, but it is a different list (context)
 * then when the store is opened. This is on purpose because it ads less interdependencies. The store can
 * have special lists whenever it wants now: this plugin is NOT in the way.
 *
 * The account dont have anything special for now: just a list of names.
 */
class accountinformation extends DatabasePlugin {

    private $currentAccount = "";

    public function getHelp(){
        return "<h3>Accepted Commands</h3>
                <ul>
                    <li>&lt;Accountname&gt;</li>
                </ul>
                <h3>Functionality</h3>
                <p>When not input is given, such as on the main screen, this plugin shows a list of Accounts.<br>
                On giving an Accountname, information regarding the account is retrieved.</p>
                <p>This plugin is different from the list of accounts shown in, for example, the shop.</p>
                <h3>Start commands</h3>
                <p>This is a list of all commands that can be used to start this plugin:</p>
                <code>".implode(", ",$this->getSynonyms())."</code>";
    }

    public function getName(){
        return "Account Information";
    }

    public function getVersion(){
        return "1.0";
    }


    public function run($command)
    {
        $return = array();

        if (in_array($command,$this->getSynonyms())){
            $this->setState("Started");
        }

        // standard hint
        if ($command != "" || $this->getState() == "Started"){
            $return[] = new Hint("Type a new or existing username.");
        }

        $accounts = $this->getAccounts();

        // return a list of accounts
        foreach ($accounts as $account) {
            $return[] = new AccountCommand($account, $account);
        }

        $return[] = $this->getSuggestions();

        if ((in_array($command, $accounts))) {
            $this->currentAccount = $command;
        }

        if (!$this->currentAccount){
            return $return; // nothing to see here...
        }

        // this is a really disgusting hack... it should be done via the normal command input...
        if ($this->startsWith($command, "impulsebuy")){
            //$this->setState("Done");
            $shop = new Shop();
            $return[] = $shop->run($command);
        }

        // get popular products for this account
        $popular = $this->DB->query("select product, amountsold, stock, price from `popular products` inner join `product` on `product`.name = `popular products`.product where account = %s order by amountsold DESC LIMIT 10", $this->currentAccount);
        foreach($popular as $productStuff) {
            $return[] = new Command("impulsebuy " . $this->currentAccount . " ".$productStuff["product"], "Instantly buy: ".$productStuff["product"], "", $productStuff["price"], $productStuff["stock"]);
        }

        if ($this->currentAccount) {
            $accountInfo =  $this->DB->queryFirstRow("SELECT * FROM account WHERE name = %s", $this->currentAccount);
            $return[] = new Value("Balance for ".$accountInfo["name"]."",$this->asCurrency($accountInfo["deposit"]));
            $return[] = new Value("Created",$accountInfo["created"]);

            $return[] = new Dataset("Favorite products", $this->DB->query("select product, amountsold from `popular products` where account = %s order by amountsold DESC LIMIT 10", $this->currentAccount));
            $return[] = new Dataset("Recent transactions", $this->DB->query("select transaction, product, payshare, transactionvalue, `datetime` from `recently sold products` where account = %s LIMIT 50", $this->currentAccount));
            $return[] = new Dataset("Recent deposit mutations", $this->DB->query("SELECT account, cents, reason, datetime FROM depositmutation where account = %s order by `datetime` desc LIMIT 50",$this->currentAccount));
        }

        return $return;
    }

    public function getSynonyms(){
        return array_merge($this->getAccounts());
    }

    public function getStatistics(){
        $return = array();
        $return[] = new Value("Number of accounts","".$this->DB->queryFirstField("select count(*) from `account`"));
        return $return;
    }
}