<?php
use Slim\Http\Request;
use Slim\Http\Response;
require_once __DIR__ . '/../helpers/IdHelper.php';

return function (\Slim\App $app) {


$app->get('/reminder/list', function ($request, $response) {
    $db = $this->get('db_default');

    $params = $request->getQueryParams();
    $modul = isset($params['modul']) ? $params['modul'] : null;
    $status = isset($params['status']) ? $params['status'] : null;
    $tanggalAwal = isset($params['tgl_awal']) ? $params['tgl_awal'] : null;
    $tanggalAkhir = isset($params['tgl_akhir']) ? $params['tgl_akhir'] : null;

    try {
        $query = "SELECT * FROM mr_reminder_schedule WHERE 1=1";
        $bindings = [];

        if ($modul) {
            $query .= " AND modul_terkait = :modul";
            $bindings[':modul'] = $modul;
        }
        if ($status) {
            $query .= " AND status = :status";
            $bindings[':status'] = $status;
        }
        if ($tanggalAwal) {
            $query .= " AND tanggal_reminder >= :tgl_awal";
            $bindings[':tgl_awal'] = $tanggalAwal;
        }
        if ($tanggalAkhir) {
            $query .= " AND tanggal_reminder <= :tgl_akhir";
            $bindings[':tgl_akhir'] = $tanggalAkhir;
        }

        $stmt = $db->prepare($query);
        $stmt->execute($bindings);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $response->withJson([
            'status' => true,
            'data' => $data
        ]);
    } catch (Exception $e) {
        return $response->withStatus(500)->withJson([
            'status' => false,
            'message' => $e->getMessage()
        ]);
    }
});
};