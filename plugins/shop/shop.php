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

// todo: producten met een ampersand kunnen niet worden ingevoerd als commando...
class shop extends DatabasePlugin {

    protected $DB = null;
    private $lastProductGroupName = ""; // hmm, todo
    private $lastProductName = ""; // hmm, todo
    private $buyers = Array(); // list of AccountNames, that buy one or more products together.

    // probably input database anywah here?
    public function __construct()
    {
        $this->DB = new DB();
        $this->shoppingCart = new ShoppingCart();
        //$this->DB->debugMode(true);
    }

    public function getName(){
        return "Shop";
    }

    public function getVersion(){
        return "1.0";
    }

    public function getHelp()
    {
        return "<h3>Accepted commands to start plugin</h3>
                <ul>
                    <li>&lt;productname&gt;, adds a product to the shoppingcart</li>
                    <li>&lt;productcode&gt;, adds a product to the shoppingcart</li>
                    <li>&lt;productgroup&gt;, shows all products in a group</li>
                    <li>shop, shows a list of productgroups</li>
                    <li>impulsebuy &lt;accountname&gt; &lt;productname&gt;, instantly buys a product for an account</li>
                </ul>
                <h3>Accepted commands when running</h3>
                <ul>
                    <li>&lt;productname&gt;, adds a product to the shoppingcart</li>
                    <li>&lt;productcode&gt;, adds a product to the shoppingcart</li>
                    <li>&lt;accountname&gt;, adds an account to the list of buyers, when entered again, removes it</li>
                    <li>back, shows a list of productgroups</li>
                    <li>removeone &lt;productname&gt;, removes one of a product from the shoppingcart</li>
                    <li>removeall &lt;productname&gt;, removes all of a product from the shoppingcart</li>
                    <li>addone &lt;productname&gt;, adds a product to the shoppingcart</li>
                    <li>removeaccount &lt;accountname&gt;, removes an account from the list of byyers</li>
                    <li>buy, tries to buy the products, handle payment and log everything</li>
                </ul>
                <h3>Functionality</h3>
                <p>Provides shopping functionality: picking products and buying them. <br/>
                It is possible to order multiple products and share payment over multiple accounts.<br />
                When doing this, one of the accounts might pay a little bit more because of rounding errors.</p>
                <h3>Start commands</h3>
                <p>This is a list of all commands that can be used to start this plugin:</p>
                <code>" . implode(", ", $this->getSynonyms()) . "</code>";
    }

