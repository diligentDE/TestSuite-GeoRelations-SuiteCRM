<?php

/**
 * Class XmlHelper
 * converts google my-maps into test cases for the validation of isPointInArea-functions
 */
class XmlHelper
{
    const mapFolder = "maps";

    /**
     * grabs all .kml files in /maps/ and returns them as array
     * @return array
     */
    public static function parseAllAreas()
    {
        $resultSet = array();
        $fileList = self::getAllFilesFromMapFolder();
        foreach ($fileList as $mapFile) {
            $mapData = self::convertMapToJson($mapFile);
            array_push($resultSet, $mapData);
        }
        return $resultSet;
    }

    /**
     * parses a kml file and transforms geo-coordinates into a php array
     * @param $mapItem
     * @return array
     */
    private static function convertMapToJson($mapItem)
    {
        if (file_exists(self::mapFolder . '/' . $mapItem)) {
            $xml = simplexml_load_file(self::mapFolder . '/' . $mapItem);
            $test_found_valid_points = false;
            $test_found_invalid_points = false;
            $test_found_area = false;

            $mapData = array();
            $mapData['test_valid'] = array();
            $mapData['test_invalid'] = array();
            $mapData['area'] = array();
            $mapData['mapname'] = $mapItem;

            foreach ($xml->Document->Folder as $mapFolder) {
                //layer contains test points
                if ($mapFolder->name == "test_valid") {
                    $test_found_valid_points = true;
                    foreach ($mapFolder->Placemark as $testPoint) {
                        $pointData = array();
                        $pointData['expectedResult'] = "inside";
                        $xy = trim((string)$testPoint->Point->coordinates);
                        $xy = explode(",", $xy);
                        $pointData['lat'] = $xy[1];
                        $pointData['lng'] = $xy[0];
                        $pointData['name'] = (string)$testPoint->name;
                        array_push($mapData['test_valid'], $pointData);
                    }
                }

                //layer contains invalid test points
                if ($mapFolder->name == "test_invalid") {
                    $test_found_invalid_points = true;
                    foreach ($mapFolder->Placemark as $testPoint) {
                        $pointData = array();
                        $pointData['expectedResult'] = "outside";
                        $xy = trim((string)$testPoint->Point->coordinates);
                        $xy = explode(",", $xy);
                        $pointData['lat'] = $xy[1];
                        $pointData['lng'] = $xy[0];
                        $pointData['name'] = (string)$testPoint->name;
                        array_push($mapData['test_invalid'], $pointData);
                    }
                }
                //layer contains the polygon area
                if ($mapFolder->name == "test_area") {
                    $coordinates = (string)$mapFolder->Placemark->Polygon->outerBoundaryIs->LinearRing->coordinates;
                    $coordinates = explode("\n", $coordinates);
                    //removes empty buckets
                    $coordinates = array_filter($coordinates, function ($value) {
                        return !(is_null($value) || $value == '' || strlen(trim($value)) === 0);
                    });
                    //removes white spaces
                    $coordinates = array_map('trim', $coordinates);
                    $test_found_area = true;

                    foreach ($coordinates as $key => $mixedCoordinate) {
                        $mixedCoordinate = explode(",", $mixedCoordinate);
                        $xy = array();
                        $xy['lat'] = $mixedCoordinate[1];
                        $xy['lng'] = $mixedCoordinate[0];

                        array_push($mapData['area'], $xy);
                    }
                }
            }
            if ($test_found_valid_points && $test_found_invalid_points && $test_found_area) {
                return $mapData;
            } else {
                $valid = ($test_found_valid_points) ? "ok" : "not found";
                $invalid = ($test_found_invalid_points) ? "ok" : "not found";
                $map = ($test_found_area) ? "ok" : "not found";
                echo <<<html
                        Map {$mapItem} was not set up correctly:<br>
                        valid points found: {$valid}<br>
                        invalid points found: {$invalid}<br>
                        area found: {$map}
html;
                die;
            }
        }
    }

    /**
     * helper to distinguish friendly name and exp. result
     * @param $name
     * @return string
     */
    public static function getExpectedResultFromName($name)
    {
        if (strpos($name, "out") !== false) {
            return "out";
        }
        if (strpos($name, "in") !== false) {
            return "in";
        }
    }

    /**
     * returns list of .kml files from mapFolder
     * @return array
     */
    private static function getAllFilesFromMapFolder()
    {
        $resultSet = array();
        if (is_dir(self::mapFolder)) {
            if ($dh = opendir(self::mapFolder)) {
                while (($file = readdir($dh)) !== false) {
                    if (strpos($file, ".kml") > 0) {
                        array_push($resultSet, $file);
                    }
                }
                closedir($dh);
            }
        }
        return $resultSet;
    }
}

