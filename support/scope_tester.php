<?php

require __DIR__ .'/../Scope.php';
use Approach\path as path;
use Approach\deploy as deploy;
use Approach\runtime as runtime;
use Approach\Scope as Scope;




$array = [path::installed->value => '/srv/Approach2/'];

$CurrentScope = new Scope(
    path : [
        path::project       => '/srv/suiteux.suitespace.corp/',
    ],
    deployment: [
        deploy::base        => 'suiteux.suitespace.corp',        // deploy::session     => 'suiteux.suitespace.corp',
        deploy::orchestra   => 'suitespace.corp',
        deploy::ensemble    => 'system-00.suitespace.corp',
        deploy::instrument  => 'edge'
    ],
    mode : runtime::staging
);
var_dump( Scope::GetApproach() );
var_dump( Scope::GetPath(path::compositions) );
