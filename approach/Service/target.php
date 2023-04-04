<?php

namespace Approach\Service;

/* 
	Create caseants for the different types of targets
	used by the service class for I/O.
*/

enum target: int
{
    case file = 0;
    case database = 1;
    case cli = 2;
    case api = 3;
    case url = 4;
    case service = 5;
    case variable = 6;
    case stream = 7;
    case default = 8;
}
