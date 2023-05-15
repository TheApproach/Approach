<?php

namespace Approach\Service;
use Approach\Render\Node;
use \Fiber;
use \Exception;

trait waitable
{
	public Fiber $fiber;
	public Node $branches;

	public function branch(callable $callback, null|array ...$args): int
	{
		$promise = new self($callback, $args);
		$promise->fiber = new Fiber($callback);
		$promise->fiber->start(...$args);
		$this->branches[] = $promise;
		return count($this->branches) - 1;
	}

	public function awaitChildFibers(): void
	{
		for($i = 0; $i < count($this->branches); $i++)
		{
			$this->waitForChild($i);
		}
			// this->await(...);
		
	}

	public function await(?int ...$fiberIndices): void
	{
		if (empty($fiberIndices) && !empty($this->branches))
		{
			$fiberIndices = [];
			foreach ($this->branches as $node)
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