    // this plugin is so simple, it does not have state. It only has one action: run when there is an input.
    // return array with the possible command(s) and possible GUI Actions.
    /**
     * Handles the following commands:
     * "" -> default
     * "<productname>" -> adds a productname to the shoppingcart
     * "<productcode>" -> adds a productname to the shoppingcart
     * "<accountname>" -> adds an account to the list of buyers
     * "<productgroup>" -> shows all products in a certain group
     * "back" -> go back to the product category list
     * "removeone <productname>" -> decrements the amount of a certain product in the shoppingcart
     * "removeall <productname>" -> removes the entire product from the shoppingcart
     * "addone <productname>" -> increments the amount of a certain product in the shoppingcart
     * "removeaccount <accountname>" -> removes an accountname from the list of buyers
     * "impulsebuy <accountname> <product>" -> buys a product for an account immediately
     * "buy" -> handles payment, distributes the total amount over the buyers and stores all transaction details.
     *
     * This function returns:
     * - a shopping cart object, for which the client can build an object
     * - a list of involved accounts, so it is clear what accounts are involved in the potential transaction
     */
    public function run($command)
    {

        // handle ""
        // default... plugin not loaded. you cannot add an empty string as an end user.
        if ($command == "") {
            $return = $this->getProductGroupCommands();
            $top3Products = $this->DB->query("select `popular products`.product, price, stock from `popular products` inner join `product` on `popular products`.product = product.name GROUP BY product ORDER BY sum(amountsold) DESC LIMIT 3 ");
            foreach ($top3Products as $product){
                $return[] = new command($product["product"], "Popular: ".$product["product"],"",$this->asCurrency($product["price"]),$product["stock"]);
            }
            $return[] = new command("shop", "Shop");
            $return[] = $this->getSuggestions(); // todc: does not accept productcodes on the start screen
            //$return[] = new command("shop", "Report");
            return $return;
        }

        // always return an array with something
        $return = array();

        // always add all shopping accounts
        $accounts = $this->getShopAccounts();
        foreach ($accounts as $account) {
            $return[] = new AccountCommand($account, $account);
        }

        $return[] = new Hint("Enter product, account or amount for unlisted products.");

        // handle "removeone <productname>"
        // these are functions for the shoppingcart button, you can add and remove some products with a tap.
        // "remove productname"
        if ($this->startsWith($command,"removeone")){
            $product = $this->removeFromStart($command, "removeone");
            $this->shoppingCart->removeOne($product); // should return everything that was returned the previous time.// or because empty no change? neeh.
        }

        // thins helps with suggesting a product based on crappy input. But only do this when you're sure that there is no
        // way left to check for a product or account
        $foundSomething = false;

        // handle "removeall <productname>"
        if ($this->startsWith($command,"removeall")){
            $product = $this->removeFromStart($command, "removeall");
            $this->shoppingCart->removeAll($product); // should return everything that was returned the previous time.
            $foundSomething = true;
        }

        // handle "addone <productname>"
        // addone productname, does not change the state, should return everything as it was last time, so the same group etc... (how?)
        if ($this->startsWith($command,"addone")){
            $return[] = $this->playSound("shop/beep.mp3"); // some response...
            $product = $this->removeFromStart($command, "addone");
            $this->shoppingCart->addOne($product); // should return everything that was returned the previous time.
            $foundSomething = true;
        }

        // handle "<productname>"
        // If only a product was chosen, try to add that product to the shoppingcart and show the group where product was in.
        if ($this->isProduct($command)) {
            $this->setState("Products"); // show the products in the productgroup containing this product
            $this->lastProductName = $command;
            $this->shoppingCart->addProductByName($command);
            $return[] = $this->playSound("shop/beep.mp3"); // some response...
            $foundSomething = true;
        }

        // handle "<accountname>"
        // adds an accountname to the list of buyers, if the accountname is in the list, it is removed from the list
        if ($this->isAccount($command)){
            // normalize account
            $accountName = $this->looksLikeAnAccount($command); // get the account with the correct casing.
            $this->addToBuyers($accountName);
            $foundSomething = true;
        }

        // handle "<productcode>"
        if ($this->isProductCode($command)) {
            $this->setState("Products"); // show the products in the productgroup containing this product
            $this->lastProductName = $this->getProductByCode($command); // retrieve productname...
            $this->shoppingCart->addProductByCode($command);
            $return[] = $this->playSound("shop/beep.mp3"); // some response...
            $foundSomething = true;
        }

        // handle <productgroup>
        if ($this->isProductGroup($command)){
            $this->lastProductGroupName = $command;
            $this->setState("Products");
            $foundSomething = true;
        }


        // handle "removeaccount <accountname>"
        // remove a buyer from the buyerlist
        if ($this->startsWith($command,"removeaccount")){
            $command = $this->removeFromStart($command, "removeaccount");
            if ($this->isAccount($command)){
                foreach ($this->buyers as $key => $account){
                    if ($account == $command){
                        unset($this->buyers[$key]);
                        $this->buyers = array_values($this->buyers); // and re-index.
                    }
                }
            }
        }

        // removen en dergelijke van unlisted products...

        // handle "sort or or part of productname... this is a last resort...
        if (!$foundSomething) {
            // first try if this is an account
            $accountName = $this->looksLikeAnAccount($command);
            $this->addToBuyers($accountName);

            // no account? then try a product
            if (!$accountName) {
                $productName = $this->looksLikeASingleProductOrProductCode($command);
                if ($productName) {
                    $this->setState("Products"); // show the products in the productgroup containing this product
                    $this->lastProductName = $command;
                    $this->shoppingCart->addProductByName($productName);
                    $return[] = $this->playSound("shop/beep.mp3"); // some response...
                }
            }

            // really nothing left? well.. then add it as an unlisted product
            // handle amounts for unlisted products...
            if ($this->isAmount($command)){
                // to prevent the listing of new / unknown barcodes as unlisted amounts, an amount cannot be higher than 10000 cents 100 currency
                if ($this->isAmount($command) < 10000) {
                    $this->shoppingCart->addUnlisted($this->isAmount($command));
                } else {
                    $return[] = new Hint("Unknown barcode, please type in the products value.");
                }
            }
        }



        // handle "impulsebuy <accountname> <product>"
        if ($this->startsWith($command,"impulsebuy")){
            $input = explode(" ",$command);
            // input 0 is "impulsebuy"
            // input 1 should be an account (expecting they are not spaces now) todo bad design?
            // input 2 and the rest should be a product
            $command = str_replace($input[0], "", $command);
            $command = str_replace($input[1], "", $command);
            $product = trim($command);

            if ($this->isAccount($input[1]) && $this->isProduct($product)){
                $this->setState("Done");
                $this->shoppingCart->addProductByName($product);
                $this->addToBuyers($input[1]);
                return $this->buy(); // ignore the rest of this plugin, we don't need anything else
            }
        }



        // handle "buy"
        if ($command == "buy"){
            // goes wrong? then false...
            $win = $this->buy();
            if (!$win){
                // log something and continue with the normal shop flow...
                // todo support errors.
            } else {
                // how to get deposit? should be a hook?


                $this->setState("Done");
                return $win;
            }
        }

        // try to clear the command from any input that might have happened. Main reason is that the state switch below then is executed cleanly.
        $command = $this->removeFromStart($command, "addone");
        $command = $this->removeFromStart($command, "removeall");
        $command = $this->removeFromStart($command, "removeone");


        // state handling

        // handle "back"
        // van een overzicht van producten kan je terug naar het overzicht van groepen.
        // nadat een product gekozen is ga je terug naar het huidige overzicht van producten.
        if ($command == "back"){
            switch ($this->getState()){
                case "Products";
                    $this->setState("start");
                    $this->lastProductName = ""; // start from scratch
                    $this->lastProductGroupName = ""; // start from scratch
                break;
                default:  $this->setState("start"); break;
            }
        }


        if (!$this->shoppingCart->isEmpty()) {
            // make it more beautiful for display (cents) or should the UI do this? i think the UI should...
            // but hey... this is easier now.
            $productsInCart = $this->shoppingCart->getContents();
            $productsGivenBack = array();
            foreach($productsInCart as $product){
                $tmpProduct = clone $product;
                $tmpProduct->individualPrice = $this->asCurrency($tmpProduct->individualPrice);
                $tmpProduct->accumulatedPrice = " = ".$this->asCurrency($tmpProduct->accumulatedPrice);
                $tmpProduct->amount = $tmpProduct->amount." x";
                $productsGivenBack[] = $tmpProduct;
            }
            $return[] = $productsGivenBack;
        }

        if (!empty($this->buyers)) {
            foreach($this->buyers as $buyer){
                $return[] = new InvolvedAccountCommand($buyer,$buyer,"");
            }
        }


        // handle "shop"
        if ($command == "shop"){
            $this->setState("start");
        }


        // display logic
        switch($this->getState()){
            case "start":
                $this->setState("Products"); // next state is products
                $return = array_merge($return, $this->getProductGroupCommands());
                break;
            case "Products":
                $this->setState("Products");
                $return[] = new Command("back","Back","b");
                if ($this->isProduct($command)){
                    // in case of a selected product, just show the group
                    $return = array_merge($return, $this->getProductsCommandsFromGroupByProduct($command));
                } else if ($this->isProductGroup($command)) {
                    // if a sepecific group is chosen, instead of a group, show the group
                    $return = array_merge($return, $this->getProductsCommandsFromGroup($command));
                } else if ($this->isProductCode($command)){
                    $return = array_merge($return, $this->getProductsCommandsFromGroupByCode($command));
                } else if (!empty($this->lastProductGroupName)){
                    // the current command is not a product or a group, try the last group
                    $return = array_merge($return, $this->getProductsCommandsFromGroup($this->lastProductGroupName));
                } else if (!empty($this->lastProductName)){
                    // the current command is not a product or a group, try the last product
                    $return = array_merge($return, $this->getProductsCommandsFromGroupByProduct($command));
                } else {
                    // absolute last result, we don't know what group to display from any way. So just show the groups
                    $return = array_merge($return, $this->getProductGroupCommands());
                }
                    //$return = array_merge($return, $this->getProductsCommandsFromGroup($command));
                    //$this->lastProductGroupName = $command;
                break;
        }

        if ($this->shoppingCart->getTotalAmount()){
            $return[] = new Value("Total Amount", $this->asCurrency($this->shoppingCart->getTotalAmount()));

            if (count($this->buyers) > 1){
                $return[] = new Value("Each account pays about ", $this->asCurrency($this->shoppingCart->getTotalAmount() / count($this->buyers)));
            }
        }


        if (!$this->shoppingCart->isEmpty() && !empty($this->buyers)) {
            $return[] = new FinalCommand("buy", "TAKE MY MONEY!");
        }

        $return[] = $this->getSuggestions();

        // but if the plugin is running, also return the accountnames as suggestions.
        $shopAccounts = $this->getShopAccounts();
        foreach($shopAccounts as $account){
            $suggestions = new Suggestion($account,$account);
        }
        $return[] = $suggestions;

        return $return;

    }

