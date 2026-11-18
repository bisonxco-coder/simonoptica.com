<?php

namespace App\Http\Controllers;

use App\Helpers\SettingsHelper;
use App\Helpers\SOAPHelper;
use App\Http\Controllers\SOAPController;
use App\Models\OrderComponent;
use App\Models\OrderConsumer;
use App\Models\OrderExtension;
use App\Models\OrderHeader;
use App\Models\OrderLensProperties;
use App\Models\OrderShape;
use App\Models\OrderShapeExtension;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Boolean;
use Illuminate\Support\Facades\Log;
class OrderController extends Controller {
    /**
     * Get orderbasket orders
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getOrderbasketOrders(Request $request) {
        $orderBy = $request->input('orderby', 'OrdNumb');
        $order = $request->input('order', 'desc');
        $perPage = $request->input('count', 20);
        $reference = $request->input('reference', '');
        $dateFrom = $request->input('dateFrom', null);
        $dateTo = $request->input('dateTo', null);
        $uid = $request->input('uid', null);

        $orderBy = strtolower($orderBy);
        $order = strtolower($order);

        // Check all the WIMP orders, if received
        $this->checkOrderReceived($request);

        $query = OrderHeader::with([
            'orderComponent',
            'orderConsumer',
            'orderExtension',
            'orderLensProperties',
            'orderShape',
            'orderShapeExtension',
        ])->where([
            ['CustNumb', '=', $uid],
            ['Reference', 'LIKE', '%'.$reference.'%'],
            ['RecStat', '!=', 'SEND']
        ]);

        if ($dateFrom && $dateTo){
            $query->whereBetween('EntryDate', [$dateFrom, $dateTo]);
        }

        $orders = $query->orderBy($orderBy, $order);
        return $orders->paginate($perPage);
    }

    /**
     * Delete from order basket
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function deleteOrder(Request $request) {
        $uid = $request->input('uid', null);
        $orderNumber = $request->input('OrdNumb', '');

        $orderHeader = OrderHeader::where([
            ['CustNumb', '=', $uid],
            ['OrdNumb', '=', $orderNumber],
        ]);
        if (!$orderHeader->exists()) abort(404, "Unauthorized action or order not found.");

        $orderHeader = $orderHeader->delete();
        $orderComponent = OrderComponent::where('OrdNumb', $orderNumber)->delete();
        $orderConsumer = OrderConsumer::where('OrdNumb', $orderNumber)->delete();
        $orderLensProperties = OrderLensProperties::where('OrdNumb', $orderNumber)->delete();
        $orderShape = OrderShape::where('OrdNumb', $orderNumber)->delete();
        $orderShapeExtension = OrderShapeExtension::where('OrdNumb', $orderNumber)->delete();
        $orderExtension = OrderExtension::where('OrdNumb', $orderNumber)->delete();
        return response()->json('deleted', 200);
    }

    /**
     * Get orders
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getOrders(Request $request) {

        $getOrdersResponse = SOAPHelper::call($request, 'GetStatusListWeb', [
            'ENCODED' => 'BASE64',
            'NEWSEARCH' => '1',
            'LASTDAYS' => $request->input('days', 365),
            'SORT' => $request->input('sort', 1),
            'SORTALG' => $request->input('sortalg', 'ORDNUMBH'),
            'COUNT' => $request->input('count', 5),
            'PAGE' => $request->input('page', 1),
            'SEARCH' => $request->input('search', ''),
            'DATEFROM' => $request->input('date_from', ''),
            'DATETO' => $request->input('date_to', ''),
            'FILTERSHOP' => $request->input('filter_shop', 0),
            'ARCHIVE' => $request->input('archive', 0),
            'SUPPLIER' => $request->input('supplier', 'LWR'),
            'RXSTOCK' => $request->input('rxstock', 'B')
        ]);

        if (isset($getOrdersResponse['error'])) {
            return response()->json(['message' => $getOrdersResponse['error']], 400);
        } else {
            $data = json_decode(base64_decode($getOrdersResponse['Status']));
            return response()->json($data, 200);
        }
    }

    /**
     * Translate bulkorder item list parameter received from order overview
     *
     * @param Request $request
     * @return void
     */
    public function translateBulkorderItemList(Request $request) {
        $itemListR = $request->input('itemList', '');
        $itemListRArr = explode("#", $itemListR);
        $translatedObject = [];

        for ($j = 0; $j < 2; ++$j) {
            $start = ceil($j       * count($itemListRArr) / 2);
            $end   = ceil(($j + 1) * count($itemListRArr) / 2);

            for ($k = $start; $k < $end; $k++) {
                $singleItemRArr = explode("*", $itemListRArr[$k]);
                $translatedObject[] = [
                    "key" => $k,
                    "description" => $singleItemRArr[0],
                    "diameter" => trim($singleItemRArr[1]),
                    "sphere" => trim($singleItemRArr[2]),
                    "cylinder" => trim($singleItemRArr[3]),
                    "addition" => trim($singleItemRArr[4]),
                    "quantity" => trim($singleItemRArr[5]),
                    "compCode" => trim($singleItemRArr[7])
                ];
            }
        }
        return $translatedObject;
    }

