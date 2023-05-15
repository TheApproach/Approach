<?php

namespace Approach;

/** 
 * @object Approach\nullstate		-	Enumeration of null states
 * @package Approach
 * @subpackage Approach\core
 * @version 2.0.-1 beta
 * 
 * Each value represents a different state of null or undefined in a program. 
 * This enumeration can be used in code to represent and handle different null states. 
 * The null state is a state of a variable or object that is not defined, or is not yet defined.
 * 
 */

enum nullstate: int	
{
	case null				= 1;	// the	O.G. null, the state itself is unknown
	case defined			= 2;	// object is well-defined
	case undeclared		 	= 3;	// if(x) when x is not declared
	case undefined			= 4;	// let x; if(x > 1) -- x has no value yet
	case undefined_type		= 5;	// state of something that is explicitly not any null state (allows defined)
	case uninitialized		= 6;	// let x; if(x.a > 1) -- calling member of undefined;
	case empty				= 7;	// state of a node with null nodes and null content
	case stalled			= 8;	// state of a connection which is not yet established -- or due to instability
	case virtual			= 9;	// placeholder null. ex: new components and their token values are virtual components/tokens before saving
	case ambiguous			= 10;	// function work(x){ x.property }	does code know x has that property somewhere ?
	case becomingnull		= 11;	// state of a value about to be deleted
	case nolongernull		= 12;	// null state was filled since the last check
	case notnull			= 13;	// state of something that is explicitly not any null state (allows defined)
}
