<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\Calculation;
use App\Helpers\MainHelper;
use App\Helpers\SOAPHelper;
use App\Models\OrderHeader;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\LenswareToken;
use Illuminate\Support\Facades\Crypt;
use mysql_xdevapi\Exception;

class SOAPController extends Controller {


    /**
     * Initial Product Categories
     * @param Request $request
     * @return JsonResponse
     */
    public function productCategoryInitial(Request $request): JsonResponse
    {
        $productsCategory = SOAPHelper::call($request, 'WebShop', [
            'ENCODED' => 'BASE64',
            'SERVICE' => 'getProductCategoryInitial',
            'CUSTNUMB' => $request->uid
        ]);
        $response = [];
        $response['Materials']  = json_decode(base64_decode($productsCategory['MATERIALS']));
        $response['Specials']   = json_decode(base64_decode($productsCategory['SPECIALS']));
        $response['Groups']     = json_decode(base64_decode($productsCategory['GROUPS']));
        $response['Indices']    = json_decode(base64_decode($productsCategory['INDICES']));
        return response()->json($response, 200);
    }

    public function productCategoryUpdate(Request $request): JsonResponse
    {
        $productsCategory = SOAPHelper::call($request, 'WebShop', [
            'ENCODED' => 'BASE64',
            'SERVICE' => 'getProductCategoryUpdate',
            'CUSTNUMB' => $request->uid,
            'MATERIAL' => $request->input('material', ''),
            'SPECIAL' => $request->input('special', ''),
            'GROUP' => $request->input('group', ''),
        ]);
        $response['Specials']   = json_decode(base64_decode($productsCategory['SPECIALS']));
        $response['Groups']     = json_decode(base64_decode($productsCategory['GROUPS']));
        $response['Indices']    = json_decode(base64_decode($productsCategory['INDICES']));
        return response()->json($response, 200);
    }

    public function defaultMinThk(Request $request)
    {
        $defaultThk = SOAPHelper::call($request, 'WebShop', [
            'ENCODED' => 'BASE64',
            'SERVICE' => 'GetDefaultMinThk',
            'CUSTNUMB' => $request->uid
        ]);
        if($defaultThk != '' && json_decode(base64_decode($defaultThk['MinThk']))){
            $response['MinThk']   = json_decode(base64_decode($defaultThk['MinThk']));
        }
        else {
            $response['MinThk'] = [];
        }
        return response()->json($response, 200);
    }


    public function createToken(string $uid, string $password)
    {
        $lt =  new LenswareToken();
        $token = $lt::create([
            'name' => $uid,
            'special' => $password,
            'token' => hash('sha256', Str::random(40))
        ]);
        return $token;
    }

    public function changePassword(Request $request){
        $changePasswordResponse = SOAPHelper::call($request, 'WebShop', [
            'SERVICE' => 'CHANGEPASSWORD',
            'PWDCUR' => $request->pwdCur,
            'PWDNEW' => $request->pwdNew
        ]);
        if($changePasswordResponse == 0){
            return response()->json(['message' => 'Password changed'], 200);
        }
        return response()->json(['message' => 'Unauthorized'], 401);
        //return response()->json(['error' => 'There was an error saving the user']);
    }

    public function logout(Request $request){
        if($request->token){
            $token = Crypt::decryptString($request->token);
            LenswareToken::where('token', $token)->delete();
            return response()->json(['message' => 'LoggedOut'], 200);
        }
    }

    static function parseJwt ($token): array
    {
        $jwt = JWT::decode($token, new Key('93b766391d68da1046c8d834843bea9f', 'HS256'));
        return (array)$jwt;
    }

