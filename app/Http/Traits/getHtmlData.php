<?php
namespace App\Http\Traits;
use GuzzleHttp\Client;
use DOMDocument;
use DOMXPath;
set_time_limit(0);

trait getHtmlData {
    public function htmlData($url="") {
        $client=new Client();
        $response=$client->request(
            'GET',
            $url
        );
        $responseStatusCode=$response->getStatusCode();
        $responseBody=$response->getBody();
        $html=$responseBody->getContents();
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xPath = new DOMXPath($dom );
        return $xPath;
    }
}
