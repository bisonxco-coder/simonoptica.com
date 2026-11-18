<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderComponent;
use App\Models\OrderConsumer;
use App\Models\OrderLensProperties;
use App\Models\OrderShape;
use App\Models\OrderShapeExtension;

class OrderHeader extends Model {
    use HasFactory;
    public $timestamps = false;

    /**
     * Make all attributes mass assignable
     */
    protected $guarded = [];

    protected $table = 'order_header';

    public function orderComponent()
    {
        return $this->hasMany(OrderComponent::class, 'OrdNumb', 'OrdNumb');
    }

    public function orderConsumer()
    {
        return $this->hasMany(OrderConsumer::class, 'OrdNumb', 'OrdNumb');
    }

    public function orderLensProperties()
    {
        return $this->hasMany(OrderLensProperties::class, 'OrdNumb', 'OrdNumb');
    }

    public function orderShape()
    {
        return $this->hasMany(OrderShape::class, 'OrdNumb', 'OrdNumb');
    }

    public function orderExtension()
    {
        return $this->hasMany(OrderExtension::class, 'OrdNumb', 'OrdNumb');
    }

    public function orderShapeExtension()
    {
        return $this->hasMany(OrderShapeExtension::class, 'OrdNumb', 'OrdNumb');
    }

}
