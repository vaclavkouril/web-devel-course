<!DOCTYPE><meta charset=utf-8><title>Form</title>
<body>
 
<?php

$definition = [
  "action" => "/nswi142/practicals/script/02-handle-form.php",
  "fields" => [
    [
      "name" => "full_name",
      "label" => "Name",
      "type" => "text",
      "required" => true, // optional
    ],
    [
      "name" => "age",
      "label" => "Age",
      "type" => "number",
      "min" => 0, // optional
      "max" => 1000, // optional
      // we can have required here as well
    ],
    [
      "name" => "type",
      "type" => "hidden",
      "value" => "secret-code",
    ],
  ],
];

function generateHTMLForm($definition) {
    $action = $definition["action"];
    $fields = $definition["fields"];

    echo '<form action="' . $action . '" method="GET">';

    foreach ($fields as $field) {
        if (isset($field["type"]) && isset($field["name"])) {
            echo '<label>' . $field["label"] . ' <input type="' . $field["type"] . '" name="' . $field["name"] . '"';

            if (isset($field["required"]) && $field["required"]) {
                echo ' required';
            }

            if ($field["type"] == "number") {
                if (isset($field["min"])) {
                    echo ' min="' . $field["min"] . '"';
                }
                if (isset($field["max"])) {
                    echo ' max="' . $field["max"] . '"';
                }
            }

            if ($field["type"] == "hidden" && isset($field["value"])) {
                echo ' value="' . $field["value"] . '"';
            }

            echo '> </label>';
        }
    }
    }
    generateHTMLForm($definition);
    echo '<input type="submit" value="Submit">';
    echo '</form>';
    ?>
</body>
