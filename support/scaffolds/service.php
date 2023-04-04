<?php

use \Approach\Service\Service;

class MyService extends Service{
	public function ProcessJSON($activity): array
    {
		$response = [];
		$success = false;
		$support = $activity['incoming']['support'] ?? [];
		$command = $activity['incoming']['command'] ?? [];

		// your logic here
		$success = true;

		$response['success'] = $success;
		return $response; //Return value should be a nested array that will be json_encode into a JSON object
	}
}

$srv = new MyService();

$srv->Receive();		// Receives any HTTP GET/POST/PUSH requests | JSON/XML API Requests, places in $srv->Activity['incoming']
$srv->Process();		// Runs ProcessJSON for JSON output, Runs ProcessXML for XML output. Set $srv->Directive to direct output format json_service | xml_service
$srv->Respond();        // Outputs response to client