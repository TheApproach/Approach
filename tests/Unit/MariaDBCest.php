<?php

namespace Tests\Unit;

use Approach\Resource\MariaDB\Server;
use Approach\Service\MariaDB\Connector;
use Approach\deploy;
use Approach\nullstate;
use Approach\path;
use Approach\Resource\Resource as Resource;
use Approach\Scope;
use MyProject\Resource\MariaDB\MyData;
use Tests\Support\UnitTester;

/**
 * This is a Codeception Unit Test for the Approach\Service\MariaDB class.
 *
 * 	@package    Approach\Tests\Unit
 * 	@subpackage Approach\Tests\Unit\Service
 * 	@object     Approach\Tests\Unit\Service\MariaDBTest
 *
 * 	@internal
 * 		[ ] This class may be tested for functionality in the future.
 * 		[ ] This class may be tested for security in the future.
 * 		[ ] This class may be tested for documentation in the future.
 * 		[ ] This class may be tested for performance in the future.
 *
 *
 * 	@dependencies
 * 		[ ] \Approach\Service\MariaDB
 * 		[ ] \Approach\Resource\MariaDB\Server
 * 		[ ] \Approach\Resource
 * 		[ ] \Approach\Service
 * 		[ ] \Approach\Scope
 * 		[ ] \Approach\path
 *
 * 	@license    Apache 2.0
 * 	@version    0.0.1-alpha
 * 	@since      2023-02-04
 * 	@see        \Approach\Service\MariaDB
 */
class MariaDBCest
{
    /**
     * @var \Approach\Service\MariaDB
     * @var \Approach\Resource\MariaDB\Server
     * @var \Approach\Resource
     * @var \Approach\Service
     * @var \Approach\Scope
     */
    protected Connector $connector;

    protected $server;
    protected $resource;
    protected $scope;

    public function _before()
    {
        $path_to_project = __DIR__ . '/../../support/test_project';
        $path_to_approach = __DIR__ . '/../../approach';
        $path_to_support = __DIR__ . '/../../support';

        $this->scope = new Scope(
            project: 'MyProject',
            path: [
                path::project->value => $path_to_project,
                path::installed->value => $path_to_approach,
                path::support->value => $path_to_support,
            ],
            deployment: [
                deploy::base->value => 'localhost',
                deploy::ensemble->value => 'localhost',
                deploy::resource->value => 'localhost',
                deploy::resource_user->value => 'root',
            ]
        );

        $this->server = new Server(
            host: 'localhost',  // Scope::GetDeploy( deploy::resource ),
            user: 'root',  // Scope::GetDeploy( deploy::resource_user ),
            port: 3306,
            pass: 'NoobScience',
            database: 'test',
            label: 'MyData',
            // skip_connection: true
        );
    }

    public function _after()
    {
        if (isset($this->connector) && $this->connector->connection->connect_errno > 0) {
            // $this->connector->connection->close();
            $this->connector->disconnect();
        }

        unset($this->connector);
        unset($this->server);
        unset($this->resource);
        unset($this->scope);
    }

    // tests

    public function connectToDatabase(UnitTester $I)
    {
        // connect should have params?
        $state = $this->server->connect();
        $this->connector = $this->server->connector;

        // Check if $state is a MySQLi error number or a nullstate enum instance.
        $I->assertTrue(
            $state instanceof nullstate ||
                is_int($state)
        );

        // If $state was a MySQLi error number, then output the error from the MySQLi connection at connector->connection
        if (!($state instanceof nullstate) && $state > 0) {
            $I->outputError($this->connector->connection->connect_error);
        } elseif ($state instanceof nullstate && $state !== nullstate::defined) {
            switch ($state) {
                case nullstate::undefined:
                    $I->outputError('The connection state was undefined.');
                    break;
                case nullstate::undeclared:
                    $I->outputError('The connection state was undeclared.');
                    break;
                case nullstate::ambiguous:
                    $I->outputError('The connection state was ambiguous.');
                    break;
                case nullstate::null:
                    $I->outputError('The connection state was null.');
                    break;
                default:
                    $I->outputError('The connection state was vey ambiguous.');
                    break;
            }
        }

        // If $state was nullstate::defined, then the connection was successful.
        $I->assertEquals($state, nullstate::defined);
    }

    public function checkServerDiscovery(UnitTester $I)
    {
        /*$this->server->discover();*/

        // Check if the server has a php file at Scope::GetPath( path::project ) /Resource/
    }

    // we really got to get better at doing this instead of echo/print/json lol
    public function tryMyDataFind(UnitTester $I)
    {
        // instead of this...
        // $server = new MyData(pass: 'NoobScience'); // or this
        // even though Resource::find() should I think work after at least a MyData has been connected hmm idk
        $this->server->connector->disconnectAll();

        $r = \MyProject\Resource\MariaDB\MyData::find('MariaDB://localhost/test/names[id: 0..100]');

        // okay let's try
        // $r->load();
    }
    /*public function tryResouceFind(UnitTester $I)*/
    /*{*/
    /*    $r = Resource::find('MariaDB://MyData/test/names[id: 0..100][id]');*/
    /*}*/
}
