<?php

session_start();

include_once "../dwo.lib.php";
include_once "../db.mysql.php";
include_once "../connectdb.php";
include_once "../parameter.php";
include_once "../cekparam.php";
include_once "../header_pdf.php";

// *** Parameters ***
$TahunID = GetSetVar('TahunID');
$ProdiID = GetSetVar('ProdiID');

// *** Init PDF
$pdf = new PDF();
$pdf->SetTitle("Rekapitulasi Jumlah Mahasiswa Berdasarkan Pendapatan Ortu");
$pdf->AddPage();
$lbr = 190;

BuatIsinya($pdf);

$pdf->Output();

// *** Functions ***
function BuatHeadernya($pendapatan, $p) {
  global $lbr;
  $t = 6;
  $p->SetFont('Helvetica', 'B', 12);
  $p->Cell($lbr, $t, "Rekapitulasi Jumlah Mahasiswa Berdasarkan Pendapatan Ortu", 0, 1, 'C');
  $p->Ln($t);
  
  $t = 5;
  $p->SetFont('Helvetica', 'BI', 9);
  
  // Baris 1
  $p->Cell(15, $t, 'Tahun', 'LTR', 0, 'C');
  $p->Cell(13, $t, 'Total', 'LTR', 0, 'R');
  $p->Cell(23*sizeof($agama), $t, 'Pendapatan Ortu', 1, 0, 'C');
  $p->Ln($t);
  
  // Baris 2
  $p->Cell(15, $t, 'Angktn', 'LBR', 0, 'C');
  $p->Cell(13, $t, 'Mhsw', 'LBR', 0, 'R');
  // Agama
  foreach ($pendapatan as $k) {
    $p->Cell(23, $t, $k, 1, 0, 'R');
  }
  $p->Ln($t);
}
function BuatIsinya($p) {
  $t = 6;
  $arrAngkatan = GetArrayAngkatan($arrJml);
  $arrPendapatan = GetArrayPendapatan($arrJmlPendapatan);
  BuatHeadernya($arrPendapatan, $p);
  
  $total = 0;
  $det = array();
  for ($i = 0; $i < sizeof($arrAngkatan); $i++) {
    $angk = $arrAngkatan[$i];
    // Jumlah
    $p->SetFont('Helvetica', 'B', 10);
    $p->Cell(15, $t, $arrAngkatan[$i], 'B', 0);
    
    $p->SetFont('Helvetica', '', 10);
    $jml = number_format($arrJml[$arrAngkatan[$i]]);
    $p->Cell(13, $t, $jml, 'B', 0, 'R');
    $total += $arrJml[$arrAngkatan[$i]];
    
    
    // Kelamin
    foreach ($arrPendapatan as $pendapatan) {
      $jmlkel = $arrJmlPendapatan[$arrAngkatan[$i]][$pendapatan];
      $_jmlkel = ($jmlkel == 0)? '-' : number_format($jmlkel);
      $p->Cell(23, $t, $_jmlkel, 'B', 0, 'R');
      $det['pendapatan_'.$pendapatan] += $jmlkel;
    }
    
    $p->Ln($t);
  }
  
  $p->SetFont('Helvetica', 'B', 10);
  // Menampilkan total
  $p->Cell(15, $t, "TOTAL :", 0, 0, 'R');
  $p->Cell(13, $t, number_format($total), 0, 0, 'R');
  // Total Kelamin
  foreach ($arrPendapatan as $pendapatan) {
    $jml = $det['pendapatan_'.$pendapatan];
    $_jml = number_format($jml);
    $p->Cell(23, $t, $_jml, 0, 0, 'R');
  }
  $p->Ln($t);
}
function GetArrayAngkatan(&$arrJml) {
	$whr = (!empty($_SESSION['ProdiID']))? " where ProdiID='$_SESSION[ProdiID]' " : '';
  $s = "select m.TahunID, count(m.MhswID) as JML
    from mhsw m $whr
    group by m.TahunID
    order by m.TahunID desc";
  $r = _query($s);
  $arr = array();
  while ($w = _fetch_array($r)) {
    $arr[] = $w['TahunID'];
    $arrJml[$w['TahunID']] = $w['JML'];
  }
  return $arr;
}
function GetArrayPendapatan(&$arrJmlPendapatan) {
	$whr = (!empty($_SESSION['ProdiID']))? " AND ProdiID='$_SESSION[ProdiID]' " : '';
  $s = "select m.TahunID, count(m.MhswID) as JML, a.PenghasilanAyah
    from mhsw m 
    left outer join aplikan a on a.PMBID=m.PMBID
    where m.TahunID in (2014,2015)
    group by a.PenghasilanAyah, m.TahunID";
  $r = _query($s);
  $arr = array();
  while ($w = _fetch_array($r)) {
    if (array_search($w['PenghasilanAyah'], $arr) === false) $arr[] = $w['PenghasilanAyah'];
    $arrJmlPendapatan[$w['TahunID']][$w['PenghasilanAyah']] = $w['JML'];
    
  }
  return $arr;
}

?>
