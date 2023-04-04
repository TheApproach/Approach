<?php

namespace Approach\Render;
use \Approach\nullstate;
use \Stringable;

trait Streamability 
{
	private array $_labeled_nodes = [];		
	private array $_node_labels = [];		// string keys 
	
	public function &toArray()
	{
		return [
			$this->getNodeProperties(),
			...$this->nodes->getNodeProperties() 
		];
	}

	public function __set(mixed $label, mixed $val)
	{
		$this->offsetSet($label, $val);
	}

	public function __get(mixed $label)
	{
		return $this->offsetGet($label);
	}

	public function getNodeProperties()
	{
		return [
			...$this->getHeadProperties(),
			...$this->getCorpusProperties(),
			...$this->getTailProperties()
		];
	}

	protected function getHeadProperties(): array
	{
		return [];
	}
	protected function getCorpusProperties(): array
	{
		return [
			'content'	=> $this->content		/**<	TODO: make enum labels	>*/
		];
	}
	protected function getTailProperties(): array
	{
		return [];
	}

	public function offsetExists($label): bool
	{
		if (is_int($label))	return isset($this->nodes[$label]);
		else return
			isset($this->_labeled_nodes[$label])
			?(
				isset($this->nodes[$this->_labeled_nodes[$label]])
				?
					true
				:	nullstate::undefined
			)
			:	nullstate::undeclared;
	}

	public function offsetGet(mixed $label): mixed
	{
		if (is_int($label))	return

			// If the label is actually a direct offset to the nodes array, return it
			$this->getLabeledNode($label)
			??
			// If a provided index is not in the array, return nullstate::undeclared
			nullstate::undeclared;

		$label_index = $this->getNodeLabelIndex($label);
		// echo 'label ';
		// var_dump($label);
		// echo PHP_EOL . 'index ';
		// var_dump($label_index);

		// echo PHP_EOL . 'labeled nodes ';
		// var_dump($this->_labeled_nodes);

		// echo PHP_EOL . 'node labels ';
		// var_dump($this->_node_labels);

		return 
			// If the label exists
			$label_index !== nullstate::undeclared
			?
				// If the label points to an existing node                
				$this->getLabeledNode( $label_index )

			:	// Or else, the label was never declared	
				nullstate::undefined;
	}

	public function offsetSet(mixed $label, mixed $value): void
	{
		if ($label === null){
			$this->nodes[] = $value;
			return;
		}

		$label_index = $this->getNodeLabelIndex($label);
		
		if($label_index !== nullstate::undeclared && $this->getLabeledNode($label_index) !== nullstate::undefined)
		{
			// echo 'getting label index... found: '.$label_index.PHP_EOL;
			$selected = $this->getLabeledNode( $label_index );
			// echo 'getting labeled node... found: ' . get_class($selected) . PHP_EOL;

			$selected = &$value;
			// echo 'setting labeled node to a ' . get_class($value) . PHP_EOL;
		}
		else
		{
			// echo 'getting label index... undeclared. adding... '  . PHP_EOL;

			$this->nodes[] = $value;								// Actual Nodes, Not all labels
			$node_index = count($this->_node_labels);				// Index of the soon to be added label
			$this->_node_labels[] = $label;							// Push the label to the label array
			$this->_labeled_nodes[$node_index] = end($this->nodes);	// Label Index Storage
		}
	}

	protected function getLabeledNode(int $label_index)
	{
		return
			$this->_labeled_nodes[$label_index]
			??
			nullstate::undefined;
	}

	/**
	 * Returns an index that works with $this->_labeled_node[...] to find a node you labeled
	 * 
	 * @param string $label
	 * @return int|null
	 */
	protected function getNodeLabelIndex(string|Stringable $label)
	{
		$offset = array_search($label, $this->_node_labels);
		// echo PHP_EOL.'looking for label index.. found: '.$offset.PHP_EOL;
		return $offset !== false ?
			$offset
		:	nullstate::undeclared
		;
	}

	public function offsetUnset(mixed $label): void
	{
		if (is_int($label) && isset( $this->nodes[$label] )){
			unset($this->nodes[$label]);
			return;
		}

		if(isset($this?->nodes[$this?->_labeled_nodes[$label]]))
			unset($this->nodes[$this->_labeled_nodes[$label]]);
		
		return;
	}
}
