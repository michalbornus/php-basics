<html>
 <head>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
 </head>
 <style>
  #left {background-color:yellow; float:left; min-width:40%;}
  #right {background-color:#83ff83; float:right;}
  table, th, td {font-size:14px; padding:2px 9px; border:1px solid; border-collapse:collapse; background-color:#cce6ff;} 
 </style>
<body>


<div id="left">
Sprawdź kurs spółki: 
<form>
 <input type="text" name="akcja">
 <input type="submit" value="send">
</form>

<form>
 <select name="query">
  <option value="wycena">wycena</option>
  <option value="all">wszystkie</option>
 </select><br>
 <input type="submit">
</form>

<?php
$rodzaj = $_GET['query'];

$source = file_get_contents("http://akcje.mybank.pl");
preg_match ('@<table class="g_tab" cellspacing="1" style="width:568px">(.*)</table>@s', $source, $tabelanotowan);

//preg_match_all ('@<b>[A-Z]{3,20}</b>@',$source, $nazwy);
//preg_match_all ('@(<b>[0-9]{1,4}.[0-9]{2}</b>)@', $source, $abc);
//print_r ($nazwy);
//print_r ($abc);
//print_r ($tabelanotowan[0]);

preg_match_all ('@<b>(.*)</b>@', $tabelanotowan[0], $grube);
//print_r ($grube[0]);
$grube[0] = str_replace("<b>","",$grube[0]);
$grube[0] = str_replace("</b>","",$grube[0]);

$ilosc_el = count($grube[0]);
//echo $ilosc_el;

for ($x=0,$y=1; $x<$ilosc_el,$y<$ilosc_el; $x+=2,$y+=2){ 
    $spolki[$grube[0][$x]]=$grube[0][$y];//stworzenie tabl asocjacyjnej z indeksowanej
}

//print_r ($grube);
//echo $spolki['<b>ABCDATA</b>'];
$akc = strtoupper ($_GET['akcja']);
//echo $spolki[$akc].'<br>';//wyswietla cene wyszukanej akcji
//  ------  pobranie listy spolek z db -------
$con = mysql_connect('localhost', 'root', 'michal1vps') or die ('nie polaczono');
$db = mysql_select_db('GPW');

$wsr = mysql_query("select sr_wolne from aktywa order by id_a desc limit 1") or die ('aktywa');
$got = mysql_result($wsr,0);
echo 'srodki wolne: '.$got.'<br>';


switch ($rodzaj) {
   case all:
   echo "<table id='tab01'><tr><th>name</th><th>cena kupna</th><th>cena akt</th></tr>";
$q = mysql_query ("select * from kupione");
while ($row = mysql_fetch_assoc($q)) {
   echo "<tr><td>".$row['nazwa']."</td><td>".$row['cena']."</td><td>".$spolki[$row['nazwa']]."</td></tr>";
}
echo "</table>";
break;

   case wycena:
  //$total = 0;
$q= mysql_query ("select * from kupione where stan = 0");
echo "<table><tr><th>nazwa</th><th>ilosc</th><th>cena k</th><th>koszt k</th><th>cena akt</th><th>wycena akt</th><th>zysk str</th><th>sell</th></tr>";
while ($row = mysql_fetch_assoc($q)){
  $name = $row['nazwa'];
  $wycena = round($row['ilosc']*$spolki[$name]*0.9981, 2);
  $profit = round($wycena - $row['razem'], 2);
   if ($profit > 0) {
     $prof = "<td style='background-color:green'>".$profit."</td>";
   } else {
     $prof = "<td style='background-color:red'>".$profit."</td>";
   }
  echo "<tr><td>".$name."</td><td>".$row['ilosc']."</td><td>".$row['cena']."</td><td>".$row['razem']."</td><td>".$spolki[$name]."</td><td>".$wycena."</td>".$prof."</td><td><a href='sell.php?id=".$row['id']."&ilosc=".$row['ilosc']."'>sprz</a></td></tr>";
   $total += $profit;
$suma += $wycena;
}
echo "</table><br>";
echo "profit: ".$total."<br>";
echo "akcje łącznie: ".$suma;
//var_dump ($wycena);
echo "<br> razem obecnie: <b>".($got + $suma)."</b>";
break;

default: echo "wybierz opcje";
}
?>

</div>
<!-- -----------------      prawo lewo      ------------        -->
<div id="right">

<h3>dodaj spolke</h3>

<table>
 <form action="dodaj.php">
  <tr><td>nazwa</td><td><input type="text" name="nazwa"></td></tr>
  <tr><td>cena</td><td><input type="text" name="cena"></td></tr>
  <tr><td>ilosc</td><td><input type="text" name="ilosc"></td></tr>
  <tr><td><center><input type="submit"></center></td></tr>
 </form>
</table>
<br><br>

<h3>dywidendy</h3>

<?php
$d = date('Y-m-d');

$wyn = mysql_query("select * from dywidendy");
echo "<table>";
while ($row = mysql_fetch_assoc($wyn)) {
      echo "<tr><td>".$row['nazwa_d']."</td><td>".$row['dzien_dyw']."</td><td>".$row['dzien_wypl']."</td>";
        if ($d >= $row['dzien_wypl']) {
          echo "<td>".$row['wartosc_netto']."</td></tr>"; 
      $dyw_wypl += $row['wartosc_netto'];
          }
}
echo "</table>";
$d = mysql_query("select sum(wartosc_netto) from dywidendy");
$dz = mysql_result($d, 0);
echo '<br>dywidendy netto zaksiegowane: '.$dz;
echo '<br>dywidendy netto wyplacone: '.$dyw_wypl;
?>



</div>





</body></html>
