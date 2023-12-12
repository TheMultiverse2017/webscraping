<?php

namespace App\Livewire;

use App\Http\Traits\scrapeTrait;
use Livewire\Component;

class Scrape extends Component
{
    use scrapeTrait;

    public $location = '';
    public $result = [] ;

    public function find(){
        $this->result=$this->scrapeDeliveroo($this->location);
    }

    public function render()
    {
        return view('livewire.scrape');
    }
}
