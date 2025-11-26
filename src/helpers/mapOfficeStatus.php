<?php
// app/helpers/mapOfficeStatus.php

function distanceInMeters(float $lat1, float $lon1, float $lat2, float $lon2): float {
    $earthRadius = 6371000; // in meters

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon/2) * sin($dLon/2);

    $c = 2 * atan2(sqrt($a), sqrt(1-$a));

    return $earthRadius * $c;
}

function mapOfficeStatus(array $offices, float $lat, float $lon, float $officeRadius): array {
    $officeStatusSimple = [
        'jakarta_office' => false,
        'bandung_office' => false,
    ];
    $insideAnyOffice = false;

    foreach ($offices as $office) {
        $dist = distanceInMeters($lat, $lon, (float)$office['lat'], (float)$office['lon']);
        $inside = $dist <= $officeRadius;

        $officeNameLower = strtolower($office['office_name']);
        if (array_key_exists($officeNameLower, $officeStatusSimple)) {
            $officeStatusSimple[$officeNameLower] = $inside;
        }

        if ($inside) {
            $insideAnyOffice = true;
        }
    }

    return [
        'jakarta_office' => $officeStatusSimple['jakarta_office'],
        'bandung_office' => $officeStatusSimple['bandung_office'],
        'is_dinas' => !$insideAnyOffice,
    ];
}

