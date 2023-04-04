<?php

namespace Approach;

use \Approach\Scope;

enum path: int
{
    case installed			= 1;
    case project			= 2;
    case components			= 3;
    case compositions		= 4;
    case route			 	= 5;
    case resource			= 6;
    case imprint			= 7;
    case props			 	= 8;
    case render			 	= 9;
    case services			= 10;
    case profiles			= 11;
    case reports			= 12;
    case tasks			 	= 13;
    case statics			= 14;
    case themes			 	= 15;
    case tools			 	= 16;
    case uploads			= 17;
    case support			= 18;
	case pattern			= 19;
	case cache				= 20;
	case extension			= 21;
	case community			= 22;
	case vendor				= 23;
	case wild				= 24;
	
	

    // e.g. path::statics->path($project_dir);    // '/srv/project/static/'
    public function get(string $project_dir = '/srv/project/src'): string
    {
        return match ($this) {
            self::installed         => Scope::$context[context::path->value][self::installed->value] 	?? '/usr/local/approach/approach-' . MAJOR_VERSION . '-' . MINOR_VERSION,
            self::project           => Scope::$context[context::path->value][self::project->value] 		?? $project_dir,
            self::components        => Scope::$context[context::path->value][self::components->value] 	?? $project_dir . '/Component/',
            self::compositions      => Scope::$context[context::path->value][self::compositions->value] ?? $project_dir . '/Composition/',
            self::route             => Scope::$context[context::path->value][self::route->value] 		?? $project_dir . '/Composition/',
            self::resource          => Scope::$context[context::path->value][self::resource->value] 	?? $project_dir . '/Resource/',
            self::imprint           => Scope::$context[context::path->value][self::imprint->value] 		?? $project_dir . '/Imprint/',
            self::props             => Scope::$context[context::path->value][self::props->value] 		?? $project_dir . '/Imprint/props/',
            self::render            => Scope::$context[context::path->value][self::render->value] 		?? $project_dir . '/Render/',
            self::services          => Scope::$context[context::path->value][self::services->value] 	?? $project_dir . '/Service/',
            self::profiles          => Scope::$context[context::path->value][self::profiles->value] 	?? $project_dir . '/Service/profile/',
            self::reports           => Scope::$context[context::path->value][self::reports->value] 		?? $project_dir . '/Service/report/',
            self::tasks             => Scope::$context[context::path->value][self::tasks->value] 		?? $project_dir . '/Service/tasks/',
            self::statics           => Scope::$context[context::path->value][self::statics->value] 		?? $project_dir . '/../static/',
            self::themes            => Scope::$context[context::path->value][self::themes->value] 		?? $project_dir . '/../static/themes/',
            self::tools             => Scope::$context[context::path->value][self::tools->value] 		?? $project_dir . '/../static/tools/',
            self::uploads           => Scope::$context[context::path->value][self::uploads->value] 		?? $project_dir . '/../static/uploads/',
			self::support           => Scope::$context[context::path->value][self::support->value] 		?? $project_dir . '/../support/',
            self::pattern           => Scope::$context[context::path->value][self::pattern->value] 		?? $project_dir . '/../support/pattern/',
            self::cache           	=> Scope::$context[context::path->value][self::cache->value] 		?? $project_dir . '/../support/cache/',
			self::extension         => Scope::$context[context::path->value][self::extension->value] 	?? $project_dir . '/../support/lib/extension/',
            self::community         => Scope::$context[context::path->value][self::community->value] 	?? $project_dir . '/../support/lib/community/',
			self::vendor           	=> Scope::$context[context::path->value][self::vendor->value] 		?? $project_dir . '/../support/lib/vendor/',
			self::wild           	=> Scope::$context[context::path->value][self::wild->value] 		?? $project_dir . '/../support/lib/wild/'
        };
    }

	public function set(string $value): void
	{
		Scope::$context[context::path][$this->value] = $value;
	}

};
