<?php
require_once("custom/Extension/application/GeoRelations.php");
$job_strings[] = 'relateSaleAreasScheduler';

/**
 * method for geo-relating records to users (executed by the scheduler)
 * @return bool
 */
function relateSaleAreasScheduler(){
    LoggerManager::getLogger();
    $prefix = 'Scheduler: GeoRelate: ';
    LoggerManager::getLogger()->fatal($prefix . 'started.');

    //call helper class
    GeoRelations::updateAssignedGeoUser();

    LoggerManager::getLogger()->fatal($prefix . 'finished.');
    return true;
}