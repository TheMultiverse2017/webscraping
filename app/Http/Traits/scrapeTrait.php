<?php
namespace App\Http\Traits;
use GuzzleHttp\Client;
trait scrapeTrait {

    public function scrapeDeliveroo($postalCode="") {
        $apiKey = "8922b3fd29dc4ea0b1931875e214cbf7"; // Replace with your actual API key
        $coordinates = $this->getLatLngFromPostalCode($postalCode, $apiKey);
        return $coordinates;

        // $hashedlocationCode=$locationCode;
        // $URLDelivery='https://deliveroo.co.uk/restaurants/birmingham/birmingham-city-centre/?geohash='.$hashedlocationCode;
        // $URLPickUp='https://deliveroo.co.uk/restaurants/birmingham/birmingham-city-centre/?fulfillment_method=COLLECTION&geohash'.$hashedlocationCode;
        // $postalCode = "B4 7DA"; // Replace this with your desired postal code
    }

    function getLatLngFromPostalCode($postalCode, $apiKey) {
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
