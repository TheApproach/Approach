<?php


namespace Tests\Unit;

use \Approach\Service\Service;
use Tests\Support\UnitTester;
use \Approach\Service\Target;
use \Approach\Service\Format;
use \Approach\Service\CSV;

use \Approach\Scope;
use \Approach\path;

class ServiceCest
{
    private $scope;
    private $file_path;
    private $example_contents;
    private $example_json_decoded;
    private $example_json_encoded;
    private $expected_json_variable;
    private $example_output_file_path;
    private $example_output_file_path_csv;
    private $example_output_file_path_json;
    private $example_file_path;
    private $example_file_path_json;
    private $example_file_path_csv;
    private $example_json_contents;
    private $example_csv_contents;



    public function _before(UnitTester $I)
    {
        $path_to_project = __DIR__ . '/../..';
        $path_to_approach = __DIR__ . '/../../approach/';
        $path_to_support = __DIR__ . '/../../support/';

        // echo PHP_EOL . PHP_EOL . 'PATH TO PROJECT: ' . $path_to_project . PHP_EOL . PHP_EOL;
        // echo PHP_EOL . PHP_EOL . 'PATH TO APPROACH: ' . $path_to_approach . PHP_EOL . PHP_EOL;

        $this->scope = new Scope(
            path: [
                path::project->value        =>  $path_to_project,
                path::installed->value      =>  $path_to_approach,
                path::support->value        =>  $path_to_support,
            ],
        );

        $this->example_file_path = Scope::GetPath(path::support) . 'service/tests/example';
        $this->example_file_path_json = $this->example_file_path . '.json';
        $this->example_file_path_csv = $this->example_file_path . '.csv';


        $this->example_json_contents = file_get_contents($this->example_file_path_json);
        $this->example_csv_contents = file_get_contents($this->example_file_path_csv);

        $this->example_json_decoded = json_decode($this->example_json_contents, true);
        $this->example_json_encoded = json_encode($this->example_json_decoded);

        $this->expected_json_variable = json_encode($this->example_json_decoded);

        $this->example_output_file_path_json = Scope::GetPath(path::support) . 'service/tests/example_copy.json';
        $this->example_output_file_path_csv = Scope::GetPath(path::support) . 'service/tests/example_copy.csv';


        if (file_exists($this->example_output_file_path_json)) {
            unlink($this->example_output_file_path_json);
        }
        if (file_exists($this->example_output_file_path_csv)) {
            unlink($this->example_output_file_path_csv);
        }
    }

    public function _after(UnitTester $I)
    {
        if (file_exists($this->example_output_file_path_json)) {
            unlink($this->example_output_file_path_json);
        }
        if (file_exists($this->example_output_file_path_csv)) {
            unlink($this->example_output_file_path_csv);
        }
    }

    public function createServiceInstance(UnitTester $I)
    {
        $service = new Service(auto_dispatch: false);

        $I->assertInstanceOf(Service::class, $service);
    }

    public function ReceivePayloadTargetFromFile(UnitTester $I)
    {
        $example_payload = [$this->example_json_contents];

        $service = new Service(
            auto_dispatch: false,
            target_in: target::file,
            input: $this->example_file_path_json
        );

        $service->Request();
        $service->connect(true);
        $service->Receive();

        $I->assertEquals($example_payload, $service->payload);
    }

    public function fileToStdout(UnitTester $I)
    {

        $service = new Service(
            auto_dispatch: false,
            target_in: target::file,
            target_out: target::stream,
            format_out: format::json,
            format_in: format::json,
            input: $this->example_file_path_json
        );

        // Perform a command that causes some standard output
        $output = $service->dispatch();
        // $I->seeInShellOutput($this->example_encoded);

        #assert that the stdout contains the example encoded json
        $I->assertStringContainsString($this->example_json_encoded, $output[0]);
    }



    public function fileToVariable(UnitTester $I)
    {

        $service = new Service(
            auto_dispatch: false,
            target_in: target::file,
            target_out: target::variable,
            input: $this->example_file_path_json
        );

        $payload = $service->dispatch();
        $variable = $payload[0];

        # assert that the variable is valid json 
        $I->assertJson($variable);

        # assert that the variable contains the exaple encoded json
        $I->assertStringContainsString($this->example_json_encoded, $variable);

        # assert that the variable is the expected variable
        $I->assertEquals($this->expected_json_variable, $variable);
    }

    public function fileToFile(UnitTester $I)
    {
        $service = new Service(
            auto_dispatch: false,
            target_in: target::file,
            target_out: target::file,
            format_out: format::json,
            format_in: format::json,
            input: $this->example_file_path_json,
            output: [$this->example_output_file_path_json],
        );

        $service->dispatch();

        # assert that the destination file exists
        $I->assertFileExists($this->example_output_file_path_json);

        # assert that the destination file contains the example encoded json
        $I->assertStringContainsString($this->example_json_encoded, file_get_contents($this->example_output_file_path_json));
    }

    public function jsonFileToCsvFile(UnitTester $I)
    {
        CSV::register();
        #assert that the destination file does not exist
        $I->assertFileDoesNotExist($this->example_output_file_path_csv);

        $service = new Service(
            target_in: target::file,
            target_out: target::file,
            format_in: format::json,
            format_out: format::csv,
            input: $this->example_file_path_json,
            output: [$this->example_output_file_path_csv],
        );

        $service->dispatch();

        # assert that the destination file exists
        $I->assertFileExists($this->example_output_file_path_csv);

        # assert that the destination file contains the example encoded json
        $I->assertEquals($this->example_csv_contents, file_get_contents($this->example_output_file_path_csv));
    }
}
