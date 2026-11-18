<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use function PHPUnit\Framework\isEmpty;

class TracerTest extends TestCase
{
    // Test command: 
    // php artisan test --testsuite=Feature --filter="TracerTest" --stop-on-failure

    // Test LensTracer connection with tracer configuration

    private static $credentials = [
        "pwd" => "999999",
        "uid" => "999999",
    ];
    private static $tracer1 = "HOYNAT_Hoya GT series";
    private static $fullConfiguration = [
        "customerNo" => "matjaz132456789",
        "port" => "COM3",
        "baud" => 9600,
        "parity" => "E",
        "databit" => 8,
        "stopbit" => 2,
        "vca" => 1,
        "ip" => "localhost:33333",
    ];

    private function createTracerConfiguration() 
    {
        $params = array_merge(
            self::$credentials,
            self::$fullConfiguration, 
            ["tracer" => self::$tracer1],
        );

        $response = $this->post('api/tracer/save', $params);
        $response->assertStatus(200);

        return $response->original;
    }

    private function deleteTracerConfiguration($tracer)
    {
        $params = array_merge(
            self::$credentials,
            ['id' => $tracer->id]
        );

        $response = $this->post('api/tracer/delete', $params);
        $response->assertStatus(200);
    }

    // public function test_get_tracer_url()
    // {
    //     $tracer = self::createTracerConfiguration();
        

    //     $params = array_merge(
    //         self::$credentials,
    //         ['id' => $tracer->id]
    //     );
    //     $response = $this->post('api/tracer/get-url', $params);
    //     $response->assertStatus(200);

        
    //     self::deleteTracerConfiguration($tracer);
    // }

    public function test_get_tracer_url_from_last_tracer_configuration()
    {
        $tracerGetResponse = $this->post('api/tracer/get-last-tracer-configuration', self::$credentials);
        $tracerGetResponse->assertStatus(200);

        $lastTracerConfiguration = $tracerGetResponse->original;
        if (!$lastTracerConfiguration || isEmpty($lastTracerConfiguration)) return;

        $params = array_merge(
            self::$credentials,
            ['id' => $lastTracerConfiguration->id]
        );

        $response = $this->post('api/tracer/get-url', $params);
        
        $response->assertStatus(200);
    }

    public function test_get_tracer_definitions()
    {
        $params = array_merge(
            self::$credentials,
        );
        $response = $this->post('api/tracer/tracer-definitions', $params);
        $response->assertStatus(200);
    }
}
