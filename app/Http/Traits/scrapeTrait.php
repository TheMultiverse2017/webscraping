<?php
namespace App\Http\Traits;
use GuzzleHttp\Client;
use Sk\Geohash\Geohash;
use Sunra\PhpSimple\HtmlDomParser;
use DOMDocument;
use DOMXPath;
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
        // $URLPickUp='https://deliveroo.co.uk/restaurants/'.$var1.'/'.$var2.'/?fulfillment_method=collectionUrls&geohash'.$hashedlocationCode;

        $collectionUrls=[];
        // $collectionItems=[
        //     'restaurants',
        //     'grocery',
        //     'shopping',
        //     'all+offers',
        //     'deliveroos-choice'
        // ];
        // Note: PickUp is a direct url like delivery so not added to collectionItems[]


        // foreach($collectionItems as $collectionItem){
        //     $collectionUrls[] = $URLDelivery.'/&collection='.$collectionItem;
        // }

        $client=new Client();
        $response=$client->request(
            'GET',
            $URLDelivery
        );
        $responseStatusCode=$response->getStatusCode();
        $responseBody=$response->getBody();
        $html=$responseBody->getContents();
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xPath = new DOMXPath($dom );
        // $spanTexts = $xPath->evaluate("//span[contains(concat(' ', normalize-space(@class), ' '), ' ShortcutTileHorizontal')]/span[@class]/text()");
        // foreach ($spanTexts  as $spanText) {
        //     // $collectionUrls[] = $URLDelivery.'/&collection='.$this->stringReplacement($spanText->nodeValue);
        //     $collectionUrls[] = $spanText->nodeValue;
        // }

        // Use XPath to select the script tag with id="__NEXT_DATA__"

        $scriptElements = $xPath->query('//script[@id="__NEXT_DATA__" and @type="application/json"]');
        $matchesArray = [];

        foreach ($scriptElements as $scriptElement) {
            // Extract all occurrences of the content between "id":"collection","value":[" and "]}"
            preg_match_all('/"id":"collection","value":\["(.*?)"\]/s', $scriptElement->nodeValue, $matches);

            if (!empty($matches[1])) {
                // Merge the matches into the array
                $matchesArray = array_merge($matchesArray, $matches[1]);
            }
        }
        dd($matchesArray);
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

    function stringReplacement($string){

        // Convert to lowercase
        $lowercaseString = strtolower($string);

        // Remove special characters
        $cleanString = preg_replace('/[^a-z0-9\s]/', '', $lowercaseString);

        // Replace spaces with hyphens
        $finalString = str_replace(' ', '-', $cleanString);

        return $finalString;
    }

}
