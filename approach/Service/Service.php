<?php

/*************************************************************************

APPROACH

 *************************************************************************/

namespace Approach\Service;

use \Approach\Scope;
use \Traversable;
use \Approach\path;
use \Approach\Composition\Composition as Composition;
use Approach\Render\Node;

// trait ServiceProperties
// {
// 	public static string $service_name = 'service';
// 	public static string $service_description = 'A service';
// 	public static string $service_version = '1.0.0';
// 	public static string $service_author = 'Approach';
// 	public static string $service_author_email = '
// }

/*
    - extends from the Branch class and uses the waitable trait, as required.
    - has constants for extracting, encoding, translating, and performing known actions, as required. These constants are stored in static properties of the class.
    - has a constructor that takes an activity constant and a flow constant, as required. The constructor sets these values as instance properties.
    - has a Receive() method that performs extract, transform, and load operations on input data, as required. The method uses the await() method provided by the FiberManager trait to suspend the Fiber and wait for the result of the async operation.
    - has a Process() method that manages the underlying Branch logic and aggregates the output of child nodes into a payload property, as required. The method uses the create() and await() methods provided by the FiberManager trait to create child Fibers and wait for their results.
    - has a Respond() method that encodes and sends the payload using the specified format, as required. The method uses the await() method provided by the FiberManager trait to suspend the Fiber and wait for the result of the async operation.
*/

class Service extends Branch
{
    const STDIN = 'php://stdin';
    const STDOUT = 'php://stdout';
    const STDERR = 'php://stderr';

    public flow $flow = flow::in;
    public bool $auto_dispatch = true;
    public format $format_in = format::json;
    public format $format_out = format::json;
    public target $target_in = target::transfer;
    public target $target_out = target::transfer;
    public $input = null;
    public $output = null;
    public mixed $metadata = [];
    public ?bool $register_connection = true;

    use connectivity;
    public static Node $protocols;

    // use waitable;
    public mixed $payload = null;

    public function __construct(
        ?flow $flow = null,
        ?bool $auto_dispatch = null,
        ?format $format_in = null,
        ?format $format_out = null,
        ?target $target_in = null,
        ?target $target_out = null,
        $input = null,
        $output = null,
        mixed $metadata = null,
        ?bool $register_connection = null
    ) {

        $this->flow = $flow ?? $this->flow;
        $this->auto_dispatch = $auto_dispatch ?? $this->auto_dispatch;
        $this->format_in = $format_in ?? $this->format_in;
        $this->format_out = $format_out ?? $this->format_out;
        $this->target_in = $target_in ?? $this->target_in;
        $this->target_out = $target_out ?? $this->target_out;
        $this->input = $input ?? $this->input;
        $this->output = $output ?? $this->output;
        $this->metadata = $metadata ?? $this->metadata;
        $this->register_connection = $register_connection ?? $this->register_connection();


        if (!Decode::has($this->format_in)) {
            throw new \InvalidArgumentException(sprintf('No decoder for %s ($format_in) registered. Register decoder by using Decode::register()', $this->format_in->name));
        }

        if (!Encode::has($this->format_out)) {
            throw new \InvalidArgumentException(sprintf('No encoder for %s ($format_out) registered. Register encoder by using Encode::register()', $this->format_out->name));
        }

        // $this->connect($register_connection);

        if ($auto_dispatch) {
            return $this->dispatch();
        }
    }

    public static function __static_init()
    {
        self::$protocols = new Node();
    }

    public function dispatch()
    {
        // Request() should generally be blank if no prefetch API request is required
        // Ex: User upload, AJAX request, etc.
        // Can also be an interpreter for the incoming command argc/argv/$_GET/$_POST/$_SERVER/$_ENV, etc.
        // This is the first step in the process when fetching the command flow itself is also being handled by the service
        $this->Request();

        // Establishes communication with the connected source and preps metadata
        $this->connect($this->register_connection);

        // Should get records here from the connected source
        // Transforms source items into records
        $this->Receive();

        // Should have the records by this point
        $this->PreProcess();

        // decode the payload using the specified format: $format_in
        $this->payload = Decode::$as[$this->format_in->value]($this->payload);

        // process the payload
        $this->Process();

        // encode the payload using the specified format: $format_out
        $this->payload = Encode::$as[$this->format_out->value]($this->payload);

        $this->PostProcess();

        return $this->Respond();
    }

    public function Request(array $metadata = null)
    {
    }

    public function connect($register_connection = true)
    {
        $this->input = $this->input ?? Service::STDIN;
        $this->output = $this->output ?? Service::STDOUT;

        // Normalize the input to an array, placing single values in the first index of the array
        if (!is_array($this->input)) {
            $this->input = array($this->input);
        }
        // Normalize the output to an array, placing single values in the first index of the array
        if (!is_array($this->output)) {
            $this->output = array($this->output);
        }

        $connected = false;
        if ($register_connection) {
            $this->register_connection();
        }

        $this->connected = true;
        return true;
    }

    public function register_connection()
    {
        $proto = static::getProtocol();

        if (!isset(Service::$protocols))
            Service::$protocols = new Node();
        if (!isset(Service::$protocols[$proto]))
            Service::$protocols[$proto] = new Node();

        // $num_connected = count(Service::$protocols[$proto][$this->alias ?? $this->_render_id]->nodes);
        // if( static::$connection_limit !== null && $num_connected >= static::$connection_limit ){
        // 	$this->disconnect();
        // 	$this->ServiceException('already_connected', static::class . '::connect()', '');
        // }
        // else{
        Service::$protocols[$proto][$this->alias ?? $this->_render_id] = $this;
        // }
    }

