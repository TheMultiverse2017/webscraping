<?php

namespace App\Livewire;

use Livewire\Component;

class Scrape extends Component
{
    public $location = '';
    public $result = '' ;
    public function find(){

    }

    public function render()
    {
        return view('livewire.scrape');
    }
}
