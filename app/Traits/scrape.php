<?php
namespace App\Http\Traits;

trait scrapeTrait {

    public function scrapeDeliveroo() {
        $locationCode="";
        $hashedlocationCode=$locationCode;
        $URLDelivery='https://deliveroo.co.uk/restaurants/birmingham/birmingham-city-centre/?geohash='.$hashedlocationCode;
        $URLPickUp='https://deliveroo.co.uk/restaurants/birmingham/birmingham-city-centre/?fulfillment_method=COLLECTION&geohash'.$hashedlocationCode;

    }
}
