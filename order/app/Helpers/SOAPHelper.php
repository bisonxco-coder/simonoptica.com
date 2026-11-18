<?php

namespace App\Helpers;

use App\Models\OrderComponent;
use App\Models\OrderConsumer;
use App\Models\OrderHeader;
use App\Models\OrderLensProperties;
use App\Models\OrderShape;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapFault;
use SoapParam;

class SOAPHelper {
    /**
     * Make SOAP call
     *
     * @param Request $request
     * @param $function
     * @param $params
     * @return mixed
     */
    public static function call(Request $request, $function, $params, $includeUserParams = true) {
        try {
            $client = new SoapClient(null, array(
                'location' => config('environment.soap_url'),
                'uri'      => config('environment.soap_url'),
                'trace'    => 1
            ));

            $soapParams = [];
            $soapParams = [
                new SoapParam($request->input('uid', ''), 'UID'),
                new SoapParam($request->input('pwd', ''), 'PWD'),
                new SoapParam($request->input('PRODLIST', ''), 'PRODLIST'),
                new SoapParam($request->input('CUSTLIST', ''), 'CUSTLIST'),
                new SoapParam($request->input('PRICELIST', ''), 'PRICELIST'),
                new SoapParam($request->input('STATUS', ''), 'STATUS'),
                new SoapParam($request->input('ALIAS2', ''), 'ALIAS2'),
                new SoapParam(0, 'AUTOLOGIN'),
                new SoapParam('LensOrderWEB '.env('APP_VERSION', 2.1), 'CLIENT'),
            ];
            foreach ($params as $key => $value) {
                //if(str_contains($value, '<!CDATA'))
                //    $soapParams[] = new SoapParam(new \SoapVar($value, XSD_ANYXML), $key);
                if( $key == 'b2bjobdata')
                    $soapParams[] = new SoapParam(new \SoapVar($value, XSD_ANYXML), $key);
                else
                    $soapParams[] = new SoapParam($value, $key);
            }
            return $client->__soapCall($function, $soapParams);
        } catch (SoapFault $e) {
            // echo "Request :\n". ($client->__getLastRequest()). "\n";
            // echo "Response :\n". ($client->__getLastResponseHeaders()). "\n";
            // echo "Response :\n". ($client->__getLastResponse()). "\n";
            // \Log::info("Request :\n". ($client->__getLastRequest()). "\n");
            // \Log::info("Response :\n". ($client->__getLastResponseHeaders()). "\n");
            // \Log::info("Response :\n". ($client->__getLastResponse()). "\n");
            return ['error' => "SOAP - " .  $e->getMessage()];
        }
    }

	public static function callCalc(Request $request, $function, $params, $includeUserParams = true) {
        try {
            $client = new SoapClient(null, array(
                'location' => config('appvariables.SOAP_CALC'),
                'uri'      => config('appvariables.SOAP_CALC'),
                'trace'    => 1
            ));

            $soapParams = [];
            $soapParams = [
                new SoapParam($request->input('uid', ''), 'UID'),
                new SoapParam($request->input('pwd', ''), 'PWD'),
                new SoapParam($request->input('PRODLIST', ''), 'PRODLIST'),
                new SoapParam($request->input('CUSTLIST', ''), 'CUSTLIST'),
                new SoapParam($request->input('PRICELIST', ''), 'PRICELIST'),
                new SoapParam($request->input('STATUS', ''), 'STATUS'),
                new SoapParam($request->input('ALIAS2', ''), 'ALIAS2'),
                new SoapParam(0, 'AUTOLOGIN'),
                new SoapParam('LensOrderWEB '.env('APP_VERSION', 2.1), 'CLIENT'),
            ];
            foreach ($params as $key => $value) {
                $soapParams[] = new SoapParam($value, $key);
            }
            return $client->__soapCall($function, $soapParams);
        } catch (SoapFault $e) {
            return ['error' => "SOAP - " .  $e->getMessage()];
        }
    }
	
    /**
     * Generate CSV for bulkorder
     *
     * @param int $orderno
     * @return void
     */
    public static function generateCSVWholesale($ordNumb) {
        $data = "";
        if (config('iniproperties.LENS_ORDER_WHOLESALE_SENDORDNUMB', 'NO') == "YES") {
            $data .= $ordNumb;
        }

        $orderHeader = OrderHeader::where('OrdNumb', $ordNumb)->first();
        if (!$orderHeader) return -1;

        $row = $orderHeader;
        if (strlen(trim($row['CustNumbX'])) > 0) {
            $data .= ";".$row['CustNumbX'];
        } else {
            $data .= ";".$row['CustNumb'];
        }
        $data .= ";;".$row['Reference'].";;".$row['EntryUser'].";".$row['Remark'].";;;\r\n";



        $orderComponents = OrderComponent::where('OrdNumb', $ordNumb)->get();
        if (!$orderComponents) return -1;

        $posFound = false;
        foreach ($orderComponents as $orderComponent) {
            $row = $orderComponent;
            if ($row['ItemNumb'] == "") {
                continue;
            }
            $posFound = true;
            $data .= $row['ItemNumb'].";1;;;".$row['CribType'].";;;;".$row['Quantity']."\r\n";
        }

        if (! $posFound) return -1;

        return $data;
    }

