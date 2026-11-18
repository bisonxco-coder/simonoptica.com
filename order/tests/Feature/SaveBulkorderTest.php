<?php

namespace Tests\Feature;

use App\Models\OrderComponent;
use App\Models\OrderConsumer;
use App\Models\OrderHeader;
use App\Models\OrderLensProperties;
use App\Models\OrderShape;
use App\Models\OrderShapeExtension;
use Tests\TestCase;

class SaveBulkorderTest extends TestCase
{
    // Test command: 
    // php artisan test --filter="SaveBulkorderTest" --stop-on-failure

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
    private $bulkorder = null;
    
    public function test_create_and_send_bulkorder()
    {
        // Create bulkodrer
        $createBulkorderParams = array_merge(self::$credentials, [
            "b_4_00__0_00__13338__1" => 1,
            "b_3_75__0_00__13337__1" => 2,
            "b_3_75__0_25__13354__1" => 1,
            "b_3_50__0_00__13336__1" => 33,
            "b_3_50__0_25__13353__1" => 0,
            "b_3_50__0_50__13253__1" => 1,
            "b_3_25__0_00__13335__1" => 4,
            "b_3_25__0_25__13352__1" => 3,
            "b_3_25__0_50__13252__1" => 2,
            "b_3_25__0_75__13267__1" => 1,
            "action" => "new",
            "h_customer" => "EDITOR",
            "h_branch" => "",
            "h_reference" => "Matjaž bulkorder test123123123",
            "h_requDelvDate" => "3.4.2023",
            "h_entryUser" => "",
            "h_comment" => "+asd65f4s6a87f98as3f06wq4de06a",
            "h_reference1" => "",
            "h_reference2" => "",
            "h_test" => "",
            "h_lensgroup" => "0",
            "v_lenscode" => "UT50RW",
            "v_diameter" => "65",
            "v_side" => "B",
            "lensDescr" => "1.50 Remuve420 UG",
            "ordnumb" => "",
            "custNumbX" => "",
        ]);

        $createBulkorderResponse = $this->post('api/orders/save-bulkorder', $createBulkorderParams);
        $createBulkorderResponse->assertStatus(200);
        $this->bulkorder = $createBulkorderResponse->original;

        // Send bulkorder
        $sendBulkorderParams = array_merge(self::$credentials, [
           "orders" => [$this->bulkorder->OrdNumb] 
        ]);

        $sendBulkorderResponse = $this->post('api/orders/send', $sendBulkorderParams);
        $sendBulkorderResponse->assertStatus(200);
    }

    public function tearDown() : void {
        $orderNumber = $this->bulkorder->OrdNumb;
        OrderHeader::where('OrdNumb', $orderNumber)->delete();
        OrderComponent::where('OrdNumb', $orderNumber)->delete();
        OrderConsumer::where('OrdNumb', $orderNumber)->delete();
        OrderLensProperties::where('OrdNumb', $orderNumber)->delete();
        OrderShape::where('OrdNumb', $orderNumber)->delete();
        OrderShapeExtension::where('OrdNumb', $orderNumber)->delete();
    }
}