    public function Receive(array $payload = null): void
    {
        $this->payload = $payload ?? $this->payload;

        switch ($this->target_in) {
            case target::route:
                #serve composition from url
                foreach ($this->input as $input) {
                    $this->payload[] = Composition::Route($input);
                }
                break;
            case target::file:
            case target::stream:
            case target::transfer:
            case target::api:
                $this->stream_in();
                break;
            case target::variable:
                $this->payload = $this->input;
                break;
            case target::resource:
                foreach ($this->input as $i => $input) {
                    $this->payload[] = new $this->input(...$this->metadata[$i]); // maybe ??
                }
                break;
            case target::transfer:
                //Fetch the matching import file from cloud storage, save locally to path.get(path::files) and set the file_path property to the local path
                foreach ($this->input as $i => $transferred_file) {
                    // Get the filename from $transferred_file
                    $filename = pathinfo($transferred_file, PATHINFO_FILENAME);
                    file_put_contents(
                        Scope::GetPath(path::project) . '/support/files/' . $filename, // temporarily save the file to the project's support/files directory until path.temp is implemented
                        $this->payload[$i]
                    );
                    $this->input[$i] = Scope::GetPath(path::project) . '/support/files/' . $filename;
                }
                break;
            default:
                throw new \Exception('Unsupported input target');
                break;
        }
    }

    public function PreProcess(array $payload = null): void
    {
        $this->payload = $payload ?? $this->payload;
    }

    public function Process(array $payload = null): void
    {
        $this->payload = $payload ?? $this->payload;
    }

    public function PostProcess(array $payload = null): void
    {
        $this->payload = $payload ?? $this->payload;
    }

    public function Respond(array $payload = null): mixed
    {
        $this->payload = $payload ?? $this->payload;

        switch ($this->target_out) {
            case target::stream:
            case target::transfer:
            case target::route:
            case target::file:
            case target::cli:
            case target::api:
            case target::url:
                return $this->stream_out();
                break;
            case target::variable:
            default:
                return $this->payload;
                break;
        }
    }

    function ServiceException($mode, $ThrowingService = 'Service', $key = '')
    {
        if ($mode == 'require') {
            return $key . ' is a required value for ' . $ThrowingService . ' to run properly.';
        }
        if ($mode == 'already_connected') {
            return $ThrowingService . ':' . $this->_render_id . ' is already connected but connect() was called with $register_connections == true and connection limit has been met.';
        }
    }

    public static function getProtocol()
    {
        // Get the class path
        $classpath = explode('\\', static::class);

        // Scan for the first instance of 'Service' and return the next element as the protocol
        foreach ($classpath as $i => $class) {
            if ($class == 'Service') {
                return $classpath[$i + 1];
            }
        }

        return 'Service';
    }


    public static function disconnectAll()
    {
        $proto = static::getProtocol();

        foreach (Service::$protocols[$proto]->nodes as $alias => $connection) {
            if ($connection instanceof \Approach\Service\Service)
                $connection->disconnect();
            else {
                throw new \Exception(
                    'Protocol should always be a Service. Ambiguous node found: ' . $proto . ', alias: ' . $alias
                );
            }
        }
    }

    public static function disconnectAllExcept($alias = null, $aliases = [])
    {
        $proto = static::getProtocol();

        foreach (Service::$protocols[$proto] as $a => $connections) {
            if (in_array($a, $aliases) || $a == $alias)
                continue;

            foreach ($connections->nodes as $connection) {
                $connection->disconnect();
            }
            $connections->disconnect();
        }
    }

    public function disconnect()
    {

        if (is_resource($this->stream_in)) {
            fclose($this->stream_in);
        }
        if (is_resource($this->stream_out)) {
            fclose($this->stream_out);
        }

        foreach ($this->nodes as $branch) {
            if ($branch instanceof \Approach\Service\Service)
                $branch->disconnect();
        }
        unset(static::$connections[$this->_render_id]);
        $this->connected = false;
        return true;
    }

    function __destruct()
    {
        // close stream contexts and connections
        $this->disconnect();
    }

    public function stream_in($stream_in = NULL)
    {
        //Allow user to use stream_context_set_option on $this->stream_in

        foreach ($this->input as $i => $input) {
            $offset = 0;
            $chunk_size = NULL;
            $use_include_path = false;

            // check if $metadata provides offset, chunk or include_path at the current index
            if (isset($this->metadata[$i]['offset'])) {
                $offset = $this->metadata[$i]['offset'];
            }
            if (isset($this->metadata[$i]['chunk'])) {
                $chunk_size = $this->metadata[$i]['chunk'];
            }
            if (isset($this->metadata[$i]['include_path'])) {
                $use_include_path = $this->metadata[$i]['include_path'];
            }

            $this->payload[] = file_get_contents($input, $use_include_path, $stream_in, $offset, $chunk_size);
        }

        return $this->payload;
    }

    public function stream_out($stream_out = NULL, array $metadata = null)
    {
        foreach ($this->output as $i => $output) {
            $flags = 0;

            // check if $metadata provides flags at the current index
            if (isset($this->metadata[$i]['flags'])) {
                $flags = $this->metadata[$i]['flags'];
            }

            file_put_contents(
                $output,
                $this->payload[$i],
                $flags,
                $stream_out
            );
        }

        return $this->payload;
    }
}