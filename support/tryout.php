<?php

class Dataset
{
    public function __construct(public array $options){
        $default_value = "hi";

        $options['foo'] = $options['foo'] ?? $default_value;

        echo $options['foo'];
    }
}

$d = new Dataset(options: ['foo'=>'bar']);
//echo $d->options['foo'];