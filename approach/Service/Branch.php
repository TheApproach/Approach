<?php

namespace Approach\Service;

use \Approach\Scope;
use \Approach\Render\Node;
use \Fiber;




class Branch extends Node
{
	use waitable;

	public function __construct(public $fiber_process=null, ... $args) {
		$callback = function (...$args) {
			($this->fiber_process)(...$args);
			$this->awaitChildFibers();
		};
		$this->fiber = new Fiber($callback);
	}

	public function then(callable $callback): self {
		$child = new static($callback);
		$this->nodes[] = $child;
		return $child;
	}

	// public function catch(\Exception $e, int|callable|Fiber $callback): self {
	// 	if(is_int($callback))
	// 		$callback = Scope::$triage[$callback];
	// 	elseif($callback instanceof \Fiber && $callback->isTerminated()){
	// 		$callback = $callback->getReturn();

	// 		if($callback instanceof \Exception)
	// 			$callback = Scope::$triage[$callback->getCode()];
			
	// 		if(!is_callable($callback))
	// 			throw new \Exception('Invalid branch callback');
	// 	}

	// 	$child = new static($callback, $e);
	// 	$this->nodes[] = $child;
	// 	return $child;
	// }

	// public function branch(callable $callback, array $args = []): void
	// {
	// 	$this->fiber = new Fiber(function () use ($callback, $args)
	// 	{
	// 		return $callback(...$args);
	// 	});
	// }

	public function branchChild(callable $fiber_process, array $args = []): void {
		$child = new static($fiber_process);
		$this->nodes[] = $child;
		$child->branch($fiber_process, $args);
	}

	public function startChild(int $i): void {
		$this->nodes[$i]->fiber->start();
	}

	public function getChildResult(int $i) {
		return $this->nodes[$i]->fiber->getReturn();
	}

	public function signalChild(int $i, $signal): void {
		$this->nodes[$i]->fiber->resume($signal);
	}

	public function waitForChild(int $i): void {
		$this->fiber::suspend();
	}

	public function signalParent($signal): void {
		$this->fiber->resume($signal);
	}

	public function getResult(): mixed {
		return end($this->nodes)->fiber->getReturn();
	}
}

/*
class Branch extends Node
{
	use waitable;

	public static function await(Branch $branch): self
	{
		$i = count($branch->nodes) - 1;
		$fiber = $branch->fibers[$i];

		if ($fiber->getState() === Fiber::RUNNING)
		{
			$branch->waitForChild($i);
		}

		return $branch;
	}

	public function then(callable $callback): self
	{
		$this->branchChild(function () use ($callback)
		{
			$result = $callback($this);
			$this->signalParent($result);
		});

		return $this;
	}

	public function
	catch(callable $callback): self
	{
		$this->branchChild(function () use ($callback)
		{
			try
			{
				$result = $callback($this);
				$this->signalParent($result);
			}
			catch (\Throwable $e)
			{
				$this->signalParent($e);
			}
		});

		return $this;
	}

	public function getResult()
	{
		$i = count($this->nodes) - 1;
		return $this->getChildResult($i);
	}
}

*/