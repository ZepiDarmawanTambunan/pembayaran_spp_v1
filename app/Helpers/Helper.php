<?php
function getNamaKelompok()
{
    return [
        'A' => 'A',
        'B' => 'B',
        'C' => 'C',
    ];
}

function bulanSPP()
{
    return [
        7,8,9,10,11,12,1,2,3,4,5,6
    ];
}


function getStatusAnanda()
{
    return [
        'aktif' => 'aktif',
        'non-aktif' => 'non-aktif'
    ];
}

function ubahNamaBulan($angka)
{
    $namaBulan = [
        "" => "",
        "1"=>"Jan",
        "2"=>"Feb",
        "3"=>"Mar",
        "4"=>"Apr",
        "5"=>"Mei",
        "6"=>"Jun",
        "7"=>"Jul",
        "8"=>"Ags",
        "9"=>"Sept",
        "10"=>"Okt",
        "11"=>"Nov",
        "12"=>"Des",
    ];
    return $namaBulan[intval($angka)];
}

function formatRupiah($nominal, $prefix = null)
{
    $prefix = $prefix ? $prefix : 'Rp. ';
    return $prefix.number_format($nominal, 0, ',', '.');
}

function terbilang($x) {
    $angka = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];

    if ($x < 12)
      return " " . $angka[$x];
    elseif ($x < 20)
      return terbilang($x - 10) . " belas";
    elseif ($x < 100)
      return terbilang($x / 10) . " puluh" . terbilang($x % 10);
    elseif ($x < 200)
      return "seratus" . terbilang($x - 100);
    elseif ($x < 1000)
      return terbilang($x / 100) . " ratus" . terbilang($x % 100);
    elseif ($x < 2000)
      return "seribu" . terbilang($x - 1000);
    elseif ($x < 1000000)
      return terbilang($x / 1000) . " ribu" . terbilang($x % 1000);
    elseif ($x < 1000000000)
      return terbilang($x / 1000000) . " juta" . terbilang($x % 1000000);
}
