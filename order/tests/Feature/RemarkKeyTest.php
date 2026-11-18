<?php

namespace Tests\Feature;

use Tests\TestCase;

class RemarkKeyTest extends TestCase
{
    // Test command: 
    // php artisan test --testsuite=Feature --filter="RemarkKeyTest"


    private static $credentials = [
        "pwd" => "999999",
        "uid" => "999999",
    ];

    public function test_get_remark_key()
    {
        $response = $this->post('api/soap/remark-key', self::$credentials);
        $response->assertStatus(200);
    }

    public function test_get_en_remark_key()
    {
        $params = array_merge(self::$credentials, [
            "lang" => 'en'
        ]);
        
        $response = $this->post('api/soap/remark-key', $params);
        $response->assertStatus(200);
    }

    // public function test_get_de_remark_key()
    // {
    //     $params = array_merge(self::$credentials, [
    //         "lang" => 'de'
    //     ]);
        
    //     $response = $this->post('api/soap/remark-key', $params);
    //     $response->assertStatus(200);
    // }
}
