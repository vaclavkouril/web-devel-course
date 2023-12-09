<?php


class Router
{
    public function dispatch()
	{
		
		try{
		$action = $this->getSanitizedInput('action');
		$prefix = $this->getPrefix();
		
		if (!$action){
            $this->respondWithError(400, 'Bad Request');
		}
		
		$actionParts = explode('/', $action);

		$controllerPathParts = $this->getPath($actionParts);
		$controllerPath = "/";
		if ($controllerPathParts !== 0){
		$controllerPath = "/" . implode('/' , $controllerPathParts ) . "/"; }
        if (count($actionParts) < 2 || !preg_match('/^[a-zA-Z_]+$/', $actionParts[count($actionParts)-2])) {
            $this->respondWithError(400, 'Bad Request');
        }

        $controllerName = $this->sanitizeControllerName($actionParts[count($actionParts)-2]) . 'Controller';
		$methodName = $prefix . $this->sanitizeMethodName($actionParts[count($actionParts)-1]);
		
		// Sestavení cesty k souboru kontroleru
		$controllerFilePath = __DIR__ . "/controllers" . $controllerPath  . $this->sanitizeControllerName($actionParts[count($actionParts)-2]) . ".php";

		if (!file_exists($controllerFilePath)) {
			// var_dump($controllerInstance, $methodName, $controllerFilePath);
            $this->respondWithError(404, 'Not Found');
        }

        require_once $controllerFilePath;

		if (!class_exists($controllerName)) {
			// var_dump($controllerInstance, $methodName);
            $this->respondWithError(404, 'Not Found');
        }

        $controllerInstance = new $controllerName();

        if (!method_exists($controllerInstance, $methodName)) {
			// var_dump($controllerInstance, $methodName);
			$this->respondWithError(404, 'Not Found');
        }

        $result = $this->callControllerMethod($controllerInstance, $methodName, $actionParts);

        if ($result === null) {
            http_response_code(204);
        } else {
				$res = json_encode($result, JSON_THROW_ON_ERROR);
				echo $res;
			}
		}catch (Exception $e) {
            $this->respondWithError(500, "Internal Server Error");
		}  
    }

    private function sanitizeControllerName($name)
    {
        if (!preg_match('/^[a-zA-Z_]+$/', $name)) {
            $this->respondWithError(400, 'Bad Request');
            exit();
        }
        return ucfirst($name);
    }

    private function sanitizeMethodName($name)
    {
        if (!preg_match('/^[a-zA-Z]+$/', $name)) {
            $this->respondWithError(400, 'Bad Request');
            exit();
		}

        return $name;
    }

    private function respondWithError($statusCode, $message)
    {
        http_response_code($statusCode);
        echo $message;
        exit();
	}
	
	private function getSanitizedInput($key)
    {
        $value = filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);

        if ($value === null) {
            $value = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
        }

        return $value;
	}
	
	private function getPrefix(){
		if ($_SERVER["REQUEST_METHOD"] === "GET"){$prefix = "get";}
		elseif ($_SERVER["REQUEST_METHOD"] === "POST") {$prefix = "post";}
		else {
		$this->respondWithError(400, 'Bad Request');
		}	
		
        return $prefix;
	}

	private function getPath($arrayParts) {
		unset($arrayParts[count($arrayParts)-1]);
		unset($arrayParts[count($arrayParts)-1]);
		return $arrayParts;
	}
	
	private function callControllerMethod($controllerInstance, $methodName, $actionParts)
    {
        $reflectionMethod = new ReflectionMethod($controllerInstance, $methodName);
        
        $parameters = $reflectionMethod->getParameters();

        $arguments = [];

        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();

        $value = $this->getSanitizedInput($parameterName);
        if ($value === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $value = $this->getPostInput($parameterName);
        }

        if ($value === null && $parameter->isOptional()) {
            $value = $parameter->getDefaultValue();
        } elseif ($value === null) {
            $this->respondWithError(400, 'Bad Request');
        }

        $arguments[] = $value;        }

        return $reflectionMethod->invokeArgs($controllerInstance, $arguments);
		
	}
	private function getPostInput($key){
		$value = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
		return $value;
	}
}
