<?php

namespace App\Services\Interfaces;

interface TicketServiceInterface
{
    public function getAllTickets(array $filters);
    public function getTicketById($id);
    public function createTicket(array $data);
    public function updateTicket($id, array $data);
    public function deleteTicket($id);
    public function assignTicket($id, $agentId);
    public function changeStatus($id, $status);
}