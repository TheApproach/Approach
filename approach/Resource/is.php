<?php

namespace Approach\Resource;

enum is:int
{
	case NOT_NULL = -7;
	case NOT_LIKE = -6;
	case NOT_IN = -5;
	case NOT_BETWEEN = -4;
	
	case LESS_THAN = -3;
	case LESS_THAN_OR_EQUAL_TO = -2;
	case NOT_EQUAL_TO = -1;
	case EQUAL_TO = 1;
	case GREATER_THAN_OR_EQUAL_TO = 2;
	case GREATER_THAN = 3;
	case BETWEEN = 4;
	case IN = 5;
	case LIKE = 6;
	case NULL = 7;
}