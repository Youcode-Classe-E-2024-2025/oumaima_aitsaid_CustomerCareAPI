## 📋Table of Contents
* Overview
* Features
* System Requirements
* Installation
* Configuration
* API Documentation
* Testing
* Frontend Setup
* Project Structure
* Service Layer Pattern
* Contributing
* License
## 🌟 Overview
CustomerCareAPI is a robust customer support ticket management system built with Laravel. It provides a RESTful API for managing support tickets, user authentication, and ticket tracking. The system follows the Service Layer Design Pattern for clean code organization and maintainability.

## ✨ Features
User Authentication: Secure login, registration, and token-based authentication using Laravel Sanctum
Role-Based Access Control: Different permissions for clients, agents, and administrators
Ticket Management: Create, read, update, and delete support tickets
Response Tracking: Full history of communications for each ticket
Advanced Filtering: Search, sort, and filter tickets by various parameters
API Documentation: Comprehensive Swagger documentation
Frontend Integration: JavaScript frontend for consuming the API
## 🖥️ System Requirements
PHP 8.1 or higher
Composer 2.0+
MySQL 5.7+ or PostgreSQL 10+
Node.js 16+ and npm (for frontend)
Git
## 🚀 Installation
Clone the Repository
git clone https://github.com/Youcode-Classe-E-2024-2025/oumaima_aitsaid_CustomerCareAPI.git
cd CustomerCareAPI

Install PHP Dependencies
composer install

Set Up Environment Variables
cp .env.example .env
php artisan key:generate

Edit the .env file to configure your database connection:

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=customer
DB_USERNAME=postgres
DB_PASSWORD=1234

Run Migrations and Seeders
php artisan migrate
php artisan db:seed

This will create the necessary database tables and populate them with sample data.

Install Laravel Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate

Generate Swagger Documentation
php artisan l5-swagger:generate

Start the Development Server
php artisan serve

The API will be available at http://localhost:8000.

## ⚙️ Configuration
User Roles
The system supports three user roles:

Client: Can create tickets and view their own tickets
Agent: Can view and respond to tickets assigned to them
Admin: Has full access to all tickets and user management
Default admin credentials:

Email: admin@example.com
Password: password
Rate Limiting
API rate limiting is configured in app/Providers/RouteServiceProvider.php. By default:

Authentication endpoints: 5 requests per minute
Other endpoints: 60 requests per minute
## 📚 API Documentation
Swagger documentation is available at http://localhost:8000/api/documentation.

Authentication Endpoints
POST /api/register - Register a new user
POST /api/login - Login and get access token
POST /api/logout - Logout and invalidate token
GET /api/user - Get authenticated user information
Ticket Endpoints
GET /api/tickets - List all tickets (filtered by user role)
POST /api/tickets - Create a new ticket
GET /api/tickets/{id} - Get a specific ticket
PUT /api/tickets/{id} - Update a ticket
DELETE /api/tickets/{id} - Delete a ticket
POST /api/tickets/{id}/responses - Add a response to a ticket
GET /api/tickets/{id}/responses - Get all responses for a ticket
## 🧪 Testing
Run the test suite with:

php artisan test

Or for more detailed output:

./vendor/bin/phpunit

Test Coverage
Generate a test coverage report:

XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html reports/

The coverage report will be available in the reports directory.

## 🖌️ Frontend Setup
The frontend is built with [Vue.js/React/Angular] and communicates with the API.

Install Frontend Dependencies
cd frontend
npm install

Configure API URL
Edit the .env file in the frontend directory:

VITE_API_URL=http://localhost:8000/api

Start the Frontend Development Server
npm run dev

The frontend will be available at http://localhost:5173.

## 📂 Project Structure
CustomerCareAPI/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── API/
│   │   │       ├── AuthController.php
│   │   │       └── TicketController.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Ticket.php
│   │   └── Response.php
│   └── Services/
│       ├── Interfaces/
│       │   ├── AuthServiceInterface.php
│       │   └── TicketServiceInterface.php
│       ├── AuthService.php
│       └── TicketService.php
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
├── tests/
│   ├── Feature/
│   │   └── API/
│   └── Unit/
│       └── Services/
└── frontend/
    ├── src/
    │   ├── components/
    │   ├── views/
    │   └── services/
    └── package.json

## 🏗️ Service Layer Pattern
This project implements the Service Layer Design Pattern to separate business logic from controllers:

Controllers: Handle HTTP requests and responses
Services: Contain business logic and interact with models
Interfaces: Define contracts for services to implement
Models: Represent database entities and relationships
Example:

## Controller
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $ticket = $this->ticketService->createTicket($request->all());

    return response()->json([
        'message' => 'Ticket created successfully',
        'ticket' => $ticket
    ], 201);
}

## Service
public function createTicket(array $data)
{
    $ticket = Ticket::create([
        'title' => $data['title'],
        'description' => $data['description'],
        'status' => 'open',
        'priority' => $data['priority'] ?? 'medium',
        'user_id' => auth()->id(),
    ]);

    // Additional business logic here

    return $ticket;
}

## 🤝 Contributing
Fork the repository
Create a feature branch: git checkout -b feature-name
Commit your changes: git commit -m 'Add some feature'
Push to the branch: git push origin feature-name
Submit a pull request
## 📄 License
This project is licensed under the MIT License - see the LICENSE file for details.

Developed with ❤️ oumaima ait said.