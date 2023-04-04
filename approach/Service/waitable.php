<?php

namespace Approach\Service;
use \Fiber;
use \Exception;

trait waitable
{
	public Fiber $fiber;

	public function branch(callable $callback, null|array ...$args): int
	{
		$promise = new self($callback, $args);
		$promise->fiber = new Fiber($callback);
		$promise->fiber->start(...$args);
		$this->nodes[] = $promise;
		return count($this->nodes) - 1;
	}

	public function awaitChildFibers(): void
	{
		for($i = 0; $i < count($this->nodes); $i++)
		{
			$this->waitForChild($i);
		}
			// this->await(...);
		
	}

	public function await(?int ...$fiberIndices): void
	{
		if (empty($fiberIndices) && !empty($this->nodes))
		{
			$fiberIndices = [];
			foreach ($this->nodes as $node)
				if ($node->fiber)
					$fiberIndices[] = $node->fiber;
		}
		while (true)
		{
			$done = true;
			foreach ($fiberIndices as $fiberIndex)
			{
				$fiber = $this->fibers[$fiberIndex];
				if ($fiber->isSuspended())
				{
					$fiber->resume();
				}
				if (!$fiber->isTerminated())
				{
					$done = false;
				}
			}
			if ($done)
			{
				break;
			}
		}
	}

	public function waitForChild(int $i): void
	{
		$fiber = $this->fibers[$i];
		if ($fiber->isSuspended())
		{
			$fiber->resume();
		}
	}

	public function getChildResult(int $i): mixed
	{
		$fiber = $this->fibers[$i];
		if ($fiber->isTerminated())
		{
			return $fiber->getReturn();
		}
		else
		{
			throw new Exception('Child fiber is not terminated');
		}
	}


	public function getFiberResult(int $fiberIndex): mixed
	{
		$fiber = $this->fibers[$fiberIndex];
		if (!$fiber->isTerminated())
		{
			throw new Exception('Fiber is not terminated');
		}
		return $fiber->getReturn();
	}
}
