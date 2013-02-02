<?php

/**
 * Least Recently Used Cache
 *
 * A fixed sized cache that removes the element used last when it reaches its
 * size limit.
 *
 * The cache consists of a hashmap and a circular doubly linked list. The hashmap
 * contains references to the nodes in the linked list for fast retrieval. The
 * linked list is organized so that the next element after the head is the most
 * recently used element and the previous element before the head is the least
 * recently used element.
 */
class LRUCache {
	protected $maximumSize = 0;
	protected $map = array();
	protected $head = null;

	/**
	 * Create a LRU Cache
	 *
	 * @param int $size
	 * @throws InvalidArgumentException
	 */
	public function __construct($size) {
		if (!is_int($size) || $size <= 0) {
			throw new InvalidArgumentException();
		}
		$this->maximumSize = $size;

		// the head is a null element that always points the most and least recently
		// used elements
		$this->head = new LRUCacheNode(null, null);
		$this->head->next = $this->head->prev = $this->head;
	}

	/**
	 * Get the value cached with this key
	 *
	 * @param int|string $key     The key. Strings that are integers are cast to ints.
	 * @param mixed      $default The value to be returned if key not found. (Optional)
	 * @return mixed
	 */
	public function get($key, $default = null) {
		if ($this->containsKey($key)) {
			$this->recordAccess($this->map[$key]);
			return $this->map[$key]->value;
		} else {
			return $default;
		}
	}

	/**
	 * Put something in the cache
	 *
	 * @param int|string $key   The key. Strings that are integers are cast to ints.
	 * @param mixed      $value The value to cache
	 */
	public function put($key, $value) {
		if ($this->containsKey($key)) {
			$this->map[$key]->value = $value;
			$this->recordAccess($this->map[$key]);
		} else {
			$node = new LRUCacheNode($key, $value);
			$this->add($node);
			if ($this->size() > $this->maximumSize) {
				$this->removeStalestNode();
			}
		}
	}

	/**
	 * Get the number of elements in the cache
	 *
	 * @return int
	 */
	public function size() {
		return count($this->map);
	}

	/**
	 * Does the cache contain an element with this key
	 *
	 * @param int|string $key The key
	 * @return boolean
	 */
	public function containsKey($key) {
		if (isset($this->map[$key])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Remove the element with this key.
	 *
	 * @param int|string $key The key
	 */
	public function remove($key) {
		if ($this->containsKey($key)) {
			$this->map[$key]->remove();
			unset($this->map[$key]);
		}
	}

	/**
	 * Clear the cache
	 */
	public function clear() {
		$this->head->next = $this->head->prev = $this->head;
		$this->map = array();
	}

	protected function add(LRUCacheNode $node) {
		$this->map[$node->key] = $node;
		$node->insertAfter($this->head);
	}

	protected function recordAccess(LRUCacheNode $node) {
		$node->remove();
		$node->insertAfter($this->head);
	}

	protected function removeStalestNode() {
		$this->remove($this->head->prev->key);
	}
}