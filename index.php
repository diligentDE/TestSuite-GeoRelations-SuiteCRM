<?php
require "vendor/autoload.php";
require "helper/XmlHelper.php";
require "helper/LayoutHelper.php";
require "helper/TestCaseHelper.php";

//add a bit css
LayoutHelper::insertCss();

//load map data
$mapAreas = XmlHelper::parseAllAreas();
$html = '';
//run test case foreach map found in /maps/
foreach ($mapAreas as $singleMapTest) {
    $html .= TestCaseHelper::doMapTest($singleMapTest); // full geocodes
    $html .= TestCaseHelper::doMapTest($singleMapTest, true); //same test cases with Suite's decimals (2 instead of 5)
}
echo $html;
