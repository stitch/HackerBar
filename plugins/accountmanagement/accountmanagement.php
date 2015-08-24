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
class accountmanagement extends DatabasePlugin {

    //private $noreload = false; // prevents double usernames when this plugin is instantly in DONE state (where it gets called again)

    private $accountstocreate = array();
    private $existingaccountstodeposit = array();
    private $moneytodeposit = 0;


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
        return "Account management";
    }

    public function getVersion(){
        return "1.0";
    }

    public function run($command)
    {
        $return = array();

        // handle "commit"
        if ($command == "commit") {
            // add accounts and spread the deposited money over these accounts.
            $money = $this->moneytodeposit; // gets cleared at commit.
            $accountcreated = $this->accountstocreate;
            $win = $this->commit();
            if ($win){
                $this->setState("Done");
                if ($money){
                    $return[] = $this->playSound("accountmanagement/anditsgone.mp3");
                }
                if ($accountcreated){
                    $return[] = $this->playSound("accountmanagement/darkside.wav");
                }
                return $return;
            } else {
                $command = ""; // dont add account "commit" :)
            }
        }

        if (in_array($command,$this->getSynonyms())){
            $this->setState("Started");
        }


        // standard buttons
        if ($this->getState() != "Started") {
            $return[] = new Command("addaccount", "Add Account", "");
            $return[] = new Command("deposit", "Deposit Money", "");
            $return[] = new Command("withdraw", "Withdraw Money", "");
        }

        $accounts = $this->getAccounts();


        // standard hint
        if ($command != "" || $this->getState() == "Started"){
            $return[] = new Hint("Type an amount, new or existing username.");

            // return a list of accounts, when plugin is running...
            foreach ($accounts as $account) {
                $return[] = new AccountCommand($account, $account);
            }
        }


        // these flags are used to check if an account or new account have been addded to the lists
        // otherwise the account would be added to the list and instantly be removed. or vice versa...
        $accountstocreateAdded = false;
        $existingaccountstodepositAdded = false;


        // handle addaccount <accountname>
        // accountnames dont have spaces
        $exploded = explode(" ", $command);
        if (in_array($exploded[0], array("addaccount", "adduser"))){
            $this->setState("Started");
            if (isset($exploded[1]) && !$this->isAccount($exploded[1]) && !in_array($exploded[1], $this->accountstocreate)){
                $this->accountstocreate[] = $exploded[1];
                $accountstocreateAdded = true;
            }
        }

        // handle "<accountname>"
        if ($this->isAccount($command)){
            // todo: make separate objects of account lists..
            // todo: seems to be a bug with casing still. Not with more accountnames, but the account does not always get deleted from the list.
            $correctlyCasedAccountname = $this->looksLikeAnAccount($command);
            if (!in_array($correctlyCasedAccountname, $this->existingaccountstodeposit)) {
                $this->existingaccountstodeposit[] = $correctlyCasedAccountname;
                $existingaccountstodepositAdded = true;
            }
        }


        // handle <stringvalue> or <number>
        // <stringvalue> == new accountname
        // <number> == amount to deposit, only do this when all other things above are not performed...
        if ($this->getState() == "Started" && !empty($command)){
            $return[] = new Command("1", "Deposit <br />&euro;0.01", "");
            $return[] = new Command("2", "Deposit <br />&euro;0.02", "");
            $return[] = new Command("5", "Deposit <br />&euro;0.05", "");
            $return[] = new Command("10", "Deposit <br />&euro;0.10", "");
            $return[] = new Command("20", "Deposit <br />&euro;0.20", "");
            $return[] = new Command("50", "Deposit <br />&euro;0.50", "");
            $return[] = new Command("100", "Deposit <br />&euro;1.00", "");
            $return[] = new Command("200", "Deposit <br />&euro;2.00", "");
            $return[] = new Command("500", "Deposit <br />&euro;5.00", "");
            $return[] = new Command("1000", "Deposit <br />&euro;10.00", "");
            $return[] = new Command("2000", "Deposit <br />&euro;20.00", "");
            $return[] = new Command("5000", "Deposit <br />&euro;50.00", "");
            $return[] = new Command("-1", "Withdraw <br />&euro;0.01", "");
            $return[] = new Command("-10", "Withdraw <br />&euro;0.10", "");
            $return[] = new Command("-100", "Withdraw <br />&euro;1.00", "");
            $return[] = new Command("-1000", "Withdraw <br />&euro;10.00", "");

            // cast it to a number. like this:
            // 100 = 1 euro, 100 cents
            // 1.0 = 1 euro, 100 cents
            // 1,0 = 1 euro, 100 cents
            // 1. = 1 euro, 100 cents
            // .1 = 0.1 euro, 10 cents
            if ($this->isAmount($command)) {
                $this->moneytodeposit += $this->isAmount($command);
                // withdraws are ok
                // $this->moneytodeposit = ($this->moneytodeposit < 0) ? 0 : $this->moneytodeposit;
            } else {
                // add any accountname
                if (!in_array($command, $this->getSynonyms())
                    && !in_array($command, $this->getAccounts())
                    && !in_array($command, $this->accountstocreate)
                    && !$this->isAccount($command)
                    && $accountstocreateAdded == false) {
                    $this->accountstocreate[] = $command;
                } else {
                    // remove accountnames from the new accounts
                    if (!$accountstocreateAdded) {
                        if (in_array($command, $this->accountstocreate)) {
                            $this->accountstocreate = $this->array_removevalue($this->accountstocreate, $command);
                        }
                    }

                    if (!$existingaccountstodepositAdded) {
                        if (in_array($command, $this->existingaccountstodeposit)) {
                            $this->existingaccountstodeposit = $this->array_removevalue($this->existingaccountstodeposit, $command);
                        }
                    }
                }
            }
        }


        // render output

        $countSelectedAccounts = count($this->accountstocreate) + count($this->existingaccountstodeposit);
        if ($this->moneytodeposit) {
            if ($this->moneytodeposit > 0) {
                $return[] = new Value("Total deposit", $this->asCurrency($this->moneytodeposit));
                if ($countSelectedAccounts > 0){
                    $return[] = new Value("Selected account(s) gets about ", $this->asCurrency($this->moneytodeposit / $countSelectedAccounts));
                }
            }
            if ($this->moneytodeposit < 0) {
                $return[] = new Value("Total withdraw", $this->asCurrency($this->moneytodeposit));
                if ($countSelectedAccounts > 0) {
                    $return[] = new Value("Selected account(s) withdraws about ", $this->asCurrency($this->moneytodeposit / $countSelectedAccounts));
                }
            }
        }

        if ($countSelectedAccounts) {
            $return[] = new FinalCommand("commit", "Save!");
        }


        if (!empty($this->accountstocreate)){
            foreach($this->accountstocreate as $accountname){
                $return[] = new InvolvedAccountCommand($accountname,"*new* ".$accountname);
            }
        }

        if (!empty($this->existingaccountstodeposit)){
            foreach($this->existingaccountstodeposit as $accountname){
                $return[] = new InvolvedAccountCommand($accountname, $accountname);
            }
        }

        $return[] = $this->getSuggestions();

        return $return;
    }


    // not merging the accountstocreate and the existingaccounts
    private function commit(){
        // cannot commit when there are not new or existing accounts
        if (empty($this->accountstocreate) && empty($this->existingaccountstodeposit)){
            return false;
        }

        // add all new accounts
        foreach ($this->accountstocreate as $accounttocreate){
            $added = $this->addAccount($accounttocreate);
            if (!$added){
                return false;
            }
        }

        // divide the MONEY
        if ($this->moneytodeposit != 0) {
            $involvedAccounts = array_merge($this->accountstocreate, $this->existingaccountstodeposit);
            $moneyPerAccount = floor($this->moneytodeposit / count($involvedAccounts));
            $difference = $this->moneytodeposit - ($moneyPerAccount * count($involvedAccounts)); // the extra cents go to a random account
            $extrabenificiary = array_rand(array_flip($involvedAccounts), 1);

            //var_dump($involvedAccounts, $moneyPerAccount, $difference, $extrabenificiary);

            // add / remove money to new accounts
            foreach($involvedAccounts as $accountName) {
                $money = ($accountName == $extrabenificiary) ? ($moneyPerAccount + $difference) : $moneyPerAccount;
                $this->DB->query("UPDATE account SET deposit = deposit + %i WHERE name = %s", $money, $accountName);
                $reason = ($money > 0) ? "Deposit of ".$money."" : "Withdraw of ".$money."";
                $reason .= (count($involvedAccounts > 1) ? " shared with ".implode(", ",$involvedAccounts) : "" );
                $reason .= (count($involvedAccounts > 1) && ($extrabenificiary == $accountName) && $difference > 0) ? ", you ".($money > 0 ? "gained" : "kept")." ".$difference." extra due to rounding." : "" ;

                $this->DB->insert("depositmutation", array(
                    "account" => $accountName,
                    "cents" => $money,
                    "reason" => $reason,
                    "datetime" => DB::sqleval("NOW()")));
            }
        }

        // reset the values of the class
        $this->accountstocreate = array();
        $this->existingaccountstodeposit = array();
        $this->moneytodeposit = 0;

        return true;
    }

    /**
     * @return Array() list of synonyms for this plugin. required method by the plugin interface.
     *
     * If you want to start dynamiccaly, you also give all your dynamic values, such as usernames... or whatver is needed to start the plugin.
     */
    public function getSynonyms(){
        return array_merge($this->getAccounts(), array("deposit", "addaccount","adduser", "withdraw"));
    }

    // accountnames are at least 4 printable characters
    private function addAccount($accountName){
        // check if the name complies with all rules: no html and etc.
        $accountName = preg_replace('/[[:^alnum:]]/', '', $accountName);
        if (strlen($accountName) < 2){
            return false;
        }

        // ignore accounts that already exist.
        if (!$this->isAccount($accountName)) {
            return $this->DB->insert("account", array(
                "name" => $accountName,
                "deposit" => 0,
                "created" => DB::sqleval("NOW()")));
        }
    }

}