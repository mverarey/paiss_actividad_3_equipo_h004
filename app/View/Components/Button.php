<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Button extends Component
{
    public $type;
    public $size;
    public $icon;
    
    public function __construct($type = 'primary', $size = 'md', $icon = null)
    {
        $this->type = $type;
        $this->size = $size;
        $this->icon = $icon;
    }
    
    public function render()
    {
        return view('components.button');
    }
}