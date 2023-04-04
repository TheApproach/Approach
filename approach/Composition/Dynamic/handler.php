<?php

namespace Approach\Composition\Dynamic;

use Approach\Composition\Composition;
use Approach\nullstate;

class handler extends Composition
{

    public function __construct()
    {
        // echo PHP_EOL;
        // var_export("MADE IT TO ". get_class());
        // echo PHP_EOL;

        parent::__construct();

        // $this->publish();
    }


    public static function Route(string $url, $silent = false): Composition|nullstate
    {
        return new self();
    }
}
