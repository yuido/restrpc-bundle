<?php

namespace Yuido\RestRpcBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class RestRpcController extends Controller {

    /**
     * @Route("/create/{schema}", name="create")
     * @Method("POST")
     */
    public function createEntityAction(Request $request, $schema) {
        $restRpc = $this->get('rest_rpc');
        $schemaManager = $this->get('schema_manager');

        if (!$restRpc->requestIsValid($request, $schema)) {
            return $restRpc->errorResponse(406, 'error.request_invalid');
        }

        try {
            $id = $schemaManager->createEntity($request, $schema);
        } catch (\Exception $e) {
            return $restRpc->errorResponse(500, 'error.cannot_write_in_db', $e);
        }

        return $restRpc->successResponse($id);
    }

    /**
     * @Route("/update/{schema}/{id}", name="update")
     * @Method("POST")
     */
    public function updateEntityAction(Request $request, $schema, $id) {
        $restRpc = $this->get('rest_rpc');
        $schemaManager = $this->get('schema_manager');

        if (!$restRpc->requestIsValid($request, $schema)) {
            return $restRpc->errorResponse(406, 'error.request_invalid');
        }

        try {
            $id = $schemaManager->updateEntity($request, $schema, $id);
        } catch (\Exception $e) {
            return $restRpc->errorResponse(500, 'error.cannot_update_in_db', $e);
        }

        return $restRpc->successResponse($id);
    }

    /**
     * @Route("/retrieve/{schema}", name="retrieve")
     * @Method("POST")
     */
    public function retrieveEntityAction(Request $request, $schema) {
        $restRpc = $this->get('rest_rpc');
        $schemaManager = $this->get('schema_manager');

        if (!$restRpc->requestIsValid($request, 'query_params')) {
            return $restRpc->errorResponse(406, 'error.request_invalid');
        }

        try {
            $entities = $schemaManager->retrieveEntities($request, $schema, 'query_params');
        } catch (\Exception $e) {
            return $restRpc->errorResponse(500, 'error.cannot_read_in_db', $e);
        }

        return $restRpc->successResponse($entities);
    }

    /**
     * @Route("/delete/{schema}/{id}", name="delete")
     * @Method({"GET", "POST"})
     *
     */
    public function deleteEntitiesAction(Request $request, $schema, $id) {
        $restRpc = $this->get('rest_rpc');
        $schemaManager = $this->get('schema_manager');

        try {
            $id = $schemaManager->deleteEntity($schema, $id);
        } catch (\Exception $e) {
            return $restRpc->errorResponse(500, 'error.cannot_delete_in_db', $e);
        }

        return $restRpc->successResponse(['id' => $id]);
    }
}