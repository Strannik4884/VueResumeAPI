<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use MongoDB\Client as DB;
use MongoDB\BSON\ObjectId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResumeController extends AbstractFOSRestController
{
    /**
     * @Rest\Get("/api/cv", name="all_cvs")
     */
    public function getAllCVs(): Response
    {
        $client = new DB($this->getParameter('app.db_url'));
        $cursor = $client->selectCollection($this->getParameter('app.db_name'), $this->getParameter('app.db_collection'))->find();
        $items = $cursor->toArray();
        foreach ($items as $item) {
            $item['id'] = $item['_id']->__toString();
            unset($item['_id']);
        }

        return $this->json($items);
    }

    /**
     * @Rest\Get("/api/cv/{id}", name="cv_by_id")
     * @param string $id
     * @return Response
     */
    public function getCVById(string $id): Response
    {
        $client = new DB($this->getParameter('app.db_url'));
        $collection = $client->selectCollection($this->getParameter('app.db_name'), $this->getParameter('app.db_collection'));
        $item = $collection->findOne(array('_id' => new ObjectId($id)));
        $item['id'] = $item['_id']->__toString();
        unset($item['_id']);

        return $this->json($item);
    }

    /**
     * @Rest\Post("/api/cv/{id}/status/update", name="change_cv_status")
     * @param string $id
     * @param Request $request
     * @return Response
     */
    public function changeCVStatus(string $id, Request $request): Response
    {
        $correctStatuses = array('Новый', 'Назначено собеседование', 'Принят', 'Отказ');
        $newStatusRequest = $request->toArray();
        if(in_array($newStatusRequest['new_status'], $correctStatuses, true)){
            $client = new DB($this->getParameter('app.db_url'));
            $collection = $client->selectCollection($this->getParameter('app.db_name'), $this->getParameter('app.db_collection'));
            $result = $collection->updateOne(['_id' => new ObjectId($id)], ['$set' => ['resumeStatus' => $newStatusRequest['new_status']]]);
            if($result->getModifiedCount() === 1){
                return $this->json([
                    'message' => 'Successful'
                ]);
            }
            else{
                return $this->json([
                    'message' => 'Error while updating document',
                    'id' => $id
                ]);
            }
        }
        return $this->json([
            'message' => 'Incorrect new status'
        ]);
    }

    /**
     * @Rest\Post("/api/cv/add", name="add_cv")
     * @param Request $request
     * @return Response
     */
    public function addCV(Request $request): Response
    {
        $client = new DB($this->getParameter('app.db_url'));
        $collection = $client->selectCollection($this->getParameter('app.db_name'), $this->getParameter('app.db_collection'));
        $result = $collection->insertOne($request->toArray());

        return $this->json([
            'id' => $result->getInsertedId()->__toString()
        ]);
    }
}