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

class giveandtake extends DatabasePlugin {

    private $involvedAccounts = Array();
    private $beneficiary = ""; // multiple people can give money to an account (spread costs) "give"
    private $victim = ""; // multiple people can take money from an account (undo spread costs, whatever reason) "take"
    private $money = "";

    public function getName(){
        return "Give and Take";
    }

    public function __construct(){
        $this->DB = new DB();
        parent::__construct();
    }


    public function run($command){
        //$this->setState("Done"); // 1 button, returns some HTML but nothing more...
        $return = array();

        // handle "" (default)
        if ($command == ""){
            $return[] = new Command("give", "Give money");
            $return[] = new Command("take", "Take money");
            return $return;
        }
        // this is only run when the plugin is started...


        // handle <account>
        if ($this->isAccount($command) && $this->getState() != "give" && $this->getState() != "take"){
            $correctCasing = $this->looksLikeAnAccount($command);;
            if (in_array($command, $this->involvedAccounts)){
                $this->involvedAccounts = $this->array_removevalue($this->involvedAccounts, $correctCasing);
            } else {
                $this->involvedAccounts[] = $correctCasing;
            }
        }

        // handle <amount>
        if ($this->isAmount($command)){
            $this->money += $this->isAmount($command);
        }

        // handle "take" and the next accountname
        if ($command == "take"){
            $return[] = new Hint("What account to take from?");
            $this->setState("take");
        }

        if ($this->getState() == "take"){
            if ($this->isAccount($command)){
                $command = $this->looksLikeAnAccount($command);
                $this->beneficiary = "";
                $this->victim = $command;
                $this->setState("");
            }
        }

        // handle "give" and the next accountname
        if ($command == "give"){
            $return[] = new Hint("What account to give to?");
            $this->setState("give");
        }

        if ($this->getState() == "give"){
            if ($this->isAccount($command)){
                $command = $this->looksLikeAnAccount($command);
                $this->beneficiary = $command;
                $this->victim = "";
                $this->setState("");
            }
        }


        // check if there is both a (beneficiary or victim) and involvedaccounts
        if ($command == "finish" and (!empty($this->involvedAccounts)) and (!empty($this->beneficiary) or !empty($this->victim))){


            // check involvedaccounts areAccountNames?
            // check if there is an amount.
            $notDividedByZero = (count($this->involvedAccounts)>0) ? count($this->involvedAccounts) : 1;
            $amountPerAccount = floor($this->money / $notDividedByZero); // possibly lose some cents... todo: fix
            $extraMoney = $this->money - ($amountPerAccount * $notDividedByZero);
            $extraGiverOrReceiver = array_rand(array_flip($this->involvedAccounts), 1);
            // bij give: 1 persoon geeft wat meer
            // bij take: van 1 persoon wordt wat meer weggenomen

            $this->DB->startTransaction();
            if ($this->beneficiary){
                // withdraw parts of the money from each of the accounts
                $this->DB->query("UPDATE account SET deposit = deposit + %i where name = %s", $this->money, $this->beneficiary);
                $this->logDepositMutation($this->beneficiary, $this->money, "Got money from ".implode(", ",$this->involvedAccounts).".");
                foreach($this->involvedAccounts as $accountName){
                    $money = ($accountName == $extraGiverOrReceiver) ? ($amountPerAccount + $extraMoney) : $amountPerAccount;
                    $extraMessage = ($accountName == $extraGiverOrReceiver && $this->involvedAccounts > 1 && $extraMoney) ? " You gave a little bit extra due to rounding." : "";
                    $this->DB->query("UPDATE account SET deposit = deposit - %i where name = %s",$money, $accountName);
                    $this->logDepositMutation($accountName, $money, "Gave money to ".$this->beneficiary.".".$extraMessage);
                }
            }

            if ($this->victim){
                // divide the amount amongst the mentioned accounts
                $this->DB->query("UPDATE account SET deposit = deposit - %i where name = %s", $this->money, $this->victim);
                $this->logDepositMutation($this->victim, $this->money, "Gave money to ".implode(", ",$this->involvedAccounts).".");
                foreach($this->involvedAccounts as $accountName){
                    $money = ($accountName == $extraGiverOrReceiver) ? ($amountPerAccount + $extraMoney) : $amountPerAccount;
                    $extraMessage = ($accountName == $extraGiverOrReceiver && $this->involvedAccounts > 1 && $extraMoney) ? " You gave a little bit extra due to rounding." : "";
                    $this->DB->query("UPDATE account SET deposit = deposit + %i where name = %s",$money, $accountName);
                    $this->logDepositMutation($accountName, $money, "Got money from ".$this->victim.".".$extraMessage);
                }
            }
            $return[] = $this->playSound("giveandtake/kassa.wav");
            $this->DB->commit();
            $this->setState("Done");
            return $return;
        }

        // and display the state:
        // default hint
        if ($this->getState() != "take" and $this->getState() != "give"){
            $return[] = new Hint("Type in value or choose involved accounts.");
        }

        // help with some standard amounts.
        $return[] = new Command("give", "Give money");
        $return[] = new Command("take", "Take money");
        $return[] = new Command("1", $this->asCurrency(1));
        $return[] = new Command("2", $this->asCurrency(2));
        $return[] = new Command("5", $this->asCurrency(5));
        $return[] = new Command("10", $this->asCurrency(10));
        $return[] = new Command("20", $this->asCurrency(20));
        $return[] = new Command("50", $this->asCurrency(50));
        $return[] = new Command("100", $this->asCurrency(100));
        $return[] = new Command("200", $this->asCurrency(200));
        $return[] = new Command("500", $this->asCurrency(500));
        $return[] = new Command("1000", $this->asCurrency(1000));
        $return[] = new Command("2000", $this->asCurrency(2000));
        $return[] = new Command("5000", $this->asCurrency(5000));
        $return[] = new Command("-1", $this->asCurrency(-1));
        $return[] = new Command("-10", $this->asCurrency(-10));
        $return[] = new Command("-100", $this->asCurrency(-100));
        $return[] = new Command("-1000", $this->asCurrency(-1000));
        $accounts = $this->getAccounts();

        if ($this->money > 0){
            if ($this->beneficiary){
                $notDividedByZero = (count($this->involvedAccounts)>0) ? count($this->involvedAccounts) : 1;
                $return[] = new Value("Amount of money payed per person", $this->asCurrency($this->money));
                $return[] = new Value($this->beneficiary." will get from each about", $this->asCurrency($this->money / $notDividedByZero));
                if (round($this->money / $notDividedByZero) != $this->money / $notDividedByZero){
                    $return[] = new Value("One account pays a little more due to rounding.", "");
                }

            }
            if ($this->victim){
                // and prevent a classic division by zero.
                $notDividedByZero = (count($this->involvedAccounts)>0) ? count($this->involvedAccounts) : 1;
                //var_dump($notDividedByZero);
                $return[] = new Value("Amount of money to distribute", $this->asCurrency($this->money));
                $return[] = new Value($this->victim." will pay each about", $this->asCurrency($this->money / $notDividedByZero));
                // place a notice on rounding of currency
                if (round($this->money / $notDividedByZero) != $this->money / $notDividedByZero){
                    $return[] = new Value("One account gets a little more due to rounding.", "");
                }

            }
            if (!$this->beneficiary and !$this->victim){
                $return[] = new Value("Amount", $this->asCurrency($this->money));
            }
        }

        if ($this->beneficiary != ""){
            $return[] = new FinalCommand("finish","Give ".$this->beneficiary." money!");
        }

        if ($this->victim != ""){
            $return[] = new FinalCommand("finish","Share ".$this->victim."'s  wealth!");
        }

        $return[] = $this->autoSuggest($return); // have some significant suggestions...

        // return a list of accounts
        foreach ($accounts as $account) {
            $return[] = new AccountCommand($account, $account);
        }

        foreach ($this->involvedAccounts as $account) {
            $return[] = new InvolvedAccountCommand($account, $account);
        }

        return $return;
    }

    public function getSynonyms(){
        return  Array("give","take");
    }

}