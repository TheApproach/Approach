<?php

namespace Approach;

enum deploy: int
{
    case project        = 0;
    case protocol       = 1;
    case base           = 2;
    case remote         = 3;
    case internal       = 4;
    case local          = 5;
    case platform       = 6;
    case resource       = 7;
    case statics        = 8;
    case services       = 9;

    case statics_alias  = 20;
    case service_alias  = 21;
    case uploads        = 22;

    case orchestra      = 23;
    case session        = 24;
    case instrument     = 25;
    case ensemble       = 26;

    public function get(string $base_url): string
    {
        $_protocol = 'https';

        return match ($this) {
            self::project        => 'approach',
            self::protocol      => $_protocol,
            self::base          => $base_url,
            self::remote        => self::protocol->get($base_url),
            self::internal      => 'example.corp',
            self::local         => 'approach.home',
            self::platform      => $base_url,
            self::resource      => 'spool.project.example.corp',
            self::statics       => 'static.' . $base_url,
            self::services      => 'service.' . $base_url,

            self::statics_alias => $base_url . '/__static',
            self::service_alias => $base_url . '/__api',
            self::uploads       => 'static.' . $base_url . '/uploads',

            self::orchestra     => 'orchestra.private',
            self::session       => 'example.com',
            self::instrument    => 'edge',
            self::ensemble      => 'project.example.corp'
        };
    }
};
