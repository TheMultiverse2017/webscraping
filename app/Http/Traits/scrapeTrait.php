<?php
namespace App\Http\Traits;

use App\Models\scraping;
use GuzzleHttp\Client;
use Sk\Geohash\Geohash;
use Sunra\PhpSimple\HtmlDomParser;
use DOMDocument;
use DOMXPath;
use GuzzleHttp\Exception\RequestException;
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
                                    $link = 'https://deliveroo.co.uk'.$a->getAttribute('href');

                                    //PAGFE SCRAPE START
                                    $client = new Client();
                                    $response = $client->request('GET', $link);

                                    $responseStatusCode = $response->getStatusCode();
                                    $responseBody = $response->getBody();
                                    $html = $responseBody->getContents();

                                    $dom = new DOMDocument();
                                    @$dom->loadHTML($html);

                                    $xPath = new DOMXPath($dom);

                                    // Fetching title (H1)
                                    $title = $xPath->query('//h1')->item(0)->nodeValue;


                                    // Fetching timeData
                                    $timeDataSpans = $xPath->query('//div[@class="UILines-eb427a2507db75b3 ccl-2d0aeb0c9725ce8b ccl-45f32b38c5feda86"][1]//span');
                                    $timeData = implode(' | ', array_map(function ($node) {
                                        $nodeValue = $this->cleanString($node->nodeValue);
                                        return $this->isValidString($nodeValue) ? $nodeValue : null;
                                    }, iterator_to_array($timeDataSpans)));

                                    // Fetching distanceOpeningData
                                    $distanceOpeningDataSpans = $xPath->query('//div[@class="UILines-eb427a2507db75b3 ccl-2d0aeb0c9725ce8b ccl-45f32b38c5feda86"][2]//span');
                                    $distanceOpeningData = implode(' | ', array_map(function ($node) {
                                        $nodeValue = $this->cleanString($node->nodeValue);
                                        return $this->isValidString($nodeValue) ? $nodeValue : null;
                                    }, iterator_to_array($distanceOpeningDataSpans)));

                                    $deliveryTimeSpans = $xPath->query('//span[@class="ccl-649204f2a8e630fd ccl-a396bc55704a9c8a ccl-1672da51ae4fc4b6"]');
                                    $deliveryTime = implode(' | ', array_map(function ($node) {
                                        $nodeValue = $this->cleanString($node->nodeValue);
                                        return $this->isValidString($nodeValue) ? $nodeValue : null;
                                    }, iterator_to_array($deliveryTimeSpans)));


                                    $result = [
                                        'title' => $this->cleanString($title),
                                        'timeData' => $timeData,
                                        'distanceOpeningData' => $distanceOpeningData,
                                        'deliveryTime' => $deliveryTime,
                                    ];

                                    // dd($result);
                                    //PAGFE SCRAPE END

                                    // Modify the code to fetch descriptions within span with class 'ccl-' inside div 'BadgesOverlay-b3276e198d69aa9e'
                                    $xpath = new DOMXPath($a->ownerDocument);
                                    $relatedDivNode = $xpath->query(".//div[contains(@class, 'BadgesOverlay-b3276e198d69aa9e')]", $a)->item(0);
                                    $descriptions = [];

                                    if ($relatedDivNode) {
                                        $spanNodes = $xpath->query(".//span[starts-with(@class, 'ccl-')]", $relatedDivNode);

                                        foreach ($spanNodes as $spanNode) {
                                            $descriptions[] = $spanNode->textContent;
                                            if (!empty($description)) {
                                                $descriptions[] = $description;
                                            }
                                        }
                                    }

                                    $links[] = [
                                        'cat' => $cat,
                                        'link' => $link,
                                        'descriptions' => $descriptions,
                                        'data' => $result,
                                    ];
                                }

                                $urlResults[] = [
                                    'cat' => $cat,
                                    'links' => $links,
                                ];
                                scraping::create([
                                    'data'=>json_encode($urlResults,true),
                                ]);

                            }
                        }
                    }
                }



            }
        }


    }
// Function to clean up a string
function cleanString($str) {
    // Remove non-printable characters and trim spaces
    return trim(preg_replace('/[^\x20-\x7E]/', '', $str));
}
// Function to check if a string is empty or consists only of certain characters
function isValidString($str) {
    return !empty($str) && !preg_match('/^[|·\s]+$/', $str);
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
