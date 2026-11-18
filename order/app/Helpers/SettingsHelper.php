<?php

namespace App\Helpers;

class SettingsHelper 
{
    /**
     * Get all .env variables between $regionStart and $regionEnd keywords.
     * For example 
     * if $regionStart = "regionstart", $regionEnd = "regionend" and $regionName = "iniproperties"
     * and .env has:
     * 
     * regionstart=iniproperties
     * PROPERTY_01="value1"
     * PROPERTY_02=122
     * regionend=iniproperties
     * 
     * returns: 
     * array [
     *      PROPERTY_01="value1"
     *      PROPERTY_02=122
     * ]
     *
     * @param string $regionName
     * @return void
     */
    public static function getEnvRegion(string $regionName) {
        $regionStart = "regionstart";
        $regionEnd = "regionend";
        
        $props = [];
        $currentRegion = null;
        foreach ($_ENV as $key => $value) {
            if ($key == $regionStart) {
                $currentRegion = $value;
                continue;
            }
            if ($key == $regionEnd) {
                $currentRegion = null;
                continue;
            }
            if (!$currentRegion || $currentRegion != $regionName) continue;

            $props[$key] = $value;
        }
        return $props;
    }

    /**
     * Get parsed config properties without null values
     *
     * @param string $configName
     * @param string $property
     * @return void
     */
    public static function config(string $configName, string $property = '') {
        try {
            $fromConfig = config($configName);
            $fromEnv = self::getEnvRegion($configName);

            // Merge config + env
            $merged = array_replace_recursive($fromConfig, $fromEnv);

            // Remove null and empty values
            $filtered = array_filter($merged, function($val) {
                return $val && $val != "null";
            });
            
            // Get property
            if ($property && $property != ''){
                if (isset($filtered[$property])){
                    return $filtered[$property];    
                }
                return null;
            }

            return $filtered;

            return null;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}