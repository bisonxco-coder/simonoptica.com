<?php

namespace Tests\Feature;

use Tests\TestCase;

class SettingsTest extends TestCase
{
    // Test command: 
    // php artisan test --testsuite=Feature --filter="SettingsTest"

    private static $credentials = [
        "pwd" => "999999",
        "uid" => "999999",
    ];

    public function test_get_iniproperties()
    {
        $response = $this->post('api/settings/get-ini-properties', self::$credentials);

        $response->assertStatus(200);
    }

    public function test_frame_bevel_control01_is_present()
    {
        $response = $this->post('api/settings/get-ini-properties', self::$credentials);

        $response->assertStatus(200);
        $response->assertJsonFragment(['FRAME_BEVEL_CONTROL01' => '7;0']);
    }

    public function test_frame_material_control01_is_not_present()
    {
        /* Add to .env:
            FRAME_MATERIAL_CONTROL01=null
            FRAME_MATERIAL_LANG01=null
            FRAME_MATERIAL_DESCRIPTION01=null
        */
        
        $response = $this->post('api/settings/get-ini-properties', self::$credentials);

        $response->assertStatus(200);
        $this->assertArrayNotHasKey('FRAME_MATERIAL_CONTROL01', $response->original);
    }

    public function test_get_app_variables()
    {
        $response = $this->post('api/settings/get-app-variables', self::$credentials);

        $response->assertStatus(200);

        $this->assertArrayHasKey('lens_graphics_color', $response->original);
    }
}
