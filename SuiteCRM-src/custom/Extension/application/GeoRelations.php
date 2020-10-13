<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class GeoRelations {
    /**
     * returns the list of accounts that are currently not related to any user but have valid lng/lat-values.
     * @return mixed
     */
    private static function getUnrelatedAccounts(){
        $accountBean = BeanFactory::getBean('Accounts');
        $list =  $accountBean->get_list(
            'name',
            "(accounts.assigned_user_id ='' or accounts.assigned_user_id is null) and 
            (accounts_cstm.jjwg_maps_lat_c is not null and accounts_cstm.jjwg_maps_lng_c is not null)");
        return $list['list'];
    }

    /**
     * Method to verify if a new record lies within any jjwg_area
     */
    public static function updateAssignedGeoUser(){
        LoggerManager::getLogger();
        $prefix = 'Scheduler: GeoRelate: ';

        $accountList = self::getUnrelatedAccounts();

        foreach($accountList as $bean){
            //only necessary if no user is assigned currently
            if($bean->assigned_user_id == "" || empty($bean->assigned_user_id)){
                $longitudeSet = ($bean->jjwg_maps_lng_c === "" || empty($bean->jjwg_maps_lng_c)) ? false : true;
                $latitudeSet = ($bean->jjwg_maps_lat_c === "" || empty($bean->jjwg_maps_lat_c)) ? false : true;
                if($latitudeSet && $longitudeSet){
                    LoggerManager::getLogger()->fatal($prefix . ' trying to relate account ' . $bean->name);

                    //get available map areas
                    $mapAreaBean = BeanFactory::getBean('jjwg_Areas');
                    $mapAreaList = $mapAreaBean->get_list('',"jjwg_areas.deleted=0",0,-1);
                    if(count($mapAreaList['list'])>0){
                        foreach($mapAreaList['list'] as $mapArea){
                            if(self::isBeanInArea($bean,$mapArea)){
                                /**
                                 * Match! The current $bean is supposed to be inside the current $mapArea!
                                 */
                                LoggerManager::getLogger()->fatal($prefix . ' area ' . $mapArea->name . ' is a match for bean ' . $bean->name .' ('. $bean->id.')');
                                $bean->assigned_user_id = $mapArea->assigned_user_id;
                                $bean->save();
                                break;
                            } else {
                                /**
                                 * No match! The current $bean does not belong to the current $mapArea
                                 */
                                LoggerManager::getLogger()->fatal($prefix . ' area ' . $mapArea->name . ' not suitable for bean ' . $bean->name .' ('. $bean->id.')');
                            }
                        }
                    } else {
                        //no map areas saved in suite -> continue without update
                        LoggerManager::getLogger()->fatal($prefix . 'no map areas found, skipping.');
                    }
                } else {
                    //no assigned user, but no geo-codes available in bean
                    LoggerManager::getLogger()->fatal($prefix . 'no geo information found in bean ' . $bean->name . ', skipping.');
                }
            } else {
                //assigned user is already set -> no to do.
                LoggerManager::getLogger()->fatal($prefix . 'assigned user already set, skipping.');
            }
        }
    }

    /**
     * actual method for calculating if a point (itemBean) is within a given area (areaBean)
     * @param $itemBean
     * @param $areaBean
     * @return bool
     */
    private static function isBeanInArea($itemBean,$areaBean){
        $lng = (float)$itemBean->jjwg_maps_lng_c;
        $lat = (float)$itemBean->jjwg_maps_lat_c;
        $beanArea = explode(" ",$areaBean->coordinates);
        $cleanArea = array();
        foreach($beanArea as $key=>$area){
            $tempArray = explode(",",$area);
            $point = array();
            $point['lat'] = $tempArray[1];
            $point['lng'] = $tempArray[0];
            array_push($cleanArea,$point);
        }

        return  \GeometryLibrary\PolyUtil::containsLocation([
            'lng' => $lng,
            'lat' => $lat
        ],
            $cleanArea,
            false
        );
    }
}