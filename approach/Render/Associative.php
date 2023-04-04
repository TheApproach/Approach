<?php

namespace Approach\Render;


trait Associative
{
    private $_keys = [];

    public function &toArray(): array
    {
        return $this->nodes;
    }

    /**
     * @param mixed $name
     * @param mixed $content
     * @return self
     */
    public function add($name, $content): self
    {
        $self = new (self::class)($name, $content);

        $this->nodes[] = $self;
        $this->_keys[(string)$name] = &$self;
        return $self;
    }

    public function has($name): bool
    {
        return isset($this->_keys[(string)$name]);
    }

    public function get($name): ?self
    {
        return $this->_keys[(string)$name] ?? null;
    }

    public function set($name, $content): self
    {
        if ($this->has($name)) {
            $this->_keys[(string)$name]->content = $content;
        } else {
            $this->add($name, $content);
        }

        return $this->_keys[(string)    $name];
    }

    public function remove($name): self
    {
        if ($this->has($name)) {
            unset($this->_keys[(string)$name]);
        }
        return $this;
    }
    public function offsetExists($offset): bool
    {
        return $this->has((string)$offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get((string)$offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set((string)$offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->remove((string)$offset);
    }
}
