<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UploadedShapeTest extends TestCase
{
    private static $credentials = [
        "pwd" => "999999",
        "uid" => "999999",
    ];
    
    public function test_get_standard_shapes_images()
    {
        $response = $this->post('/api/uploaded-shapes/standard-shapes-images', self::$credentials);

        $response->assertStatus(200);
    }

    public function test_get_standard_shape_form()
    {
        $params = array_merge(self::$credentials, [
            'shapeId' => 5
        ]);

        $response = $this->post('/api/uploaded-shapes/standard-shape-form', $params);
        $response->assertStatus(200);
    }
}
