<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WholeSaleTest extends TestCase
{
    // Test command: 
    // php artisan test --testsuite=Feature --filter="WholeSaleTest"

    private static $credentials = [
        "pwd" => "999999",
        "uid" => "999999",
        "PRODLIST" => "        39",
        "PRICELIST" => 53,
        "CUSTLIST" => 53,
        "STATUS" => 1
    ];

    public function test_get_stock_lenses_by_prod_list()
    {
        $response = $this->post('api/soap/stock-lenses-by-prod-list', self::$credentials);
        $response->assertStatus(200);
    }

    public function test_get_stock_lenses_whole_sale()
    {
        $response = $this->post('api/soap/stock-lenses-whole-sale', self::$credentials);
        $response->assertStatus(200);
    }

    public function test_get_customer_list_whole_sale()
    {
        $response = $this->post('api/soap/customer-list-whole-sale', self::$credentials);
        $response->assertStatus(200);
    }

    public function test_get_prod_lists_whole_sale()
    {
        $response = $this->post('api/soap/prod-lists-whole-sale', self::$credentials);
        $response->assertStatus(200);
    }

    public function test_get_stock_items()
    {
        $params = array_merge(self::$credentials, [
            "lensCode" => "UT50RW",
            "diameter" => "65",
            "side" => "B",
        ]);
        
        $response = $this->post('api/soap/stock-items', $params);
        $response->assertStatus(200);
    }

}
