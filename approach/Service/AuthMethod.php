<?php

namespace Approach\Service;

enum AuthMethod
{
	case None;
	case API_KEY;
	case API_KEY_WITH_ACCOUNT_SECRET;
	case API_KEY_WITH_SESSION_SECRET;
	case Basic;
	case Bearer;
	case Certificate;
	case Digest;
	case KeyCloak;
	case HMAC_SHA256;
	case HOBA;
	case Mutual;
	case Negotiate;
	case OAuth;
	case OpenIDConnect;
	case SAML;
	case SCRAM;
	case TwoFactor;
	case VAPID;
}