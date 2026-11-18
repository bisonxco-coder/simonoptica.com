<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TracerConfigurationTest extends TestCase
{
    // Test command: 
    // php artisan test --testsuite=Feature --filter="TracerConfigurationTest" --stop-on-failure

    // Test getting, creating, updating and deleting configuration


    private static $credentials = [
        "pwd" => "999999",
        "uid" => "999999",
    ];
    private static $customerNo = "999999";
    private static $tracer = "HOYNAT_Hoya GT series";
    private static $fullConfiguration = [
        "port" => "COM3",
        "baud" => 9600,
        "parity" => "E",
        "databit" => 8,
        "stopbit" => 2,
        "vca" => 1,
        "ip" => "localhost:33333",
    ];


    public function test_get_tracer_configuration()
    {
        $response = $this->post('api/tracer', self::$credentials);
        $response->assertStatus(200);
    }

    public function test_create_basic_tracer_configuration()
    {
        $params = [
            "customerNo" => self::$customerNo,
            "tracer" => self::$tracer
        ];

        $response = $this->post('api/tracer/save', array_merge($params, self::$credentials));
        $response->assertStatus(200);
    }

    public function test_get_last_tracer_configuration()
    {
        $tracerGetResponse = $this->post('api/tracer', self::$credentials);
        $tracerGetResponse->assertStatus(200);

        if (!$tracerGetResponse->original) return;
        
        $params = [
            'id' => $tracerGetResponse->original->id
        ];

        $response = $this->post('api/tracer', array_merge($params, self::$credentials));
        $response->assertStatus(200);
        $this->assertEquals($response->original->customerNo, self::$customerNo);
        $response->assertJsonFragment(["customerNo" => self::$customerNo]);
    }

    public function test_delete_last_tracer_configuration()
    {
        $tracerGetResponse = $this->post('api/tracer', self::$credentials);
        $tracerGetResponse->assertStatus(200);

        if (!$tracerGetResponse->original) return;

        $params = [
            'id' => $tracerGetResponse->original->id
        ];

        $response = $this->post('api/tracer/delete', array_merge($params, self::$credentials));
        $response->assertStatus(200);
        $response->assertJsonFragment(["customerNo" => self::$customerNo]);
    }

    public function test_customerno_of_last_tracer_configuration_should_not_be_matjaz132456789()
    {
        $tracerGetResponse = $this->post('api/tracer', self::$credentials);
        $tracerGetResponse->assertStatus(200);

        if (!$tracerGetResponse->original) return;

        $params = [
            'id' => $tracerGetResponse->original->id
        ];

        $response = $this->post('api/tracer', array_merge($params, self::$credentials));
        $response->assertStatus(200);
        $response->assertJsonMissing(["customerNo" => 'matjaz132456789']);
    }

    public function test_create_basic_tracer_configuration_again()
    {
        $params = [
            "customerNo" => self::$customerNo,
            "tracer" => self::$tracer
        ];

        $response = $this->post('api/tracer/save', array_merge($params, self::$credentials));
        $response->assertStatus(200);
    }

    public function test_update_last_tracer_configuration_with_full_configuration()
    {
        $tracerGetResponse = $this->post('api/tracer', self::$credentials);
        $tracerGetResponse->assertStatus(200);

        if (!$tracerGetResponse->original) return;

        $params = [
            'id' => $tracerGetResponse->original->id
        ];

        $response = $this->post('api/tracer/update', array_merge($params, self::$fullConfiguration, self::$credentials));
        $response->assertStatus(200);
        
        $response->assertJsonFragment(self::$fullConfiguration);
    }

    public function test_delete_last_tracer_full_configuration()
    {
        $tracerGetResponse = $this->post('api/tracer', self::$credentials);
        $tracerGetResponse->assertStatus(200);

        if (!$tracerGetResponse->original) return;

        $params = [
            'id' => $tracerGetResponse->original->id
        ];

        $response = $this->post('api/tracer/delete', array_merge($params, self::$credentials));
        $response->assertStatus(200);
        $response->assertJsonFragment(self::$fullConfiguration);
    }

    public function test_customerno_of_last_tracer_configuration_should_not_be_matjaz132456789_again()
    {
        $tracerGetResponse = $this->post('api/tracer', self::$credentials);
        $tracerGetResponse->assertStatus(200);

        if (!$tracerGetResponse->original) return;

        $params = [
            'id' => $tracerGetResponse->original->id
        ];

        $response = $this->post('api/tracer', array_merge($params, self::$credentials));

        $response->assertStatus(200);
        $response->assertJsonMissing(["customerNo" => "matjaz132456789"]);
    }
}
