<?php


function __includeTemplate($templatePath, $parameters = [])
{
    extract($parameters);

    include $templatePath;
}


$page = isset($_GET['page']) ? $_GET['page'] : null;

if ($page === null || !preg_match('/^[a-zA-Z\/]+$/', $page)) {
    http_response_code(400); // Bad Request
    exit;
}

$templatePath = __DIR__ . '/templates/' . $page . (is_dir('templates/' . $page) ? '/index.php' : '.php');

if (!file_exists($templatePath)) {
    http_response_code(404); // Not Found
    exit;
}

$parameterDescriptorPath = __DIR__ .'/parameters/' . $page . '.php';
$parameters = [];

if (file_exists($parameterDescriptorPath)) {
    $parameterDescriptor = include $parameterDescriptorPath;

    foreach ($parameterDescriptor as $param => $type) {
        if (!isset($_GET[$param])) {
            http_response_code(400); // Bad Request
            exit;
        }

        switch ($type) {
            case 'int':
                if (!is_numeric($_GET[$param])) {
                    http_response_code(400); // Bad Request
                    exit;
                }
                $parameters[$param] = (int)$_GET[$param];
                break;

            case 'string':
                $parameters[$param] = (string)$_GET[$param];
                break;

            case is_array($type):
                if (!in_array($_GET[$param], $type)) {
                    http_response_code(400); // Bad Request
                    exit;
                }
                $parameters[$param] = $_GET[$param];
                break;

            default:
                http_response_code(500); // Internal Server Error
                exit;
        }
    }
}

include __DIR__ . '/templates/_header.php';


__includeTemplate($templatePath, $parameters);

include __DIR__ . '/templates/_footer.php';