    /**
     * SOAP login
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public static function checkLogin(Request $request) {
        $rmb = '';
        if($request->token){
            $token = Crypt::decryptString($request->token);
            $data = LenswareToken::where('token', $token)->first();
            if($data){
                $request['pwd'] = $data->special;
                $request['uid'] = $data->name;
            }
            $data->last_used_at = new \DateTime();
            $data->save();
        }
        $loginResponse = SOAPHelper::call($request, 'checkLogin', []);
        if($request->rmb == 1){
            $rmb = Crypt::encryptString((new SOAPController)->createToken($request->uid, $request->pwd)->token);
        }

        if($request->jwt) {
            try{
                //Log::info($request->jwt);
                $jwt = SOAPController::parseJwt(trim($request->jwt));
                $user = $jwt[config('auth.jwt_user')];
                //Log::info(trim($user));
                $request->request->add(['uid' => trim($user)]);
                $loginResponse = SOAPHelper::call($request, 'checkLogin', ['JWTLOGIN' => 1, 'uid' => trim($user)]);
                //Log::info(print_r($loginResponse, true));
            }
            catch (Exception $e) {
                Log::info(print_r($e, true));
            }
        }
        if (isset($loginResponse['error'])) {
            return response()->json(['message' => $loginResponse['error']], 400);
        } else {
            AuthHelper::setUserProperties($loginResponse);

            return response()->json([
                'LANG' => $loginResponse['LANG'],
                'CUSTLIST' => $loginResponse['CUSTLIST'],
                'PRICELIST' => $loginResponse['PRICELIST'],
                'PRODLIST' => $loginResponse['PRODLIST'],
                'STATUS' => $loginResponse['STATUS'],
                'ALIAS2' => $loginResponse['ALIAS2'],
                'UID' => $loginResponse['UID'],
                'PWD' => $loginResponse['PWD'],
                'RMB' => $rmb
            ], 200);
        }
    }

    /**
     * Interpolate
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function interpolate(Request $request) {
        $interpolateResponse = SOAPHelper::call($request, 'interpolate', [
            'ENCODED' => 'BASE64',
            'VCA' => $request->input('vca', ''),
            'TYPE' => $request->input('type', '')
        ]);

        if (isset($interpolateResponse['error'])) {
            return response()->json(['message' => $interpolateResponse['error']], 400);
        } else {
            if (isset($interpolateResponse['VCA'])) {
                $data = base64_decode($interpolateResponse['VCA']);
                return response()->json($data, 200);
            } else {
                return response()->json($interpolateResponse, 200);
            }
        }
    }

    /**
     * Get individual ini
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getIndividualIni(Request $request) {
        $individualIniResponse = SOAPHelper::call($request, 'WebShop', [
            'ENCODED' => 'BASE64',
            'SERVICE' => 'getIndividualIni'
        ]);

        if (isset($individualIniResponse['error'])) {
            return response()->json(['message' => $individualIniResponse['error']], 400);
        } else {
            if (isset($individualIniResponse['INDINI'])) {
                $data = base64_decode($individualIniResponse['INDINI']);
                return response()->json($data, 200);
            } else {
                return response()->json($individualIniResponse, 200);
            }
        }
    }

    /**
     * Get shape list
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getShapesList(Request $request) {
        $shapeListResponse = SOAPHelper::call($request, 'WebShop', [
            'ENCODED' => 'BASE64',
            'SEARCH' => $request->input('search', ''),
            'SERVICE' => 'getSalesShapesList'
        ]);

        if (isset($shapeListResponse['error'])) {
            return response()->json(['message' => $shapeListResponse['error']], 400);
        } else {
            if (isset($shapeListResponse['SHAPES'])) {
                $data = json_decode(base64_decode($shapeListResponse['SHAPES']));
                return response()->json($data, 200);
            } else {
                return response()->json($shapeListResponse, 200);
            }
        }
    }

    /**
     * Get lens filter
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getLensFilter(Request $request) {
        $lensFilterResponse = SOAPHelper::call($request, 'WebShop', [
            'ENCODED' => 'BASE64',
            'SERVICE' => 'getLensFilter'
        ]);

        if (isset($lensFilterResponse['error'])) {
            return response()->json(['message' => $lensFilterResponse['error']], 400);
        } else {
            if (isset($lensFilterResponse['LENSFILTER'])) {
                $data = json_decode(base64_decode($lensFilterResponse['LENSFILTER']));
                return response()->json($data, 200);
            } else {
                return response()->json($lensFilterResponse, 200);
            }
        }
    }

    /**
     * Get sales frames supplier list
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getSalesFramesSupplierList(Request $request) {
        $salesFramesSupplierResponse = SOAPHelper::call($request, 'WebShop', [
            'ENCODED' => 'BASE64',
            'SERVICE' => 'GETSALESFRAMESSUPPLIERLIST',
        ]);

        if (isset($salesFramesSupplierResponse['error'])) {
            return response()->json(['message' => $salesFramesSupplierResponse['error']], 400);
        } else {
            if (isset($salesFramesSupplierResponse['FRAMESSUPP'])) {
                $data = json_decode(base64_decode($salesFramesSupplierResponse['FRAMESSUPP']));
                return response()->json($data, 200);
            } else {
                return response()->json($salesFramesSupplierResponse, 200);
            }
        }
    }

    /**
     * Get sales frames list
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getSalesFramesList(Request $request) {
        $salesFramesResponse = SOAPHelper::call($request, 'WebShop', [
            'ENCODED' => 'BASE64',
            'SERVICE' => 'getSalesFramesList',
            'SUPPLIER' => $request->input('supplier', ''),
            'MODEL' => $request->input('model', ''),
            'EYESIZE' => $request->input('eyeSize', ''),
            'DBL' => $request->input('dbl', ''),
            'FRAMETYPE' => $request->input('frameType', ''),
            'COLOR' => $request->input('color', ''),
            'FRAMENUMB' => $request->input('frameNumb', ''),
        ]);

        if (isset($salesFramesResponse['error'])) {
            return response()->json(['message' => $salesFramesResponse['error']], 400);
        } else {
            if (isset($salesFramesResponse['FRAMES'])) {
                $data = base64_decode($salesFramesResponse['FRAMES']);
                return $data;
                // return response()->json($data, 200);
            } else {
                return response()->json($salesFramesResponse, 200);
            }
        }
    }

    /**
     * Get sales shapes list
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getSalesShapesList(Request $request) {
        $salesShapesResponse = SOAPHelper::call($request, 'WebShop', [
            'ENCODED' => 'BASE64',
            'SERVICE' => 'getSalesShapesList',
            'SEARCH' => $request->input('search', ''),
        ]);

        if (isset($salesShapesResponse['error'])) {
            return response()->json(['message' => $salesShapesResponse['error']], 400);
        } else {
            if (isset($salesShapesResponse['SHAPES'])) {
                $data = base64_decode($salesShapesResponse['SHAPES']);
                return response()->json($data, 200);
            } else {
                return response()->json($salesShapesResponse, 200);
            }
        }
    }

    /**
     * Get lens types
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getLensTypes(Request $request) {
        $lensTypesResponse = SOAPHelper::call($request, 'getLenses', [
            'ENCODED' => 'BASE64',
            'MATERIAL' => $request->input('material', ''),
            'INDEX' => $request->input('index', ''),
            'SPECIAL' => $request->input('special', ''),
            'GROUP' => $request->input('group', ''),
            'TYPE' => $request->input('type', ''),
            'USAGE' => $request->input('usage', ''),
            'DESIGN' => $request->input('design', ''),
            'SEARCHFIELD' => $request->input('search_field', ''),
            'SEARCHWITHFILTER' => $request->input('search_with_filter', '')
        ]);


        if (isset($lensTypesResponse['error'])) {
            return response()->json(['message' => $lensTypesResponse['error']], 400);
        } else {
            if (isset($lensTypesResponse['LENSES'])) {
                $data = json_decode(base64_decode($lensTypesResponse['LENSES']));
                return response()->json($data, 200);
            } else {
                return response()->json($lensTypesResponse, 200);
            }
        }
    }

    /**
     * Get lens types only
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getLensTypesOnly(Request $request) {
        $soapData = [
            'ENCODED' => 'BASE64',
            'MATERIAL' => $request->input('material', ''),
            'INDEX' => $request->input('index', ''),
            'SPECIAL' => $request->input('special', ''),
            'GROUP' => $request->input('group', ''),
            'TYPE' => $request->input('type', ''),
            'USAGE' => $request->input('usage', ''),
            'DESIGN' => $request->input('design', ''),
            'SEARCHFIELD' => $request->input('search_field', ''),
            'SEARCHWITHFILTER' => $request->input('search_with_filter', ''),
            'LENSFILTER' => $request->input('lens_filter', ''),
            'REFRACTION' => 'X'
        ];

        $filterOnRef = $request->input('filter_on_ref', '');
        $spherer = $request->input('spherer', '');
        $cylinderr = $request->input('cylinderr', '');
        $axisr = $request->input('axisr', '');
        $additionr = $request->input('additionr', '');
        $prism1r = $request->input('prism1r', '');
        $prism2r = $request->input('prism2r', '');
        $spherel = $request->input('spherel', '');
        $cylinderl = $request->input('cylinderl', '');
        $axisl = $request->input('axisl', '');
        $additionl = $request->input('additionl', '');
        $prism1l = $request->input('prism1l', '');
        $prism2l = $request->input('prism2l', '');

        if ($filterOnRef == "YES" &&
            $spherer == "" && $cylinderr == "" && $axisr == "" && $additionr == "" && $prism1r == "" && $prism2r == "" &&
            $spherel == "" && $cylinderl == "" && $axisl == "" && $additionl == "" && $prism1l == "" && $prism2l == "") {
            // do nothing
        } else {
            if ($request->input('filter_on_ref', '') == "YES") {
                $soapData['refraction'] = $spherer.";".$cylinderr.";".$axisr.";".$additionr.";".$prism1r.";".$prism2r.";".$spherel.";".$cylinderl.";".$axisl.";".$additionl.";".$prism1l.";".$prism2l;
            }
            $lensTypesOnlyResponse = SOAPHelper::call($request, 'getOnlyLenses', $soapData);
            if (isset($lensTypesOnlyResponse['error'])) {
                return response()->json(['message' => $lensTypesOnlyResponse['error']], 400);
            } else {
                if (isset($lensTypesOnlyResponse['LENSES'])) {
                    if(env('UTF8_DECODE') === 'YES') {
                        $data = json_decode(utf8_decode(base64_decode($lensTypesOnlyResponse['LENSES'])));
                    }
                    else
                        $data = json_decode(base64_decode($lensTypesOnlyResponse['LENSES']));
                    return response()->json($data, 200);
                } else {
                    return response()->json($lensTypesOnlyResponse, 200);
                }
            }
        }
    }

    /**
     * Get frames supplier list
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getFramesSupplierList(Request $request) {
        $shapeListResponse = SOAPHelper::call($request, 'WebShop', [
            'ENCODED' => 'BASE64',
            'SERVICE' => 'GETSALESFRAMESSUPPLIERLIST'
        ]);

        if (isset($shapeListResponse['error'])) {
            return response()->json(['message' => $shapeListResponse['error']], 400);
        } else {
            if (isset($shapeListResponse['FRAMESSUPP'])) {
                $data = json_decode(base64_decode($shapeListResponse['FRAMESSUPP']));
                return response()->json($data, 200);
            } else {
                return response()->json($shapeListResponse, 200);
            }
        }
    }

    /**
     * Get ranges combination supplier list
     *
     * @param Request $request
     * @param bool $
     * @return Response
     */
    public function getRangesCombination(Request $request, bool $indi = false) {
        $rangesCombinationResponse = SOAPHelper::call($request, 'getRangesCombination', [
            'ENCODED' => 'BASE64',
            'LENSCODER' => $request->input('lenscoder', ''),
            'LENSCODEL' => $request->input('lenscodel', ''),
            'PUREJSON' => 'YES'
        ]);
        if (isset($rangesCombinationResponse['error'])) {
            return response()->json(['message' => $rangesCombinationResponse['error']], 400);
        } else {
            if (isset($rangesCombinationResponse['CODELIST'])) {
                $data = json_decode(base64_decode($rangesCombinationResponse['CODELIST']));
                // $data = base64_decode($rangesCombinationResponse['CODELIST']);
                // $utf8Encode = mb_convert_encoding($data, 'UTF-8', mb_list_encodings());
                if($indi) return $data;
                return response()->json($data, 200);
            } else {
                return response()->json($rangesCombinationResponse, 200);
            }
        }
    }

