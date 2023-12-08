<?php


class Router
{
    public function dispatch()
	{
		
		$action = $this->getSanitizedInput('action');
		$prefix = $this->getPrefix('action');
		
        // Rozdělení parametru action na části
        $actionParts = explode('/', $action);

		$controllerPathParts = $this->getPath($actionParts);
		$controllerPath = "/";
		if ($controllerPathParts !== 0){
		$controllerPath = implode('/' , $controllerPathParts ) . "/"; }
        // Kontrola správného formátu parametru action
        if (count($actionParts) < 2 || !preg_match('/^[a-zA-Z_]+$/', $actionParts[count($actionParts)-2])) {
            $this->respondWithError(400, 'Bad Request');
        }

        // Získání názvu kontroleru a metody
        $controllerName = $this->sanitizeControllerName($actionParts[count($actionParts)-2]) . 'Controller';
		$methodName = $prefix . $this->sanitizeMethodName($actionParts[count($actionParts)-1]);
		// Sestavení cesty k souboru kontroleru
		$controllerFilePath = __DIR__ . "/controllers" . $controllerPath  . $this->sanitizeControllerName($actionParts[count($actionParts)-2]) . ".php";

        // Kontrola existence souboru kontroleru
		if (!file_exists($controllerFilePath)) {
            $this->respondWithError(404, 'Not Found');
        }

        // Načtení souboru kontroleru
        require_once $controllerFilePath;

        // Kontrola existence třídy kontroleru
		if (!class_exists($controllerName)) {
            $this->respondWithError(404, 'Not Found');
        }

        // Vytvoření instance kontroleru
        $controllerInstance = new $controllerName();

        // Kontrola existence metody kontroleru
        if (!method_exists($controllerInstance, $methodName)) {
			$this->respondWithError(404, 'Not Found');
        }

        // Zavolání metody kontroleru
        $result = $controllerInstance->$methodName();

        // Kontrola návratové hodnoty
        if ($result === null) {
            http_response_code(204);
        } else {
            // Serializace a výpis návratové hodnoty ve formátu JSON
            echo json_encode($result);
        }	
    }

    private function sanitizeControllerName($name)
    {
        // Sanitize and validate the controller name (allow only letters and underscore)
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

        return ucfirst($name);
    }

    private function respondWithError($statusCode, $message)
    {
        http_response_code($statusCode);
        echo $message;
        exit();
	}
	
	private function getSanitizedInput($key)
    {
        // Získání a validace vstupu (GET nebo POST)
        $value = filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);

        if ($value === null) {
            $value = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
        }

        return $value;
	}
	
	private function getPrefix($key)
    {
		$value = filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);
		$prefix = "get";

        if ($value === null) {
			$value = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
			$prefix = "post";
        }
		if ($value === null){
            $this->respondWithError(400, 'Bad Request');
		}
        return $prefix;
	}

	private function getPath($arrayParts) {
		unset($arrayParts[count($arrayParts)-1]);
		unset($arrayParts[count($arrayParts)-1]);
		return $arrayParts;
	}
}
