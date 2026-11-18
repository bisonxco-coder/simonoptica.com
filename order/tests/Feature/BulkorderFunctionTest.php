<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BulkorderFunctionTest extends TestCase
{
    // Test command: 
    // php artisan test --filter="BulkorderFunctionTest" --stop-on-failure

    private static $credentials = [
        "uid" => "EDITOR",
        "pwd" => "EDITOR",
        "LANG" => "en",
        "CUSTLIST" => "",
        "PRODLIST" => "        39",
        "PRICELIST" => "",
        "STATUS" => "1",
        "ALIAS2" => "",
        "langCode" => "en"
    ];
    private $itemList = "B: CR Multi*70* -3.50*  0.50*  0.00*1*0*MPCR50#B: CR Multi*70* -3.75*  0.75*  0.00*3*0*MPCR50#B: CR Multi*70* -3.75*  0.50*  0.00*5*0*MPCR50#B: CR Multi*70* -4.00*  0.50*  0.00*1*0*MPCR50#B: CR Multi*70* -4.25*  0.25*  0.00*1*0*MPCR50";

    public function test_translate_item_list()
    {
        $params = array_merge(self::$credentials, [
            "itemList" => $this->itemList,
        ]);

        $response = $this->post('/api/orders/translate-bulkorder-item-list', $params);

        $response->assertStatus(200);
    }
}
