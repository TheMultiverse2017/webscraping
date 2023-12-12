<?php
namespace App\Http\Traits;
use GuzzleHttp\Client;
use Sk\Geohash\Geohash;

set_time_limit(0);

trait scrapeTrait {
use getHtmlData;
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

    function getUrls($url){

    }

    public function getLatLngFromPostalCode($postalCode, $apiKey) {
        $client = new Client();

        $response = $client->get('https://api.opencagedata.com/geocode/v1/json', [
            'query' => [
                'q' => urlencode($postalCode),
                'key' => $apiKey,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        if ($data['total_results'] > 0) {
            $result = $data['results'][0]['geometry'];
            $city = $data['results']['0']['components'];

            return [
                'latitude' => $result['lat'],
                'longitude' => $result['lng'],
                'city' => $city['city'],
            ];
        } else {
            return null; // Geocoding failed
        }
    }

}
