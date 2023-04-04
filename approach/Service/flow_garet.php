<?php

namespace Approach\Service;

/**
 * Set the target for an I/O stream, and the format to use for it.
 * For example a target may be file, database, CLI, URL or Approach Service,
 * while the format may be anything you have an encoder and/or decoder for
 * such as: JSON, XML, CSV, List and any Approach Renderable with a parser.
 * 
 */

trait flow
{
    const in = 0;
    const out = 1;

    protected array $format = [
        self::in => format::json,
        self::out => format::json,
    ];

    protected array $target = [
        self::in => null,
        self::out => null,
    ];


    public function setIncomingFormat($format)
    {
        $this->format[self::in] = $format;
    }

    public function setOutgoingFormat($format)
    {
        $this->format[self::out] = $format;
    }

    public function getIncomingFormat()
    {
        return $this->format[self::in];
    }

    public function getOutgoingFormat()
    {
        return $this->format[self::out];
    }

    public function setIncomingTarget($target)
    {
        $this->target[self::in] = $target;
    }

    public function setOutgoingTarget($target)
    {
        $this->target[self::out] = $target;
    }

    public function getIncomingTarget()
    {
        return $this->target[self::in];
    }

    public function getOutgoingTarget()
    {
        return $this->target[self::out];
    }
}
