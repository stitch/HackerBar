<?php
// since singletons are evil, and DI requires more boilerplate, we just make the simplest approach possible
// and you can just instatiate this everywhere to have that singleton feeling.
class Configuration
{
    public static $version = "1.0";
    public static $productName = "Hacker Bar";

    // THE DATABASE SETTINGS ARE STORED IN THE MEEKRODB CLASS!

    public static $plugins = Array();

    public function __construct()
    {
        // in relevant order...

        $this->plugins[] = "shop";
        $this->plugins[] = "accountinformation";
        $this->plugins[] = "accountmanagement";
        $this->plugins[] = "giveandtake";
        $this->plugins[] = "foghorn";
        $this->plugins[] = "soundboard";
        $this->plugins[] = "spacecams";
        $this->plugins[] = "youtube";
        $this->plugins[] = "soma";
        $this->plugins[] = "statistics";
        $this->plugins[] = "restock";
        $this->plugins[] = "undo";
        $this->plugins[] = "spacestate";
        $this->plugins[] = "sessions";
        $this->plugins[] = "help";
        $this->plugins[] = "about";

        /*$this->plugins[] = "deposit";
        $this->plugins[] = "account";
        $this->plugins[] = "stock";
        $this->plugins[] = "buy";
        $this->plugins[] = "deposit";
        $this->plugins[] = "report";
        $this->plugins[] = "unbuy";
        $this->plugins[] = "help";
        $this->plugins[] = "transfer"; // take, give
        $this->plugins[] = "withdraw";*/
    }
}
