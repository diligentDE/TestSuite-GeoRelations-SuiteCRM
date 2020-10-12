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
    $html .= TestCaseHelper::doMapTest($singleMapTest);
}
echo $html;
