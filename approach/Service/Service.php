<?php

/*************************************************************************

APPROACH

 *************************************************************************/

namespace Approach\Service;

use \Approach\Scope;
use \Traversable;


/*
	- extends from the Branch class and uses the waitable trait, as required.
	- has constants for extracting, encoding, translating, and performing known actions, as required. These constants are stored in static properties of the class.
	- has a constructor that takes an activity constant and a flow constant, as required. The constructor sets these values as instance properties.
	- has a Receive() method that performs extract, transform, and load operations on input data, as required. The method uses the await() method provided by the FiberManager trait to suspend the Fiber and wait for the result of the async operation.
	- has a Process() method that manages the underlying Branch logic and aggregates the output of child branches into a payload property, as required. The method uses the create() and await() methods provided by the FiberManager trait to create child Fibers and wait for their results.
	- has a Respond() method that encodes and sends the payload using the specified format, as required. The method uses the await() method provided by the FiberManager trait to suspend the Fiber and wait for the result of the async operation.
*/

class Service extends Branch
{
    use waitable;

    public static array $encoder;
    public static array $decoder;

    public mixed $meta_data;
    public array $payload;

    protected static array $connections;

    public function __construct(
        public flow $flow = flow::in,
        public bool $auto_dispatch = true,
        public format $format_in = format::json,
        public format $format_out = format::json,
        public target $target_in = target::url,
        public target $target_out = target::api,
        public ?callable $preProcessCallback = null,
        public ?callable $postProcessCallback = null,
    ) {
        static::$encoder        = Scope::$format_encoders;
        static::$decoder        = Scope::$decoders;

        if ($auto_dispatch) {
            parent::__construct($this->dispatch);
            $this->fiber->start();
        }
    }

    public function dispatch()
    {
        $this->payload = $this->Receive($this->payload);

        $this->payload = $this->PreProcess($this->payload);

        // $decoded_payload = decode::as[$format_in]($payload);
        $this->payload = $this->Process($this->payload);
        // $encoded_payload = encode::as[$format_out]($processed);

        $this->payload = $this->PostProcess($this->payload);

        $this->payload = $this->Respond($this->payload);
    }

    public function Receive(array $payload): array
    {
        // if (!$this->message && $this->flow == flow::in) {
        //     $this->acquire();
        //     if ($this->message === null) {
        //         $payload = ['error' => static::ServiceException('require', static::class, 'message')];
        //     }
        // } else if ($this->flow == flow::out) {
        //     $format = $this->format[$this->flow];
        //     $this->message = $this->await(
        //         static::$encoder[$format][$this->flow]($this->message)
        //     );
        // }

        return $payload;
    }

    public function PreProcess(array $payload): array
    {
        if (is_callable($this->preProcessCallback)) {
            $payload = call_user_func($this->preProcessCallback, $payload);
        }

        return $payload;
    }

    public function Process(array $payload): array
    {
        // $this->branch(self::$activity[$this->which_activity]);
        // $this->payload = [];
        // foreach ($this->nodes as $node) {
        //     $this->payload[] = $this->await($node->getResult());
        // }
        return $payload;
    }

    public function PostProcess(array $payload): array
    {
        if (is_callable($this->postProcessCallback)) {
            $payload = call_user_func($this->postProcessCallback, $payload);
        }
        return $payload;
    }

    public function Respond(array $payload): array
    {
        return $payload;
    }

    protected function acquire()
    {
        return function () {
            // implementation details go here
            return true;
        };
    }

    // public function RenderHead(): Traversable
    // {
    //     if ($this->flow == flow::in) {
    //         yield $this->Receive();
    //     } else if ($this->flow == flow::out) {
    //         yield $this->Respond();
    //     }
    // }

    // public function RenderCorpus(): Traversable
    // {
    //     yield $this->Process();
    // }

    // public function RenderTail(): Traversable
    // {
    //     if ($this->flow == flow::in) {
    //         yield $this->Respond();
    //     } else if ($this->flow == flow::out) {
    //         yield $this->Receive();
    //     }
    // }

    function ServiceException($mode, $ThrowingService, $key)
    {
        if ($mode == 'require') {
            return $key . ' is a required value for ' . $ThrowingService . ' to run properly.';
        }
    }
}
