<?php
namespace App\Http\Traits;
use GuzzleHttp\Client;
use Sk\Geohash\Geohash;
use Sunra\PhpSimple\HtmlDomParser;
use DOMDocument;
use DOMXPath;
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

        $collections=$this->getUrls($URLDelivery);
        if(isset($collections) && !empty($collections)){
            $collectionsDeliveryUrls=[];
            $collectionsPickUpUrls=[];
            foreach ($collections as $collection) {
                $collectionsDeliveryUrl=$URLDelivery.'&collection='.$collection;
                $collectionsPickUpUrl=$URLPickUp.'&collection='.$collection;
                $collectionsDeliveryUrls[]=$collectionsDeliveryUrl;
                $collectionsPickUpUrls[]=$collectionsPickUpUrl;
            }

            //Fetch data from html with category
            if(isset($collectionsDeliveryUrls) && !empty($collectionsDeliveryUrls)){
                $pageData=[];
                foreach ($collectionsDeliveryUrls as $url) {
                    $classPrefix = 'HomeFeedGrid';
                    $query = "//li[contains(@class, '$classPrefix')]";
                    $liNodes = $this->htmlData($url)->query($query);

                    $urlResults = [];

                    foreach ($liNodes as $liNode) {
                        $divNode = $liNode->getElementsByTagName('div')->item(0);

                        if ($divNode) {
                            $h3Node = $divNode->getElementsByTagName('h3')->item(0);

                            if ($h3Node) {
                                $cat = $h3Node->textContent;
                                $links = [];

                                foreach ($liNode->getElementsByTagName('a') as $a) {
                                    $link = $a->getAttribute('href');

                                    // Modify the code to fetch descriptions within span with class containing the dynamic part
                                    $spanNodes = $this->getElementsByClassContains($a, 'span', 'ccl-');
                                    $descriptions = [];

                                    foreach ($spanNodes as $spanNode) {
                                        $descriptions[] = $spanNode->textContent;
                                    }

                                    $links[] = [
                                        'link' => $link,
                                        'descriptions' => $descriptions,
                                    ];
                                }

                                $urlResults[] = [
                                    'cat' => $cat,
                                    'links' => $links,
                                ];
                            }
                        }
                    }

                    dd($urlResults);
                }


            }
        }


    }
// Helper function to get elements by class containing a specified prefix
function getElementsByClassContains($element, $tagName, $prefix) {
    $elements = [];

    foreach ($element->getElementsByTagName($tagName) as $node) {
        $class = $node->getAttribute('class');

        if (strpos($class, $prefix) !== false) {
            $elements[] = $node;
        }
    }

    return $elements;
}
    function getUrls($url){
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

        // $spanTexts = $xPath->evaluate("//span[contains(concat(' ', normalize-space(@class), ' '), ' ShortcutTileHorizontal')]/span[@class]/text()");
        // foreach ($spanTexts  as $spanText) {
        //     // $collectionUrls[] = $URLDelivery.'/&collection='.$this->stringReplacement($spanText->nodeValue);
        //     $collectionUrls[] = $spanText->nodeValue;
        // }

        // Use XPath to select the script tag with id="__NEXT_DATA__"

        $scriptElements = $this->htmlData($url)->query('//script[@id="__NEXT_DATA__" and @type="application/json"]');
        $collections = [];

        foreach ($scriptElements as $scriptElement) {
            // Extract all occurrences of the content between "id":"collection","value":[" and "]}"
            preg_match_all('/"id":"collection","value":\["(.*?)"\]/s', $scriptElement->nodeValue, $matches);

            if (!empty($matches[1])) {
                // Merge the matches into the array
                $collections = $this->stringReplacement(array_merge($collections, array_unique($matches[1])));
            }
        }

        return $collections;
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
        // Replace spaces with '-'
        $stringReplace = preg_replace('/[ _]/', '-', $string);
        // Remove special characters except '-'
        $cleanString = preg_replace('/[^A-Za-z0-9\-]/', '', $stringReplace);

        return $cleanString;
    }

}
