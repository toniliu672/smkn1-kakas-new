<?php
// admin/functions/helpers/dateIndonesia.php

function formatTanggalIndonesia($tanggal) {
    $bulan = array(
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );

    $pecahkan = explode('-', date('d-m-Y', strtotime($tanggal)));
    
    return $pecahkan[0] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[2];
}

function formatTanggalWaktuIndonesia($tanggal) {
    $bulan = array(
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );

    $pecahkan = explode(' ', $tanggal);
    $pecahTanggal = explode('-', date('d-m-Y', strtotime($pecahkan[0])));
    
    return $pecahTanggal[0] . ' ' . $bulan[(int)$pecahTanggal[1]] . ' ' . $pecahTanggal[2] . ' ' . $pecahkan[1];
}