<?php
namespace App\Http\Traits;
use GuzzleHttp\Client;
use Sk\Geohash\Geohash;
trait scrapeTrait {

    public function scrapeDeliveroo($postalCode="") {
        $apiKey = "8922b3fd29dc4ea0b1931875e214cbf7"; // Replace with your actual API key
        $coordinates = $this->getLatLngFromPostalCode($postalCode, $apiKey);
        $g = new Geohash();
        $hash = $g->encode($coordinates['latitude'],$coordinates['longitude']);
        $neighbors = $g->getNeighbors($hash);

        $hashedlocationCode=$hash;
        $URLDelivery='https://deliveroo.co.uk/restaurants/birmingham/birmingham-city-centre/?geohash='.$hashedlocationCode;
        $URLPickUp='https://deliveroo.co.uk/restaurants/birmingham/birmingham-city-centre/?fulfillment_method=COLLECTION&geohash'.$hashedlocationCode;

        dd($URLDelivery);
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
            return [
                'latitude' => $result['lat'],
                'longitude' => $result['lng'],
            ];
        } else {
            return null; // Geocoding failed
        }
    }

}
