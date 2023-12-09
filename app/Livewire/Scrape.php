<?php

namespace App\Livewire;

use App\Http\Traits\scrapeTrait;
use Livewire\Component;
use Sk\Geohash\Geohash;
class Scrape extends Component
{
    use scrapeTrait;

    public $location = '';
    public $result = [] ;
    public $hash = '';
    public $neighbors = [] ;

    public function find(){
        $this->result=$this->scrapeDeliveroo($this->location);
        $g = new Geohash();
        $this->hash = $g->encode($this->result['latitude'],$this->result['longitude']);
        $this->neighbors = $g->getNeighbors($this->hash);
    }

    public function render()
    {
        return view('livewire.scrape');
    }
}
