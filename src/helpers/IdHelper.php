<?php
// src/helpers/IdHelper.php

if (!function_exists('generateCustomId')) {
    function generateCustomId() {
        $part1 = rand(100, 999);       // 3 digit
        $part2 = rand(100000, 999999); // 6 digit
        $part3 = rand(10, 99);         // 2 digit

        return $part1 . "-" . $part2 . "-" . $part3;
    }
}


function generateUserId($db) {
    // Ambil ID terakhir
    $sql = "SELECT id FROM mr_users ORDER BY id DESC LIMIT 1";
    $stmt = $db->query($sql);
    $last = $stmt->fetchColumn();

    if (!$last) {
        return "USR001"; // Jika tidak ada data
    }

    // Ambil angka 3 digit terakhir, convert ke integer
    $number = intval(substr($last, 3));

    // Increment
    $newNumber = $number + 1;

    // Format kembali: USR + zero padding 3 digit
    return "USR" . str_pad($newNumber, 3, "0", STR_PAD_LEFT);
}


function generateClientId($db) {
    // Ambil angka terbesar dari ID yang sudah ada
    $stmt = $db->query("
        SELECT MAX(CAST(SUBSTRING(id, 5) AS UNSIGNED)) AS max_num 
        FROM ar_client
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $nextNumber = ($result && $result['max_num']) ? ((int)$result['max_num'] + 1) : 1;

    // Format ke CLT-001, CLT-002, dst
    return sprintf("CLT-%03d", $nextNumber);
}


function generateProjectId($db) {
    $stmt = $db->query("SELECT id FROM ar_client_project ORDER BY created_at DESC LIMIT 1");
    $last = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($last && isset($last['id'])) {
        // Ambil angka terakhir dari ID
        $number = (int)substr($last['id'], 4);
        $nextNumber = $number + 1;
    } else {
        $nextNumber = 1;
    }

    // Format ke PRJ-001, PRJ-002, dst
    return sprintf("PRJ-%03d", $nextNumber);
}

// ===============================
// 🔹 Generate Activity ID
// ===============================
function generateActivityId($db) {
    $stmt = $db->query("SELECT id FROM ar_client_project_activity WHERE id LIKE 'ACT-%' ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetch(PDO::FETCH_ASSOC);

    $num = $last ? (int) str_replace('ACT-', '', $last['id']) + 1 : 1;
    return 'ACT-' . str_pad($num, 3, '0', STR_PAD_LEFT);
}
