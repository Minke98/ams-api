<?php
use Slim\Http\Request;
use Slim\Http\Response;
require_once __DIR__ . '/../helpers/OneSignalHelper.php';

return function (\Slim\App $app) {

    $app->get('/reminder/list', function ($request, $response) {
        $db = $this->get('db_default');
    
        try {
            $params = $request->getQueryParams();
            $userId = $params['user_id'] ?? null;
            $role = $params['role'] ?? null;
    
            if (!$userId || $role === null) {
                return $response->withStatus(400)->withJson([
                    "status" => false,
                    "message" => "user_id dan role diperlukan"
                ]);
            }
    
            // Ambil SDM ID user ini (untuk filter sertifikat)
            $q = $db->prepare("SELECT id FROM mr_sdm WHERE user_id = ?");
            $q->execute([$userId]);
            $mySdm = $q->fetch(PDO::FETCH_ASSOC);
            $mySdmId = $mySdm['id'] ?? null;
    
            $today = new DateTime();
    
            $urgent = [];
            $upcoming = [];
    
            // =====================================================
            // 1. DATA KERUSAKAN – akses tergantung role
            // =====================================================
            if (in_array($role, ['0', '1', '2', '3'])) {
                // Role 0,1,2,3 boleh lihat semua kerusakan
                $qKerusakan = $db->prepare("
                    SELECT k.*, a.nama_alat, s.nama_software
                    FROM mr_laporan_kerusakan k
                    LEFT JOIN mr_alat a ON k.alat_id = a.id
                    LEFT JOIN mr_software s ON k.software_id = s.id
                    WHERE k.status != 3
                ");
                $qKerusakan->execute();
                $kerusakan = $qKerusakan->fetchAll(PDO::FETCH_ASSOC);
    
                foreach ($kerusakan as $k) {
                    $urgent[] = ["type" => "kerusakan", "data" => $k];
                }
            }
    
            // =====================================================
            // 2. DATA SERTIFIKASI
            // =====================================================
            $qSertif = $db->prepare("
                SELECT s.*, u.full_name
                FROM mr_sertifikasi s
                LEFT JOIN mr_sdm sd ON s.sdm_id = sd.id
                LEFT JOIN mr_users u ON sd.user_id = u.id
            ");
            $qSertif->execute();
            $sertifikasi = $qSertif->fetchAll(PDO::FETCH_ASSOC);
    
            foreach ($sertifikasi as $s) {

                // FILTER ROLE → siapa yang boleh lihat apa
                if ($role == '3' || $role == '4' || $role == '2') {
                    // Laboran atau Dosen → hanya sertifikat miliknya sendiri
                    if ($s['sdm_id'] != $mySdmId) continue;
                }
    
                // HITUNG EXPIRED / WARNING
                $exp = new DateTime($s['tanggal_expiry']);
    
                // ❌ jika sudah lewat → jangan tampil
                if ($exp < $today) {
                    continue;
                }
    
                $diff = $today->diff($exp)->days;
    
                // <=30 hari → urgent
                if ($diff <= 30) {
                    $urgent[] = ["type" => "sertifikasi", "data" => $s];
                } else {
                    $upcoming[] = ["type" => "sertifikasi", "data" => $s];
                }
            }
    
            // =====================================================
            // 3. DATA MAINTENANCE
            // =====================================================
            if (in_array($role, ['0', '1', '2', '3'])) {
                // Role 0,1,2,3 boleh lihat maintenance
                $qMaint = $db->prepare("
                    SELECT m.*, a.nama_alat, s.nama_software
                    FROM mr_maintenance m
                    LEFT JOIN mr_alat a ON m.alat_id = a.id
                    LEFT JOIN mr_software s ON m.software_id = s.id
                ");
                $qMaint->execute();
                $maintenance = $qMaint->fetchAll(PDO::FETCH_ASSOC);
    
                foreach ($maintenance as $m) {
    
                    if (!empty($m['tanggal_selesai_maintenance'])) {
                        $selesai = new DateTime($m['tanggal_selesai_maintenance']);
    
                        // ❌ Jika sudah lewat → jangan tampil
                        if ($selesai < $today) {
                            continue;
                        }
    
                        $diff = $today->diff($selesai)->days;
    
                        if ($diff <= 7) {
                            $urgent[] = ["type" => "maintenance", "data" => $m];
                        } else {
                            $upcoming[] = ["type" => "maintenance", "data" => $m];
                        }
                    }
                }
            }
    
            return $response->withJson([
                "status" => true,
                "urgent" => $urgent,
                "upcoming" => $upcoming
            ]);
    
        } catch (Exception $e) {
            return $response->withStatus(500)->withJson([
                "status" => false,
                "message" => $e->getMessage()
            ]);
        }
    });



    $app->get('/cron/notify-expiry', function ($request, $response) {
        $db = $this->get('db_default');
        $today = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        $today->setTime(0, 0, 0);

        $daysBeforeExpiry = 30;
        $daysBeforeUrgent = 7;

        $notifResult = [];

        try {
            // Ambil user aktif
            $stmtUsers = $db->prepare("
                SELECT id, role, player_id
                FROM mr_users
                WHERE is_claim = 1
                AND role IN ('0','1','3','4')
            ");
            $stmtUsers->execute();
            $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

            foreach ($users as $user) {

                $role     = $user['role'];
                $userId   = $user['id'];
                $playerId = $user['player_id'];

                if (!$playerId) continue;

                // Ambil SDM jika role 3/4
                $sdmIds = [];
                if (in_array($role, ['3','4'])) {
                    $stmtSdm = $db->prepare("SELECT id FROM mr_sdm WHERE user_id = ?");
                    $stmtSdm->execute([$userId]);
                    $sdmIds = $stmtSdm->fetchAll(PDO::FETCH_COLUMN);

                    if (empty($sdmIds)) continue;
                }

                // ===============================
                //     NOTIFIKASI SERTIFIKASI
                // ===============================
                if (in_array($role, ['0','1'])) {
                    $qSertif = $db->prepare("SELECT * FROM mr_sertifikasi");
                    $qSertif->execute();
                } else {
                    $in = implode(",", array_fill(0, count($sdmIds), "?"));
                    $qSertif = $db->prepare("SELECT * FROM mr_sertifikasi WHERE sdm_id IN ($in)");
                    $qSertif->execute($sdmIds);
                }

                $sertifikasi = $qSertif->fetchAll(PDO::FETCH_ASSOC);
                $certificateMessages = [];

                foreach ($sertifikasi as $s) {

                    if (empty($s['tanggal_expiry'])) continue;

                    $expiry = new DateTime($s['tanggal_expiry'], new DateTimeZone('Asia/Jakarta'));
                    $expiry->setTime(0, 0, 0);

                    // Jika sudah lewat → expired
                    if ($expiry < $today) {

                        if ($s['status'] != 2) {
                            $db->prepare("
                                UPDATE mr_sertifikasi 
                                SET status = 2 
                                WHERE id = ?
                            ")->execute([$s['id']]);

                            $certificateMessages[] =
                                "Sertifikasi '{$s['nama_sertifikat']}' sudah EXPIRED pada " . indoDate($s['tanggal_expiry']);
                        }

                        continue;
                    }

                    $diffDays = (int)$today->diff($expiry)->days;
                    $customMsg = null;

                    // 30 hari sebelum expired
                    if ($diffDays == $daysBeforeExpiry && $s['reminder_sent'] == 0) {
                        $customMsg =
                            "Sertifikasi '{$s['nama_sertifikat']}' akan expired dalam 30 hari pada " . indoDate($s['tanggal_expiry']);

                        $db->prepare("
                            UPDATE mr_sertifikasi 
                            SET reminder_sent = 1, status = 3 
                            WHERE id = ?
                        ")->execute([$s['id']]);
                    }
                    // Hari H expired
                    elseif ($diffDays == 0) {
                        $customMsg =
                            "Sertifikasi '{$s['nama_sertifikat']}' EXPIRED hari ini (" . indoDate($s['tanggal_expiry']) . ")";

                        $db->prepare("
                            UPDATE mr_sertifikasi 
                            SET status = 2 
                            WHERE id = ?
                        ")->execute([$s['id']]);
                    }
                    // Urgent 1–7 hari
                    elseif ($diffDays <= $daysBeforeUrgent && $diffDays >= 1) {
                        $customMsg =
                            "Sertifikasi '{$s['nama_sertifikat']}' akan expired dalam $diffDays hari pada " . indoDate($s['tanggal_expiry']);
                    }

                    if ($customMsg) {
                        $certificateMessages[] = $customMsg;
                    }
                }

                // ===============================
                //       NOTIFIKASI MAINTENANCE
                // ===============================
                $maintenanceMessages = [];

                if (in_array($role, ['0','1','3'])) {

                    $qMaint = $db->prepare("
                        SELECT 
                            m.id,
                            m.alat_id,
                            m.software_id,
                            m.tanggal_selesai_maintenance,
                            a.nama_alat,
                            s.nama_software
                        FROM mr_maintenance m
                        LEFT JOIN mr_alat a ON m.alat_id = a.id
                        LEFT JOIN mr_software s ON m.software_id = s.id
                    ");
                    $qMaint->execute();
                    $maintenances = $qMaint->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($maintenances as $m) {

                        if (empty($m['tanggal_selesai_maintenance'])) continue;

                        $done = new DateTime($m['tanggal_selesai_maintenance'], new DateTimeZone('Asia/Jakarta'));
                        $done->setTime(0, 0, 0);

                        if ($done < $today) continue;

                        $diffDays = (int)$today->diff($done)->days;

                        if ($diffDays <= 7 && $diffDays >= 0) {

                            // build nama item
                            if (!empty($m['nama_alat']) && !empty($m['nama_software'])) {
                                $label = "{$m['nama_alat']} / {$m['nama_software']}";
                            } elseif (!empty($m['nama_alat'])) {
                                $label = $m['nama_alat'];
                            } elseif (!empty($m['nama_software'])) {
                                $label = $m['nama_software'];
                            } else {
                                $label = "Item Maintenance";
                            }

                            $maintenanceMessages[] =
                                "Maintenance '$label' akan selesai pada " . indoDate($m['tanggal_selesai_maintenance']);
                        }
                    }
                }

                // ===============================
                //      KIRIM NOTIF TERPISAH
                // ===============================

                // Notifikasi Sertifikat
                if (!empty($certificateMessages)) {
                    $msg = implode("\n", $certificateMessages);
                    $notifResult["sertifikat_$userId"] =
                        OneSignalHelper::sendNotification($playerId, $msg, "Reminder Sertifikat");
                }

                // Notifikasi Maintenance
                if (!empty($maintenanceMessages)) {
                    $msg = implode("\n", $maintenanceMessages);
                    $notifResult["maintenance_$userId"] =
                        OneSignalHelper::sendNotification($playerId, $msg, "Reminder Maintenance");
                }
            }

            return $response->withJson([
                'status'  => true,
                'message' => 'Notifikasi berhasil dikirim',
                'result'  => $notifResult
            ]);

        } catch (Exception $e) {

            return $response->withJson([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    });



};