    /**
     * Translate individual string to properties
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function translateIndividual(Request $request) {
        $side = $request->input('side', "");
        $indtype = $request->input('indtype', "");
        $individualstring = $request->input('individualstring', null);
        $individualstring_arr = explode(";", $individualstring );
        $lenscoder = $request->input('lencoder', 'null');
        $translatedString = [];
        $result = SOAPHelper::call($request, 'WebShop', [
            'ENCODED' => 'BASE64',
            'INDTYPE' => $indtype,
            'SERVICE' => 'getIndividuals'
        ]);
        $data = json_decode(base64_decode($result['INDIVIDUALS']), true);

        if($lenscoder !== ''){
            $rangesCombinationResponse = (new SOAPController())->getRangesCombination($request, true);
        }
        if($individualstring) {
            foreach ($data as $key => $val) {
                if(count($individualstring_arr) > $data[$key]['POS'])
                    if($individualstring_arr[$data[$key]['POS']])
                        $translatedString[$key] = $individualstring_arr[$data[$key]['POS']];
            }
        }
        return response()->json(['translated' => $translatedString, 'indi' => $rangesCombinationResponse], 200);

        if (!$individualstring) return $translatedString;

        // Right side
        if ($side === 'right') {
            if(strpos($indtype, "FFU") !== false){
                $translatedString = [
                    'initialgrav' => $individualstring_arr[0],
                    'bvdr'        => $individualstring_arr[1],
                    'panto'       => $individualstring_arr[2],
                    'objectDist'  => $individualstring_arr[3],
                    'ztilt'       => $individualstring_arr[4],
                    'corrlen'     => $individualstring_arr[5],
                    'insetr'      => $individualstring_arr[6],
                    'degression'  => $individualstring_arr[7],
                    'lenti'       => $individualstring_arr[8],
                ];

                if (sizeof($individualstring_arr) >= 12) {
                      $translatedString['nearSphere']   = $individualstring_arr[ 9];
                      $translatedString['nearCylinder'] = $individualstring_arr[10];
                      $translatedString['nearAxis']     = $individualstring_arr[11];
                }
                if (sizeof($individualstring_arr) >= 14) {
                      $translatedString['designCode'] = $individualstring_arr[12];
                      $translatedString['corrCode']   = $individualstring_arr[13];
                }
                if (sizeof($individualstring_arr) >= 15) {
                      $translatedString['lentiCode']  = $individualstring_arr[14];
                }
                if (sizeof($individualstring_arr) >= 18) {
                      $translatedString['blendType']  = $individualstring_arr[15];
                      $translatedString['blendDA']    = $individualstring_arr[16];
                      $translatedString['blendAng']   = $individualstring_arr[17];
                }
                if (sizeof($individualstring_arr) >= 21) {
                      $translatedString['blBf']       = $individualstring_arr[18];
                      $translatedString['blBfDia']    = $individualstring_arr[19];
                      $translatedString['blBfUp']     = $individualstring_arr[20];
                }
                if (sizeof($individualstring_arr) >= 22) {
                      $translatedString['atoric']     = $individualstring_arr[21];
                }
                if (sizeof($individualstring_arr) >= 25) {
                      $translatedString['adjSph']     = $individualstring_arr[22];
                      $translatedString['adjCyl']     = $individualstring_arr[23];
                      $translatedString['adjAdd']     = $individualstring_arr[24];
                }
                if (sizeof($individualstring_arr) >= 28) {
                      $translatedString['varioD']     = $individualstring_arr[25];
                      $translatedString['varioI']     = $individualstring_arr[26];
                      $translatedString['varioN']     = $individualstring_arr[27];
                }
                if (sizeof($individualstring_arr) >= 30) {
                      $translatedString['SlBp']       = $individualstring_arr[28];
                      $translatedString['SlHt']       = $individualstring_arr[29];
                }
                if (sizeof($individualstring_arr) >= 31) {
                    $translatedString['fwd']          = $individualstring_arr[30];
                }
                if (sizeof($individualstring_arr) >= 32) {
                    $translatedString['vmap']         = $individualstring_arr[31];
                }
                if (sizeof($individualstring_arr) >= 33) {
                    $translatedString['nwd']        = $individualstring_arr[32];
                }
                if (sizeof($individualstring_arr) >= 34) {
                    $translatedString['clage']     = $individualstring_arr[33];
                }
                if (sizeof($individualstring_arr) >= 35) {
                   $translatedString['frameFit']   = $individualstring_arr[34];
                }
                if (sizeof($individualstring_arr) >= 38) {
                   $translatedString['bifoBlend']  = $individualstring_arr[35];
                   $translatedString['ltFrame']    = $individualstring_arr[36];
                   $translatedString['ltWidth']    = $individualstring_arr[37];
                }
                if (sizeof($individualstring_arr) >= 45) {
                   $translatedString['headEye']    = $individualstring_arr[38];
                   $translatedString['stable']     = $individualstring_arr[39];
                   $translatedString['cap']        = $individualstring_arr[40];
                   $translatedString['DERC']       = $individualstring_arr[41];
                   $translatedString['domEye']     = $individualstring_arr[42];
                   $translatedString['eyeCodeLgc'] = $individualstring_arr[43];
                   $translatedString['nvb']        = $individualstring_arr[44];
                }
                if (sizeof($individualstring_arr) >= 46) {
                   $translatedString['nearType']   = $individualstring_arr[45];
                }
                if (sizeof($individualstring_arr) >= 47) {
                   $translatedString['phoria']     = $individualstring_arr[46];
                }
                if (sizeof($individualstring_arr) >= 48) {
                   $translatedString['drive']      = $individualstring_arr[47];
                }
                if (sizeof($individualstring_arr) >= 49) {
                   $translatedString['nearPD']     = $individualstring_arr[48];
                }
                if (sizeof($individualstring_arr) >= 52) {
                   $translatedString['designType'] = $individualstring_arr[49];
                   $translatedString['fvs']        = $individualstring_arr[50];
                   $translatedString['nvs']        = $individualstring_arr[51];

                }
                if (sizeof($individualstring_arr) >= 53) {
                   $translatedString['fineEdge']   = $individualstring_arr[52];
                }
                if (sizeof($individualstring_arr) >= 54) {
                   $translatedString['headIncl']   = $individualstring_arr[53];
                }
                if (sizeof($individualstring_arr) >= 55) {
                   $translatedString['preference'] = $individualstring_arr[54];
                }
                if (sizeof($individualstring_arr) >= 56) {
                   $translatedString['bodyHeight'] = $individualstring_arr[55];
                }
                if (sizeof($individualstring_arr) >= 57) {
                   $translatedString['hand']       = $individualstring_arr[56];
                }
                if (sizeof($individualstring_arr) >= 58) {
                   $translatedString['fictiv']     = $individualstring_arr[57];
                }
                if (sizeof($individualstring_arr) >= 59) {
                   $translatedString['mid']        = $individualstring_arr[58];
                }
                if (sizeof($individualstring_arr) >= 60) {
                   $translatedString['pdia']       = $individualstring_arr[59];
                }
                if (sizeof($individualstring_arr) >= 61) {
                   $translatedString['cvdRef']     = $individualstring_arr[60];
                }
                if (sizeof($individualstring_arr) >= 62) {
                   $translatedString['ocr']        = $individualstring_arr[61];
                }
                $translatedString['engraving'] = $individualstring_arr[0];
            }
        }

        // Left side
        if ($side == 'left'){
            if(strpos($indtype, "FFU") !== false){
                $translatedString = [
                    'initialgrav' => $individualstring_arr[0],
                    'bvdl'        => $individualstring_arr[1],
                    'panto'       => $individualstring_arr[2],
                    'objectDist'  => $individualstring_arr[3],
                    'ztilt'       => $individualstring_arr[4],
                    'corrlen'     => $individualstring_arr[5],
                    'insetl'      => $individualstring_arr[6],
                    'degression'  => $individualstring_arr[7],
                    'lenti'       => $individualstring_arr[8],
                ];

                if (sizeof($individualstring_arr) >= 12) {
                      $translatedString['nearSphere']   = $individualstring_arr[ 9];
                      $translatedString['nearCylinder'] = $individualstring_arr[10];
                      $translatedString['nearAxis']     = $individualstring_arr[11];
                }
                if (sizeof($individualstring_arr) >= 14) {
                      $translatedString['designCode'] = $individualstring_arr[12];
                      $translatedString['corrCode']   = $individualstring_arr[13];
                }
                if (sizeof($individualstring_arr) >= 15) {
                      $translatedString['lentiCode']  = $individualstring_arr[14];
                }
                if (sizeof($individualstring_arr) >= 18) {
                      $translatedString['blendType']  = $individualstring_arr[15];
                      $translatedString['blendDA']    = $individualstring_arr[16];
                      $translatedString['blendAng']   = $individualstring_arr[17];
                }
                if (sizeof($individualstring_arr) >= 21) {
                      $translatedString['blBf']       = $individualstring_arr[18];
                      $translatedString['blBfDia']    = $individualstring_arr[19];
                      $translatedString['blBfUp']     = $individualstring_arr[20];
                }
                if (sizeof($individualstring_arr) >= 22) {
                      $translatedString['atoric']     = $individualstring_arr[21];
                }
                if (sizeof($individualstring_arr) >= 25) {
                      $translatedString['adjSph']     = $individualstring_arr[22];
                      $translatedString['adjCyl']     = $individualstring_arr[23];
                      $translatedString['adjAdd']     = $individualstring_arr[24];
                }
                if (sizeof($individualstring_arr) >= 28) {
                      $translatedString['varioD']     = $individualstring_arr[25];
                      $translatedString['varioI']     = $individualstring_arr[26];
                      $translatedString['varioN']     = $individualstring_arr[27];
                }
                if (sizeof($individualstring_arr) >= 30) {
                      $translatedString['SlBp']       = $individualstring_arr[28];
                      $translatedString['SlHt']       = $individualstring_arr[29];
                }
                if (sizeof($individualstring_arr) >= 31) {
                    $translatedString['fwd']          = $individualstring_arr[30];
                }
                if (sizeof($individualstring_arr) >= 32) {
                    $translatedString['vmap']         = $individualstring_arr[31];
                }
                if (sizeof($individualstring_arr) >= 33) {
                    $translatedString['nwd']        = $individualstring_arr[32];
                }
                if (sizeof($individualstring_arr) >= 34) {
                    $translatedString['clage']     = $individualstring_arr[33];
                }
                if (sizeof($individualstring_arr) >= 35) {
                   $translatedString['frameFit']   = $individualstring_arr[34];
                }
                if (sizeof($individualstring_arr) >= 38) {
                   $translatedString['bifoBlend']  = $individualstring_arr[35];
                   $translatedString['ltFrame']    = $individualstring_arr[36];
                   $translatedString['ltWidth']    = $individualstring_arr[37];
                }
                if (sizeof($individualstring_arr) >= 45) {
                   $translatedString['headEye']    = $individualstring_arr[38];
                   $translatedString['stable']     = $individualstring_arr[39];
                   $translatedString['cap']        = $individualstring_arr[40];
                   $translatedString['DERC']       = $individualstring_arr[41];
                   $translatedString['domEye']     = $individualstring_arr[42];
                   $translatedString['eyeCodeLgc'] = $individualstring_arr[43];
                   $translatedString['nvb']        = $individualstring_arr[44];
                }
                if (sizeof($individualstring_arr) >= 46) {
                   $translatedString['nearType']   = $individualstring_arr[45];
                }
                if (sizeof($individualstring_arr) >= 47) {
                   $translatedString['phoria']     = $individualstring_arr[46];
                }
                if (sizeof($individualstring_arr) >= 48) {
                   $translatedString['drive']      = $individualstring_arr[47];
                }
                if (sizeof($individualstring_arr) >= 49) {
                   $translatedString['nearPD']     = $individualstring_arr[48];
                }
                if (sizeof($individualstring_arr) >= 52) {
                   $translatedString['designType'] = $individualstring_arr[49];
                   $translatedString['fvs']        = $individualstring_arr[50];
                   $translatedString['nvs']        = $individualstring_arr[51];

                }
                if (sizeof($individualstring_arr) >= 53) {
                   $translatedString['fineEdge']   = $individualstring_arr[52];
                }
                if (sizeof($individualstring_arr) >= 54) {
                   $translatedString['headIncl']   = $individualstring_arr[53];
                }
                if (sizeof($individualstring_arr) >= 55) {
                   $translatedString['preference'] = $individualstring_arr[54];
                }
                if (sizeof($individualstring_arr) >= 56) {
                   $translatedString['bodyHeight'] = $individualstring_arr[55];
                }
                if (sizeof($individualstring_arr) >= 57) {
                   $translatedString['hand']       = $individualstring_arr[56];
                }
                if (sizeof($individualstring_arr) >= 58) {
                   $translatedString['fictiv']     = $individualstring_arr[57];
                }
                if (sizeof($individualstring_arr) >= 59) {
                   $translatedString['mid']        = $individualstring_arr[58];
                }
                if (sizeof($individualstring_arr) >= 60) {
                   $translatedString['pdia']       = $individualstring_arr[59];
                }
                if (sizeof($individualstring_arr) >= 61) {
                   $translatedString['cvdRef']     = $individualstring_arr[60];
                }
                if (sizeof($individualstring_arr) >= 62) {
                   $translatedString['ocr']        = $individualstring_arr[61];
                }
                $translatedString['engraving'] = $individualstring_arr[0];
            }
        }

        return response()->json($translatedString, 200);
    }

    /**
     * Translate individual properties to string
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    /*public function translateIndividualProp(Request $request) {
        $side = $request->input('side', "");
        $indtype = $request->input('indtype', "");
        $individualProp = $request->input('individualProp', null);
        $individualstring;

        if (!$individualProp) return $individualstring;

        // Right side
        if ($side === 'right') {
            if(strpos($indtype, "FFU") !== false){
                $translatedString = [
                    'initialgrav' => $individualstring_arr[0],
                    'bvdr'        => $individualstring_arr[1],
                    'panto'       => $individualstring_arr[2],
                    'objectDist'  => $individualstring_arr[3],
                    'ztilt'       => $individualstring_arr[4],
                    'corrlen'     => $individualstring_arr[5],
                    'insetr'      => $individualstring_arr[6],
                    'degression'  => $individualstring_arr[7],
                    'lenti'       => $individualstring_arr[8],
                ];

                if (sizeof($individualstring_arr) >= 12) {
                      $translatedString['nearSphere']   = $individualstring_arr[ 9];
                      $translatedString['nearCylinder'] = $individualstring_arr[10];
                      $translatedString['nearAxis']     = $individualstring_arr[11];
                }
                if (sizeof($individualstring_arr) >= 14) {
                      $translatedString['designCode'] = $individualstring_arr[12];
                      $translatedString['corrCode']   = $individualstring_arr[13];
                }
                if (sizeof($individualstring_arr) >= 15) {
                      $translatedString['lentiCode']  = $individualstring_arr[14];
                }
                if (sizeof($individualstring_arr) >= 18) {
                      $translatedString['blendType']  = $individualstring_arr[15];
                      $translatedString['blendDA']    = $individualstring_arr[16];
                      $translatedString['blendAng']   = $individualstring_arr[17];
                }
                if (sizeof($individualstring_arr) >= 21) {
                      $translatedString['blBf']       = $individualstring_arr[18];
                      $translatedString['blBfDia']    = $individualstring_arr[19];
                      $translatedString['blBfUp']     = $individualstring_arr[20];
                }
                if (sizeof($individualstring_arr) >= 22) {
                      $translatedString['atoric']     = $individualstring_arr[21];
                }
                if (sizeof($individualstring_arr) >= 25) {
                      $translatedString['adjSph']     = $individualstring_arr[22];
                      $translatedString['adjCyl']     = $individualstring_arr[23];
                      $translatedString['adjAdd']     = $individualstring_arr[24];
                }
                if (sizeof($individualstring_arr) >= 28) {
                      $translatedString['varioD']     = $individualstring_arr[25];
                      $translatedString['varioI']     = $individualstring_arr[26];
                      $translatedString['varioN']     = $individualstring_arr[27];
                }
                if (sizeof($individualstring_arr) >= 30) {
                      $translatedString['SlBp']       = $individualstring_arr[28];
                      $translatedString['SlHt']       = $individualstring_arr[29];
                }
                if (sizeof($individualstring_arr) >= 31) {
                    $translatedString['fwd']          = $individualstring_arr[30];
                }
                if (sizeof($individualstring_arr) >= 32) {
                    $translatedString['vmap']         = $individualstring_arr[31];
                }
                if (sizeof($individualstring_arr) >= 33) {
                    $translatedString['nwd']        = $individualstring_arr[32];
                }
                if (sizeof($individualstring_arr) >= 34) {
                    $translatedString['clage']     = $individualstring_arr[33];
                }
                if (sizeof($individualstring_arr) >= 35) {
                   $translatedString['frameFit']   = $individualstring_arr[34];
                }
                if (sizeof($individualstring_arr) >= 38) {
                   $translatedString['bifoBlend']  = $individualstring_arr[35];
                   $translatedString['ltFrame']    = $individualstring_arr[36];
                   $translatedString['ltWidth']    = $individualstring_arr[37];
                }
                if (sizeof($individualstring_arr) >= 45) {
                   $translatedString['headEye']    = $individualstring_arr[38];
                   $translatedString['stable']     = $individualstring_arr[39];
                   $translatedString['cap']        = $individualstring_arr[40];
                   $translatedString['DERC']       = $individualstring_arr[41];
                   $translatedString['domEye']     = $individualstring_arr[42];
                   $translatedString['eyeCodeLgc'] = $individualstring_arr[43];
                   $translatedString['nvb']        = $individualstring_arr[44];
                }
                if (sizeof($individualstring_arr) >= 46) {
                   $translatedString['nearType']   = $individualstring_arr[45];
                }
                if (sizeof($individualstring_arr) >= 47) {
                   $translatedString['phoria']     = $individualstring_arr[46];
                }
                if (sizeof($individualstring_arr) >= 48) {
                   $translatedString['drive']      = $individualstring_arr[47];
                }
                if (sizeof($individualstring_arr) >= 49) {
                   $translatedString['nearPD']     = $individualstring_arr[48];
                }
                if (sizeof($individualstring_arr) >= 52) {
                   $translatedString['designType'] = $individualstring_arr[49];
                   $translatedString['fvs']        = $individualstring_arr[50];
                   $translatedString['nvs']        = $individualstring_arr[51];

                }
                if (sizeof($individualstring_arr) >= 53) {
                   $translatedString['fineEdge']   = $individualstring_arr[52];
                }
                if (sizeof($individualstring_arr) >= 54) {
                   $translatedString['headIncl']   = $individualstring_arr[53];
                }
                if (sizeof($individualstring_arr) >= 55) {
                   $translatedString['preference'] = $individualstring_arr[54];
                }
                if (sizeof($individualstring_arr) >= 56) {
                   $translatedString['bodyHeight'] = $individualstring_arr[55];
                }
                if (sizeof($individualstring_arr) >= 57) {
                   $translatedString['hand']       = $individualstring_arr[56];
                }
                if (sizeof($individualstring_arr) >= 58) {
                   $translatedString['fictiv']     = $individualstring_arr[57];
                }
                if (sizeof($individualstring_arr) >= 59) {
                   $translatedString['mid']        = $individualstring_arr[58];
                }
                if (sizeof($individualstring_arr) >= 60) {
                   $translatedString['pdia']       = $individualstring_arr[59];
                }
                if (sizeof($individualstring_arr) >= 61) {
                   $translatedString['cvdRef']     = $individualstring_arr[60];
                }
                if (sizeof($individualstring_arr) >= 62) {
                   $translatedString['ocr']        = $individualstring_arr[61];
                }
                $translatedString['engraving'] = $individualstring_arr[0];
            }
        }

        // Left side
        if ($side == 'left'){
            if(strpos($indtype, "FFU") !== false){
                $translatedString = [
                    'initialgrav' => $individualstring_arr[0],
                    'bvdl'        => $individualstring_arr[1],
                    'panto'       => $individualstring_arr[2],
                    'objectDist'  => $individualstring_arr[3],
                    'ztilt'       => $individualstring_arr[4],
                    'corrlen'     => $individualstring_arr[5],
                    'insetl'      => $individualstring_arr[6],
                    'degression'  => $individualstring_arr[7],
                    'lenti'       => $individualstring_arr[8],
                ];

                if (sizeof($individualstring_arr) >= 12) {
                      $translatedString['nearSphere']   = $individualstring_arr[ 9];
                      $translatedString['nearCylinder'] = $individualstring_arr[10];
                      $translatedString['nearAxis']     = $individualstring_arr[11];
                }
                if (sizeof($individualstring_arr) >= 14) {
                      $translatedString['designCode'] = $individualstring_arr[12];
                      $translatedString['corrCode']   = $individualstring_arr[13];
                }
                if (sizeof($individualstring_arr) >= 15) {
                      $translatedString['lentiCode']  = $individualstring_arr[14];
                }
                if (sizeof($individualstring_arr) >= 18) {
                      $translatedString['blendType']  = $individualstring_arr[15];
                      $translatedString['blendDA']    = $individualstring_arr[16];
                      $translatedString['blendAng']   = $individualstring_arr[17];
                }
                if (sizeof($individualstring_arr) >= 21) {
                      $translatedString['blBf']       = $individualstring_arr[18];
                      $translatedString['blBfDia']    = $individualstring_arr[19];
                      $translatedString['blBfUp']     = $individualstring_arr[20];
                }
                if (sizeof($individualstring_arr) >= 22) {
                      $translatedString['atoric']     = $individualstring_arr[21];
                }
                if (sizeof($individualstring_arr) >= 25) {
                      $translatedString['adjSph']     = $individualstring_arr[22];
                      $translatedString['adjCyl']     = $individualstring_arr[23];
                      $translatedString['adjAdd']     = $individualstring_arr[24];
                }
                if (sizeof($individualstring_arr) >= 28) {
                      $translatedString['varioD']     = $individualstring_arr[25];
                      $translatedString['varioI']     = $individualstring_arr[26];
                      $translatedString['varioN']     = $individualstring_arr[27];
                }
                if (sizeof($individualstring_arr) >= 30) {
                      $translatedString['SlBp']       = $individualstring_arr[28];
                      $translatedString['SlHt']       = $individualstring_arr[29];
                }
                if (sizeof($individualstring_arr) >= 31) {
                    $translatedString['fwd']          = $individualstring_arr[30];
                }
                if (sizeof($individualstring_arr) >= 32) {
                    $translatedString['vmap']         = $individualstring_arr[31];
                }
                if (sizeof($individualstring_arr) >= 33) {
                    $translatedString['nwd']        = $individualstring_arr[32];
                }
                if (sizeof($individualstring_arr) >= 34) {
                    $translatedString['clage']     = $individualstring_arr[33];
                }
                if (sizeof($individualstring_arr) >= 35) {
                   $translatedString['frameFit']   = $individualstring_arr[34];
                }
                if (sizeof($individualstring_arr) >= 38) {
                   $translatedString['bifoBlend']  = $individualstring_arr[35];
                   $translatedString['ltFrame']    = $individualstring_arr[36];
                   $translatedString['ltWidth']    = $individualstring_arr[37];
                }
                if (sizeof($individualstring_arr) >= 45) {
                   $translatedString['headEye']    = $individualstring_arr[38];
                   $translatedString['stable']     = $individualstring_arr[39];
                   $translatedString['cap']        = $individualstring_arr[40];
                   $translatedString['DERC']       = $individualstring_arr[41];
                   $translatedString['domEye']     = $individualstring_arr[42];
                   $translatedString['eyeCodeLgc'] = $individualstring_arr[43];
                   $translatedString['nvb']        = $individualstring_arr[44];
                }
                if (sizeof($individualstring_arr) >= 46) {
                   $translatedString['nearType']   = $individualstring_arr[45];
                }
                if (sizeof($individualstring_arr) >= 47) {
                   $translatedString['phoria']     = $individualstring_arr[46];
                }
                if (sizeof($individualstring_arr) >= 48) {
                   $translatedString['drive']      = $individualstring_arr[47];
                }
                if (sizeof($individualstring_arr) >= 49) {
                   $translatedString['nearPD']     = $individualstring_arr[48];
                }
                if (sizeof($individualstring_arr) >= 52) {
                   $translatedString['designType'] = $individualstring_arr[49];
                   $translatedString['fvs']        = $individualstring_arr[50];
                   $translatedString['nvs']        = $individualstring_arr[51];

                }
                if (sizeof($individualstring_arr) >= 53) {
                   $translatedString['fineEdge']   = $individualstring_arr[52];
                }
                if (sizeof($individualstring_arr) >= 54) {
                   $translatedString['headIncl']   = $individualstring_arr[53];
                }
                if (sizeof($individualstring_arr) >= 55) {
                   $translatedString['preference'] = $individualstring_arr[54];
                }
                if (sizeof($individualstring_arr) >= 56) {
                   $translatedString['bodyHeight'] = $individualstring_arr[55];
                }
                if (sizeof($individualstring_arr) >= 57) {
                   $translatedString['hand']       = $individualstring_arr[56];
                }
                if (sizeof($individualstring_arr) >= 58) {
                   $translatedString['fictiv']     = $individualstring_arr[57];
                }
                if (sizeof($individualstring_arr) >= 59) {
                   $translatedString['mid']        = $individualstring_arr[58];
                }
                if (sizeof($individualstring_arr) >= 60) {
                   $translatedString['pdia']       = $individualstring_arr[59];
                }
                if (sizeof($individualstring_arr) >= 61) {
                   $translatedString['cvdRef']     = $individualstring_arr[60];
                }
                if (sizeof($individualstring_arr) >= 62) {
                   $translatedString['ocr']        = $individualstring_arr[61];
                }
                $translatedString['engraving'] = $individualstring_arr[0];
            }
        }

        return response()->json($translatedString, 200);
    }*/

