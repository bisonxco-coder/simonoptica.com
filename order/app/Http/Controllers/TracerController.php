<?php

namespace App\Http\Controllers;

use App\Models\Tracer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class TracerController extends Controller
{
    /**
     * Get tracer configuration
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        try {
            $tracer = Tracer::getTracerConfiguration($request->input('uid', ''), $request->input('id', null));
            return response()->json($tracer, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Create a new tracer configuration
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customerNo' => 'required|string',
                'tracer' => 'nullable|string'
            ]);
            
            if ($validator->fails()) 
                return response()->json(['message' => $validator->errors()], 400);

            $tracer = new Tracer();

            $tracer->fillTracerData($request->all());

            $tracer->save();

            $tracer->refresh();
    
            return response()->json($tracer, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Create a new tracer configuration
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' =>  'required',
            ]);
            if ($validator->fails()) 
                return response()->json(['message' => $validator->errors()], 400);

            $tracer = Tracer::getTracerConfiguration($request->input('uid', ''), $request->input('id', null));

            if (!$tracer->exists()) 
                return response()->json(['message' => "Tracer does not exist"], 400);

            $tracer->fillTracerData($request->all());

            $tracer->update();

            return response()->json($tracer, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Remove tracer configuration
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
            
            $tracer = Tracer::getTracerConfiguration($request->input('uid', ''), $request->input('id', null));

            if ($tracer) 
                Tracer::destroy($tracer->id);
                
            return response()->json($tracer, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Get last (latest) tracer configuration
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getLastTracerConfiguration(Request $request) {
        try {
            $tracer = Tracer::getTracerConfiguration($request->input('uid', ''), $request->input('id', null));

            return response()->json($tracer, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Get LensTracer API endpoint
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getUrl(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'pwd' =>  'required|string',
                'uid' =>  'required|string',
            ]);
            if ($validator->fails()) 
                return response()->json(['message' => $validator->errors()], 400);

            // Get last tracer configuration as default or find by id
            $tracer = Tracer::getTracerConfiguration($request->input('uid', ''), $request->input('id', null));
            // $tracer = Tracer::orderBy('id','DESC')->first();
            // if (\Arr::has($request->all(), 'id')){
            //     $tracer = Tracer::find(\Arr::get($request->all(), 'id'));
            // }
            
            // Get tracer url
            $custNumb = $request->uid;
            $custPassword = $request->pwd;
            $serverName = $request->getHost();
            $serverPort = $request->getPort();
            $tracerUrl = Tracer::getUrl($tracer, $custNumb, $custPassword, $serverName, $serverPort);
            
            return response()->json($tracerUrl, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Get tracer definitions
     *
     * @return \Illuminate\Http\Response
     */
    public function getTracerDefinitions() {
        try {
            $tracerDefinitions = Tracer::tracerDefinitions();
            
            return response()->json($tracerDefinitions, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }
}
