<?php

namespace MyProject\Resource\ApproachGalera\ApproachDB\compositions;

enum fields: string
{
    case id = 'id';
    case parent = 'parent';
    case type = 'type';
    case pointer = 'pointer';
    case owner = 'owner';
    case alias = 'alias';
    case operator = 'operator';
    case theme = 'theme';
    case content = 'content';
    case auth_needed = 'auth_needed';
    case permissions = 'permissions';
    case meta = 'meta';
    case self = 'self';
    case root = 'root';
    case active = 'active';
    case title = 'title';
    case name = 'name';
    case doEnableURL = 'doEnableURL';
    case showInNav = 'showInNav';
    case mediapath = 'mediapath';
    case descript = 'descript';
    case layout_structure = 'layout_structure';
    case preview_structure = 'preview_structure';
    case type_opts = 'type_opts';
    case theme_opts = 'theme_opts';

    public function getOrdinality($case = null)
    {
        return match ($case)
        {
            $this->id => 0,
            $this->parent => 1,
            $this->type => 2,
            $this->pointer => 3,
            $this->owner => 4,
            $this->alias => 5,
            $this->operator => 6,
            $this->theme => 7,
            $this->content => 8,
            $this->auth_needed => 9,
            $this->permissions => 10,
            $this->meta => 11,
            $this->self => 12,
            $this->root => 13,
            $this->active => 14,
            $this->title => 15,
            $this->name => 16,
            $this->doEnableURL => 17,
            $this->showInNav => 18,
            $this->mediapath => 19,
            $this->descript => 20,
            $this->layout_structure => 21,
            $this->preview_structure => 22,
            $this->type_opts => 23,
            $this->theme_opts => 24,
            default => null
        };
    }

    public function getDefault($case = null)
    {
        return match ($case)
        {
            $this->id => 0,
            $this->parent => 0,
            $this->type => 0,
            $this->pointer => 0,
            $this->owner => 0,
            $this->alias => 0,
            $this->operator => 0,
            $this->theme => 0,
            $this->content => 0,
            $this->auth_needed => 0,
            $this->permissions => 0,
            $this->meta => 0,
            $this->self => 0,
            $this->root => 0,
            $this->active => 0,
            $this->title => 0,
            $this->name => 0,
            $this->doEnableURL => 0,
            $this->showInNav => 0,
            $this->mediapath => 0,
            $this->descript => 0,
            $this->layout_structure => 0,
            $this->preview_structure => 0,
            $this->type_opts => 0,
            $this->theme_opts => 1,
            default => null
        };
    }

    public function isNullable($case = null)
    {
        return match ($case)
        {
            $this->id => 0,
            $this->parent => 0,
            $this->type => 0,
            $this->pointer => 0,
            $this->owner => 0,
            $this->alias => 0,
            $this->operator => 0,
            $this->theme => 0,
            $this->content => 0,
            $this->auth_needed => 0,
            $this->permissions => 0,
            $this->meta => 0,
            $this->self => 0,
            $this->root => 0,
            $this->active => 0,
            $this->title => 0,
            $this->name => 0,
            $this->doEnableURL => 0,
            $this->showInNav => 0,
            $this->mediapath => 0,
            $this->descript => 0,
            $this->layout_structure => 0,
            $this->preview_structure => 0,
            $this->type_opts => 0,
            $this->theme_opts => 1,
            default => null
        };
    }

    public function getType($case = null)
    {
        return match ($case)
        {
            $this->id => 'bigint',
            $this->parent => 0,
            $this->type => 0,
            $this->pointer => 0,
            $this->owner => 0,
            $this->alias => 0,
            $this->operator => 0,
            $this->theme => 0,
            $this->content => 0,
            $this->auth_needed => 0,
            $this->permissions => 0,
            $this->meta => 0,
            $this->self => 0,
            $this->root => 0,
            $this->active => 0,
            $this->title => 0,
            $this->name => 0,
            $this->doEnableURL => 0,
            $this->showInNav => 0,
            $this->mediapath => 0,
            $this->descript => 0,
            $this->layout_structure => 0,
            $this->preview_structure => 0,
            $this->type_opts => 0,
            $this->theme_opts => 'json',
            default => null
        };
    }

    public function getPermissions($case = null)
    {
        return match ($case)
        {
            $this->id => 0,
            $this->parent => 0,
            $this->type => 0,
            $this->pointer => 0,
            $this->owner => 0,
            $this->alias => 0,
            $this->operator => 0,
            $this->theme => 0,
            $this->content => 0,
            $this->auth_needed => 0,
            $this->permissions => 'array',
            $this->meta => 0,
            $this->self => 0,
            $this->root => 0,
            $this->active => 0,
            $this->title => 0,
            $this->name => 0,
            $this->doEnableURL => 0,
            $this->showInNav => 0,
            $this->mediapath => 0,
            $this->descript => 0,
            $this->layout_structure => 0,
            $this->preview_structure => 0,
            $this->type_opts => 0,
            $this->theme_opts => 0,
            default => null
        };
    }
}