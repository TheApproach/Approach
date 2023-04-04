<?php

namespace Approach;


enum runtime :int
{
    case prototype = 0;
    case development    = 1;
    case staging    = 2;
    case debug  = 3;
    case production = 4;
    case archive    = 5;
    case running    = 6;
    case await  = 7;
    case fetch  = 8;
    case reply  = 9;
    case barrier    = 10;
    case blocking   = 11;
    case locked = 12;
    case unlocking  = 13;
    case connect    = 14;
    case authenticate   = 15;
    case authorize  = 16;
    case branch = 17;
    case merge  = 18;
    case collapse   = 19;
    case resume = 20;
    case pause  = 21;
    case idle   = 22;
    case sleep  = 23;
    case force_exit = 24;
    case clean_exit = 25;
    case recoverable    = 26;
    case recovering = 27;
    case need_sync  = 28;
    case syncing    = 29;
    case failure    = 30;
    case diagnosing = 31;
    case anomaly    = 32;
    case rendering  = 33;
    case render_error   = 34;
    case imprinting = 35;
    case imprint_error  = 36;
    case sourcing   = 37;
    case source_error   = 38;
    case component_building = 39;
    case component_error    = 40;
    case composing  = 41;
    case composition_error  = 42;
    case serving    = 43;
    case service_error  = 44;

/*
    public function get(string $runtime_mode_or_runtime_state): self
    {
        return match ($this) {
            // modes
            // the current run can operate conditionally based on modes
            self::prototype     =>  self::prototype;
            self::development   =>  self::development;
            self::staging       =>  self::staging;
            self::debug         =>  self::debug;
            self::production    =>  self::production;
            self::archive       =>  self::archive;
            self::running       =>  self::running;
            self::await       =>  self::await;
            self::fetch       =>  self::fetch;
            self::reply       =>  self::reply;
            self::barrier  =>  self::barrier;
            self::blocking  =>  self::blocking;
            self::locked  =>  self::locked;
            self::unlocking  =>  self::unlocking;
            self::connect       =>  self::connect;
            self::authenticate       =>  self::authenticate;
            self::authorize       =>  self::authorize;
            self::branch  =>  self::branch;
            self::merge  =>  self::merge;
            self::collapse  =>  self::collapse;
            self::resume  =>  self::resume;
            self::pause  =>  self::pause;
            self::idle  =>  self::idle;
            self::sleep  =>  self::sleep;
            self::force_exit  =>  self::force_exit;
            self::clean_exit  =>  self::clean_exit;
            self::recoverable  =>  self::recoverable;
            self::recovering  =>  self::recovering;
            self::need_sync   =>  self::need_sync;
            self::syncing  =>  self::syncing;
            self::failure  =>  self::failure;
            self::diagnosing  =>  self::diagnosing;
            self::anomaly  =>  self::anomaly;
            self::rendering  =>  self::rendering;
            self::render_error  =>  self::render_error;
            self::imprinting  =>  self::imprinting;
            self::imprint_error  =>  self::imprint_error;
            self::sourcing  =>  self::sourcing;
            self::source_error  =>  self::source_error;
            self::component_building  =>  self::component_building;
            self::component_error  =>  self::component_error;
            self::composing  =>  self::composing;
            self::composition_error  =>  self::composition_error;
            self::serving  =>  self::serving;
            self::service_error  =>  self::service_error
        };
    }
    */
}
