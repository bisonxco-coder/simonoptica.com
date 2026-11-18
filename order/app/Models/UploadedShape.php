<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandarShapesData
{
    public int $id = 0;
    public ?string $image = "";
    public ?string $image_url = "";
    public bool $image_url_exists = false;
    public ?string $form = "";
    public ?string $form_url = "";
    public bool $form_url_exists = false;

    public function __construct(array $params)
    {
        $this->id = \Arr::get($params, 'id', -1);
        $this->image = \Arr::get($params, 'image', null);
        $this->image_url = \Arr::get($params, 'image_url', null);
        $this->image_url_exists = \Arr::get($params, 'image_url_exists', false);
        $this->form = \Arr::get($params, 'form', null);
        $this->form_url = \Arr::get($params, 'form_url', null);
        $this->form_url_exists = \Arr::get($params, 'form_url_exists', false);
    }
}

class UploadedShape extends Model
{
    use HasFactory;

    

    protected $fillable = ['type', 'reference', 'form'];

    public static function validateForm($form) {
        // If all these keywords are found in $form, return true
        $testTrueKeywords = ['TRCFMT', 'R='];

        // If any of these keywords are found in $form, return false
        $testFalseKeywords = ['\r\n', '\\r\\n'];

        // If x amount of these keywords are found, return true, appending $testTrueKeywords
        $testMaybeKeywordsAmount = 2;
        $testMaybeKeywords = [';E;L;F', 'E;L;P', ';E;R;F', 'E;R;P', 'DO', 'JOB', 'CIRC', 'VBOX', 'HBOX'];
        $testMaybeKeywordsAmount += count($testTrueKeywords);
        $testMaybeKeywords = array_merge($testTrueKeywords, $testMaybeKeywords);

        try {
            if (!$form || $form == '' || strlen($form) <= 0)
                return "File is empty.";

            // Test true keywords
            foreach ($testTrueKeywords as $keyword) {
                $contains = strpos($form, $keyword);
                if ($contains === false)
                    return "Incorrect file content.";
            }

            // Test false keywords
            foreach ($testFalseKeywords as $keyword) {
                $contains = strpos($form, $keyword);
                if ($contains !== false)
                    return "Incorrect file content.";
            }

            // Test some/maybe keywords
            $maybeContainsCounter = 0;
            foreach ($testMaybeKeywords as $keyword) {
                $contains = strpos($form, $keyword);
                if ($contains !== false)
                    $maybeContainsCounter++;
            }
            if ($maybeContainsCounter < $testMaybeKeywordsAmount)
                return "Incorrect file content.";

        } catch (\Throwable $th) {
            \Log::error($th);
            return $th->getMessage();
        }

        return true;
    }

    /**
     * Returns an array of standard shapes data, based on index.json 
     *
     * @param string $subFolder
     * @param string $indexName
     * @return array|string
     */
    public static function getStandardShapes(string $subFolder = "", string $indexName = 'index.json')
    {
        $shapesFolder = \Storage::disk('formen'); 

        $indexPath = $subFolder . $indexName;
        if(!$shapesFolder->exists($indexPath)){
            return "Missing index.json file";
        }
        
        $shapeData = json_decode($shapesFolder->get($indexPath));
        if(!$shapeData){
            return "Incorrect json structure in index.json";
        }

        // Get all images and create urls
        return array_map(function($shape) use($subFolder, $shapesFolder) {
            $imageUrlExists = $shape->image ? $shapesFolder->exists($subFolder . $shape->image) : false;
            $formUrlExists = $shape->form ? $shapesFolder->exists($subFolder . $shape->form) : false;
            return new StandarShapesData([
                'id' => $shape->id,
                'image' => $shape->image,
                'image_url' => $imageUrlExists ? $subFolder . $shape->image : null,
                'image_url_exists' => $imageUrlExists,
                'form' => $shape->form,
                'form_url' => $formUrlExists ? $subFolder. $shape->form : null,
                'form_url_exists' => $formUrlExists,
            ]);
        }, $shapeData);
    }

    /**
     * Returns an array of standard shape images, 
     * converted to base64, ready to be displayed in the front-end
     *
     * @param array $params
     * @return array
     */
    public static function getStandardShapeImages(array $params) 
    {
        $shapesFolder = \Storage::disk('formen'); 
        $imageUrlPrefix = 'data:image/jpg;base64,';

        // Cast params to StandarShapesData
        $standardShapesData = array_map(function($a) { 
            return new StandarShapesData($a); 
        }, $params);
        
        // Remove all empty values
        $standardShapesData = array_filter($standardShapesData, function(StandarShapesData $shape) {
            return $shape->image_url_exists && $shape->form_url_exists;
        });

        // Sort if there is any gap in between ids
        array_multisort($standardShapesData);
        
        // Return all shapes data
        return array_map(function(StandarShapesData $shape) use($shapesFolder, $imageUrlPrefix){
            $imageContents = $shapesFolder->get($shape->image_url);
            $imageBase64 = base64_encode($imageContents);
            
            return [
                'id' => $shape->id,
                'image_url' => $imageUrlPrefix . $imageBase64,
            ];
        }, $standardShapesData);
    }

    public static function getStandardShapeForm(int $shapeId, array $params)
    {
        $shapesFolder = \Storage::disk('formen');

        // Cast params to StandarShapesData
        $standardShapesData = array_map(function($a) { 
            return new StandarShapesData($a); 
        }, $params);

        // Remove all empty values
        $standardShapesData = array_filter($standardShapesData, function(StandarShapesData $shape) {
            return $shape->image_url_exists && $shape->form_url_exists;
        });

        // Sort if there is any gap in between ids
        array_multisort($standardShapesData);

        // Find specified form shape id
        foreach ($standardShapesData as $shape) {
            if ($shape->id == $shapeId) 
                return $shapesFolder->get($shape->form_url);
        }
        return null;
    }
}
