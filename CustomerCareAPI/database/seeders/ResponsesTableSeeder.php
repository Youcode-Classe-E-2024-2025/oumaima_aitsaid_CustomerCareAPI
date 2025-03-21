<?php

namespace Database\Seeders;

use App\Models\Response;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResponsesTableSeeder extends Seeder
{
    public function run()
    {
        $tickets = Ticket::all();
        $agent = User::where('role', 'agent')->first();
        $client = User::where('role', 'client')->first();

        foreach ($tickets as $ticket) {
            // Agent response
            Response::create([
                'content' => 'Thank you for your ticket. We are looking into this issue.',
                'ticket_id' => $ticket->id,
                'user_id' => $agent->id,
            ]);

            // Client response
            Response::create([
                'content' => 'Thank you for your quick response. I look forward to a resolution.',
                'ticket_id' => $ticket->id,
                'user_id' => $client->id,
            ]);
        }
    }
}