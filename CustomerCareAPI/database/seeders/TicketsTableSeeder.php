<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketsTableSeeder extends Seeder
{
    public function run()
    {
        $client = User::where('role', 'client')->first();
        $agent = User::where('role', 'agent')->first();

        // Create tickets
        Ticket::create([
            'title' => 'Cannot login to my account',
            'description' => 'I am trying to login but it says invalid credentials',
            'status' => 'open',
            'priority' => 'high',
            'user_id' => $client->id,
        ]);

        Ticket::create([
            'title' => 'Feature request: dark mode',
            'description' => 'Please add dark mode to the application',
            'status' => 'in_progress',
            'priority' => 'medium',
            'user_id' => $client->id,
            'agent_id' => $agent->id,
        ]);

        Ticket::create([
            'title' => 'Billing issue',
            'description' => 'I was charged twice for my subscription',
            'status' => 'resolved',
            'priority' => 'urgent',
            'user_id' => $client->id,
            'agent_id' => $agent->id,
        ]);
    }
}