<?php

namespace App\Services\Interfaces;

interface ResponseServiceInterface
{
    public function getTicketResponses($ticketId);
    public function getResponseById($id);
    public function createResponse(array $data);
    public function updateResponse($id, array $data);
    public function deleteResponse($id);
}