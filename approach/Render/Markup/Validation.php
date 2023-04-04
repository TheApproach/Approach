<?php

namespace Approach\Render\Markup;

trait Validation
{
    public function AreNodesValid()
    {
        return true;                // return boolean true - or the first invalid attribute key
    }
}