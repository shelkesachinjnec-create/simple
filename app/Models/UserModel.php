<?php

class UserModel extends BaseModel {
    protected string $table = 'users';

    public function findByEmail(string $email): ?array {
        return $this->db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
    }

    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function incrementLoginAttempts(int $id): void {
        $this->db->execute(
            "UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?",
            [$id]
        );
    }

    public function lockAccount(int $id): void {
        $lockedUntil = date('Y-m-d H:i:s', time() + LOCKOUT_DURATION);
        $this->db->execute(
            "UPDATE users SET locked_until = ? WHERE id = ?",
            [$lockedUntil, $id]
        );
    }

    public function resetLoginAttempts(int $id): void {
        $this->db->execute(
            "UPDATE users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?",
            [$id]
        );
    }

    public function isLocked(array $user): bool {
        if ($user['login_attempts'] >= MAX_LOGIN_ATTEMPTS && $user['locked_until']) {
            return strtotime($user['locked_until']) > time();
        }
        return false;
    }

    public function getAll(): array {
        return $this->db->fetchAll(
            "SELECT id, name, email, phone, role, is_active, last_login, created_at FROM users ORDER BY created_at DESC"
        );
    }
}
