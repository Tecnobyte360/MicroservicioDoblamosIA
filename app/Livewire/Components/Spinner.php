<?php

namespace App\Livewire\Components;

use Livewire\Component;

class Spinner extends Component
{
  
    public string $target = '';

    public string $text = 'Procesando...';

    public function render()
    {
        return view('livewire.components.spinner');
    }
}
