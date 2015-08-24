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
 * Returns some datasets that are based on database views
 */
// todo; work with named datasets, these can be dumped in some sort of (jquery?) datagrid. datasetObject(name, data)
class restock extends DatabasePlugin {

    private $selectedProduct = "";
    private $changeAmount = 0;
    private $currentAmount = -900000; //
    private $originalAmount = 0; // the amount of the product before it's mutated. So you can reset it, even after you've changed it a couple of times...

    public function getName(){
        return "Product Management";
    }

    public function getVersion(){
        return "1.1";
    }

    public function getHelp()
    {
        return "<h3>Product Management</h3>
                <ul>
                    <li>restock, addproduct</li>
                </ul>
                <h3>Functionality</h3>
                <p>Changes the stock value of a product and lets everyone create new products.</p>
                <h3>Start commands</h3>
                <p>This is a list of all commands that can be used to start this plugin:</p>
                <code>" . implode(", ", $this->getSynonyms()) . "</code>";
    }

    public function run($command)
    {
        // synonym used
        $return = array();
        if ($command == "") {
            $return[] = new Command("restock","Restock Products");
            return $return;
        }

        if ($command == "dorestock"){
            if ($this->currentAmount > -900000 and $this->changeAmount && $this->selectedProduct){
                $restockValue = ($this->currentAmount + $this->changeAmount);
                $this->DB->query("UPDATE product SET stock = %i where name = %s", $restockValue, $this->selectedProduct);

                // empty the current settings, otherwise restock-clicks fast clicks create weird changes.
                $this->selectedProduct = "";
                $this->changeAmount = 0;
                $this->currentAmount = -900000;
            }
        }

        $return[] = new Hint("Enter new amount for (to be) selected product...");

        if ($this->isProduct($command)){
            $this->selectedProduct = $command;
        }

        if ($this->selectedProduct){
            $return[] = new Value("Selected Product", $this->selectedProduct);

            $productInfo = $this->DB->queryFirstRow("select * from product where name = %s", $this->selectedProduct);
            $this->currentAmount = $productInfo["stock"];
        }

        // prevent products such as 7up to be an amount, this is a bugfix.
        if (!$this->isProduct($command) and $this->isAmount($command)){
            $this->changeAmount = $command;
        }

        if ($this->currentAmount > -900000){
            $return[] = new Value("Current stock", $this->currentAmount);
        }


        if ($this->changeAmount){
            $return[] = new Value("Mutation", $this->changeAmount);
        }


        if ($this->currentAmount > -900000 and $this->changeAmount){
            $return[] = new Value("New stock", ($this->currentAmount + $this->changeAmount));
            $return[] = new FinalCommand("dorestock", "Change Stock");
        }

        $return[] = $this->getAllProductCommands();

        $return[] = $this->getSuggestions();

        return $return;
    }


    private function getAllProductCommands()
    {
        $products = $this->DB->query("SELECT * FROM `product` WHERE purchasable = 1");

        $return = array();
        foreach ($products as $product) {
            $return[] = new Command($product["name"], $product["name"],"",$this->asCurrency($product["price"]), $product["stock"]);
        }

        return $return;
    }

    /**
     * @return Array() list of synonyms for this plugin. required method by the plugin interface.
     *
     * If you want to start dynamiccaly, you also give all your dynamic values, such as usernames... or whatver is needed to start the plugin.
     */
    public function getSynonyms(){
        return array("restock");
    }

}