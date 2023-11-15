<?php

class ShoppingCart {

  // TODO: ...
    
    function add($name, $count = 1) {
        if (isset($this->cart[$name])) {
            $this->cart[$name] += $count;
        } else {
            $this->cart[$name] = $count;
        }
    }

    function remove($name) {
        unset($this->cart[$name]);
    }

    function __toString(): string {
        $output = "Content:\n";
        foreach ($this->cart as $name => $count) {
            $output .= "  $name : $count\n";
        }
        return $output;
    }

}

// Do not modify code bellow this line.

// Expected output:
// Content:
//  milk : 2
//  bread : 1

if (!isset($ignoreTest)) {
  $cart = new ShoppingCart();
  $cart->add('milk', 1);
  $cart->add('bread', 1);
  $cart->add('basil', 1);
  $cart->add('milk');
  $cart->remove('basil'); // Remove all
  print($cart);
}

