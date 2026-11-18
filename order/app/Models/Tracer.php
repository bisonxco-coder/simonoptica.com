<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tracer extends Model{
    use HasFactory;
    public $timestamps = false;

    /**
     * Make all attributes mass assignable
     */
    protected $guarded = [];

    protected $table = 'tracer_configuration';

    /**
     * Fill tracer data
     *
     * @param array $data - data array with new tracer properties
     * @return Tracer
     */
    public function fillTracerData(array $data) {
        $this->customerNo = \Arr::get($data, 'customerNo', $this->customerNo);
        $this->tracer = \Arr::get($data, 'tracer', $this->tracer);
        $this->port = \Arr::get($data, 'port', $this->port);
        $this->baud = \Arr::get($data, 'baud', $this->baud);
        $this->flow = \Arr::get($data, 'flow', $this->flow);
        $this->parity = \Arr::get($data, 'parity', $this->parity);
        $this->databit = \Arr::get($data, 'databit', $this->databit);
        $this->stopbit = \Arr::get($data, 'stopbit', $this->stopbit);
        $this->vca = \Arr::get($data, 'vca', $this->vca);
        $this->mode = \Arr::get($data, 'mode', $this->mode);
        $this->com_soft = \Arr::get($data, 'com_soft', $this->com_soft);
        $this->com_hard = \Arr::get($data, 'com_hard', $this->com_hard);
        $this->com_dtr = \Arr::get($data, 'com_dtr', $this->com_dtr);
        $this->ip = \Arr::get($data, 'ip', $this->ip);
        $this->tracerip = \Arr::get($data, 'tracerip', $this->tracerip);
        $this->tracerserialno = \Arr::get($data, 'tracerserialno', $this->tracerserialno);
        $this->proxyHost = \Arr::get($data, 'proxyHost', $this->proxyHost);
        $this->proxyPort = \Arr::get($data, 'proxyPort', $this->proxyPort);
        $this->proxyUser = \Arr::get($data, 'proxyUser', $this->proxyUser);
        $this->proxyPass = \Arr::get($data, 'proxyPass', $this->proxyPass);
    }

    /**
     * Return list of tracer definitions
     *
     * @return array tracer definitions
     */
    public static function tracerDefinitions() {
        return array(
            self::newTracerDefinition('', '', false, ' ', 0, '', 0, 0, false, false, false, true),
            self::newTracerDefinition('BRIVCA', 'Briot Attitude', true, 'S', 19200, 'N', 8, 1, false, true, false, true),
            self::newTracerDefinition('BRIVCA', 'Briot Scan 8', true, 'S', 19200, 'N', 8, 1, false, true, false, true),
            self::newTracerDefinition('BRIVCA', 'Briot Scanform Net II', true, 'S', 19200, 'N', 8, 1, false, true, false, true),
            self::newTracerDefinition('BRIVCA', 'Briot Scanform Net III', true, 'S', 19200, 'N', 8, 1, false, true, false, true),
            self::newTracerDefinition('VSRLAN','Briot Scan 8 (IPc)'                , true,'L',0,'',0,0,false,false,false,true),
            self::newTracerDefinition('VCRLAN','Briot Scan 8 (IPs)'                , true,'L',0,'',0,0,false,false,false,true),
            self::newTracerDefinition('ESLKAP', 'Essilor Kappa', false, 'S', 19200, 'N', 8, 1, true, false, false, true),
            self::newTracerDefinition('ESLVCA', 'Essilor Mr. Blue', true, 'S', 19200, 'N', 8, 1, false, true, true, true),
            self::newTracerDefinition('ESLVCA', 'Essilor Mr. Orange', true, 'S', 19200, 'N', 8, 1, false, true, true, true),
            self::newTracerDefinition('ESLVCA', 'Essilor Neksia', true, 'S', 19200, 'N', 8, 1, false, true, true, true),
            self::newTracerDefinition('ESLNAT', 'Essilor Phi', true, 'S', 19200, 'N', 8, 1, true, false, false, true),
            self::newTracerDefinition('ESLTES', 'Essilor Tess', true, 'S', 19200, 'N', 8, 1, false, false, false, true),
            self::newTracerDefinition('FJINAT', 'Fuji', false, 'S', 19200, 'N', 8, 1, false, false, false, true),
            self::newTracerDefinition('HOYNAT', 'Hoya GT series', true, 'S', 9600, 'E', 8, 2, false, false, false, true),
            self::newTracerDefinition('HGSHO3', 'Hoya GT Hoya3', false, 'S', 9600, 'N', 8, 1, false, false, false, true),
            self::newTracerDefinition('HOYVCA', 'Hoya UT series', true, 'S', 19200, 'N', 8, 1, true, false, false, true),
            self::newTracerDefinition('HVZVCA', 'Huvitz CFR-4000', true, 'S', 38400, 'N', 8, 1, false, false, false, true),
            self::newTracerDefinition('HVZVCA', 'Huvitz Excelon', true, 'S', 38400, 'N', 8, 1, false, false, false, true),
            self::newTracerDefinition('HVZVCA', 'Huvitz HFR-8000', true, 'S', 38400, 'N', 8, 1, false, false, false, true),
            self::newTracerDefinition('INDVCA', 'Indo Combimax', true, 'S', 9600, 'N', 8, 1, false, false, false, true),
            self::newTracerDefinition('INDNAT', 'Indo Schoene', true, 'S', 9600, 'N', 8, 1, false, false, false, true),
            self::newTracerDefinition('NOPVCA', 'Nat. Opt. 4T', true, 'S', 19200, 'N', 8, 1, false, false, false, true),
            self::newTracerDefinition('NOPVCA', 'Nat. Opt. 5T', true, 'S', 19200, 'N', 8, 1, false, false, false, true),
            self::newTracerDefinition('NOPVCA', 'Nat. Opt. Dimension', true, 'S', 19200, 'N', 8, 1, false, false, false, true),
            self::newTracerDefinition('NDKNAT', 'Nidek LT series', true, 'S', 9600, 'N', 8, 1, false, false, false, true),
            //self::newTracerDefinition('NDKLAN', 'Nidek LT 1000 LAN', false, 'L', 0, '', 0, 0, false, false, false, true), //not supported by LensSOAPTracer yet. CD 17.07.2017
            //self::newTracerDefinition('NDKLAN', 'Nidek RD 100', false, 'L', 0, '', 0, 0, false, false, false, true), //not supported by LensSOAPTracer yet. CD 17.07.2017
            self::newTracerDefinition('TKBVCA', 'Takubomatic FD-80', true, 'S', 9600, 'N', 8, 1, false, false, false, true),
            self::newTracerDefinition('TPCVCA', 'Topcon FR-50', true, 'S', 9600, 'N', 8, 1, false, false, false, true),
            self::newTracerDefinition('VISVCA', 'Visslo ST-88', true, 'S', 57600, 'N', 8, 1, false, false, false, true),
            self::newTracerDefinition('WCOVCA', 'Weco T.6', true, 'S', 19200, 'N', 8, 1, true, false, false, true),
            self::newTracerDefinition('WCONAT', 'Weco Trace II', false, 'S', 19200, 'N', 8, 1, false, true, true, true),
            self::newTracerDefinition('WCOVCA', 'Weco Trace II HC', true, 'S', 9600, 'N', 8, 1, false, true, true, true),
            self::newTracerDefinition('NDKNAT', 'Weco Trace II RSC', true, 'S', 9600, 'N', 8, 1, false, true, true, true),
            self::newTracerDefinition('WCOVCA', 'Weco Trace III', true, 'S', 19200, 'N', 8, 1, true, false, false, true),
            self::newTracerDefinition('TRCVCA', 'Universal VCA/OMA/DCS driver', true, 'S', 9600, 'N', 8, 1, false, false, false, true),
            self::newTracerDefinition('VSRLAN','VCA compatible device (LAN server)', true,'L',0,'',0,0,false,false,false,true),
            self::newTracerDefinition('VCRLAN','VCA compatible device (LAN client)', true,'L',0,'',0,0,false,false,false,true)
        );
    }
    private static function newTracerDefinition($tracerDriver = "", $tracerName = "", $isVca = false, $mode = " ", $baud = 0, $parity = "", $databit = 0, $stopbit = 0, $softwareHandshake = false, $hardwareHandshake = false, $dtr = false, $crc = true) {
        return [
            "tracerDriver" => $tracerDriver,
            "tracerName" => $tracerName,
            "isVca" => $isVca,
            "mode" => $mode,
            "baud" => $baud,
            "parity" => $parity,
            "databit" => $databit,
            "stopbit" => $stopbit,
            "softwareHandshake" => $softwareHandshake,
            "hardwareHandshake" => $hardwareHandshake,
            "dtr" => $dtr,
            "crc" => $crc,
        ];
    }

    /**
     * Return url for the LensTracer API endpoint
     *
     * @param Tracer $tracer
     * @param string $custNumb - customer number
     * @param string $custPassword - customer password
     * @param string $serverName - name or ip of the current server
     * @param string $serverPort - port number of the current server
     * @return string url of LensTracer endpoint
     */
    public static function getUrl(Tracer $tracer, string $custNumb, string $custPassword, string $serverName, string $serverPort) {
        // Url header
        $protocol = 'http';
        $ip = $tracer->ip;
        $action = 'getTraceData';

        // Calculated params
        $tracerType = self::getTracerTypeParam($tracer);
        $url = self::getUrlParam($serverName, $serverPort);
        $custPW = self::getCustPWParam($custPassword);
        $supplier = self::getSupplierParam();
        $ipLS = self::getIpLSParam();
        $ipLSDecrypt = self::getIpLSDecryptParam();
        $vcaFileName = self::getVcaFileNameParam($custNumb);

        // Endpoint
        return "{$protocol}://{$ip}/{$action}"
            . "?tracer={$tracerType}"
            . "&port={$tracer->port}"
            . "&baud={$tracer->baud}"
            . "&flow={$tracer->flow}"
            . "&parity={$tracer->parity}"
            . "&data={$tracer->databit}"
            . "&stop={$tracer->stopbit}"
            . "&url={$url}"
            . "&vca={$tracer->vca}"
            . "&trctp={$tracer->tracerip}"
            . "&trcser={$tracer->tracerserialno}"
            . "&custNumb={$custNumb}"
            . "&custPW={$custPW}"
            . "&ip={$tracer->ip}"
            . "&proxyHost={$tracer->proxyHost}"
            . "&proxyPort={$tracer->proxyPort}"
            . "&proxyUser={$tracer->proxyUser}"
            . "&proxyPass={$tracer->proxyPass}"
            . "&supplier={$supplier}"
            . "&ipLS={$ipLS}"
            . "&ipLSDecrypt={$ipLSDecrypt}"
            . "&vcaFileName={$vcaFileName}"
        ;
    }
    private static function getTracerTypeParam(Tracer $tracer) {

        $tracerDefinitions = self::tracerDefinitions();
        $foundTracerDefinition = [];
        foreach ($tracerDefinitions as $tracerDefinition) {
            if ($tracerDefinition['tracerName'] == $tracer->tracer){
                $foundTracerDefinition = $tracerDefinition;
                break;
            }
        }

        $type = '';
        if ($foundTracerDefinition)
            $type = $foundTracerDefinition['tracerDriver'];

        if ($tracer->mode == 'L') {
            $type = substr($type, 0, 3) . 'LAN';
        }

        return $type;
    }
    private static function getUrlParam($serverName, $serverPort) {
        $server = "{$serverName}:{$serverPort}";

        if ($serverPort == 443) {
            $server = "https://".$server;
        } else {
            $server = "http://".$server;
        }

        return $server;
    }
    private static function getCustPWParam($custPassword) {
        $utf8Encode = mb_convert_encoding($custPassword, 'UTF-8', mb_list_encodings());
        return strToUpper(bin2hex(base64_encode($utf8Encode)));
    }
    private static function getSupplierParam() {
        return "LWR";
    }
    private static function getIpLSParam() {
        return config('environment.soap_url');
    }
    private static function getIpLSDecryptParam() {
        return config('environment.soap_url');
    }
    private static function getVcaFileNameParam($custNumb) {
        return $custNumb."_".time();
    }

    public static function getTracerConfiguration($customerNo, $id = null){
        return self::where('customerNo', '=', $customerNo)
        ->when($id, function ($q) use($id) { $q->where('id', '=', $id);})
        ->orderBy('id','DESC')
        ->first() ?: [];
    }
}
