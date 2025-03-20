<?php

namespace App\Services\Interfaces;

interface TicketServiceInterface
{
    public function getAllTickets(array $filters = [], int $perPage = 15);
    public function getTicketById(int $id);
    public function createTicket(array $data);
    public function updateTicket(int $id, array $data);
    public function deleteTicket(int $id);
    public function assignTicket(int $ticketId, int $agentId);
    public function changeStatus(int $ticketId, string $status);
}
