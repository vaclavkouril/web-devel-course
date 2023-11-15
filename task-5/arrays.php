<?php

class ShoppingCart implements ArrayAccess, Iterator  {

    private $cart = [];

    public function offsetSet($offset, $value) {
        if ($value === null) {
            unset($this->cart[$offset]);
        } else {
            $this->cart[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->cart[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->cart[$offset]);
    }

    public function offsetGet($offset) {
        return $this->cart[$offset];
    }
    private $position = null;
    
    public function rewind() {
        reset($this->cart);
        $this->position = key($this->cart;);
    }

    public function current() {
        return $this->cart[key($this->cart)];
    }

    public function key() {
        return key($this->cart);
    }

    public function next() {
        next($this->cart);
    }

    public function valid() {
        return key($this->cart) !== false;
    }

}

// Do not modify code bellow this line.

// Expected output:
// Content:
//  apple : 3

if (!isset($ignoreTest)) {
  $cart = new ShoppingCart();
  print("Content:\n");
  $cart['apple'] = 2;
  ++$cart['apple'];
  foreach($cart as $name => $count) {
    print("  $name : $count\n");
  }
}
