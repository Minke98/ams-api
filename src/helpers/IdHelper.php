<?php
// src/helpers/IdHelper.php

// =====================================================
// 🔹 Generate Custom Random ID (opsional)
// =====================================================
if (!function_exists('generateCustomId')) {
    function generateCustomId() {
        $part1 = rand(100, 999);       // 3 digit
        $part2 = rand(100000, 999999); // 6 digit
        $part3 = rand(10, 99);         // 2 digit

        return $part1 . "-" . $part2 . "-" . $part3;
    }
}


// =====================================================
// 🔹 Generate User ID → USR001, USR002, dst.
// =====================================================
function generateUserId($db) {
    $sql = "SELECT id FROM mr_users ORDER BY id DESC LIMIT 1";
    $stmt = $db->query($sql);
    $last = $stmt->fetchColumn();

    if (!$last) {
        return "USR001";
    }

    $number = intval(substr($last, 3));
    $newNumber = $number + 1;

    return "USR" . str_pad($newNumber, 3, "0", STR_PAD_LEFT);
}


// =====================================================
// 🔹 Generate Client ID → CLT-001, CLT-002, dst.
// =====================================================
function generateClientId($db) {
    $stmt = $db->query("
        SELECT MAX(CAST(SUBSTRING(id, 5) AS UNSIGNED)) AS max_num 
        FROM ar_client
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $nextNumber = ($result && $result['max_num']) ? ((int)$result['max_num'] + 1) : 1;

    return sprintf("CLT-%03d", $nextNumber);
}


// =====================================================
// 🔹 Generate Project ID → PRJ-001, PRJ-002, dst.
// =====================================================
function generateProjectId($db) {
    $stmt = $db->query("SELECT id FROM ar_client_project ORDER BY created_at DESC LIMIT 1");
    $last = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($last && isset($last['id'])) {
        $number = (int)substr($last['id'], 4);
        $nextNumber = $number + 1;
    } else {
        $nextNumber = 1;
    }

    return sprintf("PRJ-%03d", $nextNumber);
}


// =====================================================
// 🔹 Generate Activity ID → ACT-001, ACT-002, dst.
// =====================================================
function generateActivityId($db) {
    $stmt = $db->query("SELECT id FROM ar_client_project_activity WHERE id LIKE 'ACT-%' ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetch(PDO::FETCH_ASSOC);

    $num = $last ? (int) str_replace('ACT-', '', $last['id']) + 1 : 1;

    return 'ACT-' . str_pad($num, 3, '0', STR_PAD_LEFT);
}



// =====================================================
// 🔥 NEW — Generate Room ID → ROOM-001, ROOM-002, dst.
// =====================================================
function generateRoomId($db) {
    // Ambil angka terbesar dari id di tabel mr_ruangan
    $stmt = $db->query("
        SELECT MAX(CAST(SUBSTRING(id, 2) AS UNSIGNED)) AS max_num
        FROM mr_ruangan
    ");

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $nextNumber = ($result && $result['max_num'])
        ? ((int)$result['max_num'] + 1)
        : 1;

    // Format R001, R002, R010, dst
    return "R" . str_pad($nextNumber, 3, "0", STR_PAD_LEFT);
}

