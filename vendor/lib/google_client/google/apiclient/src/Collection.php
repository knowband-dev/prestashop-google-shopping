<?php

namespace Google;

/**
 * Extension to the regular Google\Model that automatically
 * exposes the items array for iteration, so you can just
 * iterate over the object rather than a reference inside.
 */
class Collection extends Model implements \Iterator, \Countable
{
  protected $collection_key = 'items';

  /*
  * PHP 8: add ReturnTypeWillChange for Iterator/Countable compatibility
  * 01-08-2026
  */
  #[\ReturnTypeWillChange]
  public function rewind()
  {
    if (isset($this->{$this->collection_key})
        && is_array($this->{$this->collection_key})) {
      reset($this->{$this->collection_key});
    }
  }

  /*
  * PHP 8: add ReturnTypeWillChange for Iterator::current() compatibility
  * 01-08-2026
  */
  #[\ReturnTypeWillChange]
  public function current()
  {
    $this->coerceType($this->key());
    if (is_array($this->{$this->collection_key})) {
      return current($this->{$this->collection_key});
    }
  }

  /*
  * PHP 8: add ReturnTypeWillChange for Iterator::key() compatibility
  * 01-08-2026
  */
  #[\ReturnTypeWillChange]
  public function key()
  {
    if (isset($this->{$this->collection_key})
        && is_array($this->{$this->collection_key})) {
      return key($this->{$this->collection_key});
    }
  }

  /*
  * PHP 8: add ReturnTypeWillChange for Iterator::next() compatibility
  * 01-08-2026
  */
  #[\ReturnTypeWillChange]
  public function next()
  {
    return next($this->{$this->collection_key});
  }

  /*
  * PHP 8: add ReturnTypeWillChange for Iterator::valid() compatibility
  * 01-08-2026
  */
  #[\ReturnTypeWillChange]
  public function valid()
  {
    $key = $this->key();
    return $key !== null && $key !== false;
  }

  /*
  * PHP 8: add ReturnTypeWillChange for Countable::count() compatibility
  * 01-08-2026
  */
  #[\ReturnTypeWillChange]
  public function count()
  {
    if (!isset($this->{$this->collection_key})) {
      return 0;
    }
    return count($this->{$this->collection_key});
  }

  /*
  * PHP 8: add ReturnTypeWillChange for ArrayAccess::offsetExists() compatibility
  * 01-08-2026
  */
  #[\ReturnTypeWillChange]
  public function offsetExists($offset)
  {
    if (!is_numeric($offset)) {
      return parent::offsetExists($offset);
    }
    return isset($this->{$this->collection_key}[$offset]);
  }

  /*
  * PHP 8: add ReturnTypeWillChange for ArrayAccess::offsetGet() compatibility
  * 01-08-2026
  */
  #[\ReturnTypeWillChange]
  public function offsetGet($offset)
  {
    if (!is_numeric($offset)) {
      return parent::offsetGet($offset);
    }
    $this->coerceType($offset);
    return $this->{$this->collection_key}[$offset];
  }

  /*
  * PHP 8: add ReturnTypeWillChange for ArrayAccess::offsetSet() compatibility
  * 01-08-2026
  */
  #[\ReturnTypeWillChange]
  public function offsetSet($offset, $value)
  {
    if (!is_numeric($offset)) {
      return parent::offsetSet($offset, $value);
    }
    $this->{$this->collection_key}[$offset] = $value;
  }

  /*
  * PHP 8: add ReturnTypeWillChange for ArrayAccess::offsetUnset() compatibility
  * 01-08-2026
  */
  #[\ReturnTypeWillChange]
  public function offsetUnset($offset)
  {
    if (!is_numeric($offset)) {
      return parent::offsetUnset($offset);
    }
    unset($this->{$this->collection_key}[$offset]);
  }

  private function coerceType($offset)
  {
    $keyType = $this->keyType($this->collection_key);
    if ($keyType && !is_object($this->{$this->collection_key}[$offset])) {
      $this->{$this->collection_key}[$offset] =
          new $keyType($this->{$this->collection_key}[$offset]);
    }
  }
}
