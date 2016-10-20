<?php

namespace Yuido\RestRpcBundle;

class SchemaManager {

    private $conn;
    private $restRpc;

    public function __construct($conn, $restRpc) {
        $this->conn = $conn;
        $this->restRpc = $restRpc;
    }

    public function createEntity($request, $schema) {

        $requestParams = $this->restRpc->getRequestParams($request, $schema);
        
        $queryBuilder = $this->conn->createQueryBuilder()
                ->insert($schema);
        $i = 0;
        foreach ($requestParams as $k => $v){
            $queryBuilder->setValue($k, '?')->setParameter($i, $v);
            $i ++;
        }
      
        $queryBuilder->execute();
        
        return $this->conn->lastInsertId();
    }

    public function updateEntity($request, $schema, $id){
        $requestParams = $this->restRpc->getRequestParams($request, $schema);
        
        $queryBuilder = $this->conn->createQueryBuilder()
                ->update($schema);
        $i = 0;
        foreach ($requestParams as $k => $v){
            $queryBuilder->set($k, '?')->setParameter($i, $v);
            $i ++;
        }
        
        $queryBuilder->where('id = ?')->setParameter($i, $id);
      
        $queryBuilder->execute();
        
        return $id;
    }
    
    public function deleteEntity($schema, $id){
        
        $queryBuilder = $this->conn->createQueryBuilder()
                ->delete($schema);
        
       
        $queryBuilder->where('id = ?')->setParameter(0, $id);
      
        $queryBuilder->execute();
        
        return $id;
    }

    public function retrieveEntities($request, $schema, $querySchema) {
        $requestParams = $this->restRpc->getRequestParams($request, $querySchema);
        
        $filters  = $requestParams['filters'];
        $order_by = $requestParams['order_by'];
        $limit    = $requestParams['limit'];
        $offset   = $requestParams['offset'];
        
        $queryBuilder = $this->conn->createQueryBuilder()
                ->select('*')->from($schema);
        
        $i = 0;
        $w = '';
        
        foreach ($filters as $f) {
            $w .= $f['property'] . ' LIKE ?' ;
            $w .= ($i + 1 == count($filters))? '' : ' and ';
            $queryBuilder->setParameter($i, '%' . $f['value'] . '%');
            $i++;
        }
        
        if($w != ''){
            $queryBuilder->where($w);
        }
         
        foreach ($order_by as $o) {
            $queryBuilder->addOrderBy($o['property'], $o['value']);
        }

        if (!is_null($limit)) {
            $queryBuilder->setMaxResults($limit);
        }

        if (!is_null($offset)) {
            $queryBuilder->setFirstResult($offset);
        }
        
        $result = $queryBuilder->execute()->fetchAll();

        return $result;
    }

}