    /**
     * Edit order
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function editOrder(Request $request) {
        return $this->saveOrder($request, true);
    }

    /**
     * Save order
     *
     * @param Request $request
     * @param Boolean $isEdit
     * @return \Illuminate\Http\Response
     */
    public function saveOrder(Request $request, $isEdit = false, $justReturnData = false, $randomInt = 0) {
        $customer = $request->input('uid', '');
        $action = $request->input('action', '');
        $interpolateForm = $request->input('interpolateForm', '');
        session(['interpolateForm' => $interpolateForm]);

        $individual = $request->input('individual', []);
        $ovalr           = '';
        $ovall           = '';
        $additionr       = '';
        $additionl       = '';
        $addDegr         = '';
        $addDegl         = '';
        $diameterr       = '';
        $diam2r          = '';
        $diam3r          = '';
        $diameterl       = '';
        $diam2l          = '';
        $diam3l          = '';
        $optimizedr      = '';
        $optimizedl      = '';
        $cribtyper       = '';
        $cribtypel       = '';
        $listr           = '';
        $listl           = '';
        $minedger        = null;
        $devcenterr      = null;
        $basecurver      = null;
        $minedgel        = null;
        $devcenterl      = null;
        $basecurvel      = null;
        $prism1r         = '';
        $base1r          = '';
        $prism2r         = '';
        $base2r          = '';
        $prism1l         = '';
        $base1l          = '';
        $prism2l         = '';
        $base2l          = '';
        $rundr           = '';
        $rundl           = '';
        $ellipser1       = '';
        $ellipser2       = '';
        $ellipsel1       = '';
        $ellipsel2       = '';
        $bvd             = '';
        $bvdr            = '';
        $bvdl            = '';
        $insetr          = '';
        $insetl          = '';
        $panto           = '';
        $frcv            = '';
        $objectDist             = '';
        $ztilt           = '';
        $corrlen         = '';
        $pref            = '';
        $dercr           = '';
        $dercl           = '';
        $comport         = '';
        $comportlist     = '';
        $baud            = 0;
        $flow            = 0;
        $parity          = 0;
        $databit         = 0;
        $stopbit         = 0;
        $vca             = 0;
        $com_soft        = 0;
        $com_hard        = 0;
        $com_dtr         = 0;
        $tracerIP        = '';
        $tracerSerialNo  = '';
        $tracedata       = '';
        $str             = '';
        $vcafile         = null;
        $ordertype       = '';
        $tint            = '';
        $coating         = '';
        $indtype         = '';
        $handSelect      = '';
        $readdist        = '';
        $readref         = '';
        $fpinb           = 0;
        $pinb            = 0;
        $polish          = "0";
        $bevp            = '';
        $bevm            = '';
        $headtilt        = '';
        $lensdescrl      = '';
        $lensdescrr      = '';
        $tintdescrl      = '';
        $tintdescrr      = '';
        $coatdescrl      = '';
        $coatdescrr      = '';
        $stockRXr        = '';
        $stockRXl        = '';
        $initialgrav     = '';
        $lenti           = '';
        $tintremark      = '';
        $additionalRemark= '';
        $engraving       = '';
        $uv              = '';
        $uvdescrr         = '';
        $vmap            = '';
        $fwd             = '';
        $degression      = '';
        $material        = 'X';
        $index           = 'X';
        $group           = 'X';
        $v_drillThk      = '';
        $consStreet      = '';
        $consCity        = '';
        $consZip         = '';
        $consTitle       = '';
        $consFirstName   = '';
        $consLastName    = '';
        $consPhone       = '';
        $consLang        = '';
        $consDateOfBirth = '';
        $clage           = '';
        $entryUser       = '';
        $reqDelv         = '';
        $consEMail		 = '';
        $consRes1		 = '';
        $consRes2		 = '';
        $consRes3		 = '';
        $consRes4		 = '';
        $consRes5		 = '';
        $consRes6		 = '';
        $consRes7		 = '';
        $consRes8		 = '';
        $consRes9		 = '';
        $consRes10		 = '';
        $frame_remark 	 = '';
        $filter         = '';
        $trctype         = '';
        $has_forcedDecentration = false;
        $forcedDecentration_decentrationxr = '';
        $forcedDecentration_decentrationyr = '';
        $forcedDecentration_decentrationxl = '';
        $forcedDecentration_decentrationyl = '';

        $selectDiamR = 9;
        $selectDiamL = 9;

        $data = $request->all();
        foreach ($data as $key => $value) {
            $GLOBALS[$key] = $value;
        }
        foreach ($data as $key => $value) {
            ${$key} = $value;
        }

        if ($trctype === null) $trctype = "";
        session(['Trctype' => $trctype]);

        if (isset($orderbutton) || isset($orderbuttonSave) || isset($checkbutton)) {
            if ($R == 0) {
                $lenstyper = '';
                $spherer = '';
                $cylinderr = '';
                $axisr = '';
                $additionr = '';
                $addDegr = '';
                $prism1r = '';
                $base1r = '';
                $prism2r = '';
                $base2r = '';
                $minedger = '';
                $devcenterr = '';
                $basecurver = '';
                $pdr = '';
                $heightr = '';
                $tintr = '';
                $coatingr = '';
                $diam1r = '';
                $diam2r = '';
                $diam3r = '';
                $insetr = '';
                $lensdescrr = '';
                $tintdescrr = '';
                $coatdescrr = '';
                $stockRXr = '';
            } else {
                if (!isset($spherer) || $spherer === "")
                    $spherer = null;
                if (!isset($cylinderr) || $cylinderr === "")
                    $cylinderr = null;
                if (!isset($axisr) || $axisr === "")
                    $axisr = null;
                if (!isset($additionr) || $additionr === "")
                    $additionr = null;
                if (!isset($addDegr) || $addDegr === "")
                    $addDegr = null;
                if (!isset($prism1r) || $prism1r === "")
                    $prism1r = null;
                if (!isset($base1r) || $base1r === "")
                    $base1r = null;
                if (!isset($prism2r) || $prism2r === "")
                    $prism2r = null;
                if (!isset($base2r) || $base2r === "")
                    $base2r = null;
                if (!isset($minedger) || $minedger === "")
                    $minedger = null;
                if (!isset($devcenterr) || $devcenterr === "")
                    $devcenterr = null;
                if (!isset($basecurver) || $basecurver === "")
                    $basecurver = null;
                if (!isset($pdr) || $pdr === "")
                    $pdr = null;
                if (!isset($heightr) || $heightr === "")
                    $heightr = null;
                if (!isset($diam1r) || $diam1r === "")
                    $diam1r = "";
                if (!isset($diam2r) || $diam2r === "")
                    $diam2r = "";
                if (!isset($diam3r) || $diam3r === "")
                    $diam3r = "";

                /*if ($selectDiamR == 1) {        //Liste Rund
                    $diam1r = substr($rundrList, 0, 2);
                    $diam2r = substr($rundrList, 2, 2);
                } else if ($selectDiamR == 2) { //manuelle Eingabe Rund
                    $rundr = str_replace("/", "", $rundr);
                    $diam1r = substr($rundr, 0, 2);
                    $diam2r = substr($rundr, 2, 2);
                } else if ($selectDiamR == 3) { //Liste Oval
                    $diam1r = substr($ellipserList, 0, 2);
                    $diam2r = substr($ellipserList, 2, 2);
                    $diam3r = substr($ellipserList, 5, 2);
                } else if ($selectDiamR == 4) { //manuelle Eingabe Oval
                    $diam1r = substr($ellipser1, 0, 2);
                    $diam2r = substr($ellipser1, 2, 2);
                    $diam3r = $ellipser2;
                } else
                if ($v_ctor == 1) {
                    $diam1r = substr($v_diamrCTO, 0, 2);
                    $diam2r = substr($v_diamrCTO, 2, 2);
                } else {
                    $diam1r = substr($rundrList, 0, 2);
                    $diam2r = substr($rundrList, 2, 2);
                }*/

                if (isset($stockr)) {
                    $stockRXr = "S";
                } else {
                    $stockRXr = "R";
                }
            }

            if ($L == 0) {
                $lenstypel = '';
                $spherel = '';
                $cylinderl = '';
                $axisl = '';
                $additionl = '';
                $addDegl = '';
                $prism1l = '';
                $base1l = '';
                $prism2l = '';
                $base2l = '';
                $minedgel = '';
                $devcenterl = '';
                $basecurvel = '';
                $pdl = '';
                $heightl = '';
                $tintl = '';
                $coatingl = '';
                $diam1l = '';
                $diam2l = '';
                $diam3l = '';
                $insetl = '';
                $lensdescrl = '';
                $tintdescrl = '';
                $coatdescrl = '';
                $stockRXl = '';
            } else {
                if (!isset($spherel) || $spherel === "")
                    $spherel = null;
                if (!isset($cylinderl) || $cylinderl === "")
                    $cylinderl = null;
                if (!isset($axisl) || $axisl === "")
                    $axisl = null;
                if (!isset($additionl) || $additionl === "")
                    $additionl = null;
                if (!isset($addDegl) || $addDegl === "")
                    $addDegl = null;
                if (!isset($prism1l) || $prism1l === "")
                    $prism1l = null;
                if (!isset($base1l) || $base1l === "")
                    $base1l = null;
                if (!isset($prism2l) || $prism2l === "")
                    $prism2l = null;
                if (!isset($base2l) || $base2l === "")
                    $base2l = null;
                if (!isset($minedgel) || $minedgel === "")
                    $minedgel = null;
                if (!isset($devcenterl) || $devcenterl === "")
                    $devcenterl = null;
                if (!isset($basecurvel) || $basecurvel === "")
                    $basecurvel = null;
                if (!isset($pdl) || $pdl === "")
                    $pdl = null;
                if (!isset($heightl) || $heightl === "")
                    $heightl = null;
                if (!isset($diam1l) || $diam1l === "")
                    $diam1l = "";
                if (!isset($diam2l) || $diam2l === "")
                    $diam2l = "";
                if (!isset($diam3l) || $diam3l === "")
                    $diam3l = "";

                /*if ($selectDiamL == 1) {        //Liste Rund
                    $diam1l = substr($rundlList, 0, 2);
                    $diam2l = substr($rundlList, 2, 2);
                } else if ($selectDiamL == 2) { //manuelle Eingabe Rund
                    $rundl = str_replace("/", "", $rundl);
                    $diam1l = substr($rundl, 0, 2);
                    $diam2l = substr($rundl, 2, 2);
                } else if ($selectDiamL == 3) { //Liste Oval
                    $diam1l = substr($ellipselList, 0, 2);
                    $diam2l = substr($ellipselList, 2, 2);
                    $diam3l = substr($ellipselList, 5, 2);
                } else if ($selectDiamL == 4) { //manuelle Eingabe Oval
                    $diam1l = substr($ellipsel1, 0, 2);
                    $diam2l = substr($ellipsel1, 2, 2);
                    $diam3l = $ellipsel2;
                } else
                if ($v_ctol == 1) {
                     $diam1l = substr($v_diamlCTO, 0, 2);
                     $diam2l = substr($v_diamlCTO, 2, 2);
                 } else {
                    $diam1l = substr($rundlList, 0, 2);
                    $diam2l = substr($rundlList, 2, 2);
                }*/

                if (isset($stockl)) {
                    $stockRXl = "S";
                } else {
                    $stockRXl = "R";
                }
            }
            //echo "Datei: ../".$_SESSION['VCAFileName'];

            // !! What does this do?
            // if (file_exists("../" . session('VCAFileName'))) {
            //     unlink("../" . session('VCAFileName'));
            // }

            // $tracedata = session('strForLensGeo');


            if (trim($frameNo) == "" && config('iniproperties.LENS_ORDER_CONFIGURATION_PATCHFRAMENO', 'NO') == "YES") {
                $frameNo = $selectedForm;
            }

            if (isset($bvdSelect)) {
                $bvd = $bvdSelect;
            }
            if (isset($pantoSelect)) {
                $panto = $pantoSelect;
            }
            if (isset($frameCurveSelect)) {
                $frcv = $frameCurveSelect;
            }
            if (isset($faceformSelect)) {
                $ztilt = $faceformSelect;
            }
            if (isset($sizeSelect)) {
                $readdist = substr($sizeSelect, 0, 2);
                $readref = substr($sizeSelect, 2, 2);
            }

            if ($v_pinbevel == "1") {
                $fpinb = $fpinb_def;
                $pinb = $pinb_def;
            }
            if ($v_polish == "0") {
                $v_polish = "0";
            }

            /*   if ($v_consCheckBox1 == "1"){
                 $consCheckBox1 = 1;
              }
              if ($v_consCheckBox2 == "1"){
                $consCheckBox2 = 1;
              }
              if ($v_consCheckBox3 == "1" ){
                $consCheckBox3 = 1;
              }  */
            $consRes1 = $v_consRes1;
            $consRes2 = $v_consRes2;
            $consRes3 = $v_consRes3;
            $consRes4 = $v_consRes4;
            $consRes5 = $v_consRes5;
            $consRes6 = $v_consRes6;
            $consRes7 = $v_consRes7;
            $consRes8 = $v_consRes8;
            $consRes9 = $v_consRes9;
            $consRes10 = $v_consRes10;
            if (!$consRes1) $consRes1 = "";
            if (!$consRes2) $consRes2 = "";
            if (!$consRes3) $consRes3 = "";
            if (!$consRes4) $consRes4 = "";
            if (!$consRes5) $consRes5 = "";
            if (!$consRes6) $consRes6 = "";
            if (!$consRes7) $consRes7 = "";
            if (!$consRes8) $consRes8 = "";
            if (!$consRes9) $consRes9 = "";
            if (!$consRes10) $consRes10 = "";

            $control = 0;
            //   if ($v_frametype == "1") {
            $control = "CONTROL" . str_pad($v_frametype, 2, "0", STR_PAD_LEFT);
            //   } else if ($v_frametype == "2") {
            //      $control = "CONTROL02";
            //   } else if ($v_frametype == "3") {
            //      $control = "CONTROL03";
            //   } else if ($v_frametype == "4") {
            //      $control = "CONTROL04";
            //   } else if ($v_frametype == "5") {
            //      $control = "CONTROL05";
            //   } else if ($v_frametype == "6") {
            //      $control = "CONTROL06";
            //   }

            // $result = SettingsHelper::config('iniproperties', "FRAME_MATERIAL_DESCRIPTION08");

            $mat = config('iniproperties.FRAME_MATERIAL_' . $control);
            $mat_arr = explode(';', $mat, 2);
            $ftype = $mat_arr[0];
            $etype = $mat_arr[1];

            if (isset($v_bevelpos)) {
                if (file_exists("../" . session('supplier') . "/product_all_html_formDialog_bevelpos.php")) {
                    $bevelx = config('iniproperties.FRAME_BEVEL_CONTROL' . str_pad($v_bevelpos, 2, "0", STR_PAD_LEFT));
                    $bevelx_arr = explode(';', $bevelx);
                    $bevp = $bevelx_arr[0];
                    if (sizeof($bevelx_arr) > 2) {
                        $bevm = $bevelx_arr[2];
                    } else if ($bevelx_arr[1] == "1" || $bevelx_arr[1] == "2") {
                        $bevm = $v_bevm;
                    } else {
                        $bevm = $bevelx_arr[1];
                    }
                } else {
                    if ($v_bevelpos == "1") {
                        $bevel01 = config('iniproperties.FRAME_BEVEL_CONTROL01');
                        $bevel01_arr = explode(';', $bevel01, 2);
                        $bevp = $bevel01_arr[0];
                        $bevm = "0";
                    } else if ($v_bevelpos == "2") {
                        $bevel02 = config('iniproperties.FRAME_BEVEL_CONTROL02');
                        $bevel02_arr = explode(';', $bevel02, 2);
                        $bevp = $bevel02_arr[0];
                        $bevm = "0";
                    } else if ($v_bevelpos == "3") {
                        $bevel03 = config('iniproperties.FRAME_BEVEL_CONTROL03');
                        $bevel03_arr = explode(';', $bevel03, 2);
                        $bevp = $bevel03_arr[0];
                        $bevm = $v_bevm;
                    } else if ($v_bevelpos == "4") {
                        $bevel04 = config('iniproperties.FRAME_BEVEL_CONTROL04');
                        $bevel04_arr = explode(';', $bevel04, 2);
                        $bevp = $bevel04_arr[0];
                        $bevm = $v_bevm;
                    } else if ($v_bevelpos == "5") {
                        $bevel05 = config('iniproperties.FRAME_BEVEL_CONTROL05');
                        $bevel05_arr = explode(';', $bevel05, 3);
                        $bevel05_p = $bevel05_arr[0];
                        $bevm = $bevel05_arr[2];
                    } else if ($v_bevelpos == "6") {
                        $bevel06 = config('iniproperties.FRAME_BEVEL_CONTROL06');
                        $bevel06_arr = explode(';', $bevel06, 3);
                        $bevp = $bevel06_arr[0];
                        $bevm = $bevel06_arr[2];
                    } else if ($v_bevelpos == "7") {
                        $bevel07 = config('iniproperties.FRAME_BEVEL_CONTROL07');
                        $bevel07_arr = explode(';', $bevel07, 3);
                        $bevp = $bevel07_arr[0];
                        $bevm = $bevel07_arr[2];
                    } else if ($v_bevelpos == "8") {
                        $bevel08 = config('iniproperties.FRAME_BEVEL_CONTROL08');
                        $bevel08_arr = explode(';', $bevel08, 3);
                        $bevp = $bevel08_arr[0];
                        $bevm = $bevel08_arr[2];
                    } else if ($v_bevelpos == "9") {
                        $bevel09 = config('iniproperties.FRAME_BEVEL_CONTROL09');
                        $bevel09_arr = explode(';', $bevel09, 3);
                        $bevp = $bevel09_arr[0];
                        $bevm = $bevel09_arr[2];
                    }else if ($v_bevelpos == "10") {
                        $bevel10 = config('iniproperties.FRAME_BEVEL_CONTROL10');
                        $bevel10_arr = explode(';', $bevel10, 2);
                        $bevp = $bevel10_arr[0];
                        $bevm = $v_bevm;
                    }
                }
            }

            if (!isset($v_grvWidth) || $v_grvWidth === "") {
                $v_grvWidth = "0";
            }
            if (!isset($v_grvDepth) || $v_grvDepth === "") {
                $v_grvDepth = "0";
            }

            $return = 0;
            if ($tintremark != "") {
                $remark = $remark . " Tint: " . $tintremark;
            }

            if ($additionalRemark != "") {
                $remark = $remark . " #" . $additionalRemark . "#";
            }

            if ($ordertype == "N" && $tracedata != "") {
                $ordertype = "F";
            }

            $drillholesR = "";
            $drillholesL = "";
            $drillR = "";
            $drillL = "";
            if ($ftype == "3" && ($etype == "2" || $etype == "33")) {
                // Right
                if ($v_drillsX1R != "") {
                    $drillholesR = $drillholesR . $v_drillsX1R . "," . $v_drillsY1R . "," . $v_drilleX1R . "," . $v_drilleY1R . "," . $v_drilldi1R . "," . $v_drillde1R . ",," . $v_direction1R . ";";
                }
                if ($v_drillsX2R != "") {
                    $drillholesR = $drillholesR . $v_drillsX2R . "," . $v_drillsY2R . "," . $v_drilleX2R . "," . $v_drilleY2R . "," . $v_drilldi2R . "," . $v_drillde2R . ",," . $v_direction2R . ";";
                }
                if ($v_drillsX3R != "") {
                    $drillholesR = $drillholesR . $v_drillsX3R . "," . $v_drillsY3R . "," . $v_drilleX3R . "," . $v_drilleY3R . "," . $v_drilldi3R . "," . $v_drillde3R . ",," . $v_direction3R . ";";
                }
                if ($v_drillsX4R != "") {
                    $drillholesR = $drillholesR . $v_drillsX4R . "," . $v_drillsY4R . "," . $v_drilleX4R . "," . $v_drilleY4R . "," . $v_drilldi4R . "," . $v_drillde4R . ",," . $v_direction4R . ";";
                }
                if ($v_drillsX5R != "") {
                    $drillholesR = $drillholesR . $v_drillsX5R . "," . $v_drillsY5R . "," . $v_drilleX5R . "," . $v_drilleY5R . "," . $v_drilldi5R . "," . $v_drillde5R . ",," . $v_direction5R . ";";
                }
                if ($v_drillsX6R != "") {
                    $drillholesR = $drillholesR . $v_drillsX6R . "," . $v_drillsY6R . "," . $v_drilleX6R . "," . $v_drilleY6R . "," . $v_drilldi6R . "," . $v_drillde6R . ",," . $v_direction6R . ";";
                }

                // LEft
                if ($v_drillsX1L != "") {
                    $drillholesL = $drillholesL . $v_drillsX1L . "," . $v_drillsY1L . "," . $v_drilleX1L . "," . $v_drilleY1L . "," . $v_drilldi1L . "," . $v_drillde1L . ",," . $v_direction1L . ";";
                }
                if ($v_drillsX2L != "") {
                    $drillholesL = $drillholesL . $v_drillsX2L . "," . $v_drillsY2L . "," . $v_drilleX2L . "," . $v_drilleY2L . "," . $v_drilldi2L . "," . $v_drillde2L . ",," . $v_direction2L . ";";
                }
                if ($v_drillsX3L != "") {
                    $drillholesL = $drillholesL . $v_drillsX3L . "," . $v_drillsY3L . "," . $v_drilleX3L . "," . $v_drilleY3L . "," . $v_drilldi3L . "," . $v_drillde3L . ",," . $v_direction3L . ";";
                }
                if ($v_drillsX4L != "") {
                    $drillholesL = $drillholesL . $v_drillsX4L . "," . $v_drillsY4L . "," . $v_drilleX4L . "," . $v_drilleY4L . "," . $v_drilldi4L . "," . $v_drillde4L . ",," . $v_direction4L . ";";
                }
                if ($v_drillsX5L != "") {
                    $drillholesL = $drillholesL . $v_drillsX5L . "," . $v_drillsY5L . "," . $v_drilleX5L . "," . $v_drilleY5L . "," . $v_drilldi5L . "," . $v_drillde5L . ",," . $v_direction5L . ";";
                }
                if ($v_drillsX6L != "") {
                    $drillholesL = $drillholesL . $v_drillsX6L . "," . $v_drillsY6L . "," . $v_drilleX6L . "," . $v_drilleY6L . "," . $v_drilldi6L . "," . $v_drillde6L . ",," . $v_direction6L . ";";
                }
            }

            if (isset($_POST['UV'])) {
                $uv = $_POST['UV'];
            } else if (isset($_GET['UV'])) {
                $uv = $_GET['UV'];
            };

            //echo "<script type='text/javascript'>alert('$uv');</script>";

            if ($drillholesR != "") {
                $drillholesR = substr($drillholesR, 0, -1);

                $drillR = SoapController::transformDrillHoles($request, [
                    "drillHoles" => $drillholesR,
                    "trcstr" => $request->input('tracedata', ''),
                    "type" => $request->input('trctype', ''),
                    "side" => "R",
                ]);

                if ($v_drillThk !== '') {
                    $drhThkHex = strToUpper(bin2hex("\r\n_DRHTHK=" . $v_drillThk));
                    $drillR = $drillR . $drhThkHex;
                }
            }
            if ($drillholesL != "") {
                $drillholesL = substr($drillholesL, 0, -1);

                $drillL = SoapController::transformDrillHoles($request, [
                    "drillHoles" => $drillholesL,
                    "trcstr" => $request->input('tracedata', ''),
                    "type" => $request->input('trctype', ''),
                    "side" => "L",
                ]);

                if ($v_drillThk !== '') {
                    $drhThkHex = strToUpper(bin2hex("\r\n_DRHTHK=" . $v_drillThk));
                    $drillL = $drillL . $drhThkHex;
                }
            }
            if ($consDateOfBirth != '') {
                // Patient, translate date from field type 'date' into internal format
                $consDateOfBirth = substr($consDateOfBirth, 0, 4) . substr($consDateOfBirth, 5, 2) . substr($consDateOfBirth, 8, 2);
            }
            if ($reqDelv != '') {
                // Patient, translate date from field type 'date' into internal format
                $reqDelv = substr($reqDelv, 0, 4) . substr($reqDelv, 5, 2) . substr($reqDelv, 8, 2);
            }
        }

        try {
            DB::beginTransaction();

            if ($isEdit) {
                if (empty($order['OrdNumb'])) {
                    DB::rollback();
                    return response()->json(['message' => 'Missing order id'], 400);
                }
                $ordNumb = $this->changeOrder($individual,$order['OrdNumb'], $customer, $reference, $remark, $tint, $coating, $L, $lenstypel, $ovall, $spherel, $cylinderl, $axisl, $additionl, $prism1l, $base1l, $prism2l, $base2l, $minedgel, $devcenterl, $basecurvel, $pdl, $heightl,
                    $diam1l, $diam2l, $diam3l, $bvd, $bvdr, $bvdl, $insetl, $R, $lenstyper, $ovalr, $spherer, $cylinderr, $axisr, $additionr, $prism1r, $base1r, $prism2r, $base2r, $minedger, $devcenterr, $basecurver, $pdr,
                    $heightr, $diam1r, $diam2r, $diam3r, $insetr, $frameNo, $v_fReference, $corrlen, $ztilt, $panto, $frcv, $objectDist, $tracedata, $ordertype, $indtyper, $indtypel, $handSelect, $readdist, $readref, $fpinb, $pinb, $v_grvWidth,
                    $v_grvDepth, $v_polish, $v_fdecxl, $v_fdecyl, $v_fdecxr, $v_fdecyr, $v_dbl, $v_hboxl, $v_vboxl, $v_hboxr, $v_vboxr, $ftype, $etype, $bevp, $bevm, $headtilt, $lensdescrl, $lensdescrr, $tintdescrl, $tintdescrr,
                    $coatdescrl, $coatdescrr, $stockRXl, $stockRXr, $material, $group, $initialgrav, $lenti, $v_ctor, $v_ctol,$pref,$dercr,$dercl,$engraving,$drillR,$drillL,$uv,$uvdescrr,$vmap,$degression,$fwd,
                    $consStreet,$consCity,$consZip,$consTitle,$consFirstName,$consLastName,$consPhone,$consLang,$consDateOfBirth,$clage,$entryUser,$reqDelv,$consEMail,$consRes1,$consRes2,$consRes3,$consRes4,
                    $custNumbX,$frame_remark,$filter,$otherListl,$otherListr, $has_forcedDecentration, $forcedDecentration_decentrationxr, $forcedDecentration_decentrationyr, $forcedDecentration_decentrationxl, $forcedDecentration_decentrationyl,$addDegr, $addDegl);
            } else {
                $ordNumb = $this->createOrder($individual,$customer, $reference, $remark, $tint, $coating, $L, $lenstypel, $ovall, $spherel, $cylinderl, $axisl, $additionl, $prism1l, $base1l, $prism2l, $base2l, $minedgel, $devcenterl, $basecurvel, $pdl, $heightl,
                    $diam1l, $diam2l, $diam3l, $bvd, $bvdr, $bvdl, $insetl, $R, $lenstyper, $ovalr, $spherer, $cylinderr, $axisr, $additionr, $prism1r, $base1r, $prism2r, $base2r, $minedger, $devcenterr, $basecurver, $pdr,
                    $heightr, $diam1r, $diam2r, $diam3r, $insetr, $frameNo, $v_fReference, $corrlen, $ztilt, $panto, $frcv, $objectDist, $tracedata, $ordertype, $indtyper, $indtypel, $handSelect, $readdist, $readref, $fpinb, $pinb, $v_grvWidth,
                    $v_grvDepth, $v_polish, $v_fdecxl, $v_fdecyl, $v_fdecxr, $v_fdecyr, $v_dbl, $v_hboxl, $v_vboxl, $v_hboxr, $v_vboxr, $ftype, $etype, $bevp, $bevm, $headtilt, $lensdescrl, $lensdescrr, $tintdescrl, $tintdescrr,
                    $coatdescrl, $coatdescrr, $stockRXl, $stockRXr, $material, $group, $initialgrav, $lenti, $v_ctor, $v_ctol,$pref,$dercr,$dercl,$engraving,$drillR,$drillL,$uv,$uvdescrr,$vmap,$degression,$fwd,
                    $consStreet,$consCity,$consZip,$consTitle,$consFirstName,$consLastName,$consPhone,$consLang,$consDateOfBirth,$clage,$entryUser,$reqDelv,$consEMail,$consRes1,$consRes2,$consRes3,$consRes4,
                    $custNumbX,$frame_remark,$filter,$otherListl,$otherListr,$justReturnData, $has_forcedDecentration, $forcedDecentration_decentrationxr, $forcedDecentration_decentrationyr, $forcedDecentration_decentrationxl, $forcedDecentration_decentrationyl,$addDegr, $addDegl);
            }

            if ($action == 'draft') {
                $this->changeOrderStatus($ordNumb, 'DRFT', '');

                DB::commit();
                return ['orderNum' => $ordNumb];
            } else {
                $orderCheckResponse = $this->checkOrder($request, $ordNumb, SOAPHelper::generateSOAPRequest(($justReturnData ? $randomInt : $ordNumb), ($justReturnData ? 2 : 0), $request->input('uid', ''), ($justReturnData ? $ordNumb : "")));
                if ($orderCheckResponse !== "1"){
                    DB::rollback();
                    if($justReturnData)
                        return ['message' => $orderCheckResponse, 'error' => 400];
                    return response()->json(['message' => $orderCheckResponse], 400);
                }

                DB::commit();
                return [
                    'orderNum' => $ordNumb,
                    'orderCheck' => $orderCheckResponse
                ];
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    private function getEmptyModel($tableName) {
        $columnNames = \Schema::getColumnListing($tableName);
        $result = [];
        foreach ($columnNames as $c) {
            $result[$c] = '';
        }
        return $result;
    }

    /**
     * Create order in database
     */
    public function createOrder($individual,$customer,$reference,$remark,$tint,$coating,$L,$lenstypel,$ovall,$spherel,$cylinderl,$axisl,$additionl,
                                $prism1l,$base1l,$prism2l,$base2l,$minedgel,$devcenterl,$basecurvel,$pdl,$heightl,$diam1l,$diam2l,$diam3l,$bvd,$bvdr,$bvdl,
                                $ernrinl,$R,$lenstyper,$ovalr,$spherer,$cylinderr,$axisr,$additionr,$prism1r,$base1r,$prism2r,$base2r,
                                $minedger,$devcenterr,$basecurver,$pdr,$heightr,$diam1r,$diam2r,$diam3r,$ernrinr,$frameNo,$fReference,
                                $corrlen,$ztilt,$panto, $frcv,$objectDist,$tracedata,$ordertype,$indtypeR,$indtypeL,$handSelect,$readdist,$readref,$fpinb,$pinb,
                                $grvWidth,$grvDepth,$polish,$fdecxl,$fdecyl,$fdecxr,$fdecyr,$dbl,$hboxl,$vboxl,$hboxr,$vboxr,$ftype,$etype,$bevp,$bevm,
                                $headtilt,$lensdescrl,$lensdescrr,$tintdescrl,$tintdescrr,$coatdescrl,$coatdescrr,$stockRXl,$stockRXr,$material,$group,
                                $initialgrav,$lenti,$ctor,$ctol,$pref,$dercr,$dercl,$engraving,$drillR,$drillL,$uv,$uvdescrr,$vmap,$degression,$fwd,
                                $consStreet,$consCity,$consZip,$consTitle,$consFirstName,$consLastName,$consPhone,$consLang,$consDateOfBirth,$clage,$entryUser,
                                $reqDelv,$consEMail,$consRes1,$consRes2,$consRes3,$consRes4,$custNumbX,$frame_remark,$filter,$otherListl,$otherListr,$justReturnData,
                                $has_forcedDecentration, $forcedDecentration_decentrationxr, $forcedDecentration_decentrationyr, $forcedDecentration_decentrationxl, $forcedDecentration_decentrationyl,$addDegr, $addDegl) {


        $emptyOrderComponent = self::getEmptyModel('order_component');
        $emptyOrderHeader = self::getEmptyModel('order_header');
        $emptyOrderConsumer = self::getEmptyModel('order_consumer');
        $emptyOrderLensproperties = self::getEmptyModel('order_lensproperties');
        $emptyOrderShape = self::getEmptyModel('order_header');
        $emptyOrderShapeExtension = self::getEmptyModel('order_header');
        $returnOrderData = [
            'order' => $emptyOrderHeader,
            'consumer' => $emptyOrderConsumer,
            'lenscodeR' => $emptyOrderComponent,
            'propR' => $emptyOrderLensproperties,
            'tintR' => $emptyOrderComponent,
            'coatingR' => $emptyOrderComponent,
            'uvR' => $emptyOrderComponent,
            'shapeR' => $emptyOrderShape,
            'lenscodeL' => $emptyOrderComponent,
            'propL' => $emptyOrderLensproperties,
            'tintL' => $emptyOrderComponent,
            'coatingL' => $emptyOrderComponent,
            'uvL' => $emptyOrderComponent,
            'shapeL' => $emptyOrderShape,
            'otherL' => [$emptyOrderComponent],
            'otherR' => [$emptyOrderComponent],
            'shapeExtensionL' => $emptyOrderShapeExtension,
            'shapeExtensionR' => $emptyOrderShapeExtension,
        ];

        $date = date("Y-m-d", time());
        $time = date("H:i:s", time());

        if (!isset($reqDelv) || $reqDelv === "") {
            $reqDelv = null;
        } else {
            $reqDelv = "'".$reqDelv."'";
        }

        if ($L == 1 && $R == 1) {
            $side = "B";
        }
        if ($L == 1 && $R == 0) {
            $side = "L";
        }
        if ($L == 0 && $R == 1) {
            $side = "R";
        }

        if ($filter == "" && strpos($group, ";") !== false) {
            $filter = substr($group, strpos($group, ";") + 1, 1);
        }

        $group = substr($group, 0, 1);
        if ($group == ";") {
            $group = "";
        }

        $special = "";
        /*if ($engraving != "") {
            $initialgrav = $engraving;
            $indtypeR = "FFU";
            $indtypeL = "FFU";
        }*/

        $individualstringR = "";
        $individualstringL = "";
        $individualAll = [];
        if($individual['common']) {
            foreach ($individual as $key => $value) {
                foreach ($individual[$key] as $key1 => $value1) {
                    $individualAll[] = [
                        'related' => $key,
                        'position' => $individual[$key][$key1]['position'],
                        'value' => $individual[$key][$key1]['value'],
                        'name' => $individual[$key][$key1]['name']
                    ];
                }
            }
            $position = array_column($individualAll, 'position');
            array_multisort($position, SORT_ASC, $individualAll);

            for($i = 0; $i <= max(array_column($individualAll, 'position')); $i++) {
                $array = array_filter($individualAll, function($data) use($i){
                   return $data['position'] == $i;
                });
                foreach ($array as $key1=>$val1) {
                    if($array[$key1]['related'] == 'common' || $array[$key1]['related'] == 'left') {
                        $individualstringL .= $array[$key1]['value'];
                    }
                    if($array[$key1]['related'] == 'common' || $array[$key1]['related'] == 'right') {
                        $individualstringR .= $array[$key1]['value'];
                    }
                }
                $individualstringL .= ';';
                $individualstringR .= ';';
            }
        }
       /*if ($indtypeR == "") {
            $individualstringR = "";
        } else if ($indtypeR == "BB2" || $indtypeR == "BB3" || $indtypeR == "BB4") {
            //$individualstringR = $bvd . ";" . $panto . ";" . $ztilt . ";" . $readdist . ";" . $readref . ";" . $ernrinr . ";" . $handSelect;
        } else if (strpos($indtypeR, "RZV") !== false) {
            //$individualstringR = $bvd . ";" . $panto . ";" . $ztilt . ";" . $readdist . ";" . $headtilt . ";" . $corrlen . ";" . $ernrinr . ";" . $pref . ";" . $handSelect . ";" . $dercr;
        } else if (strpos($indtypeR, "FFU") !== false) {
            //$individualstringR = $initialgrav . ";" . $bvdr . ";" . $panto . ";" . $objectDist . ";" . $ztilt . ";" . $corrlen . ";" . $ernrinr . ";" . $degression .  ";" . $lenti . ";;;;;;;;;;;;;;;;;;;;;;".$fwd.";".$vmap.";;".$clage;
        } else if (strpos($indtypeR, "RH2") !== false) {
            //$individualstringR = ";" . $bvd . ";". $pref;
        } else if (strpos($indtypeR, "RH3") !== false) {
            //$individualstringR = $readdist . ";" . $bvd . ";" . $headtilt . ";" . $ztilt . ";";
        } else if (strpos($indtypeR, "NIK") !== false) {
            //$individualstringR = $readdist . ";" . $bvd . ";" . $panto . ";" . $ztilt . ";" . $ernrinr;
        } else if (strpos($indtypeR, "NI2") !== false) {
            //$individualstringR = $ztilt;
        } else if (strpos($indtypeR, "SPT") !== false) {
            //$individualstringR = $ztilt;
        } else if (strpos($indtypeR, "RUH") !== false) {
            //$individualstringR = $bvd . ";" . $headtilt;
        } else {
            $individualstringR = "";
        }

        if ($indtypeL == "") {
            $individualstringL = "";
        } else if ($indtypeL == "BB2" || $indtypeL == "BB3" || $indtypeL == "BB4") {
            //$individualstringL = $bvd . ";" . $panto . ";" . $ztilt . ";" . $readdist . ";" . $readref . ";" . $ernrinl . ";" . $handSelect;
        } else if (strpos($indtypeL, "RZV") !== false) {
            //$individualstringL = $bvd . ";" . $panto . ";" . $ztilt . ";" . $readdist . ";" . $headtilt . ";" . $corrlen . ";" . $ernrinl . ";" . $pref . ";" . $handSelect . ";" . $dercl;
        } else if (strpos($indtypeL, "FFU") !== false) {
            //$individualstringL = $initialgrav . ";" . $bvdl . ";" . $panto . ";" . $objectDist . ";" . $ztilt . ";" . $corrlen . ";" . $ernrinl . ";" . $degression . ";" . $lenti . ";;;;;;;;;;;;;;;;;;;;;;".$fwd.";".$vmap.";;".$clage;
        } else if (strpos($indtypeL, "RH2") !== false) {
            //$individualstringL = ";" . $bvd . ";". $pref;
        } else if (strpos($indtypeL, "RH3") !== false) {
            //$individualstringL = $readdist . ";" . $bvd . ";" . $headtilt . ";" . $ztilt . ";";
        } else if (strpos($indtypeL, "NIK") !== false) {
            //$individualstringL = $readdist . ";" . $bvd . ";" . $panto . ";" . $ztilt . ";" . $ernrinl;
        } else if (strpos($indtypeL, "NI2") !== false) {
            //$individualstringL = $ztilt;
        } else if (strpos($indtypeL, "SPT") !== false) {
            //$individualstringL = $ztilt;
        } else if (strpos($indtypeL, "RUH") !== false) {
            //$individualstringL = $bvd . ";" . $headtilt;
        } else {
            $individualstringL = "";
        }*/

        if (config('iniproperties.LENS_ORDER_CONFIGURATION_ENTRYUSER', 'NO') == 'YES' && $entryUser != '') {
            $user = $entryUser;
        } else {
            $user = ''; //"WEB"
        }

        if ($frame_remark != "") {
            $remark .= "ED_REMARK#SEP#".$frame_remark."#CRLF#";
        }

        $consRef = str_pad($consTitle, 2, " ") . str_pad($consLang, 2, " ") . str_pad($consDateOfBirth, 8, " ") . str_pad($consPhone, 18, " ");


        $orderHeaderParams = [
            'CustNumb' => $customer,
            'RecStat' => 'REQU',
            'Rectype' => $ordertype,
            'Ordtype' => 'R',
            'OrdCat' => '',
            'Side' => $side,
            'Reference' => $reference,
            'EntryType' => '',
            'EntryUser' => $user,
            'EntryDate' => $date,
            'EntryTime' => $time,
            'Status' => 'PRPT',
            'Remark' => $remark,
            'FrameNo' => $frameNo,
            'FReference' => $fReference,
            'Special' => $special,
            'ConsRef' => $consRef,
            'ConsName' => $consLastName,
            'ConsStreet' => $consStreet,
            'ConsZipC' => $consZip,
            'ConsCity' => $consCity,
            'ConsArea' => $consFirstName,
            'ReqDelv' => $reqDelv,
            'CustNumbX' => $custNumbX
        ];
        $returnOrderData['order'] = $orderHeaderParams;

        if (!$justReturnData) {
            $orderHeader = OrderHeader::create($orderHeaderParams);
            if (!$orderHeader || !$orderHeader->id) return -1;
            $id = $orderHeader->id;
            OrderExtension::create(['OrdNumb' => $id, 'RecType' => 'ADDDEG_R', 'Flag1' => $addDegr]);
            OrderExtension::create(['OrdNumb' => $id, 'RecType' => 'ADDDEG_L', 'Flag1' => $addDegl]);
        }


        //Neues Handling für ORDER_CONSUMER  2020-12-08
        if (config('iniproperties.TABLE_ORDER_CONSUMER_TABLE', 'NO') == "YES") {
            $orderConsumerParams = [
                'Title' => $consTitle,
                'Name' => $consLastName,
                'FirstName' => $consFirstName,
                'Street' => $consStreet,
                'ZipCode' => $consZip,
                'City' => $consCity,
                'Area' => '',
                'Country' => '',
                'Language' => $consLang,
                'Phone' => $consPhone,
                'Mobile' => '',
                'EMail' => $consEMail,
                'Fax' => '',
                'DateOfBirth' => $consDateOfBirth,
                'Res1' => $consRes1,
                'Res2' => $consRes2,
                'Res3' => $consRes3,
                'Res4' => $consRes4,
                'Res5' => '',
                'Res6' => '',
                'Res7' => '',
                'Res8' => '',
                'Res9' => '',
                'Res10' => ''
            ];
            $returnOrderData['consumer'] = $orderConsumerParams;
            if (!$justReturnData){
                $orderConsumer = OrderConsumer::firstOrCreate(['OrdNumb' => $id], $orderConsumerParams);
                if (!$orderConsumer) return -1;
            }
        }

        if ($L == 1) {
            if ($lenstypel == null) $lenstypel = '';

            //Lenstype
            $orderComponentParams = [
                'Side' => 'L',
                'Position' => '001',
                'Comptype' => '01',
                'Compcode' => $lenstypel,
                'Diam1' => $diam1l,
                'Diam2' => $diam2l,
                'Diam3' => $diam3l,
                'CompDescr' => $lensdescrl,
            ];
            $returnOrderData['lenscodeL'] = $orderComponentParams;
            if (!$justReturnData){
                $orderComponent = OrderComponent::create(array_merge(['OrdNumb' => $id], $orderComponentParams));
                if (!$orderComponent) return -1;
            }

            //Lensproperties
            if ($ovall == "on") {
                $cribtypel = "O";
            } else {
                $cribtypel = "R";
            }

            $orderLensPropertiesParams = [
                'Side' => 'L',
                'StockRX' => $stockRXl,
                'Mat_Ext' => $material,
                'Group_Ext' => $group,
                'Material' => '',
                'LensGroup' => $filter,
                'Cribtype' => $cribtypel,
                'Sphere' => $spherel,
                'Cylinder' => $cylinderl,
                'Axis' => $axisl,
                'Addition' => $additionl,
                'Prism1' => $prism1l,
                'PrBase1' => $base1l,
                'Prism2' => $prism2l,
                'PrBase2' => $base2l,
                'Edgethick' => $minedgel,
                'Centthick' => $devcenterl,
                'BaseCurve' => $basecurvel,
                'PD' => $pdl,
                'Height' => $heightl,
                'Diam1' => $diam1l,
                'Diam2' => $diam2l,
                'Diam3' => $diam3l,
                'IndType' => $indtypeL,
                'Individual' => $individualstringL,
                'CTO' => $ctol,
                'DecentX' => $forcedDecentration_decentrationxl,
                'DecentY' => $forcedDecentration_decentrationyl,
            ];

            $returnOrderData['propL'] = $orderLensPropertiesParams;
            if (!$justReturnData){
                $orderLensProperties = OrderLensProperties::create(array_merge(['OrdNumb' => $id], $orderLensPropertiesParams));
                if (!$orderLensProperties) return -1;
            }

            if (strlen($tint) > 0) {//Tint
                $orderComponentParams = [
                    'Side' => 'L',
                    'Position' => '002',
                    'Comptype' => '02',
                    'Compcode' => $tint,
                    'Cribtype' => $cribtypel,
                    'Diam1' => $diam1l,
                    'Diam2' => $diam2l,
                    'Diam3' => $diam3l,
                    'CompDescr' => $tintdescrl
                ];
                $returnOrderData['tintL'] = $orderComponentParams;
                if (!$justReturnData){
                    $orderComponent = OrderComponent::create(array_merge(['OrdNumb' => $id], $orderComponentParams));
                    if (!$orderComponent) return -1;
                }
            }
            if (strlen($coating) > 0) {//Coating
                if ($tint != "") {
                    $posl = "003";
                } else {
                    $posl = "003"; //002
                }
                $orderComponentParams = [
                    'Side' => 'L',
                    'Position' => $posl,
                    'Comptype' => '03',
                    'Compcode' => $coating,
                    'Cribtype' => $cribtypel,
                    'Diam1' => $diam1l,
                    'Diam2' => $diam2l,
                    'Diam3' => $diam3l,
                    'CompDescr' => $coatdescrl
                ];
                $returnOrderData['coatingL'] = $orderComponentParams;
                if (!$justReturnData){
                    $orderComponent = OrderComponent::create(array_merge(['OrdNumb' => $id], $orderComponentParams));
                    if (!$orderComponent) return -1;
                }
            }
            if (strlen($uv) > 0) {//UV
                $orderComponentParams = [
                    'Side' => 'L',
                    'Position' => '004',
                    'Comptype' => '05',
                    'Compcode' => $uv,
                    'Cribtype' => $cribtypel,
                    'Diam1' => $diam1l,
                    'Diam2' => $diam2l,
                    'Diam3' => $diam3l,
                    'CompDescr' => $uvdescrr
                ];
                $returnOrderData['uvL'] = $orderComponentParams;
                if (!$justReturnData){
                    $orderComponent = OrderComponent::create(array_merge(['OrdNumb' => $id], $orderComponentParams));
                    if (!$orderComponent) return -1;
                }
            }

            if (isset($otherListl) && is_array($otherListl) && count($otherListl) > 0) {//OTHER
                foreach ($otherListl as $index => $other) {
                    $orderComponentParams = [
                        'Side' => 'L',
                        'Position' => $index,
                        'Comptype' => '??',
                        'Compcode' => $other['code'],
                        'Cribtype' => $cribtypel,
                        'Diam1' => $diam1l,
                        'Diam2' => $diam2l,
                        'Diam3' => $diam3l,
                        'CompDescr' => $other['description']
                    ];
                    $returnOrderData['otherL' . $index] = $orderComponentParams;
                    if (!$justReturnData){
                        $orderComponent = OrderComponent::create(array_merge(['OrdNumb' => $id], $orderComponentParams));
                        if (!$orderComponent) return -1;
                    }
                }
            }
            $panto = null;
            if (strlen($tracedata) > 0) {//Trace
                $orderShapeParams = [
                    'Side' => 'L',
                    'Trctype' => session('Trctype'),
                    'PD' => $pdl,
                    'Height' => $heightl,
                    'FDecX' => $fdecxl,
                    'FDecY' => $fdecyl,
                    'DBL' => $dbl,
                    'HBox' => $hboxl,
                    'VBox' => $vboxl,
                    'Etyp' => $etype,
                    'FTyp' => $ftype,
                    'BevP' => $bevp,
                    'BevM' => $bevm,
                    'Form' => $tracedata,
                    'GDepth' => $grvDepth,
                    'GWidth' => $grvWidth,
                    'FPinB' => $fpinb,
                    'PinB' => $pinb,
                    'Polish' => $polish,
                    'Drill' => hex2bin($drillL)
                ];
                $addFrcv = null;
                $addPanto = null;
                $addFaceForm = null;
                if($frcv)
                    $addFrcv = [
                        'Frcv' => $frcv,
                    ];
                if($panto)
                    $addPanto = [
                        'Panto' => $panto,

                    ];
                if($ztilt)
                    $addFaceForm = [
                        'FaceFormA' => $ztilt,
                    ];
                $returnOrderData['shapeL'] = $orderShapeParams;
                if (!$justReturnData){
                    $array = array_merge(['OrdNumb' => $id], $orderShapeParams);
                    if($addFrcv) {
                        $array = array_merge($array, $addFrcv);
                    }
                    if($addPanto) {
                        $array = array_merge($array, $addPanto);
                    }
                    if($addFaceForm) {
                        $array = array_merge($array, $addFaceForm);
                    }


                $returnOrderData['shapeL'] = $array;
                //if (!$justReturnData){
                    //$orderShape = OrderShape::create(array_merge(['OrdNumb' => $id], $orderShapeParams));
                    $orderShape = OrderShape::create($array);
                    if (!$orderShape) return -1;
                }
            } else if ($frameNo != "") { //only frameNo
                $orderShapeParams = [
                    'Side' => 'L',
                    'DBL' => $dbl,
                    'Trctype' => ''
                ];
                $returnOrderData['shapeL'] = $orderShapeParams;
                if (!$justReturnData){
                    $orderShape = OrderShape::create(array_merge(['OrdNumb' => $id], $orderShapeParams));
                    if (!$orderShape) return -1;
                }
            }

            if(strlen(session('interpolateForm')) > 0){//TracePreview
                $orderShapeExtensionParams = [
                    'Side' => 'L',
                    'FormPreview' => session('interpolateForm')
                ];
                $returnOrderData['shapeExtensionL'] = $orderShapeExtensionParams;
                if (!$justReturnData){
                    $orderShapeExtension = OrderShapeExtension::create(array_merge(['OrdNumb' => $id], $orderShapeExtensionParams));
                    if (!$orderShapeExtension) return -1;
                }
            }
        }
        if ($R == 1) {
            if ($lenstyper == null) $lenstyper = '';

            //Lenstype
            $orderComponentParams = [
                'Side' => 'R',
                'Position' => '001',
                'Comptype' => '01',
                'Compcode' => $lenstyper,
                'Diam1' => $diam1r,
                'Diam2' => $diam2r,
                'Diam3' => $diam3r,
                'CompDescr' => $lensdescrr
            ];
            $returnOrderData['lenscodeR'] = $orderComponentParams;
            if (!$justReturnData){
                $orderComponent = OrderComponent::create(array_merge(['OrdNumb' => $id], $orderComponentParams));
                if (!$orderComponent) return -1;
            }

            //Lensproperties
            if ($ovalr == "on") {
                $cribtyper = "O";
            } else {
                $cribtyper = "R";
            }

            $orderLensPropertiesParams = [
                'Side' => 'R',
                'StockRX' => $stockRXr,
                'Mat_Ext' => $material,
                'Group_Ext' => $group,
                'Material' => '',
                'LensGroup' => $filter,
                'Cribtype' => $cribtyper,
                'Sphere' => $spherer,
                'Cylinder' => $cylinderr,
                'Axis' => $axisr,
                'Addition' => $additionr,
                'Prism1' => $prism1r,
                'PrBase1' => $base1r,
                'Prism2' => $prism2r,
                'PrBase2' => $base2r,
                'Edgethick' => $minedger,
                'Centthick' => $devcenterr,
                'BaseCurve' => $basecurver,
                'PD' => $pdr,
                'Height' => $heightr,
                'Diam1' => $diam1r,
                'Diam2' => $diam2r,
                'Diam3' => $diam3r,
                'IndType' => $indtypeR,
                'Individual' => $individualstringR,
                'CTO' => $ctor,
                'DecentX' => $forcedDecentration_decentrationxr,
                'DecentY' => $forcedDecentration_decentrationyr,
            ];

            $returnOrderData['propR'] = $orderLensPropertiesParams;
            if (!$justReturnData){
                $orderLensProperties = OrderLensProperties::create(array_merge(['OrdNumb' => $id], $orderLensPropertiesParams));
                if (!$orderLensProperties) return -1;
            }

            if (strlen($tint) > 0) {//Tint
                $orderComponentParams = [
                    'Side' => 'R',
                    'Position' => '002',
                    'Comptype' => '02',
                    'Compcode' => $tint,
                    'Cribtype' => $cribtyper,
                    'Diam1' => $diam1r,
                    'Diam2' => $diam2r,
                    'Diam3' => $diam3r,
                    'CompDescr' => $tintdescrr
                ];
                $returnOrderData['tintR'] = $orderComponentParams;
                if (!$justReturnData){
                    $orderComponent = OrderComponent::create(array_merge(['OrdNumb' => $id], $orderComponentParams));
                    if (!$orderComponent) return -1;
                }
            }
            if (strlen($coating) > 0) {//Coating
                if ($tint != "") {
                    $posr = "003";
                } else {
                    $posr = "003";
                }

                $orderComponentParams = [
                    'Side' => 'R',
                    'Position' => $posr,
                    'Comptype' => '03',
                    'Compcode' => $coating,
                    'Cribtype' => $cribtyper,
                    'Diam1' => $diam1r,
                    'Diam2' => $diam2r,
                    'Diam3' => $diam3r,
                    'CompDescr' => $coatdescrr
                ];
                $returnOrderData['coatingR'] = $orderComponentParams;
                if (!$justReturnData){
                    $orderComponent = OrderComponent::create(array_merge(['OrdNumb' => $id], $orderComponentParams));
                    if (!$orderComponent) return -1;
                }
            }
            if (strlen($uv) > 0) {//UV
                $orderComponentParams = [
                    'Side' => 'R',
                    'Position' => '004',
                    'Comptype' => '05',
                    'Compcode' => $uv,
                    'Cribtype' => $cribtyper,
                    'Diam1' => $diam1r,
                    'Diam2' => $diam2r,
                    'Diam3' => $diam3r,
                    'CompDescr' => $uvdescrr
                ];
                $returnOrderData['uvR'] = $orderComponentParams;
                if (!$justReturnData){
                    $orderComponent = OrderComponent::create(array_merge(['OrdNumb' => $id], $orderComponentParams));
                    if (!$orderComponent) return -1;
                }
            }

            if (isset($otherListr) && is_array($otherListr) && count($otherListr) > 0) {//OTHER
                foreach ($otherListr as $index => $other) {
                    $orderComponentParams = [
                        'Side' => 'R',
                        'Position' => $index,
                        'Comptype' => '??',
                        'Compcode' => $other['code'],
                        'Cribtype' => $cribtyper,
                        'Diam1' => $diam1r,
                        'Diam2' => $diam2r,
                        'Diam3' => $diam3r,
                        'CompDescr' => $other['description']
                    ];
                    $returnOrderData['otherR' . $index] = $orderComponentParams;
                    if (!$justReturnData){
                        $orderComponent = OrderComponent::create(array_merge(['OrdNumb' => $id], $orderComponentParams));
                        if (!$orderComponent) return -1;
                    }
                }
            }

            if (strlen($tracedata) > 0) {//Trace
                $orderShapeParams = [
                    'Side' => 'R',
                    'Trctype' => session('Trctype'),
                    'PD' => $pdr,
                    'Height' => $heightr,
                    'FDecX' => $fdecxr,
                    'FDecY' => $fdecyr,
                    'DBL' => $dbl,
                    'HBox' => $hboxr,
                    'VBox' => $vboxr,
                    'Etyp' => $etype,
                    'FTyp' => $ftype,
                    'BevP' => $bevp,
                    'BevM' => $bevm,
                    'Form' => $tracedata,
                    'GDepth' => $grvDepth,
                    'GWidth' => $grvWidth,
                    'FPinB' => $fpinb,
                    'PinB' => $pinb,
                    'Polish' => $polish,
                    'Drill' => hex2bin($drillR)
                ];
                $addFrcv = null;
                $addPanto = null;
                $addFaceForm = null;
                if($frcv)
                    $addFrcv = [
                        'Frcv' => $frcv,
                    ];
                if($panto)
                    $addPanto = [
                        'Panto' => $panto,

                    ];
                if($ztilt)
                    $addFaceForm = [
                        'FaceFormA' => $ztilt,
                    ];
                $returnOrderData['shapeR'] = $orderShapeParams;
                if (!$justReturnData){
                    $array = array_merge(['OrdNumb' => $id], $orderShapeParams);
                    if($addFrcv) {
                        $array = array_merge($array, $addFrcv);
                    }
                    if($addPanto) {
                        $array = array_merge($array, $addPanto);
                    }
                    if($addFaceForm) {
                        $array = array_merge($array, $addFaceForm);
                    }


                    $returnOrderData['shapeR'] = $array;
                    //if (!$justReturnData){
                    //$orderShape = OrderShape::create(array_merge(['OrdNumb' => $id], $orderShapeParams));
                    $orderShape = OrderShape::create($array);
                    if (!$orderShape) return -1;
                }
            } else if ($frameNo != "") { //only frameNo
                $orderShapeParams = [
                    'Side' => 'R',
                    'DBL' => $dbl,
                    'Trctype' => ''
                ];
                $returnOrderData['shapeR'] = $orderShapeParams;
                if (!$justReturnData){
                    $orderShape = OrderShape::create(array_merge(['OrdNumb' => $id], $orderShapeParams));
                    if (!$orderShape) return -1;
                }
            }

            if(strlen(session('interpolateForm')) > 0){//TracePreview
                $orderShapeExtensionParams = [
                    'Side' => 'R',
                    'FormPreview' => session('interpolateForm')
                ];
                $returnOrderData['shapeExtensionR'] = $orderShapeExtensionParams;
                if (!$justReturnData){
                    $orderShapeExtension = OrderShapeExtension::create(array_merge(['OrdNumb' => $id], $orderShapeExtensionParams));
                    if (!$orderShapeExtension) return -1;
                }
            }
        }
        if ($justReturnData) {
            return $returnOrderData;
        }
        return $id;
    }

    /**
     * Change order in database
     */
    public function changeOrder ($individual,$orderno,$customer,$reference,$remark,$tint,$coating,$L,$lenstypel,$ovall,$spherel,$cylinderl,$axisl,$additionl,
                          $prism1l,$base1l,$prism2l,$base2l,$minedgel,$devcenterl,$basecurvel,$pdl,$heightl,$diam1l,$diam2l,$diam3l,$bvd,$bvdr,$bvdl,
                          $ernrinl,$R,$lenstyper,$ovalr,$spherer,$cylinderr,$axisr,$additionr,$prism1r,$base1r,$prism2r,$base2r,
                          $minedger,$devcenterr,$basecurver,$pdr,$heightr,$diam1r,$diam2r,$diam3r,$ernrinr,$frameNo,$fReference,
                          $corrlen,$ztilt,$panto,$frcv,$objectDist,$tracedata,$ordertype,$indtypeR,$indtypeL,$handSelect,$readdist,$readref,$fpinb,$pinb,
                          $grvWidth,$grvDepth,$polish,$fdecxl,$fdecyl,$fdecxr,$fdecyr,$dbl,$hboxl,$vboxl,$hboxr,$vboxr,$ftype,$etype,$bevp,$bevm,
                          $headtilt,$lensdescrl,$lensdescrr,$tintdescrl,$tintdescrr,$coatdescrl,$coatdescrr,$stockRXl,$stockRXr,$material,$group,
                          $initialgrav,$lenti,$ctor,$ctol,$pref,$dercr,$dercl,$engraving,$drillR,$drillL,$uv,$uvdescrr,$vmap,$degression,$fwd,
                          $consStreet,$consCity,$consZip,$consTitle,$consFirstName,$consLastName,$consPhone,$consLang,$consDateOfBirth,$clage,$entryUser,
                          $reqDelv,$consEMail,$consRes1,$consRes2,$consRes3,$consRes4,$custNumbX,$frame_remark,$filter,$otherListl,$otherListr,
                          $has_forcedDecentration, $forcedDecentration_decentrationxr, $forcedDecentration_decentrationyr, $forcedDecentration_decentrationxl, $forcedDecentration_decentrationyl, $addDegr, $addDegl) {

        $date = date ("Y-m-d", time());
        $time = date ("H:i:s", time());

        OrderExtension::updateOrCreate(['OrdNumb' => $orderno, 'RecType' => 'ADDDEG_R'],['Flag1' => $addDegr]);
        OrderExtension::updateOrCreate(['OrdNumb' => $orderno, 'RecType' => 'ADDDEG_L'],['Flag1' => $addDegl]);

        if (!isset($reqDelv) || $reqDelv === "") {
            $reqDelv = null;
        } else {
            $reqDelv = "'".$reqDelv."'";
        }

        if ($L == 1 && $R == 1) {
            $side = "B";
        }
        if ($L == 1 && $R == 0) {
            $side = "L";
        }
        if ($L == 0 && $R == 1) {
            $side = "R";
        }

        if ($filter == "" && strpos($group, ";") !== false) {
            $filter = substr($group, strpos($group, ";") + 1, 1);
        }

        $group = substr($group, 0, 1);
        if ($group == ";") {
            $group = "";
        }

        $special = "";
        /*if ($engraving != "") {
            $initialgrav = $engraving;
            $indtypeR = "FFU";
            $indtypeL = "FFU";
        }*/
        $individualstringR = "";
        $individualstringL = "";
        $individualAll = [];
        if($individual['common']) {
            foreach ($individual as $key => $value) {
                foreach ($individual[$key] as $key1 => $value1) {
                    $individualAll[] = [
                        'related' => $key,
                        'position' => $individual[$key][$key1]['position'],
                        'value' => $individual[$key][$key1]['value'],
                        'name' => $individual[$key][$key1]['name']
                    ];
                }
            }
            $position = array_column($individualAll, 'position');
            array_multisort($position, SORT_ASC, $individualAll);

            for($i = 0; $i <= max(array_column($individualAll, 'position')); $i++) {
                $array = array_filter($individualAll, function($data) use($i){
                    return $data['position'] == $i;
                });
                foreach ($array as $key1=>$val1) {
                    if($array[$key1]['related'] == 'common' || $array[$key1]['related'] == 'left') {
                        $individualstringL .= $array[$key1]['value'];
                    }
                    if($array[$key1]['related'] == 'common' || $array[$key1]['related'] == 'right') {
                        $individualstringR .= $array[$key1]['value'];
                    }
                }
                $individualstringL .= ';';
                $individualstringR .= ';';
            }
        }

        /*if ($indtypeR == "") {
            $individualstringR = "";
        } else if ($indtypeR == "BB2" || $indtypeR == "BB3" || $indtypeR == "BB4") {
            //$individualstringR = $bvd . ";" . $panto . ";" . $ztilt . ";" . $readdist . ";" . $readref . ";" . $ernrinr . ";" . $handSelect;
        } else if (strpos($indtypeR, "RZV") !== false) {
            //$individualstringR = $bvd . ";" . $panto . ";" . $ztilt . ";" . $readdist . ";" . $headtilt . ";" . $corrlen . ";" . $ernrinr . ";" . $pref . ";" . $handSelect . ";" . $dercr;
        } else if (strpos($indtypeR, "FFU") !== false) {
            //$individualstringR = $initialgrav . ";" . $bvdr . ";" . $panto . ";" . $objectDist . ";" . $ztilt . ";" . $corrlen . ";" . $ernrinr . ";" . $degression . ";" . $lenti . ";;;;;;;;;;;;;;;;;;;;;;".$fwd.";".$vmap.";;".$clage;
        } else if (strpos($indtypeR, "RH2") !== false) {
            //$individualstringR = ";" . $bvd . ";". $pref;
        } else if (strpos($indtypeR, "RH3") !== false) {
            //$individualstringR = $readdist . ";" . $bvd . ";" . $headtilt . ";" . $ztilt . ";";
        } else if (strpos($indtypeR, "NIK") !== false) {
            //$individualstringR = $readdist . ";" . $bvd . ";" . $panto . ";" . $ztilt . ";" . $ernrinr;
        } else if (strpos($indtypeR, "NI2") !== false) {
            //$individualstringR = $ztilt;
        } else if (strpos($indtypeR, "SPT") !== false) {
            //$individualstringR = $ztilt;
        } else if (strpos($indtypeR, "RUH") !== false) {
            //$individualstringR = $bvd . ";" . $headtilt;
        } else {
            $individualstringR = "";
        }

        if ($indtypeL == "") {
            $individualstringL = "";
        } else if ($indtypeL == "BB2" || $indtypeL == "BB3" || $indtypeL == "BB4") {
            //$individualstringL = $bvd . ";" . $panto . ";" . $ztilt . ";" . $readdist . ";" . $readref . ";" . $ernrinl . ";" . $handSelect;
        } else if (strpos($indtypeL, "RZV") !== false) {
            //$individualstringL = $bvd . ";" . $panto . ";" . $ztilt . ";" . $readdist . ";" . $headtilt . ";" . $corrlen . ";" . $ernrinl . ";" . $pref . ";" . $handSelect . ";" . $dercl;
        } else if (strpos($indtypeL, "FFU") !== false) {
            //$individualstringL = $initialgrav . ";" . $bvdl . ";" . $panto . ";" . $objectDist . ";" . $ztilt . ";" . $corrlen . ";" . $ernrinl . ";" . $degression . ";" . $lenti . ";;;;;;;;;;;;;;;;;;;;;;".$fwd.";".$vmap.";;".$clage;
        } else if (strpos($indtypeL, "RH2") !== false) {
            //$individualstringL = ";" . $bvd . ";". $pref;
        } else if (strpos($indtypeL, "RH3") !== false) {
            //$individualstringL = $readdist . ";" . $bvd . ";" . $headtilt . ";" . $ztilt . ";";
        } else if (strpos($indtypeL, "NIK") !== false) {
            //$individualstringL = $readdist . ";" . $bvd . ";" . $panto . ";" . $ztilt . ";" . $ernrinl;
        } else if (strpos($indtypeL, "NI2") !== false) {
            //$individualstringL = $ztilt;
        } else if (strpos($indtypeL, "SPT") !== false) {
            //$individualstringL = $ztilt;
        } else if (strpos($indtypeL, "RUH") !== false) {
            //$individualstringL = $bvd . ";" . $headtilt;
        } else {
            $individualstringL = "";
        }*/

        if (config('iniproperties.LENS_ORDER_CONFIGURATION_ENTRYUSER', 'NO') == 'YES' && $entryUser != '') {
            $user = $entryUser;
        } else {
            $user = ''; //"WEB"
        }

        if ($frame_remark != "") {
            $remark .= "ED_REMARK#SEP#".$frame_remark."#CRLF#";
        }

        $consRef = str_pad($consTitle, 2, " ") . str_pad($consLang, 2, " ") . str_pad($consDateOfBirth, 8, " ") . str_pad($consPhone, 18, " ");
        OrderHeader::where('OrdNumb', $orderno)->update([
            'RecStat' => 'REQU',
            'Rectype' => $ordertype,
            'Ordtype' => 'R',
            'OrdCat' => '',
            'Side' => $side,
            'EntryUser' => $user,
            'Reference' => $reference,
            'Remark' => $remark,
            'FReference' => $fReference,
            'FrameNo' => $frameNo,
            'Special' => $special,
            'ConsRef' => $consRef,
            'ConsName' => $consLastName,
            'ConsStreet' => $consStreet,
            'ConsZipC' => $consZip,
            'ConsCity' => $consCity,
            'ConsArea' => $consFirstName,
            'ReqDelv' => $reqDelv,
            'CustNumbX' => $custNumbX,
        ]);

        //Neues Handling für ORDER_CONSUMER  2020-12-08
        if (config('iniproperties.TABLE_ORDER_CONSUMER_TABLE', 'NO') == "YES") {
            $orderConsumer = OrderConsumer::firstOrCreate(
                ['OrdNumb' => $orderno],
                [
                    'Title' => $consTitle,
                    'Name' => $consLastName,
                    'FirstName' => $consFirstName,
                    'Street' => $consStreet,
                    'ZipCode' => $consZip,
                    'City' => $consCity,
                    'Area' => '',
                    'Country' => '',
                    'Language' => $consLang,
                    'Phone' => $consPhone,
                    'Mobile' => '',
                    'EMail' => $consEMail,
                    'Fax' => '',
                    'DateOfBirth' => $consDateOfBirth,
                    'Res1' => $consRes1,
                    'Res2' => $consRes2,
                    'Res3' => $consRes3,
                    'Res4' => $consRes4,
                    'Res5' => '',
                    'Res6' => '',
                    'Res7' => '',
                    'Res8' => '',
                    'Res9' => '',
                    'Res10' => ''
                ]
            );
            if (!$orderConsumer) return -1;
        }


        if ($L == 1) {
            if ($ovall == "on") {
                $cribtypel = "O";
            } else {
                $cribtypel = "R";
            }

            //Lenstype
            $orderComponentLensType = OrderComponent::where([
                'OrdNumb' => $orderno,
                'Side' => 'L',
                'Comptype' => '01',
            ])->first();

            if (!$orderComponentLensType) {//Neu anlegen
                $orderComponentLensType = OrderComponent::create([
                    'OrdNumb' => $orderno,
                    'Side' => 'L',
                    'Position' => '001',
                    'Comptype' => '01',
                    'Compcode' => $lenstypel,
                    'Diam1' => $diam1l,
                    'Diam2' => $diam2l,
                    'Diam3' => $diam3l,
                    'CompDescr' => $lensdescrl
                ]);
                if (!$orderComponentLensType) return -1;
            } else {
                OrderComponent::where([
                    'OrdNumb' => $orderno,
                    'Side' => 'L',
                    'Comptype' => '01',
                ])->update([
                    'Compcode' => $lenstypel,
                    'Diam1' => $diam1l,
                    'Diam2' => $diam2l,
                    'Diam3' => $diam3l,
                    'CompDescr' => $lensdescrl
                ]);
            }

            //Tint
            $orderComponentTint = OrderComponent::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'L'],
                ['Comptype', '=', '02']
            ])->first();

            if (!$orderComponentTint) {//Neu anlegen
                if (strlen($tint) > 0) {
                    $orderComponentTint = OrderComponent::create([
                        'OrdNumb' => $orderno,
                        'Side' => 'L',
                        'Position' => '002',
                        'Comptype' => '02',
                        'Compcode' => $tint,
                        'Cribtype' => $cribtypel,
                        'Diam1' => $diam1l,
                        'Diam2' => $diam2l,
                        'Diam3' => $diam3l,
                        'CompDescr' => $tintdescrl
                    ]);
                    if (!$orderComponentTint) return -1;
                }
            } else {
                if (strlen($tint) > 0) {//Tint
                    OrderComponent::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'L'],
                        ['Comptype', '=', '02']
                    ])->update([
                        'Compcode' => $tint,
                        'Cribtype' => $cribtypel,
                        'Diam1' => $diam1l,
                        'Diam2' => $diam2l,
                        'Diam3' => $diam3l,
                        'CompDescr' => $tintdescrl
                    ]);
                } else {
                    OrderComponent::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'L'],
                        ['Comptype', '=', '02']
                    ])->delete();
                }
            }

            //Coating
            $orderComponentCoating = OrderComponent::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'L'],
                ['Comptype', '=', '03']
            ])->first();

            if (!$orderComponentCoating) {//Neu anlegen
                if (strlen($coating) > 0) {
                    if ($tint > 0) {
                        $posl = "003";
                    } else {
                        $posl = "003";
                    }

                    $orderComponentCoating = OrderComponent::create([
                        'OrdNumb' => $orderno,
                        'Side' => 'L',
                        'Position' => $posl,
                        'Comptype' => '03',
                        'Compcode' => $coating,
                        'Cribtype' => $cribtypel,
                        'Diam1' => $diam1l,
                        'Diam2' => $diam2l,
                        'Diam3' => $diam3l,
                        'CompDescr' => $coatdescrl
                    ]);
                    if (!$orderComponentCoating) return -1;
                }
            } else {
                if (strlen($coating) > 0) {
                    OrderComponent::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'L'],
                        ['Comptype', '=', '03']
                    ])->update([
                        'Compcode' => $coating,
                        'Cribtype' => $cribtypel,
                        'Diam1' => $diam1l,
                        'Diam2' => $diam2l,
                        'Diam3' => $diam3l,
                        'CompDescr' => $coatdescrl
                    ]);
                } else {
                    OrderComponent::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'L'],
                        ['Comptype', '=', '03']
                    ])->delete();
                }
            }

            //UV
            $orderComponentUV = OrderComponent::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'L'],
                ['Comptype', '=', '05']
            ])->first();

            if (!$orderComponentUV) {//Neu anlegen
                if (strlen($uv) > 0) {
                    if ($uv > 0) {
                        $posl = "004";
                    } else {
                        $posl = "004";
                    }

                    $orderComponentUV = OrderComponent::create([
                        'OrdNumb' => $orderno,
                        'Side' => 'L',
                        'Position' => $posl,
                        'Comptype' => '05',
                        'Compcode' => $uv,
                        'Cribtype' => $cribtypel,
                        'Diam1' => $diam1l,
                        'Diam2' => $diam2l,
                        'Diam3' => $diam3l,
                        'CompDescr' => $uvdescrr
                    ]);
                    if (!$orderComponentUV) return -1;
                }
            } else {
                if (strlen($uv) > 0) {
                    OrderComponent::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'L'],
                        ['Comptype', '=', '05']
                    ])->update([
                        'Compcode' => $uv,
                        'Cribtype' => $cribtypel,
                        'Diam1' => $diam1l,
                        'Diam2' => $diam2l,
                        'Diam3' => $diam3l,
                        'CompDescr' => $uvdescrr
                    ]);
                } else {
                    OrderComponent::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'L'],
                        ['Comptype', '=', '05']
                    ])->delete();
                }
            }

            //OTHER
            $orderComponentOtherListl = OrderComponent::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'L'],
                ['Comptype', '=', '??']
            ])->get();

            if (!$orderComponentOtherListl) {
                if (isset($otherListl) && is_array($otherListl) && count($otherListl) > 0) {
                    foreach ($otherListl as $index => $other) {
                        $orderComponentOtherListl = OrderComponent::create([
                            'OrdNumb' => $orderno,
                            'Side' => 'L',
                            'Position' => $index,
                            'Comptype' => '??',
                            'Compcode' => $other['code'],
                            'Cribtype' => $cribtypel,
                            'Diam1' => $diam1l,
                            'Diam2' => $diam2l,
                            'Diam3' => $diam3l,
                            'CompDescr' => $other['description']
                        ]);
                        if (!$orderComponentOtherListl) return -1;
                    }
                }
            } else {
                if (isset($otherListl) && is_array($otherListl) && count($otherListl) > 0) {
                    OrderComponent::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'L'],
                        ['Comptype', '=', '??']
                    ])->delete();

                    foreach ($otherListl as $index => $other) {
                        $orderComponentOtherListl = OrderComponent::create([
                            'OrdNumb' => $orderno,
                            'Side' => 'L',
                            'Position' => $index,
                            'Comptype' => '??',
                            'Compcode' => $other['code'],
                            'Cribtype' => $cribtypel,
                            'Diam1' => $diam1l,
                            'Diam2' => $diam2l,
                            'Diam3' => $diam3l,
                            'CompDescr' => $other['description']
                        ]);
                        if (!$orderComponentOtherListl) return -1;
                    }
                }
            }

            //Lensproperties
            $orderLensProperties = OrderLensProperties::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'L']
            ])->first();

            if ($ovall == "on") {
                $cribtypel = "O";
            } else {
                $cribtypel = "R";
            }

            if (!$orderLensProperties) {//Neu anlegen
                $orderLensProperties = OrderLensProperties::create([
                    'OrdNumb' => $orderno,
                    'Side' => 'L',
                    'StockRX' => $stockRXl,
                    'Mat_Ext' => $material,
                    'Group_Ext' => $group,
                    'Material' => '',
                    'LensGroup' => $filter,
                    'Cribtype' => $cribtypel,
                    'Sphere' => $spherel,
                    'Cylinder' => $cylinderl,
                    'Axis' => $axisl,
                    'Addition' => $additionl,
                    'Prism1' => $prism1l,
                    'PrBase1' => $base1l,
                    'Prism2' => $prism2l,
                    'PrBase2' => $base2l,
                    'Edgethick' => $minedgel,
                    'Centthick' => $devcenterl,
                    'BaseCurve' => $basecurvel,
                    'PD' => $pdl,
                    'Height' => $heightl,
                    'Diam1' => $diam1l,
                    'Diam2' => $diam2l,
                    'Diam3' => $diam3l,
                    'IndType' => $indtypeL,
                    'Individual' => $individualstringL,
                    'CTO' => $ctol,
                    'DecentX' => $forcedDecentration_decentrationxl,
                    'DecentY' => $forcedDecentration_decentrationyl,
                ]);
                if (!$orderLensProperties) return -1;
            } else {
                OrderLensProperties::where([
                    ['OrdNumb', '=', $orderno],
                    ['Side', '=', 'L']
                ])->update([
                    'StockRX' => $stockRXl,
                    'Mat_Ext' => $material,
                    'Group_Ext' => $group,
                    'LensGroup' => $filter,
                    'Cribtype' => $cribtypel,
                    'Sphere' => $spherel,
                    'Cylinder' => $cylinderl,
                    'Axis' => $axisl,
                    'Addition' => $additionl,
                    'Prism1' => $prism1l,
                    'PrBase1' => $base1l,
                    'Prism2' => $prism2l,
                    'PrBase2' => $base2l,
                    'Edgethick' => $minedgel,
                    'Centthick' => $devcenterl,
                    'BaseCurve' => $basecurvel,
                    'PD' => $pdl,
                    'Height' => $heightl,
                    'Diam1' => $diam1l,
                    'Diam2' => $diam2l,
                    'Diam3' => $diam3l,
                    'IndType' => $indtypeL,
                    'Individual' => $individualstringL,
                    'CTO' => $ctol,
                    'DecentX' => $forcedDecentration_decentrationxl,
                    'DecentY' => $forcedDecentration_decentrationyl,
                ]);
            }

            //LensShape
            $orderShape = OrderShape::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'L']
            ])->first();

            if (!$orderShape) {//Neu anlegen
                if (strlen($tracedata) > 0) {//Trace
                    $orderShape = OrderShape::create([
                        'OrdNumb' => $orderno,
                        'Side' => 'L',
                        'Trctype' => session('Trctype'),
                        'Form' => $tracedata,
                        'Etyp' => $etype,
                        'FTyp' => $ftype,
                        'BevP' => $bevp,
                        'BevM' => $bevm,
                        'GDepth' => $grvDepth,
                        'GWidth' => $grvWidth,
                        'FPinB' => $fpinb,
                        'PinB' => $pinb,
                        'Polish' => $polish,
                        'DBL' => $dbl,
                        'HBox' => $hboxl,
                        'VBox' => $vboxl,
                        'Drill' => hex2bin($drillL),
                    ]);
                    if (!$orderShape) return -1;
                } else if ($frameNo != "") { //only frameNo
                    $orderShape = OrderShape::create([
                        'OrdNumb' => $orderno,
                        'Side' => 'L',
                        'DBL' => $dbl,
                        'Trctype' => ''
                    ]);
                    if (!$orderShape) return -1;
                }

                if(strlen(session('interpolateForm')) > 0){//TracePreview
                    $orderShapeExtension = OrderShapeExtension::create([
                        'OrdNumb' => $orderno,
                        'Side' => 'L',
                        'FormPreview' => session('interpolateForm')
                    ]);
                    if (!$orderShapeExtension) return -1;
                }
            } else {
                OrderShape::where([
                    ['OrdNumb', '=', $orderno],
                    ['Side', '=', 'L']
                ])->update([
                    'Trctype' => session('Trctype'),
                    'Form' => $tracedata,
                    'Etyp' => $etype,
                    'FTyp' => $ftype,
                    'BevP' => $bevp,
                    'BevM' => $bevm,
                    'GDepth' => $grvDepth,
                    'GWidth' => $grvWidth,
                    'FPinB' => $fpinb,
                    'PinB' => $pinb,
                    'Polish' => $polish,
                    'DBL' => $dbl,
                    'HBox' => $hboxl,
                    'VBox' => $vboxl,
                    'Drill' => hex2bin($drillL),
                ]);

                if(strlen(session('interpolateForm')) > 0){//TracePreview
                    OrderShapeExtension::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'L']
                    ])->update([
                        'FormPreview' => session('interpolateForm')
                    ]);
                }
            }

        } else {//Einträge löschen
            OrderComponent::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'L']
            ])->delete();

            OrderLensProperties::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'L']
            ])->delete();

            OrderShape::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'L']
            ])->delete();

            OrderShapeExtension::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'L']
            ])->delete();
        }

        if ($R == 1) {
            if ($ovalr == "on") {
                $cribtyper = "O";
            } else {
                $cribtyper = "R";
            }

            //Lenstype
            $orderComponentLensType = OrderComponent::where([
                'OrdNumb' => $orderno,
                'Side' => 'R',
                'Comptype' => '01',
            ])->first();

            if (!$orderComponentLensType) {//Neu anlegen
                $orderComponentLensType = OrderComponent::create([
                    'OrdNumb' => $orderno,
                    'Side' => 'R',
                    'Position' => '001',
                    'Comptype' => '01',
                    'Compcode' => $lenstyper,
                    'Diam1' => $diam1r,
                    'Diam2' => $diam2r,
                    'Diam3' => $diam3r,
                    'CompDescr' => $lensdescrr
                ]);
                if (!$orderComponentLensType) return -1;
            } else {
                OrderComponent::where([
                    'OrdNumb' => $orderno,
                    'Side' => 'R',
                    'Comptype' => '01',
                ])->update([
                    'Compcode' => $lenstyper,
                    'Diam1' => $diam1r,
                    'Diam2' => $diam2r,
                    'Diam3' => $diam3r,
                    'CompDescr' => $lensdescrr
                ]);
            }

            //Tint
            $orderComponentTint = OrderComponent::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'R'],
                ['Comptype', '=', '02']
            ])->first();

            if (!$orderComponentTint) {//Neu anlegen
                if (strlen($tint) > 0) {
                    $orderComponentTint = OrderComponent::create([
                        'OrdNumb' => $orderno,
                        'Side' => 'R',
                        'Position' => '002',
                        'Comptype' => '02',
                        'Compcode' => $tint,
                        'Cribtype' => $cribtyper,
                        'Diam1' => $diam1r,
                        'Diam2' => $diam2r,
                        'Diam3' => $diam3r,
                        'CompDescr' => $tintdescrr
                    ]);
                    if (!$orderComponentTint) return -1;
                }
            } else {
                if (strlen($tint) > 0) {//Tint
                    OrderComponent::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'R'],
                        ['Comptype', '=', '02']
                    ])->update([
                        'Compcode' => $tint,
                        'Cribtype' => $cribtyper,
                        'Diam1' => $diam1r,
                        'Diam2' => $diam2r,
                        'Diam3' => $diam3r,
                        'CompDescr' => $tintdescrr
                    ]);
                } else {
                    OrderComponent::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'R'],
                        ['Comptype', '=', '02']
                    ])->delete();
                }
            }

            //Coating
            $orderComponentCoating = OrderComponent::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'R'],
                ['Comptype', '=', '03']
            ])->first();

            if (!$orderComponentCoating) {//Neu anlegen
                if (strlen($coating) > 0) {
                    if ($tint > 0) {
                        $posr = "003";
                    } else {
                        $posr = "003"; //002
                    }

                    $orderComponentCoating = OrderComponent::create([
                        'OrdNumb' => $orderno,
                        'Side' => 'R',
                        'Position' => $posr,
                        'Comptype' => '03',
                        'Compcode' => $coating,
                        'Cribtype' => $cribtyper,
                        'Diam1' => $diam1r,
                        'Diam2' => $diam2r,
                        'Diam3' => $diam3r,
                        'CompDescr' => $coatdescrr
                    ]);
                    if (!$orderComponentCoating) return -1;
                }
            } else {
                if (strlen($coating) > 0) {
                    OrderComponent::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'R'],
                        ['Comptype', '=', '03']
                    ])->update([
                        'Compcode' => $coating,
                        'Cribtype' => $cribtyper,
                        'Diam1' => $diam1r,
                        'Diam2' => $diam2r,
                        'Diam3' => $diam3r,
                        'CompDescr' => $coatdescrr
                    ]);
                } else {
                    OrderComponent::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'R'],
                        ['Comptype', '=', '03']
                    ])->delete();
                }
            }

            //UV
            $orderComponentUV = OrderComponent::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'R'],
                ['Comptype', '=', '05']
            ])->first();

            if (!$orderComponentUV) {//Neu anlegen
                if (strlen($uv) > 0) {
                    if ($tint > 0) {
                        $posr = "004";
                    } else {
                        $posr = "004"; //002
                    }

                    $orderComponentUV = OrderComponent::create([
                        'OrdNumb' => $orderno,
                        'Side' => 'R',
                        'Position' => $posr,
                        'Comptype' => '05',
                        'Compcode' => $uv,
                        'Cribtype' => $cribtyper,
                        'Diam1' => $diam1r,
                        'Diam2' => $diam2r,
                        'Diam3' => $diam3r,
                        'CompDescr' => $uvdescrr
                    ]);
                    if (!$orderComponentUV) return -1;
                }
            } else {
                if (strlen($uv) > 0) {
                    OrderComponent::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'R'],
                        ['Comptype', '=', '05']
                    ])->update([
                        'Compcode' => $uv,
                        'Cribtype' => $cribtyper,
                        'Diam1' => $diam1r,
                        'Diam2' => $diam2r,
                        'Diam3' => $diam3r,
                        'CompDescr' => $uvdescrr
                    ]);
                } else {
                    OrderComponent::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'R'],
                        ['Comptype', '=', '05']
                    ])->delete();
                }
            }

            //OTHER
            $orderComponentOtherListr = OrderComponent::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'R'],
                ['Comptype', '=', '??']
            ])->get();
            if (!$orderComponentOtherListr) {
                if (isset($otherListr) && is_array($otherListr) && count($otherListr) > 0) {
                    foreach ($otherListr as $index => $other) {
                        $orderComponentOtherListr = OrderComponent::create([
                            'OrdNumb' => $orderno,
                            'Side' => 'R',
                            'Position' => $index,
                            'Comptype' => '??',
                            'Compcode' => $other['code'],
                            'Cribtype' => $cribtyper,
                            'Diam1' => $diam1r,
                            'Diam2' => $diam2r,
                            'Diam3' => $diam3r,
                            'CompDescr' => $other['description']
                        ]);
                        if (!$orderComponentOtherListr) return -1;
                    }
                }
            } else {
                if (isset($otherListr) && is_array($otherListr) && count($otherListr) > 0) {
                    OrderComponent::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'R'],
                        ['Comptype', '=', '??']
                    ])->delete();

                    foreach ($otherListr as $index => $other) {
                        $orderComponentOtherListr = OrderComponent::create([
                            'OrdNumb' => $orderno,
                            'Side' => 'R',
                            'Position' => $index,
                            'Comptype' => '??',
                            'Compcode' => $other['code'],
                            'Cribtype' => $cribtyper,
                            'Diam1' => $diam1r,
                            'Diam2' => $diam2r,
                            'Diam3' => $diam3r,
                            'CompDescr' => $other['description']
                        ]);
                        if (!$orderComponentOtherListr) return -1;
                    }
                }
            }

            //Lensproperties
            $orderLensProperties = OrderLensProperties::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'R']
            ])->first();

            if (!$orderLensProperties) {//Neu anlegen
                if ($ovalr == "on") {
                    $cribtyper = "O";
                } else {
                    $cribtyper = "R";
                }

                $orderLensProperties = OrderLensProperties::create([
                    'OrdNumb' => $orderno,
                    'Side' => 'R',
                    'StockRX' => $stockRXr,
                    'Mat_Ext' => $material,
                    'Group_Ext' => $group,
                    'Material' => '',
                    'LensGroup' => $filter,
                    'Cribtype' => $cribtyper,
                    'Sphere' => $spherer,
                    'Cylinder' => $cylinderr,
                    'Axis' => $axisr,
                    'Addition' => $additionr,
                    'Prism1' => $prism1r,
                    'PrBase1' => $base1r,
                    'Prism2' => $prism2r,
                    'PrBase2' => $base2r,
                    'Edgethick' => $minedger,
                    'Centthick' => $devcenterr,
                    'BaseCurve' => $basecurver,
                    'PD' => $pdr,
                    'Height' => $heightr,
                    'Diam1' => $diam1r,
                    'Diam2' => $diam2r,
                    'Diam3' => $diam3r,
                    'IndType' => $indtypeR,
                    'Individual' => $individualstringR,
                    'CTO' => $ctor,
                    'DecentX' => $forcedDecentration_decentrationxr,
                    'DecentY' => $forcedDecentration_decentrationyr,
                ]);
                if (!$orderLensProperties) return -1;
            } else {
                if ($ovalr == "on") {
                    $cribtyper = "O";
                } else {
                    $cribtyper = "R";
                }

                OrderLensProperties::where([
                    ['OrdNumb', '=', $orderno],
                    ['Side', '=', 'R']
                ])->update([
                    'StockRX' => $stockRXr,
                    'Mat_Ext' => $material,
                    'Group_Ext' => $group,
                    'LensGroup' => $filter,
                    'Cribtype' => $cribtyper,
                    'Sphere' => $spherer,
                    'Cylinder' => $cylinderr,
                    'Axis' => $axisr,
                    'Addition' => $additionr,
                    'Prism1' => $prism1r,
                    'PrBase1' => $base1r,
                    'Prism2' => $prism2r,
                    'PrBase2' => $base2r,
                    'Edgethick' => $minedger,
                    'Centthick' => $devcenterr,
                    'BaseCurve' => $basecurver,
                    'PD' => $pdr,
                    'Height' => $heightr,
                    'Diam1' => $diam1r,
                    'Diam2' => $diam2r,
                    'Diam3' => $diam3r,
                    'IndType' => $indtypeR,
                    'Individual' => $individualstringR,
                    'CTO' => $ctor,
                    'DecentX' => $forcedDecentration_decentrationxr,
                    'DecentY' => $forcedDecentration_decentrationyr,
                ]);
            }

            //LensShape
            $orderShape = OrderShape::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'R']
            ])->first();

            if (!$orderShape) {//Neu anlegen
                if (strlen($tracedata) > 0) {//Trace
                    $orderShape = OrderShape::create([
                        'OrdNumb' => $orderno,
                        'Side' => 'R',
                        'Trctype' => session('Trctype'),
                        'Form' => $tracedata,
                        'Etyp' => $etype,
                        'FTyp' => $ftype,
                        'BevP' => $bevp,
                        'BevM' => $bevm,
                        'GDepth' => $grvDepth,
                        'GWidth' => $grvWidth,
                        'FPinB' => $fpinb,
                        'PinB' => $pinb,
                        'Polish' => $polish,
                        'DBL' => $dbl,
                        'HBox' => $hboxr,
                        'VBox' => $vboxr,
                        'Drill' => hex2bin($drillR),
                    ]);
                    if (!$orderShape) return -1;
                } else if ($frameNo != "") { //only frameNo
                    $orderShape = OrderShape::create([
                        'OrdNumb' => $orderno,
                        'Side' => 'R',
                        'DBL' => $dbl,
                        'Trctype' => ''
                    ]);
                    if (!$orderShape) return -1;
                }

                if(strlen(session('interpolateForm')) > 0){//TracePreview
                    $orderShapeExtension = OrderShapeExtension::create([
                        'OrdNumb' => $orderno,
                        'Side' => 'R',
                        'FormPreview' => session('interpolateForm')
                    ]);
                    if (!$orderShapeExtension) return -1;
                }
            } else {
                OrderShape::where([
                    ['OrdNumb', '=', $orderno],
                    ['Side', '=', 'R']
                ])->update([
                    'Trctype' => session('Trctype'),
                    'Form' => $tracedata,
                    'Etyp' => $etype,
                    'FTyp' => $ftype,
                    'BevP' => $bevp,
                    'BevM' => $bevm,
                    'GDepth' => $grvDepth,
                    'GWidth' => $grvWidth,
                    'FPinB' => $fpinb,
                    'PinB' => $pinb,
                    'Polish' => $polish,
                    'DBL' => $dbl,
                    'HBox' => $hboxr,
                    'VBox' => $vboxr,
                    'Drill' => hex2bin($drillR),
                ]);

                if(strlen(session('interpolateForm')) > 0){//TracePreview
                    OrderShapeExtension::where([
                        ['OrdNumb', '=', $orderno],
                        ['Side', '=', 'R']
                    ])->update([
                        'FormPreview' => session('interpolateForm')
                    ]);
                }
            }
        } else {//Einträge löschen
            OrderComponent::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'R']
            ])->delete();

            OrderLensProperties::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'R']
            ])->delete();

            OrderShape::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'R']
            ])->delete();

            OrderShapeExtension::where([
                ['OrdNumb', '=', $orderno],
                ['Side', '=', 'R']
            ])->delete();
        }
        return $orderno;
    }

    /**
     * Send orders
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function sendOrders(Request $request) {
        $validator = Validator::make($request->all(), [
            'orders' => 'required|array'
        ], []);
        if ($validator->fails()) return response()->json(['message' => $validator->errors()], 400);

        $ordersResponse = [];
        foreach ($request->input('orders', []) as $orderNumber) {

            // Find order type
            $orderHeader = OrderHeader::where('OrdNumb', $orderNumber)->first();
            if (!$orderHeader){
                return response()->json(['message' => "Order doesn't exist"], 400);
            }

            // Bulk Orders
            if ($orderHeader->Rectype === "W"){
                $soapRequestData = SOAPHelper::generateCSVWholesale($orderNumber);
                if ($soapRequestData === -1){
                    return response()->json(['message' => "Error sending"], 400);
                }
                $xmlB2Bresult = SOAPHelper::call($request, 'wholeSale', [
                    'SERVICE' => 'SendBulkOrder',
                    'DATA' => $soapRequestData,
                    'FORMAT' => 'CSV',
                ]);
            }else {
                // Normal Orders
                $soapRequestData = SOAPHelper::generateSOAPRequest($orderNumber, 0, $request->input('uid', ''));
                $xmlB2Bresult = SOAPHelper::call($request, 'SendOrder', [
                    'FORMAT' => 'XMLB2B',
                    'DATA' => base64_encode($soapRequestData)
                ]);
            }

            // Return Error
            if (isset($xmlB2Bresult['error'])) {
                return response()->json(['message' => $xmlB2Bresult['error']], 400);
            }

            if ($xmlB2Bresult != -1) {
                if (config('iniproperties.LENS_ORDER_CONFIGURATION_HANDLEWIMP', 'NO') == "YES") {
                    $this->changeOrderStatus($orderNumber, 'WIMP', '');
                    $ordersResponse[$orderNumber] = ['status' => 'WIMP', 'error' => ''];
                } else {
                    $this->changeOrderStatus($orderNumber, 'SEND', '');
                    $ordersResponse[$orderNumber] = ['status' => 'SEND', 'error' => ''];
                }
            }
        }

        return response()->json($ordersResponse, 200);
    }

    /**
     * Send normal order
     *
     * @param Request $request
     * @param mixed $data
     * @return void
     */
    public function sendOrder(Request $request, $data) {
        $soapResponse = SOAPHelper::call($request, 'SendOrder', [
            'FORMAT' => 'XMLB2B',
            'DATA' => base64_encode($data)
        ]);

        return $soapResponse;
    }

    /**
     * Send bulkorder order
     *
     * @param Request $request
     * @param mixed $data
     * @return void
     */
    public function sendBulkorder(Request $request, $data) {
        $soapResponse = SOAPHelper::call($request, 'wholeSale', [
            'SERVICE' => 'SendBulkOrder',
            'FORMAT' => 'CSV',
            'DATA' => $data,
        ]);

        return $soapResponse;
    }

    /**
     * Check order
     * @param Request $request
     * @param $xmlB2Bresult
     * @return mixed
     */
    public function checkOrder(Request $request, $orderNumber, $xmlB2Bresult) {
        $checkOrderResponse = SOAPHelper::call($request, 'checkOrder', [
            'ENCODED' => 'BASE64',
            'XMLB2B' => base64_encode($xmlB2Bresult),
            'SUPPLIER' => $request->input('supplier', 'LWR')
        ]);
        if (isset($checkOrderResponse['error'])) {
            return response()->json(['message' => $checkOrderResponse['error']], 400);
        } else {
            if (isset($checkOrderResponse['RESULT'])) {
                $data = base64_decode($checkOrderResponse['RESULT']);
                if ($data == '1' || $data == '') {
                    $this->changeOrderStatus($orderNumber, 'PREP', '');
                } else {
                    $this->changeOrderStatus($orderNumber, 'REQU', 'ERROR#SEP#'.$data.'#CRLF#');
                }
                return $data;
            } else {
                return $checkOrderResponse;
            }
        }
    }

    /**
     * Check order received
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function checkOrderReceived(Request $request) {
        $customer = $request->input('uid', '');
        $returnData = [];

        $orderQuery = OrderHeader::where('RecStat', '=', 'WIMP');
        if ($customer != '') $orderQuery->where('CustNumb', '=', $customer);
        $orderList = $orderQuery->pluck('OrdNumb')->toArray();
        if (!$orderList) return response()->json($returnData, 200);

        $checkOrderResponse = SOAPHelper::call($request, 'WebShop', [
            'ENCODED' => 'BASE64',
            'SERVICE' => 'checkOrderReceived',
            'ORDERLIST' => implode(';', $orderList)
        ]);

        if (isset($checkOrderResponse['error'])) {
            return response()->json(['message' => $checkOrderResponse['error']], 400);
        }

        if (empty($checkOrderResponse)) return response()->json($returnData, 200);
        $responseData = json_decode($checkOrderResponse);

        if (!empty($responseData) && !empty($responseData->OrderList)) {
            foreach ($responseData->OrderList as $orderData) {
                if ($orderData->Received == 'YES') {
                    $this->changeOrderStatus($orderData->OrdNumb, 'SEND', '');
                    $returnData[$orderData->OrdNumb] = ['status' => 'SEND', 'error' => ''];
                } else {
                    $returnData[$orderData->OrdNumb] = ['status' => 'WIMP', 'error' => ''];
                }
            }
        }

        return response()->json($returnData, 200);
    }

    /**
     * Change order status
     *
     * @param $orderno
     * @param $status
     * @param $errormsg
     * @param $handleWIMP
     * @return \Illuminate\Http\Response
     */
    public function changeOrderStatus($orderno, $status, $errormsg, $handleWIMP = 'NO') {
        $orderHeader = OrderHeader::where('OrdNumb', $orderno)->first();
        if ($orderHeader == null) return;

        $special = $orderHeader->special;
        if (strpos($special, 'ERROR#SEP#') !== false ) {
            $start = strpos($special, 'ERROR#SEP#');
            $end   = strpos($special, '#CRLF#', $start) + 6;
            $length = $end - $start;
            $special = substr_replace($special, '', $start, $length);
        }

        if ($errormsg != '') {
            $special = $special.$errormsg;
        }

        $updateData = [
            'RecStat' => $status,
            'Special' => $special
        ];

        if ($status == 'SEND' && $handleWIMP == 'YES') {
            $updateData['RecStat'] = 'WIMP';
        }

        OrderHeader::where('OrdNumb', $orderno)->update($updateData);
    }

    /**
     * Edit bulkorder order
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function editBulkorder(Request $request) {
        try {
            $data = $this->getWholeSaleOrderData($request->all());
            $result = $this->saveWholeSaleOrder($data['str'], $data['lensDescr'], $data['ordnumb'], $data['lensgroup'], $data['custNumbX']);
            if (!$result) {
                return response()->json(['message' => "Error saving"], 400);
            }
            return response()->json($result);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Save bulk order
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function saveBulkorder(Request $request) {
        try {
            DB::beginTransaction();

            $data = $this->getWholeSaleOrderData($request->all());
            $result = $this->saveWholeSaleOrder($data['str'], $data['lensDescr'], "", $data['lensgroup'], $data['custNumbX']);
            if (!$result) {
                DB::rollback();
                return response()->json(['message' => "Error saving"], 400);
            }

            DB::commit();
            return response()->json($result);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Prepare data for saving bulkorder
     *
     * @param array $data
     * @return void
     */
    public function getWholeSaleOrderData(array $data) {
        foreach ($data as $key => $value) {
            $GLOBALS[$key] = $value;
        }
        foreach ($data as $key => $value) {
            ${$key} = $value;
        }

        $h_str = ";";
        $v_str = "";
        $b_str = "";
        $isAdd = 0;
        $rectype = "";
        $special = "";

        foreach ($data as $key => $value) {
            if (substr($key, 0, 2) == "h_") {
                // echo $key." ".$value."<br>";
                if ($key == "h_requDelvDate") {
                    $value = substr($value, 6, 4) . substr($value, 3, 2) . substr($value, 0, 2);
                }
                //@todo get Addition
                if ($key == "h_lensgroup" && $value != "0") {
                    //$isAdd = 1;
                    continue;
                } else if ($key == "h_lensgroup") {
                    continue;
                }
                $h_str = $h_str . $value . ";";
            }
        }

        foreach ($data as $key => $value) {
            if (substr($key, 0, 2) == "v_") {
                $v_str = $v_str . $value . ";";
            }
        }

        foreach ($data as $key => $value) {
            if (substr($key, 0, 2) == "b_" && $value != "" && $value != "0") {
                // echo $key." ".$value."<br>";
                $sph = substr($key, 2, strpos($key, "__") - 2);
                $sph = str_replace("_", ".", $sph);

                $reststr = substr($key, strpos($key, "__") + 2);
                $cyl = substr($reststr, 0, strpos($reststr, "__"));
                $cyl = str_replace("_", ".", $cyl);

                $reststr  = substr($reststr, strpos($reststr, "__") + 2);
                $itemnumb = substr($reststr, 0, strpos($reststr, "__"));

                $locatnumb  = substr($reststr, strpos($reststr, "__") + 2);

                if ($isAdd == 0) {
                    $b_str = $b_str . $itemnumb . ";" . $locatnumb . ";" . $v_str . $sph . ";" . $cyl . ";0.00;" . $value . "\r\n";  //ADD = empty
                    //$b_str = $b_str.$itemnumb.";".$locatnumb.";".$rectype.";".$v_str.$sph.";".$cyl.";;".$value.";".$special.";\r\n";  //ADD = empty
                } else {
                    $b_str = $b_str . $itemnumb . ";" . $locatnumb . ";" . $v_str . $sph . ";0.00;" . $cyl . ";" . $value . "\r\n";  //CYL = empty; $cyl = ADD!!!
                    //$b_str = $b_str.$itemnumb.";".$locatnumb.";".$rectype.";".$v_str.$sph.";;".$cyl.";".$value.";".$special.";\r\n";  //CYL = empty; $cyl = ADD!!!
                }
            }
        }

        return [
            "str" => $h_str . "\r\n" . $b_str,
            "lensDescr" => $lensDescr,
            "ordnumb" => $ordnumb,
            "lensgroup" => $h_lensgroup,
            "custNumbX" => $custNumbX,
        ];
    }

    /**
     * Save bulkorder data to database
     *
     * @param string $str
     * @param string $lensDescr
     * @param integer $ordnumb
     * @param integer $lensgroup
     * @param string $custNumbX
     * @return void
     */
    public function saveWholeSaleOrder($str, $lensDescr, $ordnumb, $lensgroup, $custNumbX)
    {
        $orderRows = explode("\r\n", $str);
        $headRow   = explode(";", $orderRows[0]);

        $date = date("Y-m-d", time());
        $time = date("H:i:s", time());

        $entryUser = ''; //"WEB"
        if($headRow[5] !== ''){
            $entryUser = $headRow[5];
        }
        if ($ordnumb !== "") {
            $orderHeader = OrderHeader::where([
                ['OrdNumb', '=', $ordnumb],
            ])->update([
                'Reference' => $headRow[3],
                'EntryUser' => $headRow[5],
                'Remark' => $headRow[6],
                'CustNumbX' => $custNumbX,
            ]);

            OrderComponent::where([
                ['OrdNumb', '=', $ordnumb],
            ])->delete();

            $id = $ordnumb;
        } else {
            $orderHeader = OrderHeader::create([
                'CustNumb' => $headRow[1],
                'RecStat' => 'PREP',
                'Rectype' => 'W',
                'Ordtype' => 'R',
                'OrdCat' => '',
                'Side' => 'B',
                'Reference' => $headRow[3],
                'EntryType' => '',
                'EntryUser' => $entryUser,
                'EntryDate' => $date,
                'EntryTime' => $time,
                'Status' => 'PRPT',
                'Remark' => $headRow[6],
                'Special' => '',
                'CustNumbX' => $custNumbX,
            ]);
            if (!$orderHeader || !$orderHeader->id) return false;

            $id = $orderHeader->id;
            for ($i = 0; $i < 2; ++$i) {
                $side = "R";
                if ($i == 1) {
                    $side = "L";
                }

                $orderLensProperties = OrderLensProperties::create([
                    'OrdNumb' => $id,
                    'Side' => $side,
                    'StockRX' => 'S',
                    'Mat_Ext' => '2',
                    'Group_Ext' => '0',
                    'Material' => '' ,
                    'LensGroup' => '' ,
                    'Cribtype' => 'R',
                    'Diam1' => '0',
                    'Diam2' => '0',
                    'Diam3' => '0',
                    'CTO' => '0'
                ]);
                if (!$orderLensProperties) return false;

                $orderComponent = OrderComponent::create([
                    'OrdNumb' => $id,
                    'Side' => $side,
                    'Position' => '001',
                    'CompType' => '01',
                    'CompCode' => 'XXXXXX',
                    'CribType' => '' ,
                    'Diam1' => '0',
                    'Diam2' => '' ,
                    'Diam3' => '' ,
                    'CompDescr' => 'WholeSale'
                ]);
                if (!$orderComponent) return false;
            }
        }

        for ($i = 1; $i < sizeof($orderRows); ++$i) {
            if (!strpos($orderRows[$i], ";")) {
                continue;
            }

            $posRow = explode(";", $orderRows[$i]);
            $pos    = str_pad($i, 3, '0', STR_PAD_LEFT);

            $orderComponent = OrderComponent::create([
                'OrdNumb' => $id,
                'Side' => 'B',
                'Position' => $pos,
                'CompType' => '01',
                'ItemNumb' => $posRow[0],
                'ItemType' => 'ST',
                'CompCode' => $posRow[2],
                'CribType' => $posRow[4],
                'Diam1' => $posRow[3],
                'Diam2' => $lensgroup,
                'Diam3' => ''    ,
                'Quantity' => $posRow[8],
                'Sphere' => $posRow[5],
                'Cylinder' => $posRow[6],
                'Addition' => $posRow[7],
                'CompDescr' => $lensDescr
            ]);
            if (!$orderComponent) return false;
        }

        return OrderHeader::with([
            'orderComponent',
            'orderConsumer',
            'orderLensProperties',
            'orderShape',
            'orderShapeExtension',
        ])->where([
            ['OrdNumb', '=', $id],
        ])->first();
    }

}
