<?php

function addUserNotification(PDO $pdo, string $userId, string $type): bool {
    $id = uniqid(); // generate id unik untuk varchar
    $stmt = $pdo->prepare("
        INSERT INTO ar_user_notifications (id, user_id, type) 
        VALUES (:id, :user_id, :type)
    ");
    return $stmt->execute([
        ':id' => $id,
        ':user_id' => $userId,
        ':type' => $type
    ]);
}