    // returns only productgroups that have products in them.
    private function getProductGroups(){
        // SELECT name FROM `product group` inner join product on product.`group` = `product group`.name group by `product`.`group` having count(product.name) > 1
        return $this->DB->queryFirstColumn("SELECT DISTINCT(`product group`.name) FROM `product group` INNER JOIN product on (`product group`.name = product.`group`)");
    }


    private function addToBuyers($command){
        // todo: fix proper accountcasing here...
        if (!$this->isAccount($command)){
          return false;
        }

        if (!in_array($command, $this->buyers)){
            $this->buyers[] = $command;
        } else {
            $this->buyers = $this->array_removevalue($this->buyers, $command);
        }

    }


    private function isProductGroup($command){
        return $this->DB->queryFirstField("SELECT 1 FROM `product group` where name = %s LIMIT 1", $command);

    }

    private function getProductGroupCommands(){
        $productGroups = $this->getProductGroups();
        $return = array();
        foreach ($productGroups as $productGroup){
            $return[] = new command($productGroup, $productGroup);
        }
        return $return;
    }


    private function getShopAccounts(){
        return $this->DB->queryFirstColumn("select name from `account`"); // can be something else than a normal accounts list... then change it here.
    }


    private function getProductsCommandsFromGroup($group)
    {
        $products = $this->DB->query("SELECT * FROM `product` where `group` = %s and purchasable = 1", $group);
        //print_r($products);
        $return = array();
        foreach ($products as $product) {
            $return[] = new Command($product["name"], $product["name"],"",$this->asCurrency($product["price"]), $product["stock"]);
        }
        return $return;
    }

