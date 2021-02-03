<?php

namespace App\Controller;

use App\Entity\Resume;
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
        // get all items from database
        $cursor = $client->selectCollection($this->getParameter('app.db_name'), $this->getParameter('app.db_collection'))->find();
        $items = $cursor->toArray();
        // set id field
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
        try {
            // get item by id
            $item = $collection->findOne(array('_id' => new ObjectId($id)));
            // if item found
            if (isset($item)) {
                $item['id'] = $item['_id']->__toString();
                unset($item['_id']);
                return $this->json($item);
            } else {
                return $this->json([
                    'code' => 404,
                    'message' => 'Resume with id ' . $id . ' not found'
                ], 404);
            }
        } catch (\Exception $exception) {
            return $this->json([
                'code' => 400,
                'message' => 'Incorrect resume id'
            ], 400);
        }
    }

    /**
     * @Rest\Post("/api/cv/{id}/edit", name="edit_cv_by_id")
     * @param string $id
     * @param Request $request
     * @return Response
     */
    public function editCVById(string $id, Request $request): Response
    {
        $newData = $request->toArray();
        try {
            Resume::validateCV($newData);
        } catch (\Exception $exception) {
            return $this->json([
                'code' => 400,
                'message' => 'Incorrect new status'
            ], 400);
        }
        $client = new DB($this->getParameter('app.db_url'));
        $collection = $client->selectCollection($this->getParameter('app.db_name'), $this->getParameter('app.db_collection'));
        try {
            // try to update item by id
            $result = $collection->updateOne(['_id' => new ObjectId($id)], ['$set' => $newData]);
            // if item has been modified
            if ($result->getModifiedCount() === 1) {
                return $this->json([
                    'id' => $id,
                    'message' => 'Resume has been successfully updated'
                ], 200);
            } // if item not found
            else if ($result->getMatchedCount() === 0) {
                return $this->json([
                    'code' => 404,
                    'message' => 'Resume with id ' . $id . ' not found'
                ], 404);
            }
        } catch (\Exception $exception) {
            return $this->json([
                'code' => 400,
                'message' => 'Incorrect resume id'
            ], 400);
        }
        return $this->json([
            'id' => $id,
            'message' => 'Nothing changed'
        ], 200);
    }

    /**
     * @Rest\Post("/api/cv/{id}/status/update", name="change_cv_status")
     * @param string $id
     * @param Request $request
     * @return Response
     */
    public function changeCVStatus(string $id, Request $request): Response
    {
        $newStatus = $request->toArray()['new_status'];
        if (Resume::validateNewStatus($newStatus)) {
            $client = new DB($this->getParameter('app.db_url'));
            $collection = $client->selectCollection($this->getParameter('app.db_name'), $this->getParameter('app.db_collection'));
            try {
                // try to update item by id
                $result = $collection->updateOne(['_id' => new ObjectId($id)], ['$set' => ['resumeStatus' => $newStatus]]);
                // if item has been modified
                if ($result->getModifiedCount() === 1) {
                    return $this->json([
                        'id' => $id,
                        'message' => 'Resume status has been successfully updated'
                    ], 200);
                } // if item not found
                else if ($result->getMatchedCount() === 0) {
                    return $this->json([
                        'code' => 404,
                        'message' => 'Resume with id ' . $id . ' not found'
                    ], 404);
                }
            } catch (\Exception $exception) {
                return $this->json([
                    'code' => 400,
                    'message' => 'Incorrect resume id'
                ], 400);
            }
        } else {
            return $this->json([
                'code' => 400,
                'message' => 'Incorrect new status'
            ], 400);
        }
        return $this->json([
            'id' => $id,
            'message' => 'Nothing changed'
        ], 200);
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
        try {
            // validate resume
            Resume::validateCV($request->toArray());
            // try to add resume
            $result = $collection->insertOne($request->toArray());
            return $this->json([
                'id' => $result->getInsertedId()->__toString()
            ],
                201);
        } catch (\Exception $exception) {
            return $this->json([
                'code' => 400,
                'message' => $exception->getMessage()
            ],
                400);
        }
    }
}