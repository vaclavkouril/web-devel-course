<?php

class User {
  
  public string $name;

  public int $age;

  public function __construct(string $name, int $age) {
    $this->name = $name;
    $this->age = $age;
  }

}

interface Writer {
  public function asString(User &$user);
}

class JsonWriter implements Writer {
  public function asString(User &$user) {
    return json_encode(['name' => $user->name, 'age' => $user->age]);
  }
}

class StringWriter implements Writer {
  public function asString(User &$user) {
    return $user->name . ':' . $user->age;
  }
}


function createWriter($name): Writer {
  $className = ucfirst($name) . 'Writer';
  $writerVariable = new $className(); 
  return $writerVariable;
}

// Do not modify code below this line.

// Expected output:
// JSON:
// {"name":"Ailish","age":22}
// STRING
// Ailish:22

if (!isset($ignoreTest)) {
  print("JSON:\n");
  $student = new User("Ailish", 22);
  print(createWriter("json")->asString($student));
  print("\nSTRING\n");
  print(createWriter("string")->asString($student));
  print("\n");
}
