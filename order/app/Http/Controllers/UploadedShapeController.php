<?php

namespace App\Http\Controllers;

use App\Helpers\MainHelper;
use App\Models\UploadedShape;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UploadedShapeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orderBy = $request->input('orderby', 'id');
        $order = $request->input('order', 'desc');
        $perPage = $request->input('perPage', 5);
        $reference = $request->input('reference', '');
        $dateFrom = $request->input('dateFrom', null);
        $dateTo = $request->input('dateTo', null);

        $orderBy = strtolower($orderBy);
        $order = strtolower($order);

        $uploadedShapes = UploadedShape::query();
        $uploadedShapes->where([
            ['Reference', 'LIKE', '%'.$reference.'%'],
        ]);

        if ($dateFrom && $dateTo){
            $uploadedShapes->whereBetween('EntryDate', [$dateFrom, $dateTo]);
        }
        
        $result = $uploadedShapes->orderBy($orderBy, $order)->paginate($perPage);
        return response()->json($result, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => [Rule::in(['disk', 'tracer']), 'required'],
            'file' => 'required_without:vca|file',
            'vca' => 'required_without:file|string'
        ]);
        
        if ($validator->fails()) 
            return response()->json(['message' => $validator->errors()], 400);

        $form = $request->input('vca', '');

        if (\Arr::has($request->all(), 'file')){
            //$form = file_get_contents($request->file->path());
            $form = file_get_contents($request->file('file')->getPathname());

            $formValidationResponse = UploadedShape::validateForm($form);
            if ($formValidationResponse !== true){
                return response()->json(['error' => $formValidationResponse], 200);
            }
            $form = bin2hex($form);
        }

        $uploadedShape = UploadedShape::create([
            'type' => $request->input('type'),
            'reference' => $request->input('reference', ''),
            'form' => $form,
        ]);

        return response()->json($uploadedShape, 200);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' =>  'required',
            ]);
            if ($validator->fails()) 
                return response()->json(['message' => $validator->errors()], 400);
            
            $id = $request->input('id');
            $uploadedShape = UploadedShape::find($id);
            if ($uploadedShape)
                UploadedShape::destroy($id);
            return response()->json($uploadedShape, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Get standard shapes images from the Formen folder
     *
     * @return \Illuminate\Http\Response
     */
    public function getStandardShapesImages() {
        try {
            // Get main shapes
            $standardShapesResponse = UploadedShape::getStandardShapes();
            if (!is_array($standardShapesResponse))
                return response()->json(['message' => $standardShapesResponse], 400);
            
            // Get any custom shapes
            $customShapesResponse = UploadedShape::getStandardShapes("custom/");
            if (!is_array($customShapesResponse))
                $customShapesResponse = [];

            // Merge main and custom shapes
            $mergedShapes = MainHelper::merge2ArraysById($standardShapesResponse, $customShapesResponse);

            // Get standard shape images
            $shapes = UploadedShape::getStandardShapeImages($mergedShapes);
            if (!$shapes)
                return response()->json(['message' => "Missing shape"], 400);

            return response()->json($shapes, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Get standard shape form data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getStandardShapeForm(Request $request) {
        try {
            // Get shape id
            $shapeId = $request->input('shapeId', null);
            if (!$shapeId)
                return response()->json(['message' => "Missing shape Id"], 400);

            // Get main shapes
            $standardShapesResponse = UploadedShape::getStandardShapes();
            if (!is_array($standardShapesResponse))
                return response()->json(['message' => $standardShapesResponse], 400);

            // Get any custom shapes
            $customShapesResponse = UploadedShape::getStandardShapes("custom/");
            if (!is_array($customShapesResponse))
                $customShapesResponse = [];

            // Merge main and custom shapes
            $mergedShapes = MainHelper::merge2ArraysById($standardShapesResponse, $customShapesResponse);

            // Get form of the specified $shapeId
            $form = UploadedShape::getStandardShapeForm($shapeId, $mergedShapes);
            if (!$form)
                return response()->json(['message' => "Missing shape"], 400);

            return response()->json($form, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }
}
