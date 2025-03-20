<?php

namespace App\Services\Interfaces;

interface TicketServiceInterface
{
    public function getAllTickets(array $filters = []);
    public function getTicketById(int $id);
    public function createTicket(array $data);
    public function updateTicket(int $id, array $data);
    public function deleteTicket(int $id);
    public function assignTicket(int $id, int $agentId);
    public function changeStatus(int $id, string $status);
}