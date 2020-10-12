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
     * runs single unit tests for each point in every map with full geocodes
     * @dataProvider provideMapData
     * @param $point
     * @param $area
     */
    public function testPointWasPositionedCorrectlyWithFullGeocodes($point, $area)
    {
        $result = TestCaseHelper::doTestCase($point, $area, false);
        $this->assertSame("OK", $result['result'], strip_tags($result['html']));
    }

    /**
     * runs single unit tests for each point in every map with shorter geocodes
     * @dataProvider provideMapData
     * @param $point
     * @param $area
     */
    public function testPointWasPositionedCorrectlyWithShorterGeocodes($point, $area)
    {
        $result = TestCaseHelper::doTestCase($point, $area, true);
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
            foreach ($singleMapTest['test_valid'] as $testPoint) {
                $testCaseArray[$singleMapTest['mapname'] . " / " . $testPoint['name']] = array($testPoint, $singleMapTest['area']);
            }
            foreach ($singleMapTest['test_invalid'] as $testPoint) {
                $testCaseArray[$singleMapTest['mapname'] . " / " . $testPoint['name']] = array($testPoint, $singleMapTest['area']);
            }
        }
        return $testCaseArray;
    }
}