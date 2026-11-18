<?php
require_once __DIR__ . '/../includes/functions.php';

class User {
    public static function findById(int $id): ?array {
        $stmt = db()->prepare('SELECT * FROM users WHERE user_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function emailExists(string $email): bool {
        return self::findByEmail($email) !== null;
    }

    public static function studentNumberExists(string $num): bool {
        $stmt = db()->prepare('SELECT 1 FROM users WHERE student_number = ?');
        $stmt->execute([$num]);
        return (bool)$stmt->fetchColumn();
    }

    public static function create(string $studentNumber, string $fullName, string $email, string $password, string $role = 'student'): int {
        $stmt = db()->prepare(
            'INSERT INTO users (student_number, full_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$studentNumber, $fullName, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        return (int)db()->lastInsertId();
    }

    public static function updatePassword(int $userId, string $newPassword): void {
        $stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?');
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
    }

    public static function all(): array {
        return db()->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
    }

    public static function count(): int {
        return (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    // Average rating received by a user (as a seller), plus review count.
    public static function ratingStats(int $userId): array {
        $stmt = db()->prepare(
            'SELECT AVG(rating) avg_rating, COUNT(*) cnt
             FROM reviews WHERE reviewed_user_id = ?'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return ['avg' => $row['avg_rating'] !== null ? (float)$row['avg_rating'] : 0.0,
                'count' => (int)$row['cnt']];
    }
}
