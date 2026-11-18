<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderHeader;

class OrderConsumer extends Model {
    use HasFactory;
    public $timestamps = false;

    /**
     * Make all attributes mass assignable
     */
    protected $guarded = [];

    protected $table = 'order_consumer';

    public function orderHeader()
    {
        return $this->belongsTo(OrderHeader::class, 'OrdNumb', 'OrdNumb');
    }
}
