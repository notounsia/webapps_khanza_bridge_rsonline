<?php

// Data untuk update tempat tidur
function update_tt($id_tt, $jumlah, $terpakai)
{
    $rs_id = "6110012"; //kode rs dari kemenkes
    $pass = "Singgemati@#$!321";
    
    //Get current Timestamp
    $dt = new DateTime("now", new DateTimeZone("UTC"));
    $timestamp = $dt->getTimestamp();
    if($terpakai > $jumlah){
    $terpakai = $jumlah;
    }
    // Data yang akan dikirimkan
    $data = [
        "id_t_tt" => $id_tt,
        "jumlah" => $jumlah,
        "terpakai" => $terpakai
    ];

    $postdata = json_encode($data);

    // Inisialisasi cURL
    $curl = curl_init();

    // Set cURL options
    curl_setopt($curl, CURLOPT_URL, "https://sirs.kemkes.go.id/fo/index.php/Fasyankes");
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "X-rs-id: ".$rs_id,
        "X-Timestamp: ".$timestamp,
        "X-pass: ".$pass,
        "Content-type: application/json"
    ));
    // Eksekusi cURL dan ambil hasilnya
    $result = curl_exec($curl);
    // Tutup cURL
    curl_close($curl);
    // Mengembalikan hasil respons JSON
    return $result;

}
include('../../conf/config.php');

// Query untuk mengambil data
$sql = "SELECT
    a.id_t_tt,
    a.jumlah as jumlahnye,
    SUM(CASE WHEN b.stts_pulang = '-' THEN 1 ELSE 0 END) AS isi,
        a.keterangan
FROM
    z_mapping_kamar_siranap a 
LEFT JOIN 
    kamar_inap b ON SUBSTRING_INDEX(b.kd_kamar, '-', 1) = a.kd_kamar 
GROUP BY 
    a.kd_kamar";

// Eksekusi query
$query = bukaquery2($sql, '');

// Loop melalui hasil query
foreach($query as $row) 
{
    // Panggil fungsi update_tt dengan data dari query
    $kirim = update_tt($row['id_t_tt'], $row['jumlahnye'], $row['isi']);

    // Decode JSON response
    $response = json_decode($kirim, true);
    var_dump($row);

    // Tampilkan status dan message dari respons
    if (isset($response['fasyankes'][0])) {
        $status = $response['fasyankes'][0]['status'];
        $message = $response['fasyankes'][0]['message'];

        echo "Status: $status, Message: $message<br>";
    } else {
        echo "Response tidak valid atau tidak ada data <br>";
    }
}
?>
