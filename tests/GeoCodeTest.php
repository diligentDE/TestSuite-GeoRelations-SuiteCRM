<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once("helper/TestCaseHelper.php");
require_once("helper/XmlHelper.php");

final class GeoCodeTest extends TestCase
{
    /**
     * additional cli output
     * @param $text
     */
    public function consoleLog($text)
    {
        fwrite(STDERR, $text . "\n");
    }

    /**
     * runs single unit tests for each point in every map
     * @dataProvider provideMapData
     * @param $point
     * @param $area
     */
    public function testPointWasPositionedCorrectly($point, $area)
    {
        $result = TestCaseHelper::doTestCase($point, $area, false);
        $this->assertSame("OK", $result['result'], strip_tags($result['html']));
    }

    /**
     * data provider for testPointWasPositionedCorrectly
     * @return array
     */
    public function provideMapData()
    {
        //get all maps
        $mapAreas = XmlHelper::parseAllAreas();
        $testCaseArray = array();
        foreach ($mapAreas as $singleMapTest) {
            $testArray = array_merge($singleMapTest['test_valid'],$singleMapTest['test_invalid']);
            $i=0;
            foreach ($testArray as $testPoint) {
                $testCaseArray[$singleMapTest['mapname'] . " / " . $testPoint['name'] . "." . $i] = array($testPoint, $singleMapTest['area']);
                $i++;
            }
        }
        return $testCaseArray;
    }
}