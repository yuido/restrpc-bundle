<?php

namespace Yuido\RestRpcBundle;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Yuido\JsonValidator\JsonValidator;

class RestRpc {
    
    private $translator;
    private $jsonValidator;
    private $jsonSchemasDir = [__DIR__ . '/schemas'];
    private $debug;

    public function __construct($translator, JsonValidator $jsonValidator, $jsonSchemaDir = [], $debug = false) {
                
        $this->translator = $translator;
        $this->jsonValidator = $jsonValidator;
        $this->debug = $debug;
        
        if(is_string($jsonSchemaDir)){
            $jsonSchemaDir = [$jsonSchemaDir];
        }
        
        foreach ($jsonSchemaDir as $j) {
            $this->jsonSchemasDir[] = $j;
        }
    }

    public function errorResponse($code, $message = '', \Exception $exception = null) {

        $validatorErrors = $this->jsonValidator->getErrors();
        
        $m = $this->translator->trans($message);
        
        // si hay errores de validación de la request los metemos en el mensaje
        if (count($validatorErrors) > 0) {
            foreach ($validatorErrors as $error) {
                $m .= sprintf(" [%s] %s\n", $error['property'], $error['message']);
            }
        }
        
        // si está activado el debug, metemos el mensaje de la excepción
        if ($this->debug && !is_null($exception)) {                        
            $m .= '. Exception message: ' . $exception->getMessage();
        }
               
        $result = [
            'status' => 'ERROR',
            'status_code' => $code,
            'status_desc' => $m
        ];

        $response = new JsonResponse($result);

        $response->setStatusCode($code);

        return $response;
    }       

    public function successResponse($result = []) {       
                
        
        $r = [
            'status' => 'OK',
            'status_code' => 200,
            'status_desc' => $result
        ];

        return new JsonResponse($r);
    }

    public function getRequestParams(Request $request, $jsonSchemaName) {
        
        $requestArray = json_decode($request->getContent(), true);      
        
        $jsonSchemaFile = $this->getJsonSchemaFile($jsonSchemaName);

        if ($jsonSchemaFile) {
            $this->jsonValidator->setJsonSchemaFile($jsonSchemaFile);
        }
        
        // posible optimización, pero que puede dar lugar a alguna 
        // incoherencia
        //$schemaFile = $this->jsonValidator->getJsonSchemaFile();
        
        $schemaArray = json_decode(file_get_contents($jsonSchemaFile), true);

        
        $requestParams = [];
                              
        foreach ($schemaArray['properties'] as $k => $v) {           
            
            $requestParams[$k] = (isset($requestArray[$k])) ? 
                    $requestArray[$k] : (($v['type'] == 'array')? [] : null );
                       
        }                      
        
        return $requestParams;
    }

    public function requestIsValid(Request $request, $jsonSchemaName) {

        $jsonSchemaFile = $this->getJsonSchemaFile($jsonSchemaName);

        if ($jsonSchemaFile) {
            $this->jsonValidator->setJsonSchemaFile($jsonSchemaFile);
        }

        $jsonObject = json_decode($request->getContent());

        $this->jsonValidator->check($jsonObject);

        return $this->jsonValidator->isValid();
    }
      
    protected function getJsonSchemaFile($jsonSchemaName){
        foreach ($this->jsonSchemasDir as $d){
            $sf = $d . '/' . $jsonSchemaName . '.json';
            if(file_exists($sf)){
                return $sf;
            }
        }
        
        return false;
    }
}
