<?php

namespace App\Services\Interfaces;

interface UserServiceInterface
{
    public function register(array $data);
    public function login(array $credentials);
    public function logout();
    public function getUsers(array $filters = []);
    public function getUserById(int $id);
}
