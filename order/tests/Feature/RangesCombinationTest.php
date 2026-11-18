<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RangesCombinationTest extends TestCase
{
    // Test command: 
    // php artisan test --testsuite=Feature --filter="RangesCombinationTest"


    private static $credentials = [
        "pwd" => "999999",
        "uid" => "999999",
    ];

    public function test_get_ranges_combination_but_the_response_has_umlaut_characters()
    {
        $params = array_merge(self::$credentials, [
            "lenscodel" => "SPHW50",
            "lenscoder" => "SPHW50"
        ]);

        $response = $this->post('api/soap/ranges-combination', $params);

        // \Log::info(json_encode($response));
        $response->assertStatus(200);
    }
}
