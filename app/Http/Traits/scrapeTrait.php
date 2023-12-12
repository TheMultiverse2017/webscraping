<?php
namespace App\Http\Traits;
use Sk\Geohash\Geohash;

set_time_limit(0);

trait scrapeTrait {

    public function scrapeDeliveroo($postalCode="") {
        $apiKey = "8922b3fd29dc4ea0b1931875e214cbf7"; // Replace with your actual API key
        $coordinates = $this->getLatLngFromPostalCode($postalCode, $apiKey);
        $g = new Geohash();
        $hash = $g->encode($coordinates['latitude'],$coordinates['longitude']);
        $neighbors = $g->getNeighbors($hash); // Note in use currently

        $hashedlocationCode=$hash;

        $var1=$var2=$coordinates['city'];

        $URLDelivery='https://deliveroo.co.uk/restaurants/'.$var1.'/'.$var2.'/?geohash='.$hashedlocationCode;
        $URLPickUp='https://deliveroo.co.uk/restaurants/'.$var1.'/'.$var2.'/?fulfillment_method=COLLECTION&geohash'.$hashedlocationCode;

        dd($URLDelivery);
    }


}
