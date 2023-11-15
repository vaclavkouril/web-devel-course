<?php

class Container {
    private $data = [];

    public function add(string $name, int $count) {
        if (isset($this->data[$name])) {
            $this->data[$name] += $count;
        } else {
            $this->data[$name] = $count;
        }
    }

    public function remove(string $name) {
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }
    }
}

class CollectStatistics {
    private static $statistics = [];

    public static function wrap(Container $container) {
        return new self($container);
    }

    public static function wrapIntermediate(Container $container) {
        return [
            'value' => new self($container),
            'statistics' => &self::$statistics
        ];
    }

    private function __construct(Container $container) {
        $this->container = $container;
    }

    public function __call($name, $arguments) {
        if (method_exists($this->container, $name)) {
            call_user_func_array([$this->container, $name], $arguments);
            self::$statistics[$name] = isset(self::$statistics[$name]) ? self::$statistics[$name] + 1 : 1;
        }
    }

    public function printStatistics() {
        var_dump(self::$statistics);
    }
}

// Do not modify code bellow this line.

// Expected output, utilize var_dump:
// array(2) {
//  ["add"]=>
//  int(3)
//  ["remove"]=>
//  int(1)
// }

// Basic version.
if (!isset($ignoreTest)) {
    $instance = CollectStatistics::wrap(new Container());
    $instance->add('tomato', 1);
    $instance->add('tomato', 1);
    $instance->add('orange', 1);
    $instance->remove('bread');
    $instance->printStatistics();
}

// Intermediate version, enable by defining wrapIntermediate method.
if (!isset($ignoreTest) && method_exists("CollectStatistics", "wrapIntermediate")) {
    $wrap = CollectStatistics::wrapIntermediate(new Container());
    $instance = $wrap["value"];
    $instance->add('tomato', 1);
    $instance->add('tomato', 1);
    $instance->add('orange', 1);
    $instance->remove('bread');
    var_dump($wrap["statistics"]);
}