    private function getProductsCommandsFromGroupByProduct($product)
    {
        $group = $this->DB->queryFirstField("SELECT `group` FROM `product` where `name` = %s",$product);
        return $this->getProductsCommandsFromGroup($group);
    }

    private function getProductsCommandsFromGroupByCode($productCode)
    {
        $group = $this->DB->queryFirstField("SELECT `group` FROM `product` inner JOIN `productcode` ON (`product`.`name` = `productcode`.`product`) where `productcode`.`code` = %s",$productCode);
        return $this->getProductsCommandsFromGroup($group);
    }


    private function getProductByCode($command){
        return $this->DB->queryFirstField("SELECT product FROM `productcode` where code = %s", $command);
    }


    /**
     * $shop->printReceipt(); // does too much, observable pattern? needs more instances though.
     */

    // when this shows false, just show something from the shop or something, so ppl can fix this.
    // it it shows true, than you may do smoething with some other output :)
    private function buy(){

        // you need to have products in the shoppingcart
        if (empty($this->shoppingCart)) {
            return false;
        }
        // you need to have buyers
        if (empty($this->buyers)){
            return false;
        }

        // make sure that all database data is consistent during this set of operations.
        $this->DB->startTransaction();

        // check if all buyers are accounts... not be fault tolerant, so no magical things happen.
        foreach($this->buyers as $key => $buyer){
            if (!$this->isAccount($buyer)){
                return false;
            }
        }

        // todo: check that all products exist at the moment of checkout, if not, then return remove the products and continue shopping... without warnings..
        // todo: isEmpty shoppingcart does not work.
        //print_r($this->shoppingCart);
        // straightforward amounts, without any rules and other crap.
        $totalAmount = $this->shoppingCart->getTotalAmount();

        $this->DB->insert("transaction", array(
            "price" => $totalAmount,
            "price calculation" => "default",
            "datetime" => DB::sqleval("NOW()")));

        $transactionId = $this->DB->insertId();

        // register the products that where sold in this transaction
        $soldProducts = $this->shoppingCart->getListedContentsOnly();
        foreach($soldProducts as $soldProduct){
            $this->DB->insert("sold product", array(
                "transaction" => $transactionId,
                "product" => $soldProduct->name,
                "amount" => $soldProduct->amount,
                "price" => $soldProduct->individualPrice));

            $this->DB->query("UPDATE `product` SET stock = stock - %i where name = %s", $soldProduct->amount, $soldProduct->name);
        }


        $soldProducts = $this->shoppingCart->getUnlistedContentsOnly();
        foreach($soldProducts as $soldProduct){
            $this->DB->insert("unlisted product", array(
                "transaction" => $transactionId,
                "possible name" => $soldProduct->name,
                "amount" => $soldProduct->amount,
                "price" => $soldProduct->individualPrice));
        }

        // register who payed things
        $amountPerPerson = floor($totalAmount / count($this->buyers));

        // handle rounding errors. A random person has to pay a little bit more...
        $extraMoney = $totalAmount - ($amountPerPerson * count($this->buyers));
        $extrapayer = array_rand(array_flip($this->buyers), 1);

        // withdraw the money from the accounts
        // store the payshares for administration and undo purposes
        foreach($this->buyers as $accountName){
            $money = ($accountName == $extrapayer) ? ($amountPerPerson + $extraMoney) : $amountPerPerson;
            $reason = (count($this->buyers) > 1 ? "Shared Shop transaction ".$transactionId." with ".implode(", ", $this->buyers) : "Shop transaction ".$transactionId);
            $reason .= " bought: ".$this->shoppingCart->getAsString();
            $reason .= " total transaction value: ".$this->shoppingCart->getTotalAmount().". Thank you, come again!";

            // divide the payments among the accounts
            $this->DB->insert("payshare", array(
                "transaction" => $transactionId,
                "account" => $accountName,
                "amount" => $money));

            $this->logDepositMutation($accountName, $money-($money*2), $reason);

            $this->DB->query("UPDATE `account` set deposit = deposit - %i WHERE name = %s",$money, $accountName);
        }

        // add the current deposit value, is handy to have
        foreach($this->buyers as $accountName) {
            $deposit = $this->DB->queryFirstField("SELECT `deposit` from account where name =  %s", $accountName);
            $return[] = new Value("Balance for ".$accountName, $this->asCurrency($deposit));
        }

        // finish the transaction
        $this->DB->commit();

        // some postprocessing...
        // check if someone is poor now...
        $povertyDetected = $this->DB->query("SELECT 1 FROM `account` WHERE name IN %ls and deposit < -1337", $this->buyers);
        if ($povertyDetected) {
            $return[] = $this->playSound("shop/sinterklaas.mp3");
        }

        // check if belgian waffles have been ordered :)
        //print_r($this->shoppingCart);
        if ($this->shoppingCart->containsProductByName("Belgian Waffles")) {
            $return[] = $this->playSound("shop/belgian waffles.wav");
        }

        // and finally make sure the shoppingcart is thrown in a ditch and catches fire without warrant
        $this->shoppingCart->clear();

        $return[] = $this->playSound("shop/kassa.wav");
        return $return;
    }


