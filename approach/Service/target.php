<?php

namespace Approach\Service;

/* 
	Create caseants for the different types of targets
	used by the service class for I/O.
*/

enum target: int
{
    // streamable targets
    case stream 		= 0;
    case transfer 		= 1;
    case route          = 2;    
    case file 			= 3;
    case cli 			= 4;
    case api 			= 5;
    case url 			= 6;

    // passtrough targets
    case resource 		= 7;
    case service 		= 8;
    case variable 		= 9;	
}