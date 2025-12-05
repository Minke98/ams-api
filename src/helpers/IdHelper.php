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
// 🔹 Generic function untuk generate ID aman
// =====================================================
function generateSafeId($db, $table, $prefix, $length, $substrStart = null) {
    $substrStart = $substrStart ?? strlen($prefix) + 1;
    
    // Ambil nomor terbesar
    $stmt = $db->query("
        SELECT MAX(CAST(SUBSTRING(id, $substrStart) AS UNSIGNED)) AS max_num
        FROM $table
        WHERE id LIKE '$prefix%'
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $nextNumber = ($result && $result['max_num']) ? ((int)$result['max_num'] + 1) : 1;

    // Loop untuk cek apakah ID sudah ada
    do {
        $newId = $prefix . str_pad($nextNumber, $length, "0", STR_PAD_LEFT);
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM $table WHERE id = :id");
        $checkStmt->execute([':id' => $newId]);
        $exists = $checkStmt->fetchColumn() > 0;
        if ($exists) {
            $nextNumber++;
        }
    } while ($exists);

    return $newId;
}

// =====================================================
// 🔹 Generate User ID → USR001, USR002, dst.
// =====================================================
function generateUserId($db) {
    return generateSafeId($db, 'mr_users', 'USR', 3, 4);
}

// =====================================================
// 🔹 Generate SDM ID → SDM001, SDM002, dst.
// =====================================================
function generateSdmId($db) {
    return generateSafeId($db, 'mr_sdm', 'SDM', 3, 4);
}

// =====================================================
// 🔹 Generate Client ID → CLT-001, CLT-002, dst.
// =====================================================
function generateClientId($db) {
    return generateSafeId($db, 'ar_client', 'CLT-', 3, 5);
}

// =====================================================
// 🔹 Generate Project ID → PRJ-001, PRJ-002, dst.
// =====================================================
function generateProjectId($db) {
    return generateSafeId($db, 'ar_client_project', 'PRJ-', 3, 5);
}

// =====================================================
// 🔹 Generate Activity ID → ACT-001, ACT-002, dst.
// =====================================================
function generateActivityId($db) {
    return generateSafeId($db, 'ar_client_project_activity', 'ACT-', 3, 5);
}

// =====================================================
// 🔹 Generate Room ID → R001, R002, dst.
// =====================================================
function generateRoomId($db) {
    return generateSafeId($db, 'mr_ruangan', 'R', 3, 2);
}

// =====================================================
// 🔹 Generate Room Usage ID → P001, P002, dst.
// =====================================================
function generateRoomUsageId($db) {
    return generateSafeId($db, 'mr_penggunaan_ruangan', 'P', 3, 2);
}

// =====================================================
// 🔹 Generate Alat ID → A001, A002, dst.
// =====================================================
function generateAlatId($db) {
    return generateSafeId($db, 'mr_alat', 'A', 3, 2);
}

// =====================================================
// 🔹 Generate Software ID → SW001, SW002, dst.
// =====================================================
function generateSoftwareId($db) {
    return generateSafeId($db, 'mr_software', 'SW', 3, 3);
}

// =====================================================
// 🔹 Generate Maintenance ID → MT0001, MT0002, dst.
// =====================================================
function generateMaintenanceId($db) {
    return generateSafeId($db, 'mr_maintenance', 'MT', 4, 3);
}

function generateReportDamageId($db) {
    return generateSafeId($db, 'mr_laporan_kerusakan', 'LPR', 4, 4);
}

function generateCertificateId($db) {
    return generateSafeId($db, 'mr_sertifikasi', 'SRT', 3, 4);
}
