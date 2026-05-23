<?php

namespace Models;
use PDO;

class AdminUser
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admin_users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    public function verifyPassword(string $username, string $password): bool
    {
        $user = $this->findByUsername($username);
        if (!$user) {
            return false;
        }
        return password_verify($password, $user['password']);
    }
}