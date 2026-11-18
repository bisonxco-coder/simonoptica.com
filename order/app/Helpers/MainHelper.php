<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class MainHelper {
    /**
     * Make SOAP call
     *
     * @param Request $request
     * @param $function
     * @param $params
     * @return mixed
     */
    public static function getSettings(Request $request, $function, $params) {
    }

    /**
     * Transforms key-pair data: ["key1" => "value1", "key2" => "value2"]
     * to array: [["key" => "key1", "value" => "value1], ["key" => "key2", "value" => "value2"]]
     * @param mixed $data
     * @param string $key
     * @param string $value
     * @return array
     */
    public static function keyPairDataToArray($data, string $key = 'key', string $value = 'value') {
        $result = [];
        try {
            foreach ($data as $_key => $_value) {
                $result[] = [
                    $key => $_key,
                    $value => $_value
                ];
            }
        } catch (\Throwable $th) {
            $result = $data;
        }
        return $result;
    }
    
    /**
     * Merges 2 array objects into 1. Values from second array replace values in first one.
     * Id is used to match values for replacing.
     *
     * @param array $array1
     * @param array $array2
     * @param string $id
     * @return array
     */
    public static function merge2ArraysById(array $array1, array $array2, string $id = 'id') {

        try {
            // Replace values from 
            $replacedArray = [];
            for ($i=0; $i < count($array1); $i++) { 
                $array1Element = (array)$array1[$i];
                $elementToAdd = $array1Element;

                for ($j=0; $j < count($array2); $j++) { 
                    $array2Element = (array)$array2[$j];
                    
                    // Replace values
                    if ($array1Element[$id] == $array2Element[$id]){
                        $elementToAdd = $array2Element;
                        continue;
                    }
                }
                array_push($replacedArray, $elementToAdd);
            }

            $mergedArray = array_map(function($a) {return (array) $a;}, array_merge($replacedArray, $array2));
            $ids = array_map(function($a) use($id) {return $a[$id];}, $mergedArray);
            $uniqueIds = array_unique($ids);
            $result = [];
            foreach ($uniqueIds as $val => $uniqueId) {
                $result[] = $mergedArray[$val];
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return $th->getMessage();
        }

        return $result;
    }
}
