<?php

namespace App\Helpers;

class Calculation {
    public static function parse($calculationResponse, $formRight = "", $formLeft = "") {
        $result = self::setupData($calculationResponse, $formRight, $formLeft);
        return $result;
    }

    private static function setupData($calcResult, $formRight = "", $formLeft = "") {
        $data = base64_decode($calcResult);

        $strR  = "";
        $strL  = "";
        $thkR = "";
        $thkL = "";
        $diamThkR = "";
        $diamThkL = "";
        $diamR = "";
        $diamL = "";
        $formR = "";
        $formL = "";
        $degR  = "";
        $degL  = "";
        $dist1R  = "";
        $dist2R  = "";
        $dist1L  = "";
        $dist2L  = "";

        $status = false;
        $errorR = "";
        $errorL = "";
        $calcLenstypeR = "";
        $calcLenstypeL = "";
        $calcDiamR = "";
        $calcDiamL = "";
        $calcEllhR = "";
        $calcEllhL = "";
        $calcCentThicknGeoR = "";
        $calcCentThicknGeoL = "";
        $calcCentThicknOptR = "";
        $calcCentThicknOptL = "";
        $maxCalcThicknR    = "";
        $maxCalcThicknL    = "";
        $maxCalcThicknRadR = "";
        $maxCalcThicknRadL = "";
        $maxCalcThicknAxR  = "";
        $maxCalcThicknAxL  = "";
        $minCalcThicknR    = "";
        $minCalcThicknL    = "";
        $minCalcThicknRadR = "";
        $minCalcThicknRadL = "";
        $minCalcThicknAxR  = "";
        $minCalcThicknAxL  = "";
                
        $calcBasecR  = "";
        $calcBasecL  = "";
        $calcWeightR = "";
        $calcWeightL = "";
        $thinkessFound = false;

        $fcocinR = "0";
        $fcocinL = "0";
        $fcocupR = "0";
        $fcocupL = "0";

        $fcbcinR = "0";
        $fcbcinL = "0";
        $fcbcupR = "0";
        $fcbcupL = "0";
        $bcocinR = "0";
        $bcocinL = "0";
        $bcocupR = "0";
        $bcocupL = "0";

        $ltypR = "";
        $ltypL = "";
        $ltypeR = "";
        $ltypeL = "";

        $splitCR = explode("\r\n", $data);
        foreach($splitCR AS $splitCRtmp) {

            if(!(strpos($splitCRtmp, "XSTATUS=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 8));
                if ($tmpArr[0] == "R") {
                    $errorR = $splitCRtmp;
                } else if ($tmpArr[0] == "L") {
                    $errorL = $splitCRtmp;
                }
            }

            if(         !(strpos($splitCRtmp, "_C3DFMT=R") === false)){
                $inR = 1;
                $inL = 0;
            } else if ( !(strpos($splitCRtmp, "_C3DFMT=L") === false)){
                $inR = 0;
                $inL = 1;
            }
       
            if(         !(strpos($splitCRtmp, "STHKFMT=") === false) && !(strpos($splitCRtmp, ";R") === false)){
                $thinkessFound = true;
                $inThkR = 1;
                $inThkL = 0;
            } else if ( !(strpos($splitCRtmp, "STHKFMT=") === false) && !(strpos($splitCRtmp, ";L") === false)){
                $thinkessFound = true;
                $inThkR = 0;
                $inThkL = 1;
            }

            if(!(strpos($splitCRtmp, "_LDESC=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 7));
                if (isset($tmpArr[0])) {
                    $calcLenstypeR =  $tmpArr[0];
                } else {
                    $calcLenstypeR = "";
                }
                if (isset($tmpArr[1])) {
                    $calcLenstypeL =  str_replace("\r", "", $tmpArr[1]);
                }
            }

            if(!(strpos($splitCRtmp, "CRIB=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 5));
                $calcDiamR =  $tmpArr[0];
                $calcDiamL =  str_replace("\r", "", $tmpArr[1]);
            }

            if(!(strpos($splitCRtmp, "ELLH=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 5));
                $calcEllhR =  $tmpArr[0];
                $calcEllhL =  str_replace("\r", "", $tmpArr[1]);
            }
             
            if(!(strpos($splitCRtmp, "GTHK=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 5));
                $calcCentThicknGeoR =  $tmpArr[0];
                $calcCentThicknGeoL =  str_replace("\r", "", $tmpArr[1]);
            }
       
            if(!(strpos($splitCRtmp, "OTHK=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 5));
                $calcCentThicknOptR =  $tmpArr[0];
                $calcCentThicknOptL =  str_replace("\r", "", $tmpArr[1]);
            }

            //dickster Punkt ohne Form
            if(!(strpos($splitCRtmp, "THKP=") === false) && (strpos($splitCRtmp, "_THKP=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 5));
                $maxCalcThicknR =  $tmpArr[0];
                $maxCalcThicknL =  str_replace("\r", "", $tmpArr[1]);
            }

            //dickster Punkt Achse ohne Form
            if(!(strpos($splitCRtmp, "THKA=") === false) && (strpos($splitCRtmp, "_THKA=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 5));
                $maxCalcThicknAxR =  $tmpArr[0];
                $maxCalcThicknAxL =  str_replace("\r", "", $tmpArr[1]);
            }

            //dickster Punkt Radius ohne Form
            if(!(strpos($splitCRtmp, "THKR=") === false) && (strpos($splitCRtmp, "_THKR=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 5));
                $maxCalcThicknRadR =  $tmpArr[0];
                $maxCalcThicknRadL =  str_replace("\r", "", $tmpArr[1]);
            }
            
            //dünnster Punkt ohne Form
            if(!(strpos($splitCRtmp, "THNP=") === false) && (strpos($splitCRtmp, "_THNP=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 5));
                $minCalcThicknR =  $tmpArr[0];
                $minCalcThicknL =  str_replace("\r", "", $tmpArr[1]);
            }

            //dünnster Punkt Achse ohne Form
            if(!(strpos($splitCRtmp, "THNA=") === false) && (strpos($splitCRtmp, "_THNA=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 5));
                $minCalcThicknAxR =  $tmpArr[0];
                $minCalcThicknAxL =  str_replace("\r", "", $tmpArr[1]);
            }

            //dünnster Punkt Radius ohne Form
            if(!(strpos($splitCRtmp, "THNR=") === false) && (strpos($splitCRtmp, "_THNR=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 5));
                $minCalcThicknRadR =  $tmpArr[0];
                $minCalcThicknRadL =  str_replace("\r", "", $tmpArr[1]);
            }

            //dickster Punkt mit Form
            if(!(strpos($splitCRtmp, "_THKP=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 6));
                $maxCalcThicknR =  $tmpArr[0];
                $maxCalcThicknL =  str_replace("\r", "", $tmpArr[1]);
            }

            //dickster Punkt Achse mit Form
            if(!(strpos($splitCRtmp, "_THKA=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 6));
                $maxCalcThicknAxR =  $tmpArr[0];
                $maxCalcThicknAxL =  str_replace("\r", "", $tmpArr[1]);
            }
            
            //dünnster Punkt mit Form
            if(!(strpos($splitCRtmp, "_THNP=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 6));
                $minCalcThicknR =  $tmpArr[0];
                $minCalcThicknL =  str_replace("\r", "", $tmpArr[1]);
            }

            //dünnster Punkt Achse mit Form
            if(!(strpos($splitCRtmp, "_THNA=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 6));
                $minCalcThicknAxR =  $tmpArr[0];
                $minCalcThicknAxL =  str_replace("\r", "", $tmpArr[1]);
            }

            if(!(strpos($splitCRtmp, "MBASE=") === false)){
                $tmpArr     = explode(";", substr($splitCRtmp, 6));
                $calcBasecR = $tmpArr[0];
                $calcBasecL = str_replace("\r", "", $tmpArr[1]);
            }

            if(!(strpos($splitCRtmp, "WEIGHT=") === false)){
                $tmpArr      = explode(";", substr($splitCRtmp, 7));
                $calcWeightR = $tmpArr[0];
                $calcWeightL = str_replace("\r", "", $tmpArr[1]);
            }

            if(!(strpos($splitCRtmp, "LTYP=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 5));
                $ltypR = substr($tmpArr[0],0,2);
                $ltypL = substr(str_replace("\r", "", $tmpArr[1]),0,2);
            }

            if(!(strpos($splitCRtmp, "LTYPE=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 6));
                $ltypeR = substr($tmpArr[0],0,2);
                $ltypeL = substr(str_replace("\r", "", $tmpArr[1]),0,2);
            }

            if(!(strpos($splitCRtmp, "FCBCIN=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 7));
                $fcbcinR = $tmpArr[0];
                $fcbcinL = str_replace("\r", "", $tmpArr[1]);
            }

            if(!(strpos($splitCRtmp, "FCBCUP=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 7));
                $fcbcupR = $tmpArr[0];
                $fcbcupL = str_replace("\r", "", $tmpArr[1]);
            }

            if(!(strpos($splitCRtmp, "FCOCIN=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 7));
                $fcocinR = $tmpArr[0];
                $fcocinL = str_replace("\r", "", $tmpArr[1]);
            }

            if(!(strpos($splitCRtmp, "FCOCUP=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 7));
                $fcocupR = $tmpArr[0];
                $fcocupL = str_replace("\r", "", $tmpArr[1]);
            }
            
            if(!(strpos($splitCRtmp, "BCOCIN=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 7));
                $bcocinR = $tmpArr[0];
                $bcocinL = str_replace("\r", "", $tmpArr[1]);
            }

            if(!(strpos($splitCRtmp, "BCOCUP=") === false)){
                $tmpArr  = explode(";", substr($splitCRtmp, 7));
                $bcocupR = $tmpArr[0];
                $bcocupL = str_replace("\r", "", $tmpArr[1]);
            }

            if(!(strpos($splitCRtmp, "SWIDTH=") === false)){
                //$thinkessFound = false;
            }

            if(!(strpos($splitCRtmp, "_C=") === false)){
                $status = true;
                $deg   = substr($splitCRtmp, 3, 3);
                $diam  = substr($splitCRtmp, 6, 2);
                $dist1 = substr($splitCRtmp, 8, 4);
                $dist2 = substr($splitCRtmp, 12, 4);
                // $diff = intval(trim($dist2)) - intval(trim($dist1));
                if ($inR == 1) {
                    // $diffR   = $diffR.$diff.",";
                    $diamR   = $diamR.$diam.",";
                    $degR    = $degR.$deg.",";
                    $dist1R  = $dist1R.$dist1.",";
                    $dist2R  = $dist2R.$dist2.",";
                } else if($inL == 1){
                    // $diffL   = $diffL.$diff.",";
                    $diamL   = $diamL.$diam.",";
                    $degL    = $degL.$deg.",";
                    $dist1L  = $dist1L.$dist1.",";
                    $dist2L  = $dist2L.$dist2.",";
                }
            }

            if((strpos($splitCRtmp, "R=") == 0) && !(strpos($splitCRtmp, "R=") === false) && $thinkessFound == true){
                $diamtmp = "";
                $status = true;
                $diamThk = explode(";", substr($splitCRtmp, 2));
                for ($i = 0; $i < sizeof($diamThk); $i++) {
                    $diamtmp = $diamtmp.$diamThk[$i].",";
                }
                if ($inThkR == 1) {
                    $diamThkR   = $diamThkR.$diamtmp;
                } else if($inThkL == 1){
                    $diamThkL   = $diamThkL.$diamtmp;
                }
            }

            if((strpos($splitCRtmp, "T=") == 0) && !(strpos($splitCRtmp, "T=") === false) && $thinkessFound == true){
                $thktmp = "";
                $status = true;
                $thk = explode(";", substr($splitCRtmp, 2));
                for ($i = 0; $i < sizeof($thk); $i++) {
                    $thkcalc = round(floatval($thk[$i]),1);
                    $thktmp = $thktmp.$thkcalc.",";
                }
                if ($inThkR == 1) {
                    $thkR   = $thkR.$thktmp;
                } else if($inThkL == 1){
                    $thkL   = $thkL.$thktmp;
                }
            }
        }
        
        $vcaStrRDraw = $formRight;
        $vcaStrLDraw = $formLeft;
        
        // create elliptic shape data for drawing
        if ($vcaStrRDraw == "" && $calcEllhR != "") {
            $vcaStrRDraw = [];
            
            $x = $calcDiamR / 2;
            $y = $calcEllhR / 2;
            
            for ($i = 0  ;  $i < 360  ;  ++$i) {
                $x0 = $x * cos($i * (M_PI/180));
                $y0 = $y * sin($i * (M_PI/180));
                
                $vcaStrRDraw[] = sqrt(pow($x0, 2) + pow($y0, 2)) * 100;
                // $vcaStrRDraw .= sqrt(pow($x0, 2) + pow($y0, 2)) * 100;
                
                // if ($i < 359) {
                //     $vcaStrRDraw .= ",";
                // }
            }
        }
        if ($vcaStrLDraw == "" && $calcEllhL != "") {
            $vcaStrLDraw = [];

            $x = $calcDiamL / 2;
            $y = $calcEllhL / 2;
            
            for ($i = 0  ;  $i < 360  ;  ++$i) {
                $x0 = $x * cos($i * (M_PI/180));
                $y0 = $y * sin($i * (M_PI/180));
                
                $vcaStrLDraw[] = sqrt(pow($x0, 2) + pow($y0, 2)) * 100;
                // $vcaStrLDraw .= sqrt(pow($x0, 2) + pow($y0, 2)) * 100;
                
                // if ($i < 359) {
                //     $vcaStrLDraw .= ",";
                // }
            }
        }

        if (is_array($vcaStrRDraw)){
            $vcaStrRDraw = array_map('floatval', array_map('trim', $vcaStrRDraw));
        }
        if (is_array($vcaStrLDraw)){
            $vcaStrLDraw = array_map('floatval', array_map('trim', $vcaStrLDraw));
        }

        $thkRArr = array_map('floatval', array_map('trim', explode(',', substr($thkR, 0, strlen($thkR)-1))));
        $thkLArr = array_map('floatval', array_map('trim', explode(',', substr($thkL, 0, strlen($thkL)-1))));
        // $thkRArr = substr($thkR, 0, strlen($thkR)-1);
        // $thkLArr = substr($thkL, 0, strlen($thkL)-1);

        $diamThkRArr = array_map('floatval', array_map('trim', explode(',', substr($diamThkR, 0, strlen($diamThkR)-1))));
        $diamThkLArr = array_map('floatval', array_map('trim', explode(',', substr($diamThkL, 0, strlen($diamThkL)-1))));
        // $diamThkRArr = substr($diamThkR, 0, strlen($diamThkR)-1);
        // $diamThkLArr = substr($diamThkL, 0, strlen($diamThkL)-1);

        $diamRArr = array_map('floatval', array_map('trim', explode(',', substr($diamR, 0, strlen($diamR)-1))));
        $diamLArr = array_map('floatval', array_map('trim', explode(',', substr($diamL, 0, strlen($diamL)-1))));
        // $diamRArr = substr($diamR, 0, strlen($diamR)-1);
        // $diamLArr = substr($diamL, 0, strlen($diamL)-1);

        $degRArr  = array_map('floatval', array_map('trim', explode(',', substr($degR, 0, strlen($degR)-1))));
        $degLArr  = array_map('floatval', array_map('trim', explode(',', substr($degL, 0, strlen($degL)-1))));
        // $degRArr  = substr($degR, 0, strlen($degR)-1);
        // $degLArr  = substr($degL, 0, strlen($degL)-1);

        $dist1RArr  = array_map('floatval', array_map('trim', explode(',', substr($dist1R, 0, strlen($dist1R)-1))));
        $dist2RArr  = array_map('floatval', array_map('trim', explode(',', substr($dist2R, 0, strlen($dist2R)-1))));
        // $dist1RArr  = substr($dist1R, 0, strlen($dist1R)-1);
        // $dist2RArr  = substr($dist2R, 0, strlen($dist2R)-1);

        $dist1LArr  = array_map('floatval', array_map('trim', explode(',', substr($dist1L, 0, strlen($dist1L)-1))));
        $dist2LArr  = array_map('floatval', array_map('trim', explode(',', substr($dist2L, 0, strlen($dist2L)-1))));
        // $dist1LArr  = substr($dist1L, 0, strlen($dist1L)-1);
        // $dist2LArr  = substr($dist2L, 0, strlen($dist2L)-1);

        return [
            "splitCR" => $splitCR,
            "strR" => $strR,
            "strL" => $strL,
            "thkR" => $thkR,
            "thkL" => $thkL,
            "diamThkR" => $diamThkR,
            "diamThkL" => $diamThkL,
            "diamR" => $diamR,
            "diamL" => $diamL,
            "formR" => $formR,
            "formL" => $formL,
            "degR" => $degR,
            "degL" => $degL,
            "dist1R" => $dist1R,
            "dist2R" => $dist2R,
            "dist1L" => $dist1L,
            "dist2L" => $dist2L,
            "status" => $status,
            "errorR" => $errorR,
            "errorL" => $errorL,
            "calcLenstypeR" => $calcLenstypeR,
            "calcLenstypeL" => $calcLenstypeL,
            "calcDiamR" => $calcDiamR,
            "calcDiamL" => $calcDiamL,
            "calcEllhR" => $calcEllhR,
            "calcEllhL" => $calcEllhL,
            "calcCentThicknGeoR" => $calcCentThicknGeoR,
            "calcCentThicknGeoL" => $calcCentThicknGeoL,
            "calcCentThicknOptR" => $calcCentThicknOptR,
            "calcCentThicknOptL" => $calcCentThicknOptL,
            "maxCalcThicknR" => $maxCalcThicknR,
            "maxCalcThicknL" => $maxCalcThicknL,
            "maxCalcThicknRadR" => $maxCalcThicknRadR,
            "maxCalcThicknRadL" => $maxCalcThicknRadL,
            "maxCalcThicknAxR" => $maxCalcThicknAxR,
            "maxCalcThicknAxL" => $maxCalcThicknAxL,
            "minCalcThicknR" => $minCalcThicknR,
            "minCalcThicknL" => $minCalcThicknL,
            "minCalcThicknRadR" => $minCalcThicknRadR,
            "minCalcThicknRadL" => $minCalcThicknRadL,
            "minCalcThicknAxR" => $minCalcThicknAxR,
            "minCalcThicknAxL" => $minCalcThicknAxL,
            "calcBasecR" => $calcBasecR,
            "calcBasecL" => $calcBasecL,
            "calcWeightR" => $calcWeightR,
            "calcWeightL" => $calcWeightL,
            "thinkessFound" => $thinkessFound,
            "fcocinR" => $fcocinR,
            "fcocinL" => $fcocinL,
            "fcocupR" => $fcocupR,
            "fcocupL" => $fcocupL,
            "fcbcinR" => $fcbcinR,
            "fcbcinL" => $fcbcinL,
            "fcbcupR" => $fcbcupR,
            "fcbcupL" => $fcbcupL,
            "bcocinR" => $bcocinR,
            "bcocinL" => $bcocinL,
            "bcocupR" => $bcocupR,
            "bcocupL" => $bcocupL,
            "ltypR" => $ltypR,
            "ltypL" => $ltypL,
            "ltypeR" => $ltypeR,
            "ltypeL" => $ltypeL,
            "thkRArr" => $thkRArr,
            "thkLArr" => $thkLArr,
            "diamThkRArr" => $diamThkRArr,
            "diamThkLArr" => $diamThkLArr,
            "diamRArr" => $diamRArr,
            "diamLArr" => $diamLArr,
            "degRArr" => $degRArr,
            "degLArr" => $degLArr,
            "dist1RArr" => $dist1RArr,
            "dist2RArr" => $dist2RArr,
            "dist1LArr" => $dist1LArr,
            "dist2LArr" => $dist2LArr,
            "vcaStrRDraw" => $vcaStrRDraw,
            "vcaStrLDraw" => $vcaStrLDraw,
        ];
    }
}