    /**
     * Get overview order sheet for print (pdf)
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getOverviewOrderSheet(Request $request) {
        $orderSheetResponse = SOAPHelper::call($request, 'WebShop', [
            'ORDNUMBH' => $request->input('orderNumber', ''),
            'LANG' => SOAPHelper::getLangCodeFromRequest($request),
            'SERVICE' => 'getOrderSheet'
        ]);

        if (isset($orderSheetResponse['error'])) {
            return response()->json(['message' => $orderSheetResponse['error']], 400);
        } else {
            if (isset($orderSheetResponse['ORDERSHEET'])) {
                return $orderSheetResponse['ORDERSHEET'];
            } else {
                return $orderSheetResponse;
            }
        }
    }

    /**
     * Get orderbasket order sheet for print (pdf)
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getOrderbasketOrderSheet(Request $request) {
        $validator = Validator::make($request->all(), [
            'orderNumber' => 'required|numeric'
        ], []);
        if ($validator->fails()) return response()->json(['message' => $validator->errors()], 400);

        // Find order type
        $orderNumber = $request->input('orderNumber');
        $orderHeader = OrderHeader::where('OrdNumb', $orderNumber)->first();
        if (!$orderHeader){
            return response()->json(['message' => "Order doesn't exist"], 400);
        }

        // Bulk Orders
        if ($orderHeader->Rectype === "W"){
            $soapRequestData = SOAPHelper::generateCSVWholesale($orderNumber);
        }else {
            // Normal Orders
            $soapRequestData = SOAPHelper::generateSOAPRequest($orderNumber, 0, $request->input('uid', ''));
            $soapRequestData = base64_encode($soapRequestData);
        }

        // Get return from print service
        $orderSheetResponse = SOAPHelper::call($request, 'WebShop', [
            'XMLB2B' => $soapRequestData,
            'LANG' => SOAPHelper::getLangCodeFromRequest($request),
            'SERVICE' => 'getOrderSheet'
        ]);

        if (isset($orderSheetResponse['error'])) {
            return response()->json(['message' => $orderSheetResponse['error']], 400);
        } else {
            if (isset($orderSheetResponse['ORDERSHEET'])) {
                return $orderSheetResponse['ORDERSHEET'];
            } else {
                return $orderSheetResponse;
            }
        }
    }

    /**
     * Get frame picture
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getFramePic(Request $request) {
        $framePicResponse = SOAPHelper::call($request, 'getFramePic', [
            'ENCODED' => 'BASE64',
            'FRAMENO' => $request->input('frameNo', ''),
            'FRAMENOX' => $request->input('frameNoX', ''),
            'SUPPLIER' => $request->input('supplier', 'LWR')
        ]);

        if (isset($framePicResponse['error'])) {
            return response()->json(['message' => $framePicResponse['error']], 400);
        } else {
            if (isset($framePicResponse['FRAMES'])) {
                $data = json_decode(base64_decode($framePicResponse['FRAMES']));
                return response()->json($data, 200);
            } else {
                return response()->json($framePicResponse, 200);
            }
        }
    }

    /**
     * Get frame by ID
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getFrameByID(Request $request) {
        $frameByIdResponse = SOAPHelper::call($request, 'WebShop', [
            'ENCODED' => 'BASE64',
            'FRAMENO' => $request->input('frameNo', ''),
            'SERVICE' => 'getFrameByID'
        ]);

        if (isset($frameByIdResponse['error'])) {
            return response()->json(['message' => $frameByIdResponse['error']], 400);
        } else {
            if (isset($frameByIdResponse['FRAMES'])) {
                $data = json_decode(base64_decode($frameByIdResponse['FRAMES']));
                return response()->json($data, 200);
            } else {
                return response()->json($frameByIdResponse, 200);
            }
        }
    }

    /**
     * Get lens geo
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getLensGeo(Request $request) {
        $lensGeoResponse = SOAPHelper::call($request, 'getLensGeo', [
            'ENCODED' => 'BASE64',
            'LENSCODE' => $request->input('lensCode', ''),
            'TRCSTR' => $request->input('trcstr', ''),
            'TYPE' => $request->input('type', ''),
            'CTO' => $request->input('cto', ''),
            'SIDE' => $request->input('side', ''),
            'PD' => $request->input('pd', ''),
            'HEIGHT' => $request->input('height', ''),
            'DBL' => $request->input('dbl', ''),
            'SUPPLIER' => $request->input('supplier', 'LWR')
        ]);

        if (isset($lensGeoResponse['error'])) {
            return response()->json(['message' => $lensGeoResponse['error']], 400);
        } else {
            if (isset($lensGeoResponse['LENSGEO'])) {
                $data = json_decode(base64_decode($lensGeoResponse['LENSGEO']));
                return response()->json($data, 200);
            } else {
                return response()->json($lensGeoResponse, 200);
            }
        }
    }

    /**
     * Convert drill holes
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function requestConvertDrillHoles(Request $request) {
        return self::convertDrillHoles($request, [
                "drille" => $request->input('drille', ''),
                "trcstr" => $request->input('trcstr', ''),
                "type" => $request->input('type', ''),
                "modeIn" => $request->input('modeIn', 'HC'),
                "modeOut" => $request->input('modeOut', 'CC'),
                "side" => $request->input('side', ''),
            ]
        );
    }
    public function convertDrillHoles(Request $request, $data) {
        $response = SOAPHelper::call($request, 'convertDrillHoles', [
            'ENCODED' => 'BASE64',
            'DRILLE' => \Arr::get($data, 'drille', ''),
            'TRCSTR' => \Arr::get($data, 'trcstr', ''),
            'TYPE' => \Arr::get($data, 'type', ''),
            'MODEIN' => \Arr::get($data, 'modeIn', 'HC'),
            'MODEOUT' => \Arr::get($data, 'modeOut', 'CC'),
            'SIDE' =>  \Arr::get($data, 'side', '')
        ]);

        if (isset($response['error'])) {
            return response()->json(['message' => $response['error']], 400);
        }

        if (isset($response['faultcode']) || isset($response['faultstring'])) {
            return response()->json(['message' => $response], 400);
        }

        if (isset($response['RESULT'])) {
            return base64_decode($response['RESULT']);
        }
    }

    /**
     * Transform drill holes
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function requestTransformDrillHoles(Request $request) {
        return self::transformDrillHoles($request, [
                "drillHoles" => $request->input('drillHoles', ''),
                "trcstr" => $request->input('trcstr', ''),
                "type" => $request->input('type', ''),
                "modeIn" => $request->input('modeIn', 'HC'),
                "modeOut" => $request->input('modeOut', 'CC'),
                "side" => $request->input('side', ''),
            ]
        );
    }
    public static function transformDrillHoles(Request $request, $data) {
        $transformDrillHolesResponse = SOAPHelper::call($request, 'transformDrillHoles', [
            'ENCODED' => 'BASE64',
            'DRILLHOLES' => \Arr::get($data, 'drillHoles', ''),
            'TRCSTR' => \Arr::get($data, 'trcstr', ''),
            'TYPE' => \Arr::get($data, 'type', ''),
            'MODEIN' => \Arr::get($data, 'modeIn', 'HC'),
            'MODEOUT' => \Arr::get($data, 'modeOut', 'CC'),
            'SIDE' =>  \Arr::get($data, 'side', '')
        ]);

        if (isset($transformDrillHolesResponse['error'])) {
            return response()->json(['message' => $transformDrillHolesResponse['error']], 400);
        }

        if (isset($transformDrillHolesResponse['faultcode']) || isset($transformDrillHolesResponse['faultstring'])) {
            return response()->json(['message' => $transformDrillHolesResponse], 400);
        }

        if (isset($transformDrillHolesResponse['RESULT'])) {
            return base64_decode($transformDrillHolesResponse['RESULT']);
        }

        return $transformDrillHolesResponse;
    }

    /**
     * Scale shape
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function scaleShape(Request $request) {
        $response = SOAPHelper::call($request, 'scaleShape', [
            'ENCODED' => 'BASE64',
            'TRCSTR' => $request->input('trcstr', ''),
            'TYPE' => $request->input('type', ''),
            'BOXXR' => $request->input('boxXR', ''),
            'BOXYR' => $request->input('boxYR', ''),
            'BOXXL' => $request->input('boxXL', ''),
            'BOXYL' => $request->input('boxYL', ''),
        ]);

        if (isset($response['error'])) {
            return response()->json(['message' => $response['error']], 400);
        }

        if (isset($response['faultcode'])) {
            return response()->json(['message' => $response['faultcode']], 400);
        } else {
            if (isset($response['SHAPE'])) {
                $data = json_decode(base64_decode($response['SHAPE']));
                return response()->json($data, 200);
            } else {
                return response()->json($response, 200);
            }
        }
    }

    /**
     * Run the lens calculation
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getCalculation(Request $request) {

        $randomInt = random_int(100000000,900000000);
        $preparedSOAPCheckData = app('\App\Http\Controllers\OrderController')->saveOrder($request, false, true, $randomInt);
        if(isset($preparedSOAPCheckData['error']))
            if($preparedSOAPCheckData['error'] == 400)
                return response()->json(['message' => $preparedSOAPCheckData['message']], 400);
        if (!isset($preparedSOAPCheckData['orderNum']))
            return response()->json(['message' => "Internal calculation error "], 400);
        if (isset($preparedSOAPCheckData['orderCheck']) && $preparedSOAPCheckData['orderCheck'] != "1")
            return response()->json(['message' => $preparedSOAPCheckData['orderCheck']], 400);

        $calculationData = SOAPHelper::generateSOAPRequest($randomInt, 2, $request->input('uid', ''), $preparedSOAPCheckData['orderNum']);
        $calculationData = base64_encode($calculationData);
        $response = SOAPHelper::callCalc($request, 'getCalculation', [
            'ENCODED' => 'BASE64',
            'DATA' => $calculationData,
            'CALCMODE' => $request->input('calcMode', 'CONSULTING'),
            'FORMAT' => $request->input('format', 'XMLB2B'),
        ]);

        $formRight = $request->input('formRight', '');
        $formLeft = $request->input('formLeft', '');

        if (isset($response['error'])) {
            return response()->json(['message' => $response['error']], 400);
        }

        if (isset($response['faultcode'])) {
            return response()->json(['message' => $response], 400);
        } else {
            if (isset($response['CalcResult'])) {
                $data = json_decode(base64_decode($response['CalcResult']));
                return response()->json($data, 200);
            } else {
                $data = Calculation::parse($response, $formRight, $formLeft);
                return response()->json($data, 200);
            }
        }
    }

    /**
     * Decrypt traced data
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function decryptTracedata(Request $request) {
        $response = SOAPHelper::call($request, 'decryptTracedata', [
            'ENCODED' => 'BASE64',
            'SUPPLIER' => "LWR",
            'CRYPTTRACE' => $request->input('cryptTrace', ''),
        ]);

        if (isset($response['error'])) {
            return response()->json(['message' => $response['error']], 400);
        }

        if (isset($response['faultcode'])) {
            return response()->json(['message' => $response['faultcode']], 400);
        }

        if (isset($response['RESULT'])) {
            $data = base64_decode($response['RESULT']);
            return response()->json($data, 200);
        } else {
            return response()->json($response, 200);
        }
    }

    /**
     * Get additional remark data
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getRemarkKey(Request $request) {
        $soapResponse = SOAPHelper::call($request, 'WebShop', [
            'ENCODED' => 'BASE64',
            'LANG' => SOAPHelper::getLangCodeFromRequest($request),
            'SERVICE' => 'getRemStruct'
        ]);

        if (isset($soapResponse['error'])) {
            return response()->json([], 200);

            // Error returns empty array '[]' with positive status 200 bacause
            // if there is no respnose / error, the dropdown in BasicData should not be displayed.
            // return response()->json(['message' => $soapResponse['error']], 400);
        } else {
            // $utf8Encode = mb_convert_encoding($soapResponse, 'UTF-8', mb_list_encodings());
            $data = json_decode($soapResponse);
            $result = MainHelper::keyPairDataToArray($data, 'code', 'description');
            return response()->json($result, 200);
        }
    }


    /**
     * Get stock lenses by product list
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getStockLensesByProdList(Request $request) {
        $soapResponse = SOAPHelper::call($request, 'wholeSale', [
            'SERVICE' => 'getStockLensesByProdList',
            'CLIENT' => 'LensOrderWEB',
            'ENCODED' => 'BASE64',
            'PRODLIST' => $request->input('PRODLIST', ''),
        ], false);

        if (isset($soapResponse['error'])) {
            return response()->json(['message' => $soapResponse['error']], 400);
        } elseif (isset($soapResponse['LIST'])) {
            $data = json_decode(base64_decode($soapResponse['LIST']));
            return response()->json($data, 200);
        } else {
            return response()->json($soapResponse, 200);
        }
    }

    /**
     * Get customer list whole sale
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getCustomerListWholeSale(Request $request) {
        $soapResponse = SOAPHelper::call($request, 'wholeSale', [
            'ENCODED' => 'BASE64',
            'SERVICE' => 'getCustomerListWeb'
        ]);

        if (isset($soapResponse['error'])) {
            return response()->json(['message' => $soapResponse['error']], 400);
        } elseif (isset($soapResponse['RANGES'])) {
            $data = json_decode(base64_decode($soapResponse['RANGES']));
            return response()->json($data, 200);
        } else {
            return response()->json($soapResponse, 200);
        }
    }

    /**
     * Get product lists whole sale
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getProdListsWholeSale(Request $request) {
        $soapResponse = SOAPHelper::call($request, 'wholeSale', [
            'ENCODED' => 'BASE64',
            'SERVICE' => 'getProdLists'
        ]);

        if (isset($soapResponse['error'])) {
            return response()->json(['message' => $soapResponse['error']], 400);
        } elseif (isset($soapResponse['PRODLISTS'])) {
            $data = json_decode(base64_decode($soapResponse['PRODLISTS']));
            return response()->json($data, 200);
        } else {
            return response()->json($soapResponse, 200);
        }
    }

    /**
     * Get stock lenses whole sale
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getStockLensesWholeSale(Request $request) {
        $soapResponse = SOAPHelper::call($request, 'wholeSale', [
            'ENCODED' => 'BASE64',
            'CUSTOMER' => $request->input('uid', ''),
            'SERVICE' => 'getStockLenses'
        ]);

        if (isset($soapResponse['error'])) {
            return response()->json(['message' => $soapResponse['error']], 400);
        } elseif (isset($soapResponse['RANGES'])) {
            $data = json_decode(base64_decode($soapResponse['RANGES']));
            return response()->json($data, 200);
        } else {
            return response()->json($soapResponse, 200);
        }
    }

    /**
     * Get stock lenses whole sale
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getStockItems(Request $request) {
        $soapResponse = SOAPHelper::call($request, 'wholeSale', [
            'SERVICE' => 'getStockItems',
            'CLIENT' => 'LensOrderWEB',
            'ENCODED' => 'BASE64',
            'LENSCODE' => $request->input('lensCode', ''),
            'DIA' => $request->input('diameter', ''),
            'SIDE' => $request->input('side', ''),
        ]);

        if (isset($soapResponse['error'])) {
            return response()->json(['message' => $soapResponse['error']], 400);
        } elseif (isset($soapResponse['FIELDS'])) {
            $data = json_decode(base64_decode($soapResponse['FIELDS']));
            return response()->json($data, 200);
        } else {
            return response()->json($soapResponse, 200);
        }
    }

    /**
     * Get users list for shop
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getShopList(Request $request) {
        $soapResponse = SOAPHelper::call($request, 'WebShop', [
            'SERVICE' => 'getShopList',
            'CLIENT' => 'LensOrderWEB',
            'ENCODED' => 'BASE64',
            'CUSTNUMB' => $request->input('uid', ''),
        ]);

        if (isset($soapResponse['error'])) {
            return response()->json(['message' => $soapResponse['error']], 400);
        } elseif (isset($soapResponse['RESULT'])) {
            $data = json_decode(base64_decode($soapResponse['RESULT']));
            return response()->json($data, 200);
        } else {
            return response()->json($soapResponse, 200);
        }
    }

    /**
     * Get shop customer user details
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getCustomerDetails(Request $request) {
        $soapResponse = SOAPHelper::call($request, 'WebShop', [
            'SERVICE' => 'getCustomerDetails',
            'CLIENT' => 'LensOrderWEB',
            'ENCODED' => 'BASE64',
            'CUSTNUMB' => $request->input('custNumb', ''),
        ]);

        if (isset($soapResponse['error'])) {
            return response()->json(['message' => $soapResponse['error']], 400);
        } elseif (isset($soapResponse['RESULT'])) {
            $data = json_decode(base64_decode($soapResponse['RESULT']));
            return response()->json($data, 200);
        } else {
            return response()->json($soapResponse, 200);
        }
    }

    /**
     * Create new user customer
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function createCustomer(Request $request) {
        $soapResponse = SOAPHelper::call($request, 'WebShop', [
            'SERVICE' => 'createCustomer',
            'CLIENT' => 'LensOrderWEB',
            'ENCODED' => 'BASE64',
            'CUSTNUMB' => $request->input('custNumb', ''),
            'PASSWORD' => $request->input('password', ''),
            'NAME' => $request->input('name', ''),
            'STREET' => $request->input('street', ''),
            'CITY' => $request->input('city', ''),
            'ZIPCODE' => $request->input('zipCode', ''),
            'COUNTRY' => $request->input('country', ''),
        ]);

        if (isset($soapResponse['error'])) {
            return response()->json(['message' => $soapResponse['error']], 400);
        } elseif (isset($soapResponse['RESULT'])) {
            $data = json_decode(base64_decode($soapResponse['RESULT']));
            return response()->json($data, 200);
        } else {
            return response()->json($soapResponse, 200);
        }
    }

    /**
     * Change details of an existing user customer
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function changeCustomerDetails(Request $request) {
        $soapResponse = SOAPHelper::call($request, 'WebShop', [
            'SERVICE' => 'changeCustomerDetails',
            'CLIENT' => 'LensOrderWEB',
            'ENCODED' => 'BASE64',
            'CUSTNUMB' => $request->input('custNumb', ''),
            'PASSWORD' => $request->input('password', ''),
            'NAME' => $request->input('name', ''),
            'STREET' => $request->input('street', ''),
            'CITY' => $request->input('city', ''),
            'ZIPCODE' => $request->input('zipCode', ''),
            'COUNTRY' => $request->input('country', ''),
        ]);

        if (isset($soapResponse['error'])) {
            return response()->json(['message' => $soapResponse['error']], 400);
        } elseif (isset($soapResponse['RESULT'])) {
            $data = json_decode(base64_decode($soapResponse['RESULT']));
            return response()->json($data, 200);
        } else {
            return response()->json($soapResponse, 200);
        }
    }

    /**
     * Delete an existing user customer
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function deleteCustomer(Request $request) {
        $soapResponse = SOAPHelper::call($request, 'WebShop', [
            'SERVICE' => 'deleteCustomer',
            'CLIENT' => 'LensOrderWEB',
            'ENCODED' => 'BASE64',
            'CUSTNUMB' => $request->input('custNumb', ''),
        ]);

        if (isset($soapResponse['error'])) {
            return response()->json(['message' => $soapResponse['error']], 400);
        } elseif (isset($soapResponse['RESULT'])) {
            $data = json_decode(base64_decode($soapResponse['RESULT']));
            return response()->json($data, 200);
        } else {
            return response()->json($soapResponse, 200);
        }
    }

}