    public function getStatistics(){
        $moneyInProductsSold = $this->DB->queryFirstField("select sum(price * amount) from `sold product`");
        $depositValue = $this->DB->queryFirstField("select * from `deposit value`");
        $turnover = ($depositValue > 0) ? ($moneyInProductsSold) : ($moneyInProductsSold + $depositValue);

        $return = array();
        $return[] = new Value("Deposit Value",$this->asCurrency($depositValue));
        $return[] = new Value("Products Sold",$this->DB->queryFirstField("select sum(amount) from `sold product`"));
        $return[] = new Value("Products sold in euro", $this->asCurrency($moneyInProductsSold));
        $return[] = new Value("Revenue (sold + debts)", $this->asCurrency($turnover));
        $return[] = new Value("Approximate Stock Value",$this->asCurrency($this->DB->queryFirstField("select * from `stock value`")));
        $return[] = new Value("Number of products in stock","# ".$this->DB->queryFirstField("select sum(stock) from `product`"));
        $return[] = new Value("Number of different products","# ".$this->DB->queryFirstField("select count(*) from `product`"));
        $return[] = new Value("Number of productgroups","# ".$this->DB->queryFirstField("select count(*) from `product group`"));
        $return[] = new Dataset("Shamelist", $this->DB->query("select name, deposit from `account` WHERE deposit < 0 ORDER BY DEPOSIT ASC LIMIT 30"));
        $return[] = new Dataset("Top 10 popular products", $this->DB->query("select product, sum(amountsold) as amount from `popular products` GROUP BY product ORDER BY amount DESC LIMIT 30"));
        $return[] = new Dataset("Transaction value per month", $this->DB->query("select * from `transaction value per month`"));
        $return[] = new Dataset("Transaction value per week", $this->DB->query("select * from `transaction value per week`"));
        $return[] = new Dataset("Shoppinglist (min 15 products in stock)", $this->DB->query("select * from `shopping list` order by stock ASC"));


        // charts of the last 3 weeks, i made a mistake between months and weeks :)
        $return[] = $this->getCharts(date("m", strtotime("today")), date("Y", strtotime("today")));
        $return[] = $this->getCharts(date("m", strtotime("-1 month")), date("Y", strtotime("-1 month")));
        $return[] = $this->getCharts(date("m", strtotime("-2 months")), date("Y", strtotime("-2 months")));
        $return[] = $this->getCharts(date("m", strtotime("-3 months")), date("Y", strtotime("-3 months")));

        // Look at the history of the top sellers of last month. See how they evolve over 10 weeks.
        $products = $this->DB->queryFirstColumn("select source_chart_product as `product` from `monthly chart` WHERE source_chart_month = %i AND source_chart_year = %i order by source_chart_position ASC LIMIT 10", date("m", strtotime("-1 months")), date("Y", strtotime("-1 months")));
        $string = "";
        foreach ($products as $product) {
            $string .= "&products[]=".$product;
        }
        $return[] = new Image("plugins/statistics/popularProductHistoryBarchart.php?year=".date("Y", strtotime("-2 months"))."&weeknumber=".date("W", strtotime("-10 weeks"))."&weeks=10".$string, "Flow of past months top products");

        return $return;
    }

    function getCharts($month, $year)
    {
        return new Dataset("Chart of month ".$month." ".$year, $this->DB->query("select source_chart_position as `position`, positions_altered as `up/down`, source_chart_product as `product`, source_chart_amount as `# sold`
                                                                from `monthly chart` WHERE source_chart_month = %i AND source_chart_year = %i order by source_chart_position ASC LIMIT 20", $month, $year));

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

        $productcodes = $this->DB->queryFirstColumn("select `code` from `productcode`");
        $productgroups = $this->DB->queryFirstColumn("select `name` from `product group`"); // todo, consistent naming, without space?
        $products = $this->DB->queryFirstColumn("select `name` from `product`");
        $synonyms = array_merge($productcodes, $productgroups, $products, Array("shop", "impulsebuy"));
        return $synonyms;

    }

}