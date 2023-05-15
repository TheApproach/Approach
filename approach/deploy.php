<?php

namespace Approach;

use ArrayAccess;

enum deploy : int
{
    case project        = 0;
    case protocol       = 1;
    case base           = 2;
    case remote         = 3;
    case internal       = 4;
    case platform       = 5;
    case resource       = 6;
	case resource_user	= 7;
    case statics        = 8;
    case services       = 9;
    case statics_alias  = 10;
    case service_alias  = 11;
    case uploads        = 12;
    case orchestra      = 13;
    case ensemble       = 14;
    case instrument     = 15;
    case session        = 16;

    public function get(string $base_url): string
    {
		// TODO: Take advantage of Scope::$context[] to retrieve and set these bindings

        $_protocol = 'https';
        return match ($this) {
            self::project       => Scope::$context[context::deploy->value][$this->value] ??	'MyProject',
            self::protocol      => Scope::$context[context::deploy->value][$this->value] ??	$_protocol,
            self::base          => Scope::$context[context::deploy->value][$this->value] ??	$base_url,
            self::remote        => Scope::$context[context::deploy->value][$this->value] ??	self::protocol->get($base_url),
            self::internal      => Scope::$context[context::deploy->value][$this->value] ??	'my.home',	// Like remote base, but for LAN facing traffic
            self::platform      => Scope::$context[context::deploy->value][$this->value] ??	$base_url,	// When hosting many sites, this is the platform's true base URL, whether remote or internal
			self::resource      => Scope::$context[context::deploy->value][$this->value] ??	ini_get('mysqli.default_host') ?? 'data.root.my.home',
			self::resource_user => Scope::$context[context::deploy->value][$this->value] ??	ini_get('mysqli.default_user') ?? 'myhome-resources',			
            self::statics       => Scope::$context[context::deploy->value][$this->value] ??	'static.' . $base_url,
            self::services      => Scope::$context[context::deploy->value][$this->value] ??	'service.' . $base_url,
            self::statics_alias => Scope::$context[context::deploy->value][$this->value] ??	$base_url . '/__static',
            self::service_alias => Scope::$context[context::deploy->value][$this->value] ??	$base_url . '/__api',
            self::uploads       => Scope::$context[context::deploy->value][$this->value] ??	'static.' . $base_url . '/uploads',
            self::orchestra     => Scope::$context[context::deploy->value][$this->value] ??	'orchestra.private',			// For internal communication between services
            self::ensemble      => Scope::$context[context::deploy->value][$this->value] ??	'myproject.my.home',			// This project's internal DNS name
            self::instrument    => Scope::$context[context::deploy->value][$this->value] ??	'edge-00.myproject.my.home', // This instance's internal DNS name
            self::session       => Scope::$context[context::deploy->value][$this->value] ??	'myproject.my.home',			// Session Identifier
        };
	}
}