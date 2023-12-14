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
    public $message = '';
    public function find(){
        $this->result=$this->scrapeDeliveroo($this->location);
        $this->message="Data Imported";
        return $this->message ;
    }

    public function render()
    {
        return view('livewire.scrape');
    }
}