    /**
     * Generate XML for request
     *
     * @param $orderno
     * @param $onlyCheck
     * @param $custNumb
     * @return mixed
     */
    public static function generateSOAPRequest($orderno, $onlyCheck, $custNumb = '', $checkData = []) {
        $test = config('iniproperties.LENS_ORDER_CONFIGURATION_TESTWEB', 'NO');
        $entryUser = config('iniproperties.LENS_ORDER_CONFIGURATION_ENTRYUSER', 'NO');
        $reqDelv = config('iniproperties.LENS_ORDER_CONFIGURATION_REQDELV', 'NO');

        if ($onlyCheck == 2) {
            $order     = $checkData['order'];
            $consumer  = $checkData['consumer'];

            $lenscodeR = $checkData['lenscodeR'];
            $propR     = $checkData['propR'];
            $tintR     = $checkData['tintR'];
            $coatingR  = $checkData['coatingR'];
            $uvR       = $checkData['uvR'];
            $otherR    = $checkData['otherR'];
            $shapeR    = $checkData['shapeR'];

            $lenscodeL = $checkData['lenscodeL'];
            $propL     = $checkData['propL'];
            $tintL     = $checkData['tintL'];
            $coatingL  = $checkData['coatingL'];
            $uvL       = $checkData['uvL'];
            $otherL    = $checkData['otherL'];
            $shapeL    = $checkData['shapeL'];
        } else {
            $orderQuery = OrderHeader::where('OrdNumb', '=', $orderno);
            if (!$orderQuery->exists()) abort(404, "Order with id " . $orderno . " not found");

            if ($custNumb != '') $orderQuery->where(DB::raw('CustNumb'), '=', $custNumb);
            $order = $orderQuery->first();
            if ($order != null) $order->toArray();

            $lenscodeR = OrderComponent::select(['Compcode', 'CompDescr'])
                ->where(DB::raw('OrdNumb'), '=', $orderno)
                ->where('Side', '=', 'R')
                ->where('Comptype', '=', '01')
                ->first();
            if ($lenscodeR != null) $lenscodeR->toArray();

            $propR = OrderLensProperties::where(DB::raw('OrdNumb'), '=', $orderno)->where('Side', '=', 'R')->first();
            if ($propR != null) $propR->toArray();

            $tintR = OrderComponent::where(DB::raw('OrdNumb'), '=', $orderno)->where('Side', '=', 'R')->where('Comptype', '=', '02')->first();
            if ($tintR != null) $tintR->toArray();

            $coatingR = OrderComponent::where(DB::raw('OrdNumb'), '=', $orderno)->where('Side', '=', 'R')->where('Comptype', '=', '03')->first();
            if ($coatingR != null) $coatingR->toArray();

            $uvR = OrderComponent::where(DB::raw('OrdNumb'), '=', $orderno)->where('Side', '=', 'R')->where('Comptype', '=', '05')->first();
            if ($uvR != null) $uvR->toArray();

            $otherR = OrderComponent::where(DB::raw('OrdNumb'), '=', $orderno)->where('Side', '=', 'R')->where('Comptype', '=', '??')->get();
            if ($otherR != null) $otherR->toArray();

            $shapeR = OrderShape::where('OrdNumb', '=', $orderno)->where('Side', '=', 'R')->first();
            if ($shapeR != null) $shapeR->toArray();

            $lenscodeL = OrderComponent::select(['Compcode', 'CompDescr'])
                ->where(DB::raw('OrdNumb'), '=', $orderno)
                ->where('Side', '=', 'L')
                ->where('Comptype', '=', '01')
                ->first();
            if ($lenscodeL != null) $lenscodeL->toArray();

            $propL = OrderLensProperties::where(DB::raw('OrdNumb'), '=', $orderno)->where('Side', '=', 'L')->first();
            if ($propL != null) $propL->toArray();

            $tintL = OrderComponent::where(DB::raw('OrdNumb'), '=', $orderno)->where('Side', '=', 'L')->where('Comptype', '=', '02')->first();
            if ($tintL != null) $tintL->toArray();

            $coatingL = OrderComponent::where(DB::raw('OrdNumb'), '=', $orderno)->where('Side', '=', 'L')->where('Comptype', '=', '03')->first();
            if ($coatingL != null) $coatingL->toArray();

            $uvL = OrderComponent::where(DB::raw('OrdNumb'), '=', $orderno)->where('Side', '=', 'L')->where('Comptype', '=', '05')->first();
            if ($uvL != null) $uvL->toArray();

            $otherL = OrderComponent::where(DB::raw('OrdNumb'), '=', $orderno)->where('Side', '=', 'L')->where('Comptype', '=', '??')->get();
            if ($otherL != null) $otherL->toArray();

            $shapeL = OrderShape::where('OrdNumb', '=', $orderno)->where('Side', '=', 'L')->first();
            if ($shapeL != null) $shapeL->toArray();

            $consumer = OrderConsumer::where('OrdNumb', '=', $orderno)->first();
            if ($consumer != null) $consumer->toArray();
        }


        $bvdR = "";
        $bvdL = "";
        $ernrinR = "";
        $ernrinL = "";
        $corrlenR = "";
        $corrlenL = "";
        $panto = "";
        $frcv = "";
        $ztilt = "";
        $dercR      = "";
        $dercL      = "";
        $readdist   = "";
        $headInclination = "";
        $headTilt   = "";

        $content = file_get_contents(app_path() . '/XMLTemplates/XMLb2b_Template.xml');
        $today = date("d.m.Y H:i:s");

        $content = str_replace("#customersOrderId#", $orderno, $content);
        $content = str_replace("#dateTime#", $today, $content);
        if (env('APP_NAME_XML', "") == "") {
            $content = str_replace("#appnamexml#", "LensOrderWEB", $content);
        } else {
            $content = str_replace("#appnamexml#", env('APP_NAME_XML'), $content);
        }
        $content = str_replace("#version#", env('APP_VERSION'), $content);
        if (isset($order['CustNumbX']) && strlen(trim($order['CustNumbX'])) > 0) {
            $content = str_replace("#customerID#", $order['CustNumbX'], $content);
        } else {
            $content = str_replace("#customerID#", $custNumb, $content);
        }

        if ($test == "YES") {
            $remark = "WEB - ".$order['Remark'];
        } else {
            $remark = $order['Remark'];
        }
        if($remark)
            $remark = XmlEscape::xmlEscape($remark);

        if ($entryUser == "YES") {
            $content = str_replace("#entryUser#", "<entryUser>".XmlEscape::xmlEscape($order['EntryUser'])."</entryUser>", $content);
        } else {
            $content = str_replace("#entryUser#", "", $content);
        }

        if ($reqDelv == "YES") {
            $content = str_replace("#reqDelv#", str_replace("-"," ",$order['ReqDelv']), $content);
        } else {
            $content = str_replace("#reqDelv#", "", $content);
        }

        $content = str_replace("#remark#"      , self::getRemark($remark, ""), $content);

        $reference = XmlEscape::xmlEscape($order['Reference']);
        $content = str_replace("#reference#"   , $reference   , $content);

        if (self::getRemark($remark, "ED_REMARK") != "") {
            $content = str_replace("#remarkEdging#", "<remarkEdging>".self::getRemark($remark, "ED_REMARK")."</remarkEdging>", $content);
        } else {
            $content = str_replace("#remarkEdging#", ""                                                                , $content);
        }

        if (isset($lenscodeR['Compcode'])) {
            $contentR = file_get_contents(app_path() . '/XMLTemplates/XMLb2b_R_Template.xml');
            $ctoR = "";
            if ($propR['StockRX'] == "R" && isset($shapeR['Form']) && $propR['CTO'] == "1") {
                /*if (file_exists("../".$_SESSION['supplier']."/functions_specialctoR.php")) {
                    include("../".$_SESSION['supplier']."/functions_specialctoR.php");
                } else {
                    $ctoR = "<thicknessReduction>true</thicknessReduction>";
                }*/

                $ctoR = "<thicknessReduction>true</thicknessReduction>";
            }
            if ($tintR && $tintR['Compcode'] != "") {
                $tintRtemp = "<coating coatingType=\"COLOR\"><commercialCode>" . $tintR['Compcode'] . "</commercialCode></coating>";
            } else {
                $tintRtemp = "";
            }

            if ($coatingR && $coatingR['Compcode'] != "") {
                $coatingRtemp = "<coating coatingType=\"ANTIREFLEX\"><commercialCode>" . $coatingR['Compcode'] . "</commercialCode></coating>";
            } else {
                $coatingRtemp = "";
            }

            if ($uvR && $uvR['Compcode'] != "") {
                $uvRtemp = "<coating coatingType=\"UV\"><commercialCode>" . $uvR['Compcode'] . "</commercialCode></coating>";
            } else {
                $uvRtemp = "";
            }

            if ($otherR) {
                foreach ($otherR as $index => $other) {
                  $coatingRtemp .= "\r\n<coating coatingType=\"OTHER\"><commercialCode>" . $other['Compcode'] . "</commercialCode></coating>";
                }
            }

            $contentR = str_replace("#ctoR#", $ctoR, $contentR);
            $contentR = str_replace("#lenscodeR#", $lenscodeR['Compcode'], $contentR);
            $contentR = str_replace("#sphereR#", $propR['Sphere'], $contentR);
            $contentR = str_replace("#cylinderR#", $propR['Cylinder'], $contentR);
            $contentR = str_replace("#axisR#", $propR['Axis'], $contentR);
            $contentR = str_replace("#additionR#", $propR['Addition'], $contentR);
            $contentR = str_replace("#prismR#", $propR['Prism1'], $contentR);
            $contentR = str_replace("#baseR#", $propR['PrBase1'], $contentR);
            $contentR = str_replace("#prism2R#", $propR['Prism2'], $contentR);
            $contentR = str_replace("#base2R#", $propR['PrBase2'], $contentR);
            $contentR = str_replace("#tintR#", $tintRtemp, $contentR);
            $contentR = str_replace("#coatingR#", $coatingRtemp, $contentR);
            $contentR = str_replace("#uvR#", $uvRtemp, $contentR);
            $contentR = str_replace("#pdR#", $propR['PD'], $contentR);
            $contentR = str_replace("#heightR#", $propR['Height'], $contentR);
            $contentR = str_replace("#diam1R#", $propR['Diam1'], $contentR);
            $contentR = str_replace("#diam2R#", $propR['Diam2'], $contentR);
            $contentR = str_replace("#diam3R#", $propR['Diam3'], $contentR);
            $contentR = str_replace("#decentrationXR#", $propR['DecentX'], $contentR);
            $contentR = str_replace("#decentrationYR#", $propR['DecentY'], $contentR);
            if ($propR['Diam3'] != "") {
                $contentR = str_replace("#ellipticR#", "true", $contentR);
            } else {
                $contentR = str_replace("#ellipticR#", "", $contentR);
            }

            if ($propR['Edgethick'] != "") {
                $contentR = str_replace("#thicknessRefR1#", "<thickness reference=\"EDGE\">" . $propR['Edgethick'] . "</thickness>", $contentR);
            } else {
                $contentR = str_replace("#thicknessRefR1#", "", $contentR);
            }

            if ($propR['Centthick'] != "") {
                $contentR = str_replace("#thicknessRefR2#", "<thickness reference=\"CENTER\">" . $propR['Centthick'] . "</thickness>", $contentR);
            } else {
                $contentR = str_replace("#thicknessRefR2#", "", $contentR);
            }

            if (isset($shapeR['Drill']) && $shapeR['Drill'] != "" && strpos($shapeR['Drill'], "_DRHTHK") !== false) {
                $drillArray = explode("\r\n", $shapeR['Drill']);
                for ($drillCounter = 0  ;  $drillCounter < sizeof($drillArray)  ;  ++$drillCounter) {
                    if (strpos($drillArray[$drillCounter], "_DRHTHK") !== false) {
                        $contentR = str_replace("#thicknessRefR3#", "<thickness reference=\"DRILLHOLE\">" . explode("=", $drillArray[$drillCounter])[1] . "</thickness>", $contentR);
                    }
                }
            } else {
                $contentR = str_replace("#thicknessRefR3#", "", $contentR);
            }
            $contentR = str_replace("#basecurveR#", $propR['BaseCurve'], $contentR);

            $indtypeR = $propR['IndType'];
            /*if ($indtypeR == "BB3" || $indtypeR == "BB4") {
                $indR = $propR['Individual'];
                $indR_arr = explode(';', $indR);

                $bvdR = $indR_arr[0];
                $panto = $indR_arr[1];
                $ztilt = $indR_arr[2];
                $readdist = $indR_arr[3];
                $readref = $indR_arr[4];
                $ernrinR = $indR_arr[5];
                $handSelect = $indR_arr[6];
                $corrlenR = "";
            } else if (strpos($indtypeR, "RZV") !== false) {
                $indR = $propR['Individual'];
                $indR_arr = explode(';', $indR);

                $bvdR = $indR_arr[0];
                $panto = $indR_arr[1];
                $ztilt = $indR_arr[2];
                $readdist = $indR_arr[3];
                $headtilt = $indR_arr[4];
                $corrlenR = $indR_arr[5];
                $ernrinR = $indR_arr[6];
                $pref       = $indR_arr[7];
                $handSelect = $indR_arr[8];
                $dercR      = $indR_arr[9];
            } else if (strpos($indtypeR, "FFU") !== false) {
                $indR = $propR['Individual'];
                $indR_arr = explode(';', $indR);

                $initialgrav = $indR_arr[0];
                $bvdR = $indR_arr[1];
                $panto = $indR_arr[2];
                $readdist = $indR_arr[3];
                $ztilt = $indR_arr[4];
                $corrlenR = $indR_arr[5];
                $ernrinR = $indR_arr[6];
                $lenti = $indR_arr[8];
            } else if (strpos($indtypeR, "NIK") !== false) {
                $indR = $propR['Individual'];
                $indR_arr = explode(';', $indR);

                $readdist = $indR_arr[0];
                $bvdR = $indR_arr[1];
                $panto = $indR_arr[2];
                $ztilt = $indR_arr[3];
                $ernrinR = $indR_arr[4];
            }*/

            $indTypeR = "";
            $individualstringR = "";
            if ($propR['IndType'] != "") {
                $indTypeR = "<Individualtype>".$propR['IndType']."</Individualtype>";
                // Send min 6 Semicolons
                $stringMin =explode(';', $propR['Individual']);
                $i = count($stringMin);
                if($i < 7) {
                    while($i < 7) {
                        $propR['Individual'] = $propR['Individual'].';';
                        $i++;
                    }
                }
                $individualstringR = "<Individualstring>".$propR['Individual']."</Individualstring>";
            }

            //$contentR = str_replace("#bvdR#", $bvdR, $contentR);
            //$contentR = str_replace("#ernrinR#", $ernrinR, $contentR);
            //$contentR = str_replace("#corrlenR#", $corrlenR, $contentR);
            $contentR = str_replace("#indTypeR#", $indTypeR , $contentR);
            $contentR = str_replace("#individualstringR#", $individualstringR, $contentR);
            //writeLog($contentR, "../../");
        } else {
            $contentR = "";
        }

        if (isset($lenscodeL['Compcode'])) {
            $contentL = file_get_contents(app_path() . '/XMLTemplates/XMLb2b_L_Template.xml');

            $ctoL = "";
            if ($propL['StockRX'] == "R" && isset($shapeL['Form']) && $propL['CTO'] == "1") {
                /*if (file_exists("../".$_SESSION['supplier']."/functions_specialctoL.php")) {
                    include("../".$_SESSION['supplier']."/functions_specialctoL.php");
                } else {
                    $ctoL = "<thicknessReduction>true</thicknessReduction>";
                }*/

                $ctoL = "<thicknessReduction>true</thicknessReduction>";
            }

            if ($tintL && $tintL['Compcode'] != "") {
                $tintLtemp = "<coating coatingType=\"COLOR\"><commercialCode>" . $tintL['Compcode'] . "</commercialCode></coating>";
            } else {
                $tintLtemp = "";
            }

            if ($coatingL && $coatingL['Compcode'] != "") {
                $coatingLtemp = "<coating coatingType=\"ANTIREFLEX\"><commercialCode>" . $coatingL['Compcode'] . "</commercialCode></coating>";
            } else {
                $coatingLtemp = "";
            }

            if ($uvL && $uvL['Compcode'] != "") {
                $uvLtemp = "<coating coatingType=\"UV\"><commercialCode>" . $uvL['Compcode'] . "</commercialCode></coating>";
            } else {
                $uvLtemp = "";
            }

            if ($otherL) {
                foreach ($otherL as $index => $other) {
                  $coatingLtemp .= "\r\n<coating coatingType=\"OTHER\"><commercialCode>" . $other['Compcode'] . "</commercialCode></coating>";
                }
            }

            $contentL = str_replace("#ctoL#", $ctoL, $contentL);
            $contentL = str_replace("#lenscodeL#", $lenscodeL['Compcode'], $contentL);
            $contentL = str_replace("#sphereL#", $propL['Sphere'], $contentL);
            $contentL = str_replace("#cylinderL#", $propL['Cylinder'], $contentL);
            $contentL = str_replace("#axisL#", $propL['Axis'], $contentL);
            $contentL = str_replace("#additionL#", $propL['Addition'], $contentL);
            $contentL = str_replace("#prismL#", $propL['Prism1'], $contentL);
            $contentL = str_replace("#baseL#", $propL['PrBase1'], $contentL);
            $contentL = str_replace("#prism2L#", $propL['Prism2'], $contentL);
            $contentL = str_replace("#base2L#", $propL['PrBase2'], $contentL);
            $contentL = str_replace("#tintL#", $tintLtemp, $contentL);
            $contentL = str_replace("#coatingL#", $coatingLtemp, $contentL);
            $contentL = str_replace("#uvL#", $uvLtemp, $contentL);
            $contentL = str_replace("#pdL#", $propL['PD'], $contentL);
            $contentL = str_replace("#heightL#", $propL['Height'], $contentL);
            $contentL = str_replace("#diam1L#", $propL['Diam1'], $contentL);
            $contentL = str_replace("#diam2L#", $propL['Diam2'], $contentL);
            $contentL = str_replace("#diam3L#", $propL['Diam3'], $contentL);
            $contentL = str_replace("#decentrationXL#", $propL['DecentX'], $contentL);
            $contentL = str_replace("#decentrationYL#", $propL['DecentY'], $contentL);
            if ($propL['Diam3'] != "") {
                $contentL = str_replace("#ellipticL#", "true", $contentL);
            } else {
                $contentL = str_replace("#ellipticL#", "", $contentL);
            }

            if ($propL['Edgethick'] != "") {
                $contentL = str_replace("#thicknessRefL1#", "<thickness reference=\"EDGE\">" . $propL['Edgethick'] . "</thickness>", $contentL);
            } else {
                $contentL = str_replace("#thicknessRefL1#", "", $contentL);
            }

            if ($propL['Centthick'] != "") {
                $contentL = str_replace("#thicknessRefL2#", "<thickness reference=\"CENTER\">" . $propL['Centthick'] . "</thickness>", $contentL);
            } else {
                $contentL = str_replace("#thicknessRefL2#", "", $contentL);
            }

            if (isset($shapeL['Drill']) && $shapeL['Drill'] != "" && strpos($shapeL['Drill'], "_DRHTHK") !== false) {
                $drillArray = explode("\r\n", $shapeL['Drill']);
                for ($drillCounter = 0  ;  $drillCounter < sizeof($drillArray)  ;  ++$drillCounter) {
                    if (strpos($drillArray[$drillCounter], "_DRHTHK") !== false) {
                        $contentL = str_replace("#thicknessRefL3#", "<thickness reference=\"DRILLHOLE\">" . explode("=", $drillArray[$drillCounter])[1] . "</thickness>", $contentL);
                    }
                }
            } else {
                $contentL = str_replace("#thicknessRefL3#", "", $contentL);
            }
            $contentL = str_replace("#basecurveL#", $propL['BaseCurve'], $contentL);

            $indtypeL = $propL['IndType'];
            /*if ($indtypeL == "BB3" || $indtypeL == "BB4") {
                $indL = $propL['Individual'];
                $indL_arr = explode(';', $indL);

                $bvdL = $indL_arr[0];
                $panto = $indL_arr[1];
                $ztilt = $indL_arr[2];
                $readdist = $indL_arr[3];
                $readref = $indL_arr[4];
                $ernrinL = $indL_arr[5];
                $handSelect = $indL_arr[6];
                $corrlenL = "";
            } else if (strpos($indtypeL, "RZV") !== false) {
                $indL = $propL['Individual'];
                $indL_arr = explode(';', $indL);

                $bvdL = $indL_arr[0];
                $panto = $indL_arr[1];
                $ztilt = $indL_arr[2];
                $readdist = $indL_arr[3];
                $headtilt = $indL_arr[4];
                $corrlenL = $indL_arr[5];
                $ernrinL = $indL_arr[6];
                $pref       = $indL_arr[7];
                $handSelect = $indL_arr[8];
                $dercL      = $indL_arr[9];
            } else if (strpos($indtypeL, "FFU") !== false) {
                $indL = $propL['Individual'];
                $indL_arr = explode(';', $indL);

                $initialgrav = $indL_arr[0];
                $bvdL = $indL_arr[1];
                $panto = $indL_arr[2];
                $readdist = $indL_arr[3];
                $ztilt = $indL_arr[4];
                $corrlenL = $indL_arr[5];
                $ernrinL = $indL_arr[6];
                $lenti = $indL_arr[8];
            } else if (strpos($indtypeL, "NIK") !== false) {
                $indL = $propL['Individual'];
                $indL_arr = explode(';', $indL);

                $readdist = $indL_arr[0];
                $bvdL = $indL_arr[1];
                $panto = $indL_arr[2];
                $ztilt = $indL_arr[3];
                $ernrinL = $indL_arr[4];
            }*/

            $indTypeL = "";
            $individualstringL = "";
            if ($propL['IndType'] != "") {
                $indTypeL = "<Individualtype>".$propL['IndType']."</Individualtype>";
                // Send min 6 Semicolons
                $stringMin =explode(';', $propL['Individual']);
                $i = count($stringMin);
                if($i < 7) {
                    while($i < 7) {
                        $propL['Individual'] = $propL['Individual'].';';
                        $i++;
                    }
                }
                $individualstringL = "<Individualstring>". $propL['Individual']."</Individualstring>";
            }

            //$contentL = str_replace("#bvdL#", $bvdL, $contentL);
            //$contentL = str_replace("#ernrinL#", $ernrinL, $contentL);
            //$contentL = str_replace("#corrlenL#", $corrlenL, $contentL);
            $contentL = str_replace("#indTypeL#", $indTypeL, $contentL);
            $contentL = str_replace("#individualstringL#", $individualstringL, $contentL);
        } else {
            $contentL = "";
        }

        //Patient
        $contentP = file_get_contents(app_path() . '/XMLTemplates/XMLb2b_Patient_Template.xml');

        //Neues Handling für ORDER_CONSUMER  2020-12-08
        if (config('iniproperties.TABLE_ORDER_CONSUMER_TABLE', 'NO') == 'YES') {
            $dateOfBirth = trim($consumer['DateOfBirth']);
            if ($dateOfBirth != "") {
                $dateOfBirth = substr($consumer['DateOfBirth'], 0, 4) . " " . substr($consumer['DateOfBirth'], 4, 2) . " " . substr($consumer['DateOfBirth'], 6, 2);
            }
            $contentP = str_replace("#consTitle#"      , XmlEscape::xmlEscape($consumer['Title'])										  , $contentP);
            $contentP = str_replace("#consLastName#"   , XmlEscape::xmlEscape($consumer['Name'])                                           , $contentP);
            $contentP = str_replace("#consFirstName#"  , XmlEscape::xmlEscape($consumer['FirstName'])                                      , $contentP);
            $contentP = str_replace("#consStreet#"     , XmlEscape::xmlEscape($consumer['Street'])                                         , $contentP);
            $contentP = str_replace("#consZip#"        , XmlEscape::xmlEscape($consumer['ZipCode'])                                        , $contentP);
            $contentP = str_replace("#consCity#"       , XmlEscape::xmlEscape($consumer['City'])                                           , $contentP);
            $contentP = str_replace("#consLang#"       , XmlEscape::xmlEscape($consumer['Language'])										  , $contentP);
            $contentP = str_replace("#consPhone#"      , XmlEscape::xmlEscape($consumer['Phone'])										  , $contentP);
            $contentP = str_replace("#consEMail#"      , XmlEscape::xmlEscape($consumer['EMail'])										  , $contentP);
            $contentP = str_replace("#consDateOfBirth#", $dateOfBirth			    					  , $contentP);

            //Ersetzt den Tag-Platzhalter durch Tag plus variable
            for ($i = 1; $i <= 10; $i++) {
                $content  = str_replace("#consRes".$i."Node#", config('iniproperties.LENS_ORDER_CONSUMER_CONSRES' . $i . 'NODE', ''), $content);
            }

            //ersetzt die Variable im Tag
            try {
                for ($i = 1; $i <= 10; $i++) {
                    if ($consumer['Res'.$i.'\''] != ""){
                        $content  = str_replace("#consRes".$i."#", $consumer['Res'.$i.'\''], $content);
                    } else {
                        $content  = str_replace("#consRes".$i."#", ""		  				    , $content);
                    }
                }
            } catch (\Throwable $th) {
                try {
                    for ($i = 1; $i <= 10; $i++) {
                        if ($consumer['Res'.$i] != ""){
                            $content  = str_replace("#consRes".$i."#", $consumer['Res'.$i], $content);
                        } else {
                            $content  = str_replace("#consRes".$i."#", ""		  				    , $content);
                        }
                    }
                } catch (\Throwable $th) {}
            }

        } else {
            $dateOfBirth = trim(substr(str_pad($order['ConsRef'], 30, ' '),  4,  8));
            if ($dateOfBirth != "") {
                $dateOfBirth = substr($dateOfBirth, 0, 4) . " " . substr($dateOfBirth, 4, 2) . " " . substr($dateOfBirth, 6, 2);
            }
            $contentP = str_replace("#consStreet#"     , XmlEscape::xmlEscape($order['ConsStreet'])                                     , $contentP);
            $contentP = str_replace("#consCity#"       , XmlEscape::xmlEscape($order['ConsCity'])                                       , $contentP);
            $contentP = str_replace("#consZip#"        , XmlEscape::xmlEscape($order['ConsZipC'])                                       , $contentP);
            $contentP = str_replace("#consTitle#"      , XmlEscape::xmlEscape(trim(substr(str_pad($order['ConsRef']), 30, ' '),  0,  2)), $contentP);
            $contentP = str_replace("#consFirstName#"  , XmlEscape::xmlEscape($order['ConsArea'])                                       , $contentP);
            $contentP = str_replace("#consLastName#"   , XmlEscape::xmlEscape($order['ConsName'])                                       , $contentP);
            $contentP = str_replace("#consPhone#"      , XmlEscape::xmlEscape(trim(substr(str_pad($order['ConsRef']), 30, ' '), 12, 18)), $contentP);
            $contentP = str_replace("#consLang#"       , XmlEscape::xmlEscape(trim(substr(str_pad($order['ConsRef']), 30, ' '),  2,  2)), $contentP);
            $contentP = str_replace("#consDateOfBirth#", $dateOfBirth                                             , $contentP);

            //entfernen aus dem Template
            $content  = str_replace("#consRes1Node#"	 , ""				                                             , $content);
            $content  = str_replace("#consRes2Node#"	 , ""				                                             , $content);
            $content  = str_replace("#consRes3Node#"	 , ""				                                             , $content);
            $content  = str_replace("#consRes4Node#"	 , ""				                                             , $content);
            $content  = str_replace("#consRes5Node#"	 , ""				                                             , $content);
            $content  = str_replace("#consRes6Node#"	 , ""				                                             , $content);
            $content  = str_replace("#consRes7Node#"	 , ""				                                             , $content);
            $content  = str_replace("#consRes8Node#"	 , ""				                                             , $content);
            $content  = str_replace("#consRes9Node#"	 , ""				                                             , $content);
            $content  = str_replace("#consRes10Node#"	 , ""				                                             , $content);

        }

        $contentP = str_replace("#nearObjectDistance#", $readdist, $contentP);
        $contentP = str_replace("#headInclination#", $headInclination, $contentP);
        $contentP = str_replace("#headTilt#", $headTilt, $contentP);

        //Form
        $shape = [];
        if        (isset($shapeR['Trctype'])) {
            $shape = $shapeR;
        } else if (isset($shapeL['Trctype'])) {
            $shape = $shapeL;
        }

        if ((isset($shapeR['Trctype']) && $shapeR['Trctype'] != "") || (isset($shapeL['Trctype']) && $shapeL['Trctype'] != "")) {
            if ($shape['Form'] != "") {
                if (isset($lenscodeR['Compcode']) && !isset($lenscodeL['Compcode'])) { //it's correct that the right values are loaded in the left side!!!
                    $contentL = file_get_contents(app_path() . '/XMLTemplates/XMLb2b_L_Form_Template.xml');
                    $contentL = str_replace("#pdR#"    , $propR['PD']    , $contentL);
                    $contentL = str_replace("#heightR#", $propR['Height'], $contentL);
                } else if (!isset($lenscodeR['Compcode']) && isset($lenscodeL['Compcode'])) { //it's correct that the left values are loaded in the right side!!!
                    $contentR = file_get_contents(app_path() . '/XMLTemplates/XMLb2b_R_Form_Template.xml');
                    $contentR = str_replace("#pdL#"    , $propL['PD']    , $contentR);
                    $contentR = str_replace("#heightL#", $propL['Height'], $contentR);
                }

                $content_frame = file_get_contents(app_path() . '/XMLTemplates/XMLb2b_Frame_Template.xml');

                //if ($panto != "") {
                if(isset($shapeR['Panto'])) {
                    $panto = "<pantoscopicAngle>".$shapeR['Panto']."</pantoscopicAngle>";
                }
                $content_frame = str_replace("#panto#", $panto, $content_frame);
                if(isset($shapeR['Frcv'])) {
                    $frcv = "<frameCurve>".$shapeR['Frcv']."</frameCurve>";
                }
                $content_frame = str_replace("#frcv#", $frcv, $content_frame);
                //if ($ztilt != "") {
                if(isset($shapeR['FaceFormA'])) {
                    $ztilt = "<frameBowAngle>".$shapeR['FaceFormA']."</frameBowAngle>";
                }
                $content_frame = str_replace("#ztilt#", $ztilt, $content_frame);
                if(session('supplier') == "RZE") {
                    $content_frame = str_replace("#frameNo#", $order['FReference'], $content_frame);
                } else {
                    $content_frame = str_replace("#frameNo#", $order['FrameNo'], $content_frame);
                }

                $frameReference = XmlEscape::xmlEscape($order['FReference']);
                $content_frame = str_replace("#freference#", $frameReference, $content_frame);

                if (isset($shape['FTyp'])) {
                    if ($shape['FTyp'] == "1") {
                        $material = "PLASTIC";
                    } else if ($shape['FTyp'] == "2") {
                        $material = "METAL";
                    } else if ($shape['FTyp'] == "3") {
                        if ($shape['Etyp'] == "33") {
                            $material = "GROOVEDDRILLED";
                        } else if ($shape['Etyp'] == "2" || $shape['Etyp'] == "40") {
                            $material = "DRILLED";
                        } else {
                            $material = "NYLOR";
                        }
                    } else if ($shape['FTyp'] == "4") {
                        $material = "OPTYL";
                    } else if ($shape['FTyp'] == "5") {
                        $material = "SPECIAL";
                    } else {
                        $material = "METAL";
                    }
                } else {
                    $material = "METAL";
                }

                $content_frame = str_replace("#material#"    , $material        , $content_frame);
                $content_frame = str_replace("#tracerType#"  , $shape['Trctype'], $content_frame);
                $content_frame = str_replace("#tracerVersion#", $shape['Trctype'], $content_frame);
                $content_frame = str_replace("#tracerFormat#", ""               , $content_frame);
                $content_frame = str_replace("#tracestring#" , $shape['Form']   , $content_frame);
                $boxDataR = "";
                if (isset($shapeR['HBox']) && $shapeR['HBox'] != "") {
                    $boxDataR  = "<box side=\"RIGHT\">\n";
                    $boxDataR .= "  <width>"  . $shapeR['HBox'] . "</width>\n";
                    $boxDataR .= "  <height>" . $shapeR['VBox'] . "</height>\n";
                    $boxDataR .= "</box>\n";
                }
                $content_frame = str_replace("#boxDataR#"    , $boxDataR   , $content_frame);

                $boxDataL = "";
                if (isset($shapeL['HBox']) && $shapeL['HBox'] != "") {
                    $boxDataL  = "<box side=\"LEFT\">\n";
                    $boxDataL .= "  <width>"  . $shapeL['HBox'] . "</width>\n";
                    $boxDataL .= "  <height>" . $shapeL['VBox'] . "</height>\n";
                    $boxDataL .= "</box>\n";
                }
                $content_frame = str_replace("#boxDataL#"    , $boxDataL   , $content_frame);

                $content_frame = str_replace("#distanceBetweenLenses#", $shape['DBL'], $content_frame);

                $drillholes = "";
                $drillholeStr = "";
                if (isset($shapeR['Drill']) && $shapeR['Drill'] != "") {
                    $drillholes = $drillholes."<drillHoles side=\"RIGHT\">\n";
                    $drilltmp   = explode("\r\n", $shapeR['Drill']);
                    $max = sizeof($drilltmp);
                    for ($i = 0; $i < $max; $i++) {
                        if ($drilltmp[$i] != "" && !str_contains($drilltmp[$i], '_DRHTHK')) {
                            $drilltmp[$i] = str_replace("DRILLE=", "", $drilltmp[$i]);
                            $drilltmp2  = explode(";", $drilltmp[$i]);
                            $drillholeStr = $drillholeStr."<cDrillHole>\n";
                            $drillholeStr = $drillholeStr."<point>\n";
                            $drillholeStr = $drillholeStr."<x>".$drilltmp2[2]."</x>\n";
                            $drillholeStr = $drillholeStr."<y>".$drilltmp2[3]."</y>\n";
                            $drillholeStr = $drillholeStr."</point>\n";
                            if ($drilltmp2[5] != "") {
                                $drillholeStr = $drillholeStr."<point>\n";
                                $drillholeStr = $drillholeStr."<x>".$drilltmp2[5]."</x>\n";
                                $drillholeStr = $drillholeStr."<y>".$drilltmp2[6]."</y>\n";
                                $drillholeStr = $drillholeStr."</point>\n";
                            }
                            $drillholeStr = $drillholeStr."<diameterHole>".$drilltmp2[4]."</diameterHole>\n";
                            $drillholeStr = $drillholeStr."</cDrillHole>\n";
                        }
                    }
                    $drillholes = $drillholes.$drillholeStr."</drillHoles>\n";
                }
                $drillholeStr = "";
                if (isset($shapeL['Drill']) && $shapeL['Drill'] != "") {
                    $drillholes = $drillholes."<drillHoles side=\"LEFT\">\n";
                    $drilltmp   = explode("\r\n", $shapeL['Drill']);
                    $max = sizeof($drilltmp);
                    for ($i = 0; $i < $max; $i++) {
                        if ($drilltmp[$i] != "" && !str_contains($drilltmp[$i], '_DRHTHK')) {
                            $drilltmp[$i] = str_replace("DRILLE=", "", $drilltmp[$i]);
                            $drilltmp2  = explode(";", $drilltmp[$i]);
                            $drillholeStr = $drillholeStr."<cDrillHole>\n";
                            $drillholeStr = $drillholeStr."<point>\n";
                            $drillholeStr = $drillholeStr."<x>".$drilltmp2[2]."</x>\n";
                            $drillholeStr = $drillholeStr."<y>".$drilltmp2[3]."</y>\n";
                            $drillholeStr = $drillholeStr."</point>\n";
                            if ($drilltmp2[5] != "") {
                                $drillholeStr = $drillholeStr."<point>\n";
                                $drillholeStr = $drillholeStr."<x>".$drilltmp2[5]."</x>\n";
                                $drillholeStr = $drillholeStr."<y>".$drilltmp2[6]."</y>\n";
                                $drillholeStr = $drillholeStr."</point>\n";
                            }
                            $drillholeStr = $drillholeStr."<diameterHole>".$drilltmp2[4]."</diameterHole>\n";
                            $drillholeStr = $drillholeStr."</cDrillHole>\n";
                        }
                    }
                    $drillholes = $drillholes.$drillholeStr."</drillHoles>\n";
                }
                $content_frame = str_replace("#drillholes#", $drillholes, $content_frame);
            } else {
                $content_frame = "";
            }
        } else if ($order['FrameNo'] != "") {
            $content_frame = file_get_contents(app_path() . '/XMLTemplates/XMLb2b_Frame_Template.xml');
            $content_frame = str_replace("#panto#"                , $panto              , $content_frame);
            $content_frame = str_replace("#frcv#"                , $frcv              , $content_frame);
            $content_frame = str_replace("#ztilt#"                , $ztilt              , $content_frame);
            Log::info(session('supplier'));
            if(session('supplier') == "RZE") {
                $content_frame = str_replace("#frameNo#", $order['FReference'], $content_frame);
            } else {
                $content_frame = str_replace("#frameNo#", $order['FrameNo']   , $content_frame);
            }
            $content_frame = str_replace("#freference#"           , $order['FReference'], $content_frame);
            $content_frame = str_replace("#distanceBetweenLenses#", $shape['DBL']       , $content_frame);
            $content_frame = str_replace("#material#"             , ""                  , $content_frame);
            $content_frame = str_replace("#tracerType#"           , ""                  , $content_frame);
            $content_frame = str_replace("#tracerVersion#"        , ""                  , $content_frame);
            $content_frame = str_replace("#tracerFormat#"         , ""                  , $content_frame);
            $content_frame = str_replace("#tracestring#"          , ""                  , $content_frame);
            $content_frame = str_replace("#boxDataR#"             , ""                  , $content_frame);
            $content_frame = str_replace("#boxDataL#"             , ""                  , $content_frame);
        } else {
            $content_frame = "";
        }

        if ($order['Rectype'] == "E") {
            $edgingType = "ONSHAPE";
        } else if ($order['Rectype'] == "S") {
            $edgingType = "ORDEREDFRAME";
        } else if ($order['Rectype'] == "G") {
            $edgingType = "GIVENFRAME";
        } else {
            $edgingType = "NONE";
        }

        //   if ($edgingType != "NONE") {
        $content_edge = file_get_contents(app_path() . '/XMLTemplates/XMLb2b_Edge_Template.xml');

        if (isset($shape)) {
            if (isset($shape['FTyp']) && $shape['FTyp'] == "3" && substr(str_pad($shape['Etyp'], 2, ' '), 1, 1) == "3") {
                $bevelType = "GROOVED";
            } else if (isset($shape['FTyp']) && $shape['FTyp'] == "3") {
                $bevelType = "FLAT";
            } else {
                $bevelType = "BEVEL";
            }

            if (isset($shape['BevP'])) {
                if ($shape['BevP'] == "1") {
                    $bevelPosType = "FRONT";
                } else if ($shape['BevP'] == "5") {
                    $bevelPosType = "BACK";
                } else if ($shape['BevP'] == "2") {
                    $bevelPosType = "RELATION";
                } else if ($shape['BevP'] == "4") {
                    $bevelPosType = "RELATION";
                    $shape['BevM'] = "50";
                } else {
                    $bevelPosType = "AUTO";
                    $shape['BevM'] = "0";
                }
            } else {
                $bevelPosType = "AUTO";
                $shape['BevM'] = "0";
            }

            if (isset($shape['Polish']) && $shape['Polish'] == "1") {
                $polish = "true";
            } else {
                $polish = "false";
            }

            $chamfer = "MEDIUM";

            if (isset($shape['FPinB'])) {
                if ($shape['FPinB'] > 0 && $shape['PinB'] > 0) {
                    $chamferPos = "BOTH";
                } else if ($shape['FPinB'] > 0) {
                    $chamferPos = "FRONT";
                } else if ($shape['PinB'] > 0) {
                    $chamferPos = "BACK";
                } else {
                    $chamferPos = "NONE";
                    $chamfer    = "";
                }
            } else {
                $chamferPos = "NONE";
                $chamfer    = "";
            }

            $bevm   = (isset($shape['BevM'])) ? $shape['BevM'] : '';
            $gdepth   = (isset($shape['GDepth'])) ? $shape['GDepth'] : '';
            $gwidth   = (isset($shape['GWidth'])) ? $shape['GWidth'] : '';
        } else {
            $bevelType    = "";
            $bevelPosType = "";
            $polish       = "";
            $chamferPos   = "NONE";
            $chamfer      = "";
            $bevm         = "";
            $gdepth       = "";
            $gwidth       = "";
        }

        $content_edge = str_replace("#edgingType#"  , $edgingType  , $content_edge);
        $content_edge = str_replace("#bevelType#"   , $bevelType   , $content_edge);
        $content_edge = str_replace("#bevelPosType#", $bevelPosType, $content_edge);
        $content_edge = str_replace("#bevm#"        , $bevm        , $content_edge);
        $content_edge = str_replace("#gDepth#"      , $gdepth      , $content_edge);
        $content_edge = str_replace("#gWidth#"      , $gwidth      , $content_edge);
        $content_edge = str_replace("#polish#"      , $polish      , $content_edge);
        $content_edge = str_replace("#chamferPos#"  , $chamferPos  , $content_edge);
        $content_edge = str_replace("#chamfer#"     , $chamfer     , $content_edge);
        //   } else {
        //      $content_edge = "";
        //   }

        $content = str_replace("#PatientTemplate#", $contentP     , $content);
        $content = str_replace("#RTemplate#"      , $contentR     , $content);
        $content = str_replace("#LTemplate#"      , $contentL     , $content);
        $content = str_replace("#frameTemplate#"  , $content_frame, $content);
        $content = str_replace("#edgeTemplate#"   , $content_edge , $content);

        return $content;
    }

    /**
     * Get remark
     *
     * @param $remark
     * @param $type
     * @return mixed
     */
    public static function getRemark($remark, $type) {
        if ($type == "") {
            // get standard remark and remove special remarks
            if (strpos($remark, "ED_REMARK#SEP#") !== false ) {
                $start = strpos($remark, "ED_REMARK#SEP#");
                $end   = strpos($remark, "#CRLF#", $start) + 6;
                $length = $end - $start;
                $remark = substr_replace($remark, "", $start, $length);
            }
        } else {
            // extract special remark info
            if (strpos($remark, $type."#SEP#") !== false ) {
                $start = strpos($remark, $type."#SEP#") + strlen($type) + 5;
                $end   = strpos($remark, "#CRLF#", $start);
                $length = $end - $start;
                $remark = substr($remark, $start, $length);
            } else {
                return "";
            }
        }

        return $remark;
    }

    public static function getLangCodeFromRequest(Request $request, string $key = 'langCode') : string
    {
        $langCode = $request->input($key, 'en');
        if ($langCode == ''){
            $langCode = config('app.locale');
        }
        return $langCode;
    }
}
