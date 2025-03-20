<?php

namespace App\Services\Interfaces;

interface responseServiceInterface
{
    public function getResponsesByTicketId(int $ticketId);
    public function createResponse(array $data);
    public function updateResponse(int $id, array $data);
    public function deleteResponse(int $id);
}
