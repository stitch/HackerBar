<?php

include("pChart2.1.4/class/pData.class.php");
include("pChart2.1.4/class/pDraw.class.php");
include("pChart2.1.4/class/pImage.class.php");
include("pChart2.1.4/class/pCache.class.php");

// only for development
include("../../classes/meekrodb.2.3.class.php");
include("../../classes/ChartDataset.php");

class makeChart
{
    private $DB;

    public function flushCache(){
        /* Create the cache object */
        $myCache = new pCache();
        /* Flush the cache contents*/
        $myCache->flush();
    }

    public function barchart($yearStart, $weekStart, $weeks, array $products){
        $this->DB = new MeekroDB();
        $this->DB->debugMode(false);

        // todo: handle week-labels with a +0, so week 01. in the database that is.
        // the year+week and period you want to want to start viewing statistics.
        //$yearStart = 2015;
        //$weekStart = 10;

        $year = $yearStart;
        $week = $weekStart;
        //$weeks = 10;

        $labels = array();
        for($i=$weeks;$i>0;$i--){
            $labels[] = $year.str_pad($week,2,"0",STR_PAD_LEFT);
            $week += 1;
            if ($week > 52) { $week = 1; $year +=1; }
        }

        // what products we need to view
        //$products = ["Club Mate (50cl)", "Coca Cola (33cl)", "Club Mate Cola (33cl)"];

        // very fucking inefficient, or have a db table with all weeks OR write it in PHP *less preferable* or make it ineffecient and easy.
        $productDatas = array();
        $chartDataset = new ChartDataset();
        foreach ($products as $product) {
            foreach ($labels as $label){
                $productDatas[$product][]  = $this->DB->queryFirstField("select sumamount from `barchart sales` where product = %s and label = %s order by product, label ASC limit 10", $product, $label);
            }
            //print_r($productDatas[$product]);
            $chartDataset->data[] = array("points" => $productDatas[$product], "label" =>  $product);
        }

        // add the missing labels to the result set, when there have been no sales for that week.
        $chartDataset->data[] = array("points" => $labels, "label" =>  "Week");
        //print_r($chartDataset->data);
        $chartDataset->yaxisname = "sales";
        $this->drawBarChart($chartDataset);
    }



    protected function drawBarChart(ChartDataset $CDs)
    {

        if (!extension_loaded("gd") && !extension_loaded("gd2")) {
            /* Extension not loaded */
            // NO BARCHARTS AND ERRORS FOR YOU!
            return;
        }


        /* Create and populate the pData object */
        $MyData = new pData();

        if (empty($CDs->data)) return;
        //print_r($CDs->data);
        foreach ($CDs->data as $points) {
            //print_r($points);
            $MyData->addPoints($points["points"], $points["label"]);
        }

        $MyData->setAxisName(0, "Amount Sold");
        $MyData->setSerieDescription($points["label"], $points["label"]);
        $MyData->setAbscissa($points["label"]);

        /* Create the pChart object */
        $myPicture = new pImage(1000, 230, $MyData);
        // the horrible way to add fonts is just one bunch of fucked up shit, not making sense. bullshit parameters and paths.
        //$myPicture->setFontProperties(array("FontName"=>"Bedizen"));
        $myPicture->setFontProperties(array("FontName" => "pChart2.1.4//fonts/calibri.ttf", "FontSize" => 11));
        //print_r(is_file("C:/xampp_jan2015/htdocs/hackerbar/web/fonts/GeosansLight.ttf"));

        /* Turn of Antialiasing */
        $myPicture->Antialias = false;

        /* Add a border to the picture */
        $myPicture->drawGradientArea(0, 0, 1000, 230, DIRECTION_VERTICAL, array("StartR" => 240, "StartG" => 240, "StartB" => 240, "EndR" => 180, "EndG" => 180, "EndB" => 180, "Alpha" => 100));
        $myPicture->drawGradientArea(0, 0, 1000, 230, DIRECTION_HORIZONTAL, array("StartR" => 240, "StartG" => 240, "StartB" => 240, "EndR" => 180, "EndG" => 180, "EndB" => 180, "Alpha" => 20));
        $myPicture->drawRectangle(0, 0, 999, 229, array("R" => 0, "G" => 0, "B" => 0));

        /* Set the default font */
        //$myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>6));

        /* Define the chart area */
        $myPicture->setGraphArea(60, 40, 850, 200);

        /* Draw the scale */
        $scaleSettings = array("GridR" => 200, "GridG" => 200, "GridB" => 200, "DrawSubTicks" => TRUE, "CycleBackground" => TRUE);
        $myPicture->drawScale($scaleSettings);

        /* Write the chart legend */
        $myPicture->drawLegend(860, 25, array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_VERTICAL ));

        /* Turn on shadow computing */
        $myPicture->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

        /* Draw the chart */
        $myPicture->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
        $settings = array("Surrounding" => -30, "InnerSurrounding" => 30);
        $myPicture->drawBarChart($settings);

        /* Render the picture (choose the best way) */
        $myPicture->autoOutput("images/barcharts/" . $points["label"] . ".png");
        //$myPicture->auto

        $myPicture->render("myfile.png");

    }
}

$makeChart = new makeChart();
//$makeChart->flushCache();

if (!isset($_GET['year'])) $_GET['year'] = date("Y");
if (!isset($_GET['weeknumber'])) $_GET['weeknumber'] = date("Y");
if (!isset($_GET['weeks'])) $_GET['weeks'] = 1;
if (!isset($_GET['products'])) $_GET['products'] = array("Club Mate (50cl)");

$makeChart->barchart($_GET['year'],$_GET['weeknumber'],$_GET['weeks'],$_GET['products']);

?>