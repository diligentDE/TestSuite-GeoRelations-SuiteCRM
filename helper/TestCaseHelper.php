<?php
require "vendor/autoload.php";

class TestCaseHelper
{
    /**
     * creates html/restresult array per found kml file/map
     * @param $singleMap
     * @param false $shorter_codes
     * @return string
     */
    public static function doMapTest($singleMap, $shorter_codes = false)
    {
        $html = '';
        $mapName = ($shorter_codes) ? $singleMap['mapname'] . ' (shorter geocodes!)' : $singleMap['mapname'];
        $html = <<<html
<table>
<thead> 
<tr>
    <th>Map:</th>
    <th>$mapName</th>
</tr>
   
</thead>
<tbody>
html;
        $total = 0;
        $correct = 0;
        $incorrect = 0;

        $testArray = array_merge($singleMap['test_valid'],$singleMap['test_invalid']);

        //test points inside the area
        foreach ($testArray as $testPoint) {
            $resultSet = self::doTestCase($testPoint, $singleMap['area'], $shorter_codes);
            $html .= $resultSet['html'];
            if ($resultSet['result'] == "OK") {
                $total++;
                $correct++;
            } elseif ($resultSet['result'] == "NOK") {
                $total++;
                $incorrect++;
            }
        }

        $score = round($correct / $total * 100, 2);
        $html .= <<<html
</tbody>
<tr class="resultrow">
    <td>Score:</td>
    <td>{$score}% ({$correct} out of {$total})</td>
</tr>
</table>
html;
        return $html;
    }

    /**
     * decides per point if inside/outside area
     * @param $point
     * @param $area
     * @param false $shorter_codes
     * @return string[]
     */
    public static function doTestCase($point, $area, $shorter_codes = false)
    {
        $lng = $point['lng'];
        $lat = $point['lat'];

        //suite is using only 2 digits!
        if ($shorter_codes === true) {
            $lng = round($lng, 2);
            $lat = round($lat, 2);
        }

        $response = \GeometryLibrary\PolyUtil::containsLocation([
            'lng' => $lng,
            'lat' => $lat
        ],
            $area,
            false
        );
        $html = '<tr>';
        $result = '';
        if ($response === true && $point['expectedResult'] == "inside") {
            $html .= '<td class="success">OK</td>';
            $html .= '<td>Lib returned true, point is in area (' . $point['name'] . ')</td>';
            $result = "OK";
        }
        if ($response === true && $point['expectedResult'] == "outside") {
            $html .= '<td class="failed">NOT OK</td>';
            $html .= '<td>Lib returned true, but point is not in area (' . $point['name'] . ')</td>';
            $result = "NOK";
        }
        if ($response === false && $point['expectedResult'] == "outside") {
            $html .= '<td class="success">OK</td>';
            $html .= '<td>Lib returned false, point is not in area (' . $point['name'] . ')</td>';
            $result = "OK";
        }
        if ($response === false && $point['expectedResult'] == "inside") {
            $html .= '<td class="failed">NOT OK</td>';
            $html .= '<td>Lib returned false, but point is in area (' . $point['name'] . ')</td>';
            $result = "NOK";
        }
        $html .= '</tr>';

        return [
            'html' => $html,
            'result' => $result
        ];
    }
}