<?php

class EmployeeHelper
{
    public static function getPlayerIdsByEmployee($db, $employeeId)
    {
        $stmt = $db->prepare("SELECT player_id FROM ar_employee WHERE id = :id AND player_id IS NOT NULL");
        $stmt->execute(["id" => $employeeId]);
        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), "player_id");
    }

   public static function getPlayerIdsByPositions($db, array $positions)
    {
        // Buat placeholder sesuai jumlah posisi
        $inQuery = implode(",", array_fill(0, count($positions), "?"));

        $sql = "SELECT e.player_id 
                FROM ar_employee e 
                LEFT JOIN ar_position p ON e.position_id = p.id 
                WHERE p.id IN ($inQuery) 
                AND e.player_id IS NOT NULL";

        $stmt = $db->prepare($sql);
        $stmt->execute($positions);

        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), "player_id");
    }

    // ✅ Tambahkan method ini
    public static function getUserIdByPlayerId($db, $playerId)
    {
        $stmt = $db->prepare("SELECT id FROM ar_employee WHERE player_id = :player_id LIMIT 1");
        $stmt->execute(["player_id" => $playerId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? $row['id'] : null;
    }

}
