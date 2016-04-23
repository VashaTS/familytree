<?php
// drzewo genealogiczne
$ver='1.0c';
// 2012-07-01
ini_set( 'display_errors', 'Off' );
error_reporting( E_ALL );
$baz=mysql_connect('192.168.0.12','sajmon31','frugo_zielone');
//$baz=mysql_connect('127.0.0.1','root','darthv4der');
mysql_select_db('sajmon31_rodzina');
//mysql_select_db('rodzina');
mysql_query('SET NAMES utf-8');
$settings=mysql_fetch_assoc(mysql_query('select * from settings'));
if(isset($_COOKIE['zal'])){
	setcookie('zal',$_COOKIE['zal'],(time()+60*5));
	$currentuser=mysql_fetch_assoc(mysql_query('select id,flags from users where name="'.$_COOKIE['zal'].'" limit 1;'));
}
require('fpdf/fpdf.php');
class PDF extends FPDF { 
    function Rotate($angle,$x=-1,$y=-1) { 
        if($x==-1) 
            $x=$this->x; 
        if($y==-1) 
            $y=$this->y; 
        if($this->angle!=0) 
            $this->_out('Q'); 
        $this->angle=$angle; 
        if($angle!=0) 
        { 
            $angle*=M_PI/180; 
            $c=cos($angle); 
            $s=sin($angle); 
            $cx=$x*$this->k; 
            $cy=($this->h-$y)*$this->k; 
            $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy)); 
        } 
    } 
}

$request=explode('?',$_SERVER['REQUEST_URI']);
$vars=explode(',',$request[1]);
if(strlen($vars[0])>2) $id=$vars[0];
else $id='main';
$id2=$vars[1];
$id3=$vars[2];
$id4=$vars[3];
function checkname(){ 
	$res=mysql_query('select name from users;');
	$pasuje=false;
	$pasuje2=false;
	for($i=0;$i<mysql_num_rows($res);$i+=1){
		$row=mysql_fetch_assoc($res);
		if($_COOKIE['zal']==$row['name']) $pasuje=true;
	}
	if($pasuje==true){
		$row2=mysql_fetch_assoc(mysql_query('select ssid,ssid_time from users where name="'.$_COOKIE['zal'].'";'));
		if(($_COOKIE['ssid']=$row2['ssid'])) $pasuje2=true;
	}
	return $pasuje2;
}
function iso2utf(){
   $tabela = Array(
    "\xb1" => "\xc4\x85",
      "\xa1" => "\xc4\x84",
      "\xe6" => "\xc4\x87",
      "\xc6" => "\xc4\x86",
      "\xea" => "\xc4\x99",
      "\xca" => "\xc4\x98",
      "\xb3" => "\xc5\x82",
      "\xa3" => "\xc5\x81",
      "\xf3" => "\xc3\xb3",
      "\xd3" => "\xc3\x93",
      "\xb6" => "\xc5\x9b",
      "\xa6" => "\xc5\x9a",
      "\xbf" => "\xc5\xbc",
      "\xaf" => "\xc5\xbb",
      "\xbc" => "\xc5\xba",
      "\xac" => "\xc5\xb9",
      "\xf1" => "\xc5\x84",
      "\xd1" => "\xc5\x83");
   return $tabela;
  }
  function ISO88592_2_UTF8($tekst){
   return strtr($tekst, iso2utf());
  }
  function UTF8_2_ISO88592($tekst){
   return strtr($tekst, array_flip(iso2utf()));
  }
function odmiana_m($imie){
	$last2=substr($imie,-2,2);
	if(strlen(utf8_decode($last2))==1){
		switch(substr($imie,-3,1)){
			case 'a': $new=substr($imie,0,strlen($imie)-2).'ła'; break;
			case 'e': $new=substr($imie,0,strlen($imie)-3).'ła'; break;
		}
	}
	else{
	switch($last2){
		case 'ni': $new=$imie.'ego'; break;
		case 'ty':
		case 'zy':
		case 'ry': $new=substr($imie,0,strlen($imie)-1).'ego'; break;
		case 'ek': $new=substr($imie,0,strlen($imie)-2).'ka'; break;
		case 'er': $new=substr($imie,0,strlen($imie)-2).'ra'; break;
		default: $new=$imie.'a'; break;
	}}
	return $new;
}
function odmiana_k($imie){
	$post=substr($imie,-2,1);
	switch($post){
		case 'i':
		case 'j':
		case 'g':
		case 'l':
		case 'k': $new=substr($imie,0,strlen($imie)-1).'i'; break;
		default: $new=substr($imie,0,strlen($imie)-1).'y'; break;
	}
	return $new;
}
function html_start(){
	global $ver,$settings;
	header("Content-Type: text/html; charset=UTF-8");
	echo('<html><head><title>Drzewo genealogiczne');
	if(isset($_COOKIE['zal'])&checkname()) echo(' v'.$ver);
	echo('</title><link rel="stylesheet" type="text/css" href="rodzina.css" />
		<script type="text/javascript" src="rodzina.js"></script>');
		if(isset($_COOKIE['zal'])&checkname()) echo('<script src="jquery.js"></script>
		<script src="jquery.maplight.js"></script><script>
		$(document).ready(function(){
		$(\'img[usemap]\').maphilight();
		});
		');
		echo('</script></head><body><p>');
		if(isset($_COOKIE['zal'])&checkname()) echo('<h1><a href="index.php?main">famuła.pl: tryb administracyjny</a></h1>'); 
		else echo('<img usemap="#logomap" src="logo.png"><br></p><div class="all">
		<map name="logomap" id="logomap"><area shape="poly" href="index.php?main" coords="11,177,632,177,633,211,686,211,685,181,792,176,778,15,738,31,742,65,666,67,634,81,636,135,576,138,561,68,475,62,463,14,431,29,439,67,87,63,87,12,22,15,1,80"></map>');
	$menus=Array();
	$menus2=Array();
	$menus2['add']='Dodaj';
	$menus2['edit']='Edytuj';
	$menus2['todo']='Do zrobienia';
	$menus2['settings']='Ustawienia';
	$menus2['users']='Użytkownicy';
	$menus2['logs']='Logi';
	$menus['search']='Szukaj';
	$menus['stats']='Ciekawostki';
	$menus['pokaz,all']='Famuła';
	$menus['rocznik,'.date("Y")]='Roczniki';
	$menus['zdjgru']='Zdjęcia';
	$menus['info']='O stronie';
	if(isset($_COOKIE['zal'])&checkname()) $menus['logout']='Wyloguj';
	else $menus['login']='Zaloguj';
	echo('<div id="men1" class="menu" style="border:hidden; width:'.((count($menus)*110)+20).'px; background-color: #ffcc99; margin:auto; padding:0px; height: 30px; text-align:center; ">');
	foreach($menus as $k => $v) echo('<div class="mbox" onmouseover="highl(this.id)" onmouseout="downl(this.id)" onclick="menuclick(this.id)" id="'.$k.'"><p id="'.$k.'_p" class="menu">'.$v.'</p></div>');
	echo('</div>');
	if(isset($_COOKIE['zal'])&checkname()){
		echo('<div id="men1" class="menu" style="border:hidden; width:'.((count($menus2)*110)+20).'px; background-color: #ffcc99; margin:auto; padding:0px; height: 30px; text-align:center; ">');
		foreach($menus2 as $k => $v) echo('<div class="mbox" onmouseover="highl(this.id)" onmouseout="downl(this.id)" onclick="menuclick(this.id)" id="'.$k.'"><p id="'.$k.'_p" class="menu">'.$v.'</p></div>');
		echo('</div>');
	}
	echo('<p>'.$settings['all_podmenu'].'</p><hr>');
}
function html_end(){ //+google ad
	global $ver;
	echo('<hr><font size="1">Drzewo genealogiczne v'.$ver.'</font><br></div><p>');
	if(!isset($_COOKIE['zal'])) echo('<script type="text/javascript"><!--
google_ad_client = "ca-pub-5875141216022917";
/* famula_dol */
google_ad_slot = "4849225326";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>');
	echo('</p></body></html>');
}
function linkujludzia($uid,$style=1){ //do szukajki, zdjęć, rodziców, żon/mężów, dzieci
	$row=mysql_fetch_assoc(mysql_query('select * from ludzie where id='.$uid.';'));
	switch($style){
		case 1:{
			if($row['ur']==0) $rur='?';
				else $rur=$row['ur'];
				if($row['zm']==0) $rzm='?';
				else $rzm=$row['zm'];
				if($row['sex']=='k') $rse='córka';
				else $rse='syn';
				$wynik='<a href="index.php?pokaz,one,'.$row['id'].'">'.$row['imie'].' '.$row['nazwisko'];
				if($rzm=='?'){
					if($rur!='?') $wynik.=' ('.$rur.')';
				}
				else{
				$wynik.=' ('.$rur.'-'.$rzm.')';	
				}
				$wynik.='</a>';
				if(($row['rodzic1']!=0)&($row['rodzic2']!=0)){
					$r1=mysql_fetch_assoc(mysql_query('select imie from ludzie where id='.$row['rodzic1'].';'));
					$r2=mysql_fetch_assoc(mysql_query('select imie from ludzie where id='.$row['rodzic2'].';'));
					$wynik.=', '.$rse.' '.odmiana_m($r1['imie']).' i '.odmiana_k($r2['imie']);
				}
			break;
		}
		case 2:{ 
			if($row['ur']==0) $rur='?';
				else $rur=$row['ur'];
				if($row['zm']==0) $rzm='?';
				else $rzm=$row['zm'];
				if($row['sex']=='k') $rse='córka';
				else $rse='syn';
				$wynik='<a href="index.php?pokaz,one,'.$row['id'].'">'.$row['imie'].' '.$row['nazwisko'].'</a>';
				if($rzm=='?'){
					if($rur!='?') $wynik.=' ('.$rur.')';
				}
				else{
				$wynik.=' ('.$rur.'-'.$rzm.')';	
				} 
			break;
		}
	}
	return $wynik;
}
function dzieciizona($uid){ //do famuły
	$row=mysql_fetch_assoc(mysql_query('select * from ludzie where id='.$uid.';'));
	$aa=rand(1000,9999);
	if($row['sex']=='m'){
		if($row['zona1']!=0) $mz=mysql_fetch_assoc(mysql_query('select id from ludzie where id='.$row['zona1'].';'));
	}
	else{
		$mz=mysql_fetch_assoc(mysql_query('select id from ludzie where zona1='.$row['id'].' limit 1;'));
	}
	if($mz){
		if($row['sex']=='k') $dzieci=mysql_query('select id from ludzie where rodzic2='.$row['id'].' or rodzic1='.$mz['id'].' order by ur,imie;');
		else $dzieci=mysql_query('select id from ludzie where rodzic1='.$row['id'].' or rodzic2='.$mz['id'].' order by ur,imie;');
	}
	else $dzieci=mysql_query('select id from ludzie where rodzic1='.$row['id'].' or rodzic2='.$row['id'].' order by ur,imie;');
	echo('<div class="box0"><p>');
	if(mysql_num_rows($dzieci)>0) echo('<img name="obo'.$row['id'].'_'.$aa.'" id="obo'.$row['id'].'_'.$aa.'" src="icon-plus.png" onclick="ps(\'bo'.$row['id'].'_'.$aa.'\',this.id)" width="30" height="30">');
	else echo('<img src="icon-none.png" width="30" height="30">');
	echo(linkujludzia($row['id'],2));
	if($mz) echo(' + '.linkujludzia($mz['id'],2));
	echo('<div id="bo'.$row['id'].'_'.$aa.'" class="box1">');
	for($i=0;$i<mysql_num_rows($dzieci);$i+=1){
		$dz=mysql_fetch_assoc($dzieci);
		dzieciizona($dz['id']);
	}
	echo('</div></div>');
}
switch($id){
	case 'main':{
		html_start();
		$odroku=mysql_fetch_assoc(mysql_query('select min(ur) as minur from ludzie where ur!=0;'));
		echo('<h2>'.$settings['main_opis'].', od roku '.$odroku['minur'].'</h2>');
		$ile_l=mysql_fetch_assoc(mysql_query('select count(*) as li from ludzie;'));
		$ile_z=mysql_fetch_assoc(mysql_query('select count(*) as li from zdjecia;'));
		echo('<h3>W bazie danych jest: '.$ile_l['li'].' osób i '.$ile_z['li'].' zdjęć</h3>');
		if(isset($_COOKIE['zal'])&checkname()){
			$rw=mysql_fetch_assoc(mysql_query('select count(*) as cnt from ludzie where lastedit="'.$_COOKIE['zal'].'";'));
			echo('<h3>Witaj '.$_COOKIE['zal'].'! </h3><p>Dodanych / zmienionych przez ciebie: '.$rw['cnt'].'</p><br>');
			
			if(preg_match('#,personadd,#',$currentuser['flags'])) echo('<h4>Możesz dodawać nowych ludzi</h4>');
			else echo('<h4>Nie możesz dodawać nowych ludzi</h4>');
			if(preg_match('#,persondel,#',$currentuser['flags'])) echo('<h4>Możesz usuwać ludzi</h4>');
			else echo('<h4>Nie możesz usuwać ludzi</h4>');
			if(preg_match('#,personedit,#',$currentuser['flags'])) echo('<h4>Możesz edytować ludzi</h4>');
			else echo('<h4>Nie możesz edytowac ludzi</h4>');
			if(preg_match('#,picadd,#',$currentuser['flags'])) echo('<h4>Możesz dodawać zdjęcia</h4>');
			else echo('<h4>Nie możesz dodawać zdjęć</h4>');
			if(preg_match('#,picdel,#',$currentuser['flags'])) echo('<h4>Możesz usuwać zdjęcia</h4>');
			else echo('<h4>Nie możesz usuwać zdjęć</h4>');
			if(preg_match('#,picedit,#',$currentuser['flags'])) echo('<h4>Możesz edytować zdjęcia</h4>');
			else echo('<h4>Nie możesz edytować zdjęć</h4>');
			if(preg_match('#,grupersonadd,#',$currentuser['flags'])) echo('<h4>Możesz dodawać ludzi do zdjęć</h4>');
			else echo('<h4>Nie możesz dodawać ludzi do zdjęć</h4>');
			if(preg_match('#,grupersondel,#',$currentuser['flags'])) echo('<h4>Możesz usuwać ludzi ze zdjęć</h4>');
			else echo('<h4>Nie możesz usuwać ludzi ze zdjęć</h4>');
			if(preg_match('#,useredit,#',$currentuser['flags'])) echo('<h4>Możesz edytować użytkowników</h4>');
			else echo('<h4>Nie możesz edytowac użytkowników</h4>');
		}
		html_end();
		break;
	}
	case 'login':{
		html_start();
		echo('<form name="login" action="index.php?login-do" method="POST"><label>login:<input class="formfld" type="text" name="login"></label><br>
		<label>hasło:<input class="formfld" type="password" name="pass"></label><br><input class="formbtn" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)" type="submit" name="submit" value="Zaloguj"></form>');
		html_end();
		break;
	}
	case 'login-do':{
		if(isset($_POST['login'])&isset($_POST['pass'])){
			$res=mysql_query('select * from users where name="'.htmlspecialchars($_POST['login']).'";');
			if(mysql_num_rows($res)==1){
				$row=mysql_fetch_assoc($res);
				if(md5($_POST['pass'].'dupa')==$row['pass']){
					$randval=md5(md5(rand(100000,999999)));
					if(mysql_query('update users set ssid="'.$randval.'" where id='.$row['id'].';')){
						setcookie('zal',$row['name'],(time()+60*5));
					//	mysql_query('update users set ssid_time='.time("Y-m-d H:i:s",mktime(time("H"),time("i")+5)).' where id='.$row['id'].';');
						setcookie('ssid',$randval);
					}
					html_start();
					echo('<p class="ok">Login OK</p><script type="text/javascript">
					document.location="index.php?main";
					</script>');
					html_end();
				}
				else{
					html_start();
					echo('<p class="alert">Zły login lub hasło</p>');
					html_end();
				}
			}
			else{
				html_start();
				echo('<p class="alert">Zły login lub hasło</p>');
				html_end();
			}
		}
		else{
			html_start();
			echo('<p class="alert">Brak loginu lub hasła</p>');
			html_end();
		}
		break;
	}
	case 'logout':{
		setcookie("zal","null",date('U')-500);
		unset($_COOKIE['zal']);
		unset($_COOKIE['ssid']);
		html_start();
		echo('<p class="ok">Wylogowano</p>');
		html_end();
		break;
	}
	case 'add':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(preg_match('#,personadd,#',$currentuser['flags'])){
				if(isset($_POST['submit'])){
					if(strlen($_POST['imie'])>=3){
						if(strlen($_POST['nazwisko'])>=3){
							if(is_numeric($_POST['ur'])&((($_POST['ur']>999)&($_POST['ur']<=date("Y")))|($_POST['ur']==0))){
								if(is_numeric($_POST['zm'])&(($_POST['zm']==0)|(($_POST['zm']>=$_POST['ur'])&($_POST['zm']<=date("Y"))))){
									$r1=mysql_fetch_assoc(mysql_query('select ur,sex from ludzie where id='.$_POST['rodzic1']));
									$r2=mysql_fetch_assoc(mysql_query('select ur,sex from ludzie where id='.$_POST['rodzic2']));
									if(((($r1['sex']!=$r2['sex'])&($r1['ur']<$_POST['ur'])&($r2['ur']<$_POST['ur']))&($_POST['rodzic1']!=0)&($_POST['rodzic2']!=0))|(($_POST['rodzic1']==0)&($_POST['rodzic2']!=0)&($r2['ur']<$_POST['ur']))|($_POST['rodzic2']==0)|($_POST['ur']==0)){
										$q='insert into ludzie set imie="'.trim(htmlspecialchars($_POST['imie'])).'", nazwisko="'.htmlspecialchars($_POST['nazwisko']).'", ur='.$_POST['ur'].', zm='.$_POST['zm'].', sex="'.$_POST['sex'].'", pok='.$_POST['pok'].', rodzic1='.$_POST['rodzic1'].', rodzic2='.$_POST['rodzic2'];
										if($_POST['sex']=='m') $q.=', zona1='.$_POST['zona'];
										$q.=', uwagi="'.htmlspecialchars($_POST['uwagi']).'", lastedit="'.$_COOKIE['zal'].'", adres="'.htmlspecialchars($_POST['adres']).'";';
										mysql_query($q);
										echo('<p class="ok">OK, '.$_POST['imie'].' '.$_POST['nazwisko'].' dodano!</p>');
										mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Dodano '.htmlspecialchars($_POST['imie']).' '.htmlspecialchars($_POST['nazwisko']).'", time="'.date("Y-m-d H:i:s").'"');
									}
									else echo('<p class="alert">Rodzice muszą być różnej płci, muszą być starsi</p>');
								}
								else echo('<p class="alert">rok smierci nie może być większy niż rok urodzenia, ani niż obecny rok</p>');
							}
							else echo('<p class="alert">Rok urodzenia musi zawierać 4 cyfry, oraz nie może być większy niż '.date("Y").'</p>');
						}
						else echo('<p class="alert">Nazwisko musi mieć conajmniej 3 znaki</p>');
					}
					else echo('<p class="alert">Imię musi mieć conajmniej 3 znaki</p>');
				}
			echo('<script>
				function pokolenie(iid){
				var ludzie=new Array();
				var zonaindex=new Array();
				');
			$res=mysql_query('select id,pok from ludzie;');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				echo('ludzie['.$row['id'].']="'.($row['pok']+1).'";');
			}
			$res=mysql_query('select id from ludzie where sex="k" order by id;');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				$row2=mysql_fetch_assoc(mysql_query('select id,zona1 from ludzie where zona1='.$row['id'].' limit 1;'));
				if($row2['zona1']!=0) echo('zonaindex['.$row2['id'].']="'.($i+1).'";');
			}
			if(isset($id2)){
				$tbr1=mysql_fetch_assoc(mysql_query('select * from ludzie where id='.htmlspecialchars($id2).';'));
				$tbr2=$tbr1['zona1'];
				if($tbr1['zona2']!=0) $tbr2=$tbr1['zona2'];
				if($tbr1['zona3']!=0) $tbr2=$tbr1['zona3'];
				$npok=($tbr1['pok']+1);
			}
			echo('document.dodaj.pok.value=ludzie[iid];
				document.getElementById(\'r2\').selectedIndex=zonaindex[iid];
				}</script><form name="dodaj" method="POST" action="index.php?add"><label>imie:<input class="formfld" type="text" name="imie" maxlength="20" size="20"></label> <label>nazwisko:<input class="formfld" type="text" name="nazwisko" size="30" maxlength="40"></label><br>
				<label>urodzony:<input class="formfld" type="text" name="ur" size="4" value="0" maxlength="4"></label> <label>zmarł:<input class="formfld" type="text" name="zm" value="0" size="4" maxlength="4"></label> <label>płeć:</label><label><input class="formfld" type="radio" name="sex" value="m" checked="checked">M</label><label><input class="formfld" type="radio" name="sex" value="k">K</label> <label>adres:<input type="text" name="adres" class="formfld"></label><br>
				<label>rodzice:<select class="formfld" id="r1" name="rodzic1" onchange="pokolenie(this.options[this.selectedIndex].value)"><option value="0">Nieznany</option>');
			$res=mysql_query('select id,imie,nazwisko,ur,pok from ludzie where sex="m" order by id;');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				echo('<option value="'.$row['id'].'"');
				if(isset($id2)){
					if($row['id']==$id2) echo(' selected="selected"');
				}
				echo('>');
				for($j=0;$j<$row['pok'];$j+=1) echo('-');
				echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
			}
			echo('</select><select class="formfld" id="r2" name="rodzic2"><option value="0">Nieznany</option>');
			$res=mysql_query('select id,imie,nazwisko,ur,pok from ludzie where sex="k" order by id;');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				echo('<option value="'.$row['id'].'"');
				if(isset($id2)){
					if($row['id']==$tbr2) echo(' selected="selected"');
				}
				echo('>');
				for($j=0;$j<$row['pok'];$j+=1) echo('-');
				echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
			}
			echo('</select></label> <label>pokolenie:<input class="formfld" type="text" id="pok" name="pok" value="');
			if(isset($id2)) echo($npok);
			else echo('0');
			echo('" size="3" title="W papierowych zapiskach:'."\n".'0 - Czarni'."\n".'1 - Fioletowi'."\n".'2 - Niebiescy'."\n".'3 - Zieloni'."\n".'4 - Czerwoni'."\n".'5 - Pomarańczowi"></label> <label>żona: <select class="formfld" name="zona"><option value="0">Brak</option>');
			$res=mysql_query('select id,imie,nazwisko,ur,pok from ludzie where sex="k" order by id;');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				echo('<option value="'.$row['id'].'">');
				for($j=0;$j<$row['pok'];$j+=1) echo('-');
				echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
			}
			echo('</select></label><br><textarea class="formfld" name="uwagi" rows="5" cols="60"></textarea>
			<input class="formbtn" id="dodaj" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)" type="submit" name="submit" value="Zapisz"></form>');
		}
		else{
			echo('<p class="alert">Nie masz uprawnień do dodawania nowych ludzi</p>');
			mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba dodania nowego ludzia, mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'"');
		}
	}
	else{
		mysql_query('insert into logs set user="niezalogowany", action="Próba dostępu do Dodaj, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
		echo('<p class="alert">Najpierw musisz się <a href="index.php?login">zalogować</a></p>');
	}
		html_end();
		break;
	}
	case 'edit':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			$edit_ipp=$settings['edit_pp'];
			$colspan='12';
			$actp_s='<font color="red">';
			$actp_e='</font>';
			if((isset($id2))&($id2>0)&(strlen($id2)>0)) $str=$id2;
			else $str=1;
			$pcount=mysql_fetch_array(mysql_query('select count(*) from ludzie;'));
			$nop=floor($pcount[0]/$edit_ipp)+1;
			if(isset($_POST['edit'])){
				if(preg_match('#,personedit,#',$currentuser['flags'])){
					if(strlen($_POST['imie'])>=2){
						if(strlen($_POST['nazwisko'])>=2){
							if(is_numeric($_POST['ur'])&((($_POST['ur']>999)&($_POST['ur']<=date("Y")))|($_POST['ur']==0))){
								if(is_numeric($_POST['zm'])&(($_POST['zm']==0)|(($_POST['zm']>=$_POST['ur'])&($_POST['zm']<=date("Y"))))){
									$r1=mysql_fetch_assoc(mysql_query('select ur,sex from ludzie where id='.$_POST['rodzic1']));
									$r2=mysql_fetch_assoc(mysql_query('select ur,sex from ludzie where id='.$_POST['rodzic2']));
									if(((($r1['sex']!=$r2['sex'])&($r1['ur']<$_POST['ur'])&($r2['ur']<$_POST['ur']))&($_POST['rodzic1']!=0)&($_POST['rodzic2']!=0))|(($_POST['rodzic1']==0)&($_POST['rodzic2']!=0)&($r2['ur']<$_POST['ur']))|($_POST['rodzic2']==0)|($_POST['ur']==0)){
										$q='update ludzie set imie="'.htmlspecialchars($_POST['imie']).'", nazwisko="'.htmlspecialchars($_POST['nazwisko']).'", ur='.$_POST['ur'].', zm='.$_POST['zm'].', sex="'.$_POST['sex'].'", pok='.$_POST['pok'].', rodzic1='.$_POST['rodzic1'].', rodzic2='.$_POST['rodzic2'].', adres="'.htmlspecialchars($_POST['adres']).'", uwagi="'.htmlspecialchars($_POST['uwagi']).'"';
										if($_POST['sex']=='m') $q.=', zona1='.$_POST['zona1'].', zona2='.$_POST['zona2'].', zona3='.$_POST['zona3'];
										$q.=' where id='.$_POST['id'].';';
										mysql_query($q);
										echo('<p class="ok">OK, '.$_POST['imie'].' '.$_POST['nazwisko'].' zmienione!</p>');
										mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Zmieniono '.htmlspecialchars($_POST['imie']).' '.htmls($_POST['nazwisko']).'", time="'.date("Y-m-d H:i:s").'"');
									}
									else echo('<p class="alert">Rodzice muszą być różnej płci, muszą być starsi</p>');
								}
								else echo('<p class="alert">rok smierci nie może być większy niż rok urodzenia, ani niż obecny rok</p>');
							}
							else echo('<p class="alert">Rok urodzenia musi zawierać 4 cyfry, oraz nie może być większy niż '.date("Y").'</p>');
						}
						else echo('<p class="alert">Nazwisko musi mieć conajmniej 3 znaki</p>');
					}
					else echo('<p class="alert">Imię musi mieć conajmniej 3 znaki</p>');	
				}
				else{
					echo('<p class="alert">Nie masz uprawnień do edytowania ludzi</p>');
					mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba edycji '.htmlspecialchars($_POST['imie']).' '.htmlspecialchars($_POST['nazwisko']).', mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'"');
				}
			}
			if(isset($_POST['del'])){
				if(preg_match('#,persondel,#',$currentuser['flags'])){
					$res=mysql_query('select * from ludzie where id='.htmlspecialchars($_POST['id']).';');
					if(mysql_num_rows($res)==1){
						$row=mysql_fetch_assoc($res);
						if(mysql_query('delete from ludzie where id='.htmlspecialchars($_POST['id']).';')){
							mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Usunięto '.$row['imie'].' '.$row['nazwisko'].'", time="'.date("Y-m-d H:i:s").'"');
							echo('<p class="ok">'.$row['imie'].' '.$row['nazwisko'].' Usunieto</p>');
						}
						else echo('<p class="alert">Nie udało sie usunąć</p>');
					}
					else echo('<p class="alert">Nie ma kogo usunąć</p>');
				}
				else{
					echo('<p class="alert">Nie masz uprawnień do usuwania ludzi</p>');
					mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba usunięcia '.$row['imie'].' '.$row['nazwisko'].', mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'"');
				}
			}
			echo('<table border="1"><tr><th colspan="'.$colspan.'">Edycja bazy danych</th></tr><tr><th colspan="'.$colspan.'">');
			if($str>1) echo('<a href="index.php?edit,'.($str-1).'">&lt;&lt;Poprzednia</a> | ');
			if($nop<20){
				for($i=1;$i<=$nop;$i+=1){
					echo('<a href="index.php?edit,'.$i.'">');
					if($i==$str) echo($actp_s);
					echo($i);
					if($i==$str) echo('</font>');
					echo('</a>');
					if($i!=$nop) echo(' | ');
				}
			}
			else{
				if($str<6){
					for($i=1;$i<=15;$i+=1){
						echo('<a href="index.php?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($nop-3);$i<=$nop;$i+=1){
						echo('<a href="index.php?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
				}
				else if($str>($nop-6)){
					for($i=1;$i<=3;$i+=1){
						echo('<a href="index.php?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($nop-15);$i<=$nop;$i+=1){
						echo('<a href="index.php?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
				}
				else{
					for($i=1;$i<=3;$i+=1){
						echo('<a href="index.php?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($str-5);$i<=($str+5);$i+=1){
						echo('<a href="index.php?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($nop-3);$i<=$nop;$i+=1){
						echo('<a href="index.php?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
				}
			}
			if($str<$nop) echo(' | <a href="index.php?edit,'.($str+1).'">Następna&gt;&gt;</a>');
			echo('</th></tr><tr><td>id</td><td>imie</td><td>nazwisko</td><td>ur</td><td>zm</td><td>rodzice</td><td>zony</td><td>pł</td><td>pok</td><td>adres</td><td>uwagi</td><td>akcje</td></tr>');
			$res=mysql_query('select * from ludzie order by id limit '.(($str-1)*$edit_ipp).','.$edit_ipp.';');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				echo('<form name="f'.$row['id'].'" method="POST" action="index.php?edit,'.$str.'#n'.$row['id'].'"><input type="hidden" name="id" value="'.$row['id'].'"><tr');
				if($row['id']==$_POST['id']) echo(' class="zazn"');
				echo('><td>'.$row['id'].'<a name="n'.$row['id'].'"></a></td><td><input class="formfld" type="text" name="imie" value="'.$row['imie'].'" maxlength="20" size="15"></td>
				<td><input class="formfld" type="text" name="nazwisko" value="'.$row['nazwisko'].'" maxlength="40" size="20"></td>
				<td><input class="formfld" type="text" name="ur" size="4" maxlength="4" value="'.$row['ur'].'"></td>
				<td><input class="formfld" type="text" name="zm" size="4" maxlength="4" value="'.$row['zm'].'"></td>
				<td><select class="formfld" name="rodzic1"><option value="0">Nieznany</option>');
			$res2=mysql_query('select id,imie,nazwisko,ur,pok from ludzie where sex="m";');
			for($j=0;$j<mysql_num_rows($res2);$j+=1){
				$row2=mysql_fetch_assoc($res2);
				echo('<option value="'.$row2['id'].'"');
				if($row2['id']==$row['rodzic1']) echo(' selected="selected"');
				echo('>');
				for($ji=0;$ji<$row2['pok'];$ji+=1) echo('-');
				echo($row2['imie'].' '.$row2['nazwisko'].' ('.$row2['ur'].')</option>');
			}
			echo('</select><select class="formfld" name="rodzic2"><option value="0">Nieznany</option>');
			$res2=mysql_query('select id,imie,nazwisko,ur,pok from ludzie where sex="k";');
			for($j=0;$j<mysql_num_rows($res2);$j+=1){
				$row2=mysql_fetch_assoc($res2);
				echo('<option value="'.$row2['id'].'"');
				if($row2['id']==$row['rodzic2']) echo(' selected="selected"');
				echo('>');
				for($ji=0;$ji<$row2['pok'];$ji+=1) echo('-');
				echo($row2['imie'].' '.$row2['nazwisko'].' ('.$row2['ur'].')</option>');
			}
			echo('</select></td><td><select class="formfld" name="zona1"><option value="0">Brak</option>');
			$res2=mysql_query('select id,imie,nazwisko,ur,pok from ludzie where sex="k";');
			for($j=0;$j<mysql_num_rows($res2);$j+=1){
				$row2=mysql_fetch_assoc($res2);
				echo('<option value="'.$row2['id'].'"');
				if($row2['id']==$row['zona1']) echo(' selected="selected"');
				echo('>');
				for($ji=0;$ji<$row2['pok'];$ji+=1) echo('-');
				echo($row2['imie'].' '.$row2['nazwisko'].' ('.$row2['ur'].')</option>');
			}
			echo('</select><select class="formfld" name="zona2"><option value="0">Brak</option>');
			$res2=mysql_query('select id,imie,nazwisko,ur,pok from ludzie;');
			for($j=0;$j<mysql_num_rows($res2);$j+=1){
				$row2=mysql_fetch_assoc($res2);
				echo('<option value="'.$row2['id'].'"');
				if($row2['id']==$row['zona2']) echo(' selected="selected"');
				echo('>');
				for($ji=0;$ji<$row2['pok'];$ji+=1) echo('-');
				echo($row2['imie'].' '.$row2['nazwisko'].' ('.$row2['ur'].')</option>');
			}
			echo('</select><select class="formfld" name="zona3"><option value="0">Brak</option>');
			$res2=mysql_query('select id,imie,nazwisko,ur,pok from ludzie;');
			for($j=0;$j<mysql_num_rows($res2);$j+=1){
				$row2=mysql_fetch_assoc($res2);
				echo('<option value="'.$row2['id'].'"');
				if($row2['id']==$row['zona3']) echo(' selected="selected"');
				echo('>');
				for($ji=0;$ji<$row2['pok'];$ji+=1) echo('-');
				echo($row2['imie'].' '.$row2['nazwisko'].' ('.$row2['ur'].')</option>');
			}
			echo('</select></td><td><input class="formfld" type="text" name="sex" value="'.$row['sex'].'" size="1" maxlength="1"></td>
			<td><input class="formfld" type="text" name="pok" value="'.$row['pok'].'" size="2" title="W papierowych zapiskach:'."\n".'0 - Czarni'."\n".'1 - Fioletowi'."\n".'2 - Niebiescy'."\n".'3 - Zieloni'."\n".'4 - Czerwoni'."\n".'5 - Pomarańczowi"></td><td><input class="formfld" type="text" name="adres" value="'.$row['adres'].'" size="10"></td>
			<td><input class="formfld" type="text" name="uwagi" value="'.$row['uwagi'].'" size="20"></td>
			<td><input class="formbtn" type="submit" id="zmiana'.$row['id'].'" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)" name="edit" value="Zmień"><input class="formbtn" type="submit" id="usun'.$row['id'].'" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)" name="del" value="usuń"></td>
			</tr></form>');
			}
			echo('<tr><th colspan="'.$colspan.'">');
			if($str>1) echo('<a href="index.php?edit,'.($str-1).'">&lt;&lt;Poprzednia</a> | ');
			if($nop<20){
				for($i=1;$i<=$nop;$i+=1){
					echo('<a href="index.php?edit,'.$i.'">');
					if($i==$str) echo('<font color="red">');
					echo($i);
					if($i==$str) echo('</font>');
					echo('</a>');
					if($i!=$nop) echo(' | ');
				}
			}
			else{
				if($str<6){
					for($i=1;$i<=15;$i+=1){
						echo('<a href="index.php?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($nop-3);$i<=$nop;$i+=1){
						echo('<a href="index.php?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
				}
				else if($str>($nop-6)){
					for($i=1;$i<=3;$i+=1){
						echo('<a href="index.php?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($nop-15);$i<=$nop;$i+=1){
						echo('<a href="index.php?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
				}
				else{
					for($i=1;$i<=3;$i+=1){
						echo('<a href="index.php?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($str-5);$i<=($str+5);$i+=1){
						echo('<a href="index.php?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($nop-3);$i<=$nop;$i+=1){
						echo('<a href="index.php?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
				}
			}
			if($str<$nop) echo(' | <a href="index.php?edit,'.($str+1).'">Następna&gt;&gt;</a>');
			echo('</th></tr></table>');
		}
		else{
			mysql_query('insert into logs set user="niezalogowany", action="Próba dostępu do Edytuj, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">Najpierw się <a href="index.php?login">zaloguj</a></p>');
		}
		html_end();
		break;
	}
	case 'edit1':{
		html_start();
		$logs_in=mysql_fetch_assoc(mysql_query('select imie,nazwisko from ludzie where id='.htmlspecialchars($id2).' limit 1;'));
		if(isset($_COOKIE['zal'])&checkname()){
			echo('<a href="index.php?pokaz,one,'.$id2.'">Wróć do opisu</a>');
			if(isset($_POST['zdjdod'])){
				if(preg_match('#,picadd,#',$currentuser['flags'])){
					if(is_uploaded_file($_FILES['zdj']['tmp_name'])){
						$newname='zdj'.date('U');
						if($_FILES['zdj']['size']<=$_POST['MAX_FILE_SIZE']){	 
							move_uploaded_file($_FILES['zdj']['tmp_name'], 'gfx/'.$newname.'.jpg');
							mysql_query('insert into zdjecia set path="gfx/'.$newname.'.jpg", osoby="'.htmlspecialchars($id2).'", rok='.htmlspecialchars($_POST['rok']).';');
							$im=imagecreatefromjpeg('gfx/'.$newname.'.jpg');
							$imsize=getimagesize('gfx/'.$newname.'.jpg');
							$nih=($imsize[1]*200)/$imsize[0];
							$nim=imagecreatetruecolor(200,$nih);
							imagecopyresampled($nim,$im,0,0,0,0,200,$nih,$imsize[0],$imsize[1]);
							imagejpeg($nim,'gfx/'.$newname.'.jpg');
							echo('<p class="ok">Plik '.$_FILES['zdj']['name'].' został dodany do galerii</p>');
							mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Dodano zdjęcie '.$logs_in['imie'].' '.$logs_in['nazwisko'].'", time="'.date("Y-m-d H:i:s").'";');
						}
						else{
							echo('Plik: <strong>'.$_FILES['zdj']['name'].'</strong> jest zbyt duży! Jego rozmiar przekracza '.($_POST['MAX_FILE_SIZE']/1024/1024).' MB<br>');
							mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba dodania zbyt dużego zdjęcia  '.$logs_in['imie'].' '.$logs_in['nazwisko'].'", time="'.date("Y-m-d H:i:s").'";');
						}	
					}
					else echo('<p class="alert">Niepoprawny plik</p>');
				}
				else{
					mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba dodania zdjęcia '.$logs_in['imie'].' '.$logs_in['nazwisko'].' mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
					echo('<p class="alert">Nie masz uprawnień do dodawania zdjęć</p>');
				}
			}
			if(isset($_POST['submit'])){
				if(preg_match('#,personedit,#',$currentuser['flags'])){
					if(strlen($_POST['imie'])>=2){
						if(strlen($_POST['nazwisko'])>=2){
							if(is_numeric($_POST['ur'])&((($_POST['ur']>999)&($_POST['ur']<=date("Y")))|($_POST['ur']==0))){
								if(is_numeric($_POST['zm'])&(($_POST['zm']==0)|(($_POST['zm']>=$_POST['ur'])&($_POST['zm']<=date("Y"))))){
									$r1=mysql_fetch_assoc(mysql_query('select ur,sex from ludzie where id='.$_POST['rodzic1']));
									$r2=mysql_fetch_assoc(mysql_query('select ur,sex from ludzie where id='.$_POST['rodzic2']));
									if(((($r1['sex']!=$r2['sex'])&($r1['ur']<$_POST['ur'])&($r2['ur']<$_POST['ur']))&($_POST['rodzic1']!=0)&($_POST['rodzic2']!=0))|(($_POST['rodzic1']==0)&($_POST['rodzic2']!=0)&($r2['ur']<$_POST['ur']))|($_POST['rodzic2']==0)|($_POST['ur']==0)){
										$q='update ludzie set imie="'.htmlspecialchars($_POST['imie']).'", nazwisko="'.htmlspecialchars($_POST['nazwisko']).'", ur='.$_POST['ur'].', zm='.$_POST['zm'].', sex="'.$_POST['sex'].'", pok='.$_POST['pok'].', rodzic1='.$_POST['rodzic1'].', rodzic2='.$_POST['rodzic2'].', adres="'.htmlspecialchars($_POST['adres']).'", uwagi="'.htmlspecialchars($_POST['uwagi']).'", lastedit="'.$_COOKIE['zal'].'", rnazw="'.$_POST['rnazw'].'"';
										if($_POST['sex']=='m') $q.=', zona1='.$_POST['zona1'].', zona2='.$_POST['zona2'].', zona3='.$_POST['zona3'];
										$q.=' where id='.htmlspecialchars($id2).';';
										if(mysql_query($q)){
											echo('<p class="ok">OK, '.$_POST['imie'].' '.$_POST['nazwisko'].' zmienione!</p>');
											mysql_query('insert into logs set user="'.$_COOKIE['azl'].'", action="Edycja '.htmlspecialchars($_POST['imie']).' '.htmlspecialchars($_POST['nazwisko']).'", time="'.date("Y-m-d H:i:s").'";');
										}
									}
									else echo('<p class="alert">Rodzice muszą być różnej płci, muszą być starsi</p>');
								}
								else echo('<p class="alert">rok smierci nie może być większy niż rok urodzenia, ani niż obecny rok</p>');
							}
							else echo('<p class="alert">Rok urodzenia musi zawierać 4 cyfry, oraz nie może być większy niż '.date("Y").'</p>');
						}
						else echo('<p class="alert">Nazwisko musi mieć conajmniej 3 znaki</p>');
					}
					else echo('<p class="alert">Imię musi mieć conajmniej 3 znaki</p>');	
				}
				else{
					mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba edycji '.$logs_in['imie'].' '.$logs_in['nazwisko'].' mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
					echo('Nie masz uprawnień do edytowania ludzi');
				}
			}
			$theone=mysql_fetch_assoc(mysql_query('select * from ludzie where id='.htmlspecialchars($id2).';'));
			if($theone){
				echo('<table width="100%" border="0"><tr><td width="50%" align="right">');
				$zdjecia=mysql_query('select * from zdjecia where osoby="'.$theone['id'].'";');
				for($z=0;$z<mysql_num_rows($zdjecia);$z+=1){
					$zdjeciar=mysql_fetch_assoc($zdjecia);
					echo('<p>'.$zdjeciar['path'].'</p>');
				}
				echo('<form enctype="multipart/form-data" action="index.php?edit1,'.$id2.'" method="POST">
				<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
				<table border="0"><tr><td>Dodaj zdjęcie:</td><td>Rok</td><td>&nbsp;</td></tr><tr><td><input class="formfld" name="zdj" type="file" /></td><td><input type="text" name="rok" class="formfld" size="4" maxlength="4"></td>
				<td><input class="formbtn" id="zdjdod" name="zdjdod" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)" type="submit" value="Wyślij" /></td></tr></table></form>
				</td><td width="50%">');
				echo('<form name="edit1" method="POST" action="index.php?edit1,'.$id2.'">');
				echo('<label>imie:<input type="text" name="imie" value="'.$theone['imie'].'" class="formfld" maxlength="20" size="20"></label> <label>nazwisko:<input type="text" name="nazwisko" value="'.$theone['nazwisko'].'" class="formfld" maxlength="40"></label><label title="wpisać tylko jeżeli inne niż nazwisko ojca lub inne niż nazwisko po mężu"><br>prawdziwe nazwisko: <input name="rnazw" type="text" size="20" name="rnazw" class="formfld" value="'.$theone['rnazw'].'"></label><br>');
				echo('<label>ur:<input type="text" name="ur" value="'.$theone['ur'].'" size="4" maxlength="4" class="formfld"></label> <label>zm:<input type="text" name="zm" value="'.$theone['zm'].'" size="4" maxlength="4" class="formfld"</label> <label>płeć:<input type="text" name="sex" value="'.$theone['sex'].'" class="formfld" size="1" maxlength="1"></label> <label>pokolenie: <input type="text" class="formfld" name="pok" value="'.$theone['pok'].'"></label><br>');
				echo('<label>adres:<input type="text" name="adres" value="'.$theone['adres'].'" class="formfld"></label>');
				echo('</td></tr><tr><td>');
				$res=mysql_query('select * from ludzie where rodzic1='.$theone['id'].' or rodzic2='.$theone['id'].';');
				if(mysql_num_rows($res)>0){
					echo('<h3>Dzieci ('.mysql_num_rows($res).'):</h3>');
					for($i=0;$i<mysql_num_rows($res);$i+=1){
						$row=mysql_fetch_assoc($res);
						echo('<p><a href="index.php?pokaz,one,'.$row['id'].'">'.$row['imie'].' '.$row['nazwisko'].'</a> ('.$row['ur'].')</p>');
					}
				}
				echo('</td><td>');
				echo('<h3>Żona:</h3>');
				echo('<select class="formfld" id="z1" name="zona1"><option value="0">Nieznany</option>');
				$res=mysql_query('select id,imie,nazwisko,ur,pok from ludzie where sex="k" order by id;');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<option value="'.$row['id'].'"');
					if($row['id']==$theone['zona1']) echo(' selected="selected"');
					echo('>');
					for($j=0;$j<$row['pok'];$j+=1) echo('-');
					echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
				}
				echo('</select><select class="formfld" id="z2" name="zona2"><option value="0">Nieznany</option>');
				$res=mysql_query('select id,imie,nazwisko,ur,pok from ludzie where sex="k" order by id;');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<option value="'.$row['id'].'"');
					if($row['id']==$theone['zona2']) echo(' selected="selected"');
					echo('>');
					for($j=0;$j<$row['pok'];$j+=1) echo('-');
					echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
				}
				echo('</select><select class="formfld" id="z3" name="zona3"><option value="0">Nieznany</option>');
				$res=mysql_query('select id,imie,nazwisko,ur,pok from ludzie where sex="k" order by id;');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<option value="'.$row['id'].'"');
					if($row['id']==$theone['zona3']) echo(' selected="selected"');
					echo('>');
					for($j=0;$j<$row['pok'];$j+=1) echo('-');
					echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
				}
				echo('</select><h3>Rodzice:</h3>');
				echo('<select class="formfld" id="r1" name="rodzic1"><option value="0">Nieznany</option>');
				$res=mysql_query('select id,imie,nazwisko,ur,pok from ludzie where sex="m" order by id;');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<option value="'.$row['id'].'"');
					if($row['id']==$theone['rodzic1']) echo(' selected="selected"');
					echo('>');
					for($j=0;$j<$row['pok'];$j+=1) echo('-');
					echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
				}
				echo('</select><br><select class="formfld" id="r2" name="rodzic2"><option value="0">Nieznany</option>');
				$res=mysql_query('select id,imie,nazwisko,ur,pok from ludzie where sex="k" order by id;');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<option value="'.$row['id'].'"');
					if($row['id']==$theone['rodzic2']) echo(' selected="selected"');
					echo('>');
					for($j=0;$j<$row['pok'];$j+=1) echo('-');
					echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
				}
				echo('</select>');
				echo('</td></tr><tr><td colspan="2" align="center">');
				echo('<textarea name="uwagi" rows="5" cols="80" class="formfld">'.$theone['uwagi'].'</textarea>');
				echo('</td></tr></table><input type="submit" name="submit" value="Zapisz" class="formbtn" id="edit1btn" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></form>');
			}
		}
		else{
			mysql_query('insert into logs set user="niezalogowany", action="Próba dostępu do edycji '.$logs_in['imie'].' '.$logs_in['nazwisko'].', z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class"alert">Najpierw sie <a href="index.php?login">zaloguj</a></p>');
		}
		html_end();
		break;
	}
	case 'search':{
		html_start();
		echo('<p>Uwaga! Wpisuj nazwisko rodowe!<form name="search" method="POST" action="index.php?search"><center><table border="0"><tr><td>imie</td><td>nazwisko</td><td>&nbsp;</td></tr><tr><td><input class="formfld" type="text" name="q1" value="'.$_POST['q1'].'"></td><td><input class="formfld" type="text" name="q2" value="'.$_POST['q2'].'"></td><td><input class="formbtn" id="szukaj" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)" type="submit" name="submit" value="Szukaj"></td></tr><tr><td align="center" colspan="2"><label><input class="formfld" type="checkbox" name="exact" value="1"');
		if(isset($_POST['exact'])) echo(' checked="checked"');
		echo('>dokładnie to</label></td><td>&nbsp;</td></tr></table></center></form></p><br>');
		if(isset($_POST['q1'])|isset($_POST['q2'])){
			if(isset($_POST['exact'])){
				if((strlen($_POST['q1'])>0)&(strlen($_POST['q2'])>0)) $res=mysql_query('select id from ludzie where imie="'.htmlspecialchars($_POST['q1']).'" and nazwisko="'.htmlspecialchars($_POST['q2']).'" order by imie,nazwisko;');			
				else if(strlen($_POST['q1'])>0) $res=mysql_query('select id from ludzie where imie="'.htmlspecialchars($_POST['q1']).'" order by imie,nazwisko;');
				else if(strlen($_POST['q2'])>0) $res=mysql_query('select id from ludzie where nazwisko="'.htmlspecialchars($_POST['q2']).'" order by imie,nazwisko;');
			}
			else $res=mysql_query('select id from ludzie where imie like "%'.htmlspecialchars($_POST['q1']).'%" and nazwisko like "%'.htmlspecialchars($_POST['q2']).'%" order by imie,nazwisko;');
			echo('<h2>Znaleziono '.mysql_num_rows($res).' os');
			if(mysql_num_rows($res)==1) echo('obę');
			else if(((substr(mysql_num_rows($res),-1,1)=='2')|(substr(mysql_num_rows($res),-1,1)=='3')|(substr(mysql_num_rows($res),-1,1)=='4'))&(substr(mysql_num_rows($res),-2,1)!='1')) echo ('oby');
			else echo('ób');
			echo('</h2>');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				echo('<p>'.linkujludzia($row['id'],1).'</p>');
			}
		}
		html_end();
		break;
	}
	case 'stats':{
		html_start();
		echo('<h3>Najdłużej żyli:</h3>');
		$maxl=mysql_query('select id,imie,nazwisko,zm,ur,(zm-ur) as wiek from ludzie where ur>0 and zm>0 order by wiek desc,ur asc limit 5;');
		for($i=0;$i<mysql_num_rows($maxl);$i+=1){
			$maxlength=mysql_fetch_assoc($maxl);
			echo('<p><a href="index.php?pokaz,one,'.$maxlength['id'].'">'.$maxlength['imie'].' '.$maxlength['nazwisko'].'</a> ('.$maxlength['ur'].'-'.$maxlength['zm'].') - '.$maxlength['wiek'].' lat</p>');
		}
		echo('<h3>Najwięcej dzieci:</h3>');
		$res=mysql_query('select distinct(rodzic1) as ro1 from ludzie where rodzic1!=0;');
		$max=0;
		$mid=0;
		for($i=0;$i<mysql_num_rows($res);$i+=1){
			$row=mysql_fetch_assoc($res);
			$ldzieci=mysql_fetch_assoc(mysql_query('select count(*) as ile from ludzie where rodzic1='.$row['ro1'].';'));
			if($ldzieci['ile']>$max){
				$max=$ldzieci['ile'];
				$mid=$row['ro1'];
			}
		}
		$zona=mysql_fetch_assoc(mysql_query('select zona1 from ludzie where id='.$mid.';'));
		echo('<p>'.linkujludzia($mid,2).' i '.linkujludzia($zona['zona1'],2).' - '.$max.' dzieci</p>');
		echo('<h3>Najczęściej występujące imie:</h3>');
		$imm=mysql_query('select distinct(imie) as im from ludzie where sex="m" and imie!="???";');
		$imk=mysql_query('select distinct(imie) as im from ludzie where sex="k" and imie!="???";');
		for($i=0;$i<mysql_num_rows($imm);$i+=1){
			$row=mysql_fetch_assoc($imm);
			$li=mysql_fetch_assoc(mysql_query('select count(*) as lim from ludzie where imie="'.$row['im'].'";'));
			$imiona[$row['im']]=$li['lim'];
		}
		for($i=0;$i<mysql_num_rows($imk);$i+=1){
			$row=mysql_fetch_assoc($imk);
			$li=mysql_fetch_assoc(mysql_query('select count(*) as lim from ludzie where imie="'.$row['im'].'";'));
			$imionak[$row['im']]=$li['lim'];
		}
		$immax='';
		$immaxc=0;
		foreach($imiona as $k => $v){
			if($v>$immaxc){
				$immaxc=$v;
				$immax=$k;
			}
		}
		echo('<p>Męskie: '.$immax.' ('.$immaxc.')</p>');
		$kimmax='';
		$kimmaxc=0;
		foreach($imionak as $k => $v){
			if($v>$kimmaxc){
				$kimmaxc=$v;
				$kimmax=$k;
			}
		}
		echo('<p>Żeńskie: '.$kimmax.' ('.$kimmaxc.')</p>');
		html_end();
		break;
	}
	case 'todo':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			echo('<h3>Nieznane imie lub nazwisko</h3>');
			$res=mysql_query('select id from ludzie where imie="???" or nazwisko="???";');
			echo('<table border="0"><tr><td>');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				if($i%(floor(mysql_num_rows($res)/7)+1)==0){
					if($i!=0){
						echo('</td><td>');
					}
				}
				$row=mysql_fetch_assoc($res);
				echo('<p>'.linkujludzia($row['id'],1).'</p>');
			}
			echo('</td></tr></table>');
			echo('<h3>Napewno jeszcze żyją? (ponad 90 lat)</h3>');
			$res=mysql_query('select id,ur from ludzie where ur<'.(date("Y")-90).' and ur!=0 and zm=0;');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				echo('<p>'.linkujludzia($row['id'],1).' (ma '.(date("Y")-$row['ur']).' lat)</p>');
			}
			$res=mysql_query('select id,pok,zona1,zona2,zona3 from ludzie where sex="m";');
			if(mysql_num_rows($res)>0) echo('<h3>Małżeństwa w różnych pokoleniach: napewno dobrze?</h3>');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				if($row['zona1']!=0){
					$z1=mysql_fetch_assoc(mysql_query('select pok from ludzie where id='.$row['zona1'].' limit 1;'));
					if($row['pok']!=$z1['pok']) echo('<p>'.linkujludzia($row['id'],2).' jest z pokolenia '.$row['pok'].', a jego żona '.linkujludzia($row['zona1'],2).' jest z pokolenia '.$z1['pok'].'</p>');
				}
				if($row['zona2']!=0){
					$z2=mysql_fetch_assoc(mysql_query('select pok from ludzie where id='.$row['zona2'].' limit 1;'));
					if($row['pok']!=$z2['pok']) echo('<p>'.linkujludzia($row['id'],2).' jest z pokolenia '.$row['pok'].', a jego żona '.linkujludzia($row['zona1'],2).' jest z pokolenia '.$z2['pok'].'</p>');
				}
				if($row['zona3']!=0){
					$z3=mysql_fetch_assoc(mysql_query('select pok from ludzie where id='.$row['zona3'].' limit 1;'));
					if($row['pok']!=$z3['pok']) echo('<p>'.linkujludzia($row['id'],2).' jest z pokolenia '.$row['pok'].', a jego żona '.linkujludzia($row['zona1'],2).' jest z pokolenia '.$z3['pok'].'</p>');
				}
			}
			//TODO: incesty
			echo('<h3>Napewno dobrze?</h3>');
			$res=mysql_query('select id,rodzic1,rodzic2,ur from ludzie;');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$one=mysql_fetch_assoc($res);
				if($one['ur']!=0){
					if($one['rodzic1']!=0){
						$r1=mysql_fetch_assoc(mysql_query('select ur from ludzie where id='.$one['rodzic1'].';'));
						if(($r1['ur']!=0)&($one['ur']-$r1['ur'])<18) echo('<p>'.linkujludzia($one['rodzic1'],2).' był ojcem '.linkujludzia($one['id'],2).' w wieku '.($one['ur']-$r1['ur']).' lat</p>');
					}
					if($one['rodzic2']!=0){
						$r2=mysql_fetch_assoc(mysql_query('select ur from ludzie where id='.$one['rodzic2'].';')); 
						if(($r2['ur']!=0)&($one['ur']-$r2['ur'])<18) echo('<p>'.linkujludzia($one['rodzic2'],2).' urodziła '.linkujludzia($one['id'],2).' w wieku '.($one['ur']-$r2['ur']).' lat</p>');
					}
				}
			}
		}
		else{
			mysql_query('insert into logs set user="niezalogowany", action="Próba dostępu do Do zrobienia, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">Najpierw się <a href="index.php?login">zaloguj</a></p>');
		}
		html_end();
		break;
	}
	case 'pokaz':{
		if(strlen($id2)>0) $co=$id2;
		else $co='all';
		html_start();
		switch($co){
			case 'all':{
				$res=mysql_query('select id from ludzie where pok=0 and sex="m" order by nazwisko;');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					dzieciizona($row['id']);
				}
				break;
			}
			case 'one':{
				$theone=mysql_fetch_assoc(mysql_query('select * from ludzie where id='.$id3.';'));
				if($theone){
					echo('<a href="index.php?tree,'.$id3.'">Rysuj drzewo</a>');
					if(isset($_COOKIE['zal'])&checkname()) echo(' | <a href="index.php?edit1,'.$id3.'">Edytuj</a> | <a href="index.php?add,'.$theone['id'].'">Dodaj dziecko</a>');
					echo('<table width="100%" border="0"><tr><td width="50%" align="right">');
					$zdjnum=mysql_fetch_assoc(mysql_query('select count(*) as num from zdjecia where osoby="'.$id3.'";'));
					$zdjnumall=mysql_fetch_assoc(mysql_query('select count(*) as num from zdjecia where osoby like "%'.$id3.'%";'));
					$slu=mysql_fetch_assoc(mysql_query('select * from zdjecia where osoby="'.$id3.'" and slub=1 limit 1;'));
					if($zdjnum['num']==0) echo('<img src="brakzdj.png" class="lud" border="4" title="Brak zdjęcia">');
					if($slu){
						echo('<img src="'.$slu['path'].'" class="lud" border="4" title="zdjęcie z roku '.$slu['rok'].'">');
						$zdj=mysql_query('select * from zdjecia where osoby="'.$id3.'" and slub=0 order by rok desc,id desc limit 1;');
					}
					else{
						$zdj=mysql_query('select * from zdjecia where osoby="'.$id3.'" order by rok desc,id desc limit 2;');
					}
					for($iz=0;$iz<mysql_num_rows($zdj);$iz+=1){
						$zdjrow=mysql_fetch_assoc($zdj);
						echo('<img src="'.$zdjrow['path'].'" class="lud" border="4" title="zdjęcie z roku '.$zdjrow['rok'].'">');
					}
					if(($zdjnumall['num']>0)|isset($_COOKIE['zal'])) echo('<br><a href="index.php?zdj,'.$theone['id'].'">Pokaż wszystkie zdjęcia</a>');
					echo('</td><td width="50%">');
					echo('<h1>'.$theone['imie'].' ');
					if($theone['sex']=='k'){
						$zony=mysql_query('select * from ludzie where zona1='.$theone['id'].' or zona2='.$theone['id'].' or zona3='.$theone['id'].';');
						if($theone['rnazw']!='no'){
							echo($theone['rnazw']);
						}
						else if(mysql_num_rows($zony)>0){
							for($ik=0;$ik<mysql_num_rows($zony);$ik+=1) $maz=mysql_fetch_assoc($zony);
							echo($maz['nazwisko'].' ('.$theone['nazwisko'].')');
						}
						else echo($theone['nazwisko']);
					}
					else echo($theone['nazwisko']);
					echo('</h1>');
					if($theone['ur']!=0) echo('ur. <a href="index.php?rocznik,'.$theone['ur'].'">'.$theone['ur'].'</a>r. ');
					if($theone['zm']!=0) echo('zm. <a href="index.php?rocznik,'.$theone['zm'].'">'.$theone['zm'].'</a>r. ');
					if(($theone['ur']!=0)&($theone['zm']!=0)){
						echo('Żył');
						if($theone['sex']=='k') echo('a');
						echo(' '.($theone['zm']-$theone['ur']).' lat');
					}
					if(strlen($theone['adres'])>1) echo('<br>'.$theone['adres']);
					if($theone['sex']=='k'){
						if(mysql_num_rows($zony)>0){
							echo('<h3>Mąż:</h3>');
							$zony=mysql_query('select * from ludzie where zona1='.$theone['id'].' or zona2='.$theone['id'].' or zona3='.$theone['id'].';');
							for($i=0;$i<mysql_num_rows($zony);$i+=1){
								$maz=mysql_fetch_assoc($zony);
								echo('<p><a href="index.php?pokaz,one,'.$maz['id'].'">'.$maz['imie'].' '.$maz['nazwisko'].'</a> ('.$maz['ur'].')</p>');
							}
						}
					}
					else{
						if($theone['zona1']!=0){
							echo('<h3>Żona:</h3>');
							$zon1=mysql_fetch_assoc(mysql_query('select * from ludzie where id='.$theone['zona1'].';'));
							echo('<p><a href="index.php?pokaz,one,'.$zon1['id'].'">'.$zon1['imie'].' '.$zon1['nazwisko'].'</a> ('.$zon1['ur'].')</p>');
							if($theone['zona2']!=0){
								$zon2=mysql_fetch_assoc(mysql_query('select * from ludzie where id='.$theone['zona2'].';'));
							}
						}
					}
					echo('</td></tr><tr><td>');
					$res=mysql_query('select * from ludzie where rodzic1='.$theone['id'].' or rodzic2='.$theone['id'].' order by ur,imie;');
					if(mysql_num_rows($res)>0){
						echo('<h3>Dzieci ('.mysql_num_rows($res).'):</h3>');
						for($i=0;$i<mysql_num_rows($res);$i+=1){
							$row=mysql_fetch_assoc($res);
							echo('<p><a href="index.php?pokaz,one,'.$row['id'].'">'.$row['imie'].' '.$row['nazwisko'].'</a> ('.$row['ur'].')</p>');
						}
					}
					echo('</td><td>');
					if($theone['rodzic1']!=0) $rod1=mysql_fetch_assoc(mysql_query('select id from ludzie where id='.$theone['rodzic1'].';'));
					if($theone['rodzic2']!=0) $rod2=mysql_fetch_assoc(mysql_query('select id from ludzie where id='.$theone['rodzic2'].';'));
					echo('<h3>Rodzice:</h3>');
					if($theone['rodzic1']==0) echo('<p>Brak danych</p>');
					else echo('<p>'.linkujludzia($rod1['id'],2).'</p>');
					if($theone['rodzic2']==0) echo('<p>Brak danych</p>');
					else echo('<p>'.linkujludzia($rod2['id'],2).'</p>');
					
					echo('</td></tr><tr><td colspan="2" align="center">');
					echo('<p>'.$theone['uwagi'].'</p>');
					echo('</td></tr></table>');
				}
				else echo('<p class="alert">Nie ma takiego ;)</p>');
				break;
			}
		}
		html_end();
		break;
	}
	case 'tree':{
		html_start();
		if(isset($id2)){
			if(isset($_POST['submit'])){
				system('rm pdfgen/*.pdf');
				$theone=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where id='.$id2.';'));
				$filename='pdfgen/tree'.$theone['id'].'.pdf';
				$pdf=new PDF();
				$pdf->Open();
				$pdf->AddPage();
				$x=$pdf->GetX();
				$y=$pdf->GetY();
				$w=266; // wiredly, 266 works - [mm] - for A4 page
				$h=174; // [mm] - for A4 page
				$pdf->AddFont('arialpl','','arialpl.php');
				$pdf->SetFont('arialpl','', 40);
				$onezdj=mysql_query('select path from zdjecia where osoby='.$theone['id'].' order by rok desc,id desc limit 1;');
				$cellh=floor($h/($_POST['pok']+1));
				if(isset($_POST['zdjecia'])&(mysql_num_rows($onezdj)==1)){
					$ozdj=mysql_fetch_assoc($onezdj);
					$imsize=getimagesize($ozdj['path']);
					$pdfzdjw=$imsize[0]*$cellh/$imsize[1];
					$pdf->Image($ozdj['path'],$x,$y,$pdfzdjw,$cellh);
					$pdf->SetXY($x+$pdfzdjw,$y);
					if($theone['nazwisko']=='̣̣???') $nazwisko=' ';
					else $nazwisko=$theone['nazwisko'];
					$pdf->MultiCell(($w-$pdfzdjw),$cellh,(UTF8_2_ISO88592($theone['imie']).' '.UTF8_2_ISO88592($nazwisko)),1,'C');
				}
				else{
					if($theone['nazwisko']=='̣̣???') $nazwisko=' ';
					else $nazwisko=$theone['nazwisko'];
					$pdf->MultiCell($w,$cellh,(UTF8_2_ISO88592($theone['imie']).' '.UTF8_2_ISO88592($nazwisko)),1,'C');
				}
				//rodzice 1
				$pdf->SetFont('arialpl','', 24);
				$ro=Array();
				if($theone['rodzic1']!=0){
					$ro[0]=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where id='.$theone['rodzic1'].';'));
					$rozdj[0]=mysql_query('select path from zdjecia where osoby='.$theone['rodzic1'].' order by rok desc,id desc limit 1;');
				}
				if($theone['rodzic2']!=0){
					$ro[1]=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where id='.$theone['rodzic2'].';'));
					$rozdj[1]=mysql_query('select path from zdjecia where osoby='.$theone['rodzic2'].' order by rok desc,id desc limit 1;');
				}
				for($ii=0;$ii<count($ro);$ii+=1){
					if(isset($_POST['zdjecia'])&(mysql_num_rows($rozdj[$ii])==1)){
						$rzdj=mysql_fetch_assoc($rozdj[$ii]);
						$imsize=getimagesize($rzdj['path']);
						$pdf_zdj_w[$ii]=$imsize[0]*$cellh/$imsize[1];
						$pdf->Image($rzdj['path'],$x+($ii*$w/2),($y+$cellh),$pdf_zdj_w[$ii],$cellh);
						$pdf->SetXY($x+$pdf_zdj_w[$ii]+($ii*$w/2),($y+$cellh));
						if($ro[$ii]['nazwisko']=='̣̣???') $nazwisko=' ';
						else $nazwisko=$ro[$ii]['nazwisko'];
						if($ro[$ii]) $pdf->MultiCell((($w/2)-$pdf_zdj_w[$ii]),$cellh,(UTF8_2_ISO88592($ro[$ii]['imie']).' '.UTF8_2_ISO88592($nazwisko)),1,'C');
					}
					else{
						$pdf->SetXY($x+($ii*$w/2),($y+$cellh));
						if($ro[$ii]['nazwisko']=='̣̣???') $nazwisko=' ';
						else $nazwisko=$ro[$ii]['nazwisko'];
						if($ro[$ii]) $pdf->MultiCell(($w/2),$cellh,(UTF8_2_ISO88592($ro[$ii]['imie']).' '.UTF8_2_ISO88592($nazwisko)),1,'C');
					}
				}
				//dziadkowie 2 
				
				for($i4=0;$i4<4;$i4+=1){
				if($ro[floor($i4/2)]['rodzic'.(($i4%2)+1)]!=0){
					$dzia[$i4]=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where id='.$ro[floor($i4/2)]['rodzic'.(($i4%2)+1)].';'));
					$dzzdj[$i4]=mysql_query('select path from zdjecia where osoby='.$ro[floor($i4/2)]['rodzic'.(($i4%2)+1)].' order by rok desc limit 1;');
				}}
				foreach($dzia as $key=>$val){
					if(isset($_POST['zdjecia'])&(mysql_num_rows($dzzdj[$key])==1)){
						$pdf->SetFont('arialpl','', 14);
						if($val){
							$dzdj=mysql_fetch_assoc($dzzdj[$key]);
							$imsize=getimagesize($dzdj['path']);
							$pdf_zdj_w[$key]=$imsize[0]*$cellh/$imsize[1];
							$pdf->Image($dzdj['path'],$x+($key*$w/4),($y+(2*$cellh)),$pdf_zdj_w[$key],$cellh);
							$pdf->SetXY($x+$pdf_zdj_w[$key]+($key*$w/4),($y+(2*$cellh)));
							$pdf->MultiCell((($w/4)-$pdf_zdj_w[$key]),$cellh,' ',1,'C');
							$pdf->SetXY($x+$pdf_zdj_w[$key]+1+($key*$w/4),($y+(2*$cellh))+1);
							if($val['nazwisko']=='̣̣???') $nazwisko=' ';
							else $nazwisko=$val['nazwisko'];
							$pdf->MultiCell((($w/4)-$pdf_zdj_w[$key])-2,($cellh/2)-2,(UTF8_2_ISO88592($val['imie'])."\n".UTF8_2_ISO88592($nazwisko)),0,'C');
						}
					}
					else{
						$pdf->SetFont('arialpl','', 16);
						$pdf->SetXY($x+($key*$w/4),($y+(2*$cellh)));
						if($val['nazwisko']=='̣̣???') $nazwisko=' ';
						else $nazwisko=$val['nazwisko'];
						if($val) $pdf->MultiCell(($w/4),$cellh,(UTF8_2_ISO88592($val['imie']).' '.UTF8_2_ISO88592($nazwisko)),1,'C');
					}
				}
				if($_POST['pok']>=3){
					//pradziadkowie 3
					$pdf->SetFont('arialpl','', 12);
					for($i5=0;$i5<8;$i5+=1){
						if($dzia[floor($i5/2)]['rodzic'.(($i5%2)+1)]!=0){
							$pra[$i5]=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where id='.$dzia[floor($i5/2)]['rodzic'.(($i5%2)+1)].';'));
							$przdj[$i5]=mysql_query('select path from zdjecia where osoby='.$dzia[floor($i5/2)]['rodzic'.(($i5%2)+1)].' order by rok desc limit 1;');
					}}
					foreach($pra as $key=>$val){
						if(isset($_POST['zdjecia'])&(mysql_num_rows($przdj[$key])==1)){
							if($val){
							$pzdj=mysql_fetch_assoc($przdj[$key]);
							$imsize=getimagesize($pzdj['path']);
							$pdf_zdj_w[$key]=$imsize[0]*($cellh/2)/$imsize[1];
							$pdf->Image($pzdj['path'],$x+($key*$w/8)-($pdf_zdj_w[$key]/2)+$w/16,($y+(3*$cellh)),$pdf_zdj_w[$key],($cellh/2));
							$pdf->SetXY($x+($key*$w/8),($y+(3*$cellh)));
							$pdf->MultiCell(($w/8),$cellh,' ',1,'C');
							$pdf->SetXY($x+1+($key*$w/8),($y+(3*$cellh))+1+($cellh/2));
							if($val['nazwisko']=='̣̣???') $nazwisko=' ';
							else $nazwisko=$val['nazwisko'];
							$pdf->MultiCell(($w/8)-2,($cellh/4)-2,(UTF8_2_ISO88592($val['imie'])."\n".UTF8_2_ISO88592($nazwisko)),0,'C');
						}
						}
						else{
							$pdf->SetXY(($x+$key*($w/8)),($y+(3*$cellh)));
							if($val){
								$pdf->MultiCell($w/8,$cellh,' ',1,'C');
								$pdf->SetXY(($x+$key*($w/8))+1,($y+3*$cellh)+1);
								if($val['nazwisko']=='̣̣???') $nazwisko=' ';
								else $nazwisko=$val['nazwisko'];
								$pdf->MultiCell(($w/8)-2,($cellh/2)-2,(iconv('utf-8','iso-8859-2',$val['imie'])."\n".iconv('utf-8','iso-8859-2',$nazwisko)),0,'C');
							}
						}
					}
					if($_POST['pok']>=4){
						//4 prapra
						$pdf->SetFont('arialpl','', 10);
						for($i6=0;$i6<16;$i6+=1){
							if($pra[floor($i6/2)]['rodzic'.(($i6%2)+1)]!=0){
								$prpr[$i6]=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where id='.$pra[floor($i6/2)]['rodzic'.(($i6%2)+1)].';'));
								$prprzdj[$i6]=mysql_query('select path from zdjecia where osoby='.$pra[floor($i6/2)]['rodzic'.(($i6%2)+1)].' order by rok desc limit 1;');
						}}
						foreach($prpr as $key=>$val){
							if(isset($_POST['zdjecia'])&(mysql_num_rows($prprzdj[$key])==1)){
								$pdzdj=mysql_fetch_assoc($prprzdj[$key]);
								$imsize=getimagesize($pdzdj['path']);
								$pdf_zdj_w[$key]=$imsize[0]*($cellh/2)/$imsize[1];
								$pdf->Image($pdzdj['path'],$x+($key*$w/16)-($pdf_zdj_w[$key]/2)+$w/32,($y+(4*$cellh)),$pdf_zdj_w[$key],($cellh/2));
							}
							$pdf->SetXY($x+$key*($w/16),($y+(4*$cellh)));
							$pdf->MultiCell($w/16,(1.2*$cellh),'',1,'C');	
							$pdf->SetXY($x+($key)*($w/16)+3,($y+5.2*$cellh));
							$pdf->Rotate(90);
							$pdf->Write(0,UTF8_2_ISO88592($val['imie']));
							$pdf->Rotate(0);
							$pdf->SetXY($x+($key)*($w/16)+9,($y+5.2*$cellh));
							$pdf->Rotate(90);			
							if($val['nazwisko']=='̣̣???') $nazwisko=' ';
							else $nazwisko=$val['nazwisko'];
							$pdf->Write(0,UTF8_2_ISO88592($nazwisko));
							$pdf->Rotate(0);
						}
					}
				}
				$pdf->SetAuthor('Szymon Marciniak');
				$pdf->SetCreator(UTF8_2_ISO88592('famuła.pl'));
				$pdf->Output($filename);
				echo('<a href="'.$filename.'" target="blank">Pokaż</a>');
				if(isset($_COOKIE['zal'])&checkname()) mysql_query('insert into logs set user="'.$_COOKIE['zal'].'" action="Wygenerowano drzewo dla '.$theone['imie'].' '.$theone['nazwisko'].'" time="'.date("Y-m-d H:i:s").'";');
				else mysql_query('insert into logs set user="niezalogowany" action="Wygenerowano drzewo dla '.$theone['imie'].' '.$theone['nazwisko'].', z ip '.$_SERVER['REMOTE_ADDR'].'" time="'.date("Y-m-d H:i:s").'";');
			}
			else if(isset($_POST['submit2'])){
				$imw=2000;
				$imh=1500;
				$im=imagecreatetruecolor($imw,$imh);
				$whi=imagecolorallocate($im,255,255,255);
				$bla=imagecolorallocate($im,0,0,0);
				imagefilledrectangle($im,0,0,$imw,$imh,$whi);
				$res=mysql_query('select path from zdjecia where osoby="'.$id2.'" limit 1;');
				if($res) $theonezdj=mysql_fetch_assoc($res);
				else $theonezdj['path']='brakzdj.png';
				$thzdj=imagecreatefromjpeg($theonezdj['path']);
				$imsize=getimagesize($theonezdj['path']);
				imagecopyresampled($im,$thzdj,(($imw/2)-($imsize[0]/2)),($imh-$imsize[1]-50),0,0,$imsize[0],$imsize[1],$imsize[0],$imsize[1]);
				putenv('GDFONTPATH=' . realpath('.'));
				$fnt='calibri';
				$th_info=mysql_fetch_assoc(mysql_query('select imie,nazwisko from ludzie where id='.htmlspecialchars($id2).';'));
				$text=$th_info['imie'].''.$th_info['nazwisko'];
				$text_size=imagettfbbox(20,0,$fnt,$text);
				$sx=(($imw/2)-($text_size[2]/2));
				$sy=($imh-10);
				imagettftext($im,20,0,$sx,$sy,$bla,$fnt,$text);
				$dzieci=mysql_query('select imie,nazwisko,ur from ludzie where rodzic1='.htmlspecialchars($id2).' or rodzic2='.htmlspecialchars($id2).';');
				$dkat=180/mysql_num_rows($dzieci);
				$r=300;
				for($i=0;$i<mysql_num_rows($dzieci);$i+=1){
					$dx=sin($i*$dkat)*$r;
					$dy=cos($i*$dkat)*$r;
					imageline($im,$sx,$sy,$sx+$dx,$sy+$dy,$bla);
				}
				imagejpeg($im,'pdfgen/tree2_'.$id2.'.jpg');
				echo('<a href="pdfgen/tree2_'.$id2.'.jpg">Pokaż</a>');
			}
			else{
				$theone=mysql_fetch_assoc(mysql_query('select imie,nazwisko,rodzic1,rodzic2 from ludzie where id='.$id2.';'));
				echo('<p>Generowanie drzewa dla: '.$theone['imie'].' '.$theone['nazwisko'].'</p><form action="index.php?tree,'.$id2.'" method="POST" name="treegen">
				<p>Pokoleń wstecz:</p>
				<label><input type="radio" class="formfld" name="pok" value="2"> do dziadków</label><br>
				<label><input type="radio" class="formfld" name="pok" value="3"> do pradziadków</label><br>
				<label><input type="radio" class="formfld" name="pok" value="4" checked="checked"> do prapradziadków</label><br><br>');
				echo('<label><input type="checkbox" class="formfld" name="zdjecia" checked="checked"> Ze zdjęciami</label><br>');
				echo('<input type="submit" name="submit" value="Generuj w dół" class="formbtn" id="treegen" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)">
					  <input type="submit" name="submit2" value="Generuj w górę" class="formbtn" id="treegen2" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)">
				</form>');
			}
		}
		else echo('<p class="alert">Kogo pokazać?</p>');
		html_end();
		break;
	}
	case 'rocznik':{
		html_start();
		if(isset($_POST['rok'])&(!isset($id2))) $id2=$_POST['rok'];
		echo('<h2><a href="index.php?rocznik,'.($id2-1).'">&lt; '.($id2-1).'</a> '.$id2.' <a href="index.php?rocznik,'.($id2+1).'">'.($id2+1).' &gt;</a></h2>');
		echo('<form name="rocznik" action="index.php?rocznik" method="POST"><input class="formfld" type="text" id="rok" name="rok"><button class="formbtn" id="przejdz" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)" onclick="rokclick(document.rocznik.rok);" type="button" name="b1" value="Pokaż">Pokaż</button></form><br>');
		if(strlen($id2)==4){
			$res=mysql_query('select id from ludzie where ur='.htmlspecialchars($id2).' order by imie,nazwisko;');
			if(mysql_num_rows($res)>0){
				echo('<h3>Urodzeni w '.$id2.'</h3>');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<p>'.linkujludzia($row['id'],2).'</p>');
				}
			}
			$res=mysql_query('select id from ludzie where zm='.htmlspecialchars($id2).' order by imie,nazwisko;');
			if(mysql_num_rows($res)>0){
				echo('<h3>Zmarli w '.$id2.'</h3>');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<p>'.linkujludzia($row['id'],2).'</p>');
				}
			}
			$res=mysql_query('select * from zdjecia where rok='.htmlspecialchars($id2).' and path like "%gru%";');
			if(mysql_num_rows($res)>0){
				echo('<h3>Zdjęcia zrobione w '.$id2.'</h3>');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					$pth=explode('.',$row['path']);
					echo('<a href="index.php?zdjgru1,'.$row['id'].'" title="'.$row['opis'].'"><img border="2" src="'.$pth[0].'m.jpg" class="lud"></a>');
				}
			}
		}
		html_end();
		break;
	}
	case 'info':{
		html_start();
		echo($settings['about']);
		html_end();
		break;
	}
	case 'zdjgru':{
		html_start();
		echo('<h3>Zdjęcia grupowe:</h3>');
		if(isset($_COOKIE['zal'])&checkname()) echo('<p><a href="index.php?zdjgru-add">Dodaj nowe zdjęcie</a></p><hr>');
		if(isset($id2)){
			if(($id2=='s')|($id2=='l')|($id2=='k')) $cat=$id2;
			else $cat='s';
		}
		else $cat='s';
		$submenu=Array('s'=>'Śluby','l'=>'Lecia','k'=>'Komunie');
		echo('<p>Pokazuj zdjęcia: ');
		foreach($submenu as $k=>$v){
			if($cat==$k) echo('<b>');
			echo('&nbsp;<a href="index.php?zdjgru,'.$k.'">'.$v.'</a>&nbsp;');
			if($cat==$k) echo('</b>');
		}
		echo('</p>');
		$res=mysql_query('select * from zdjecia where osoby like "0,%" and cat="'.$cat.'" order by rok;');
		echo('<center><table border="0">');
		for($i=0;$i<mysql_num_rows($res);$i+=1){
			$row=mysql_fetch_assoc($res);
			$pth=explode('.',$row['path']);
			echo('<tr><td><a name="gr'.$row['id'].'"></a><a href="index.php?zdjgru1,'.$row['id'].'"><img src="'.$pth[0].'m.'.$pth[1].'" usemap="#gru'.$row['id'].'"></a></td><td><font size="7">'.$row['rok'].'</font></td><td>'.$row['opis'].'</td></tr>');
		}
		echo('</table></center>');
		html_end();
		break;
	}
	case 'zdjgru-del':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(preg_match('#,picdel,#',$currentuser['flags'])){
				if(isset($id2)){
					if(isset($id3)&($id3=='taknapewno')){
						$row=mysql_fetch_assoc(mysql_query('select * from zdjecia where id='.htmlspecialchars($id2).' and osoby like "0,%" limit 1;'));
						if($row){
							$pth=explode('.',$row['path']);
							$min=$pth[0].'m.'.$pth[1];
							unlink($row['path']);
							unlink($min);
							if(mysql_query('delete from zdjecia where id='.htmlspecialchars($id2).';')){
								mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Usunięto zdjęcie grupowe: '.$row['opis'].'", time="'.date("Y-m-d H:i:s").'";');
								echo('<p class="ok">Poprawnie usunięto zdjęcie "'.$row['opis'].'"</p><a href="index.php?zdjgru">Wróć do listy zdjęć</a>');
							}
							else echo('<p class="alert">Nie udało sie usunąć</p>');
						}
						else echo('<p class="alert">Nie ma takiego zdjęcia grupowego</p>');
					}
					else{
						echo('<p>Czy napewno usunąć to zdjęcie?</p><p class="alert"><a href="index.php?zdjgru-del,'.$id2.',taknapewno">Tak, napewno usunąć</a></p><p class="ok"><a href="index.php?zdjgru1,'.$id2.'">Nie usuwaj</a></p>');
					}
				}
				else echo('<p class="alert">Nie wiem co usunąć!</p>');
			}
			else{
				mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba usunięcia zdjęcia grupowego, mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
				echo('<p class="alert">Nie masz uprawnień do usuwania zdjęć</p>');
			}
		}
		else{
			mysql_query('insert into logs set user="niezalogowany", action="Próba dostępu do Usuwania zdjęć grupowych, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">Najpierw się <a href="index.php?login">zaloguj</a></p>');
		}
		html_end();
		break;
	}
	case 'zdjgru1':{
		html_start();
		if(isset($id2)){
			$row=mysql_fetch_assoc(mysql_query('select * from zdjecia where id='.htmlspecialchars($id2).';'));
			echo('<a name="gr'.$row['id'].'"></a><h1>'.$row['rok'].': '.$row['opis'].'</h1><a href="index.php?zdjgru,'.$row['cat'].'#gr'.$row['id'].'">wróć do listy zdjęć</a><br>
			<img src="'.$row['path'].'" usemap="#gru'.$row['id'].'"><br><p>Osoby: ');
			$os=explode(',',$row['osoby']);
			for($k=1;$k<(count($os)-1);$k+=1){
				if($k!=1) echo(', ');
				$rowo=mysql_fetch_assoc(mysql_query('select * from ludzie where id='.$os[$k].';'));
				if($rowo['ur']==0) $rur='?';
				else $rur=$rowo['ur'];
				if($rowo['zm']==0) $rzm='?';
				else $rzm=$rowo['zm'];
				if($rowo['sex']=='k') $rse='córka';
				else $rse='syn';
				$wynik='<nobr><a href="index.php?pokaz,one,'.$rowo['id'].'">'.$rowo['imie'].' '.$rowo['nazwisko'].'</a>';
				if($rzm=='?'){
					if($rur!='?') $wynik.=' ('.$rur.')';
				}
				else{
				$wynik.=' ('.$rur.'-'.$rzm.')';	
				}
				echo $wynik.'</nobr>'; 
			}
			echo('</p><map id="gru'.$row['id'].'" name="gru'.$row['id'].'">');
			$coo=explode(':',$row['coords']);
			for($j=1;$j<(count($os)-1);$j+=1){
				$osoba=mysql_fetch_assoc(mysql_query('select * from ludzie where id='.$os[$j].' limit 1'));
				echo('<area shape="poly" coords="'.$coo[($j-1)].'" href="index.php?pokaz,one,'.$os[$j].'" title="'.$osoba['imie'].' '.$osoba['nazwisko'].'">');
			}
			echo('</map>');
			if(isset($_COOKIE['zal'])&checkname()) echo('<a href="index.php?zdjgru-dodos,'.$row['path'].'">Edytuj zdjęcie</a> | <a href="index.php?zdjgru-del,'.$row['id'].'">Usuń zdjęcie</a><br>');
		}
		html_end();
		break;
	}
	case 'zdjgru-usos':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(preg_match('#,grupersondel,#',$currentuser['flags'])){
				if(isset($id2)&isset($id3)){
					$zdjecie=mysql_fetch_assoc(mysql_query('select id,osoby,coords from zdjecia where path like "%'.htmlspecialchars($id2).'%";'));
					$oso=explode(',',$zdjecie['osoby']);
					$crd=explode(':',$zdjecie['coords']);
					$noso='';
					foreach($oso as $k=>$v){
						if($v!=$id3){
							if($k!=0) $noso.=',';
							$noso.=$v;
							if($v!=0){
								if($k>1) $ncrd.=':';
								$ncrd.=$crd[($k-1)];
							}
						}
					}
					if(mysql_query('update zdjecia set osoby="'.$noso.'", coords="'.$ncrd.'" where id='.$zdjecie['id'].';')){
						mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Usunięto ludzia ze zdjęcia grupowego nr '.$zdjecie['id'].'", time="'.date("Y-m-d H:i:s").'";');
						echo('<p class="ok">Poprawnie usunięto osobe ze zdjęcia</p>');
					}
					else echo('<p class="ok">Nie udało się usunąć osoby ze zdjęcia</p>');
					echo('<a href="index.php?zdjgru-dodos,'.$id2.'">Wróć do edytowania tego zdjęcia</a>');
				}
				else echo('<p class="alert">Nie wiem kogo usunąć</p>');
			}
			else{
				mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba usuwania ludzi ze zdjęć grupowych, mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
				echo('<p class="alert">Nie masz uprawnień do usuwania ludzi ze zdjęć</p>');
			}
		}
		else{
			mysql_query('insert into logs set user="niezalogowany", action="Próba dostępu do Usuwania ludzi ze zdjęć grupowych, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">Najpierw się <a href="index.php?login">zaloguj</a></p>');
		}
		html_end();
		break;
	}
	case 'zdjgru-edit':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(preg_match('#,picedit,#',$currentuser['flags'])){
				if(isset($_POST['submit'])){
					if(mysql_query('update zdjecia set rok='.htmlspecialchars($_POST['rok']).', opis="'.htmlspecialchars($_POST['opis']).'", cat="'.htmlspecialchars($_POST['cat']).'" where id='.htmlspecialchars($_POST['id']).';')){
						mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Edycja zdjęcia '.htmlspecialchars($_POST['opis']).'", time="'.date("Y-m-d H:i:s").'";');
						echo('<p class="ok">Poprawnie zmieniono</p>');
					}
					else echo('<p class="alert">Nie udało się zmienić</p>');
					echo('<a href="index.php?zdjgru1,'.$_POST['id'].'">Wróć do zdjęcia</a>');
				}
				else echo('<p class="alert">Na tę strone można wejść tylko przez klikniecie przycisku</p>');
			}
			else{
				mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba edycji zdjęcia grupowego, mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
				echo('<p class="alert">Nie masz uprawnień do edytowania zdjęć</p>');
			}
		}
		else{
			mysql_query('insert into logs set user="niezalogowany", action="Próba dostępu do Edycji zdjęć grupowych, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">Najpierw się <a href="index.php?login">zaloguj</a></p>');
		}
		html_end();
		break;
	}
	case 'zdjgru-dodos':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(isset($id2)){
				$actzdj=mysql_fetch_assoc(mysql_query('select * from zdjecia where path like "%'.htmlspecialchars($id2).'%" limit 1;'));
				echo('<a href="index.php?zdjgru1,'.$actzdj['id'].'">Zobacz jak będzie wyglądać to zdjęcie</a>');
				$osob=explode(',',$actzdj['osoby']);
				if(isset($_POST['submit'])){
					if(preg_match('#,grupersonadd,#',$currentuser['flags'])){
						$q='update zdjecia set coords="'.$actzdj['coords'].':';
						for($i=0;$i<8;$i+=1){
							if($_POST[('posx'.$i)]!=0){
								if($i!=0) $q.=',';
								$q.=$_POST[('posx'.$i)].','.$_POST[('posy'.$i)];
							}
						}
						$q.='", osoby="0';
						for($i=1;$i<(count($osob)-1);$i+=1) $q.=','.$osob[$i];
						$q.=','.$_POST['kto'].',0" where id='.$actzdj['id'].';';
						if(mysql_query($q)){
							mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Dodano ludzia do zdjęcia nr '.$actzdj['id'].'", time="'.date("Y-m-d H:i:s").'";');
							echo('<p class="ok">Poprawnie dodano</p>');
						}
						else echo('<p class="alert">Nie udało się dodać</p>');
					}
					else{
						mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba dodawania ludzi do zdjęć grupowych, mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
						echo('<p class="alert">Nie masz uprawnień do dodawania ludzi do zdjęć</p>');
					}
				}
				$actzdj=mysql_fetch_assoc(mysql_query('select * from zdjecia where path like "%'.htmlspecialchars($id2).'%" limit 1;'));
				if($actzdj){
					$imsize=getimagesize($actzdj['path']);
					$nih=($imsize[1]*1000)/$imsize[0];
					echo('<tr><td colspan="3"><form name=zdjgruedit" action="index.php?zdjgru-edit" method="POST"><label>rok:<input type="text" name="rok" size="4" class="formfld" maxlength="4" value="'.$actzdj['rok'].'"></label> <label>opis:<input type="text" name="opis" class="formfld" value="'.$actzdj['opis'].'" size="80"></label> <label title="s, l lub k">typ: <input type="tekst" name="cat" class="formfld" size="1" maxlength="1" value="'.$actzdj['cat'].'"></label><input type="hidden" name="id" value="'.$actzdj['id'].'"> <input type="submit" name="submit" value="Zapisz" class="formbtn" id="zdjeditgru" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></form></td></tr>');
					echo('<form name="stg2" action="index.php?zdjgru-dodos,'.$id2.'" method="POST"><table border="0" width="100%"><tr><td colspan="3" text-align="center"><div id="pointer_div" onclick="point_it(event)" style = "background-image:url(\''.$actzdj['path'].'\');width:1000px;height:'.$nih.'px;"></td></tr>');
					echo('<input type="hidden" name="zdjname" value="'.$actzdj['path'].'"><input type="hidden" name="rok" value="'.$actzdj['rok'].'">');
					echo('<tr><td valign="top"><select class="formfld" id="z1" name="kto"><option value="0">Nieznany</option>');
					$res=mysql_query('select id,imie,nazwisko,ur,pok from ludzie order by nazwisko,imie,ur;');
					for($i=0;$i<mysql_num_rows($res);$i+=1){
						$row=mysql_fetch_assoc($res);
						echo('<option value="'.$row['id'].'">');
						for($j=0;$j<$row['pok'];$j+=1) echo('-');
						echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
					}
					echo('</select><br>');
					for($i=0;$i<8;$i+=1){
						echo('<input type="text" name="posx'.$i.'" size="4" class="formfld" value="0"><input type="text" name="posy'.$i.'" size="4" class="formfld" value="0"><br>');
					}
					echo('<input type="submit" name="submit" class="formbtn" value="Dodaj" id="dodosdozdj" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></td><td>');
					$dous=mysql_fetch_assoc(mysql_query('select osoby,coords from zdjecia where path like "%'.htmlspecialchars($id2).'%";'));
					$oso=explode(',',$dous['osoby']);
					for($i=0;$i<(floor(count($oso))+1);$i+=1){
						if($oso[$i]!=0){
							echo('<font size="2">'.linkujludzia($oso[$i],2).' <a href="index.php?zdjgru-usos,'.$id2.','.$oso[$i].'">Usuń</a></font><br>');
						}
					}
					echo('</td><td>');
					for($i=(floor(count($oso))+1);$i<count($oso);$i+=1){
						if($oso[$i]!=0){
							echo('<font size="2">'.linkujludzia($oso[$i],2).' <a href="index.php?zdjgru-usos,'.$id2.','.$oso[$i].'">Usuń</a></font><br>');
						}
					}
					echo('</td></tr></table></form>');
				}
				else echo('<p class="alert">Pliku nie ma w bazie danych</p>');
			}
			else echo('<p class="alert">Brak nazwy zdjęcia</p>');
		}
		else{
			mysql_query('insert into logs set user="niezalogowany", action="Próba dostępu do Dodawania ludzi do zdjęć grupowych, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">Najpierw się <a href="index.php?login">zaloguj</a></p>');
		}
		html_end();
		break;
	}
	case 'zdjgru-add':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(preg_match('#,picadd,#',$currentuser['flags'])){
				if(isset($_POST['stage1'])){
					if(is_uploaded_file($_FILES['zdj']['tmp_name'])){
						$newname='gru'.date('U');
						if($_FILES['zdj']['size']<=$_POST['MAX_FILE_SIZE']){	 
							move_uploaded_file($_FILES['zdj']['tmp_name'], 'gfx/'.$newname.'.jpg');
							$im=imagecreatefromjpeg('gfx/'.$newname.'.jpg');
							$imsize=getimagesize('gfx/'.$newname.'.jpg');
							$minh=($imsize[1]*200)/$imsize[0];
							$nih=($imsize[1]*1000)/$imsize[0];
							$nim=imagecreatetruecolor(1000,$nih);
							$min=imagecreatetruecolor(200,$minh);
							imagecopyresampled($nim,$im,0,0,0,0,1000,$nih,$imsize[0],$imsize[1]);
							imagecopyresampled($min,$im,0,0,0,0,200,$minh,$imsize[0],$imsize[1]);
							imagejpeg($nim,'gfx/'.$newname.'.jpg');
							imagejpeg($min,'gfx/'.$newname.'m.jpg');
							echo('<form name="stg2" action="index.php?zdjgru-add" method="POST"><table border="0"><tr><td><div id="pointer_div" onclick="point_it(event)" style = "background-image:url(\'gfx/'.$newname.'.jpg\');width:1000px;height:'.$nih.'px;"></td></tr>');
							echo('<input type="hidden" name="zdjname" value="'.$newname.'"><input type="hidden" name="rok" value="'.$_POST['rok'].'"><input type="hidden" name="opis" value="'.$_POST['opis'].'">');
							echo('<tr><td><select class="formfld" id="z1" name="kto"><option value="0">Nieznany</option>');
							$res=mysql_query('select id,imie,nazwisko,ur,pok from ludzie order by nazwisko,imie');
							for($i=0;$i<mysql_num_rows($res);$i+=1){
								$row=mysql_fetch_assoc($res);
								echo('<option value="'.$row['id'].'">');
								for($j=0;$j<$row['pok'];$j+=1) echo('-');
								echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
							}
							echo('</select><br>');
							for($i=0;$i<8;$i+=1){
								echo('<input type="text" name="posx'.$i.'" size="4" class="formfld" value="0"><input type="text" name="posy'.$i.'" size="4" class="formfld" value="0"><br>');
							}
							echo('<input type="submit" name="stage2" class="formbtn" value="Zapisz" id="dodozdj" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></td></tr></table></form>');
						}
						else{
							echo('Plik: <strong>'.$_FILES['zdj']['name'].'</strong> jest zbyt duży! Jego rozmiar przekracza '.($_POST['MAX_FILE_SIZE']/1024/1024).' MB<br>');
						}	
					}
					else echo('<p class="alert">Niepoprawny plik</p>');
				}
				else if(isset($_POST['stage2'])){
					if(isset($_POST['zdjname'])){
						if(file_exists('gfx/'.$_POST['zdjname'].'.jpg')){
							$q='insert into zdjecia set path="gfx/'.$_POST['zdjname'].'.jpg", osoby="0,'.$_POST['kto'].',0", rok='.$_POST['rok'].', opis="'.$_POST['opis'].'", coords="';
							for($i=0;$i<8;$i+=1){
								if($_POST[('posx'.$i)]!=0){
									if($i!=0) $q.=',';
									$q.=$_POST[('posx'.$i)].','.$_POST[('posy'.$i)];
								}
							}
							$q.='";';
							if(mysql_query($q)){
								mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Dodano zdjęcia grupowe '.htmlspecialchars($_POST['zdjname']).'", time="'.date("Y-m-d H:i:s").'";');
								echo('<p class="ok">Poprawnie dodano zdjęcie</p><a href="index.php?zdjgru-dodos,'.$_POST['zdjname'].'">Dodaj jeszcze jedną osobę do tego zdjęcia</a>');
							}
							else echo('<p class="alert">Błąd w zapytaniu mysql</p>');
						}
						else echo('<p class="alert">Plik nie istnieje</p>');
					}
					else echo('<p class="alert">Brak nazwy pliku</p>');
				}
				else{ //form
					echo('<form enctype="multipart/form-data" action="index.php?zdjgru-add" method="POST">
						<input type="hidden" name="MAX_FILE_SIZE" value="100000000" />
						<table border="0"><tr><td>Dodaj zdjęcie:</td><td>Rok</td><td>&nbsp;</td></tr><tr><td><input class="formfld" name="zdj" type="file" /></td><td><input type="text" name="rok" class="formfld" size="4" maxlength="4"></td>
						<td><input class="formbtn" id="grudod" name="stage1" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)" type="submit" value="Wyślij" /></td></tr><tr><td colspan="2"><textarea rows="5" cols="60" name="opis" class="formfld"></textarea></td></tr></table></form>');
				}
			}
			else{
				mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba dodania zdjęcia grupowego, mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
				echo('<p class="alert">Nie masz uprawnień do dodawania zdjęć</p>');
			}
		}
		else{
			mysql_query('insert into logs set user="niezalogowany", action="Próba dostępu do Dodawania zdjęć grupowych, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">Najpierw się <a href="index.php?login">zaloguj</a></p>');
		}
		html_end();
		break;
	}
	case 'zdj':{
		html_start();
		if(isset($id2)){
			if(isset($_COOKIE['zal'])&checkname()){
				if(isset($_POST['del'])){
					if(preg_match('#,picdel,#',$currentuser['flags'])){
						$zdjdel=mysql_fetch_assoc(mysql_query('select * from zdjecia where id='.$_POST['id'].';'));
						if(mysql_query('delete from zdjecia where id='.$_POST['id'].';')){
							unlink($zdjdel['path']);
							echo('<p class="ok">Poprawnie usunięto zdjęcie</p>');
							mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Usunięto zdjęcie", time="'.date("Y-m-d H:i:s").'";');
						}
						else echo('<p class="alert">Nie udało się usunąć zdjęcia</p>');
					}
					else{
						mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba usunięcia zdjęcia '.$zdjdel['path'].', mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
						echo('<p class="alert">Nie masz uprawnień do usuwania zdjęć</p>');
					}
				}
				if(isset($_POST['zmien'])){
					if(preg_match('#,picedit,#',$currentuser['flags'])){
						if(is_numeric($_POST['rok'])){
							$q='update zdjecia set rok='.htmlspecialchars($_POST['rok']);
							if(isset($_POST['slub'])) $q.=', slub=1';
							else $q.=', slub=0';
							if(isset($_POST['komunia'])) $q.=', komunia=1';
							else $q.=', komunia=0';
							$q.=' where id='.$_POST['id'].';';
							if(mysql_query($q)){
								mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Edycja zdjęcia", time="'.date("Y-m-d H:i:s").'";');
								echo('<p class="ok">Poprawnie zmieniono zdjęcie</p>');
							}
							else echo('<p class="alert">Nie udało się zmienic zdjecia</p>');
						}
						else echo('<p class="alert">Podaj rok jako 4 cyfry</p>');
					}
					else{
						mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba edycji zdjecia, mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
						echo('<p class="alert">Nie masz uprawnień do edytowania zdjęć</p>');
					}
				}
			}
			echo('<h3>'.linkujludzia($id2,2).' - Zdjęcia</h3>');
			$res=mysql_query('select * from zdjecia where osoby="'.htmlspecialchars($id2).'" order by rok desc,id desc;');
			echo('<table border="0">');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				echo('<tr><td><img src="'.$row['path'].'"></td><td>');
				if(isset($_COOKIE['zal'])){
					echo('<form name="zdj'.$id2.'" action="index.php?zdj,'.$id2.'" method="POST"><input type="text" class="formfld" name="rok" value="'.$row['rok'].'"></td><td>
					<input type="hidden" name="id" value="'.$row['id'].'">
					<label><input type="checkbox" name="slub" class="formfld"');
					if($row['slub']==1) echo(' checked="checked"');
					echo('> Ślubne</label> <label><input type="checkbox" name="komunia" class="formfld"');
					if($row['komunia']==1) echo(' checked="checked"');
					echo('> Komunia</label></td><td>
					<input type="submit" name="zmien" value="Zmień" class="formbtn" id="zdjid'.$row['id'].'" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)">
					<input type="submit" name="del" value="Usuń" class="formbtn" id="zdjdel'.$row['id'].'" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></form>');
				}
				else{
					echo('<h3>'.$row['rok']);
					if($row['slub']==1) echo(', zdjęcie ze ślubu');
					echo('</h3>');
				}
				echo('</td></tr>');
			}
			echo('</table>');
			$zdjgru=mysql_query('select * from zdjecia where osoby like "%,'.htmlspecialchars($id2).',%" order by rok;');
			if(mysql_num_rows($zdjgru)>0){
				echo('<hr><h3>Jest na zdjęciach grupowych:</h3>');
				for($l=0;$l<mysql_num_rows($zdjgru);$l+=1){
					$row2=mysql_fetch_assoc($zdjgru);
					$pat=explode('.',$row2['path']);
					$min=$pat[0].'m.'.$pat[1];
					echo('<a href="index.php?zdjgru1,'.$row2['id'].'" title="'.$row2['opis'].' '.$row2['rok'].'"><img src="'.$min.'"></a> ');
				}
			}
		}
		else echo('<p class="alert">Kogo zdjęcia pokazać?</p>');
		html_end();
		break;
	}
	case 'users':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(($id2=='edit')&preg_match('#,useredit,#',$currentuser['flags'])){
				if(isset($_POST['edit'])){
					if(mysql_query('update users set name="'.$_POST['name'].'", pass="'.$_POST['pass'].'", flags="'.$_POST['flags'].'" where id='.$_POST['id'].';')) echo('<p class="ok">Poprawnie zmieniono</p>');
					else echo('<p class="alert">Nie udało się zmienić</p>');
				}
				if(isset($_POST['del'])){
					if($_POST['id']==$currentuser['id']) echo('<p class="alert">Nie możesz usunąć siebie!</p>');
					else{
						if(mysql_query('delete from users where id='.htmlspecialchars($_POST['id']).';')) echo('<p class="ok">Poprawnie usunięto użytkownika</p>');
						else echo('<p class="alert">Nie udało się usunąć uzytkownika</p>');
					}
				}
			}
			if(($id2=='add')&preg_match('#,useradd,#',$currentuser['flags'])){
				if(isset($_POST['submit'])){
					if(mysql_query('insert into users set name="'.htmlspecialchars($_POST['newname']).'", pass="'.md5(htmlspecialchars($_POST['newpass']).'dupa').'", flags="0";')) echo('<p class="ok">Poprawnie dodano użytkownika</p>');
					else echo('<p class="alert">Nie udało się dodać użytkownika</p>');
				}
				else{
					echo('<form name="adduser" action="index.php?users,add" method="post"><label>nazwa:<input type="text" class="formfld" name="newname" size="15"></label><label>hasło:<input type="text" name="newpass" class="formfld" size="15"></label><input type="submit" name="submit" value="Zapisz" class="formbtn" id="useradd-btn" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></form>');
				}
			}
			echo('<p><a href="index.php?users,add">Dodaj nowego użytkownika</a></p>');
			$res=mysql_query('select * from users');
			echo('<table border="1"><tr><td>name</td><td>hash</td><td>flags</td><td>actions</td></tr>');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				if(preg_match('#,useredit,#',$currentuser['flags'])) echo('<tr><form method="POST" name="user-'.$row['name'].'" action="index.php?users,edit"><input type="hidden" name="id" value="'.$row['id'].'"><td><input type="text" name="name" class="formfld" value="'.$row['name'].'" size="10"></td><td><input type="text" name="pass" value="'.$row['pass'].'" size="32" class="formfld"></td><td><input type="text" name="flags" value="'.$row['flags'].'" class="formfld" size="80"></td><td><input type="submit" name="edit" value="Edytuj" class="formbtn" id="user-'.$row['id'].'-edit" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"> <input type="submit" name="del" value="Usuń" class="formbtn" id="user-'.$row['id'].'-del" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></td></form></tr>');
				else echo('<tr><td>'.$row['name'].'</td><td>'.$row['pass'].'</td><td>'.$row['flags'].'</td><td>brak</td></tr>');
			}
			echo('</table>');
		}
		else{
			mysql_query('insert into logs set user="niezalogowany", action="Próba dostępu do Użytkowników, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">Najpierw się <a href="index.php?login">zaloguj</a></p>');
		}
		html_end();
		break;
	}
	case 'settings':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(isset($_POST['submit'])){
				if(mysql_query('update settings set edit_pp='.htmlspecialchars($_POST['edit_pp']).', search_pp='.htmlspecialchars($_POST['search_pp']).', main_opis="'.strip_tags($_POST['main_opis'],'<a><b><i><u>').'", all_podmenu="'.strip_tags($_POST['all_podmenu'],'<a><b><i><u>').'", about="'.strip_tags($_POST['about'],'<a><b><i><u><table><tr><td><img><li><center><p><h3><br>').'";')){
					echo('<p class="ok">Poprawnie zmieniono</p>');
					mysql_query('insert into logs set user="'.$_COOKIE['zal'].'", action="Zmiana ustawień", time="'.date("Y-m-d H:i:s").'";');
				}
				else echo('<p class="alert">Nie udało się zmienić</p>');
			}
			echo('<form name="setts" action="index.php?settings" method="POST"><table border="0">
			<tr><td>opis na stronie głównej</td><td><textarea name="main_opis" rows="3" cols="100" class="formfld">'.$settings['main_opis'].'</textarea></td></tr>
			<tr><td>tekst pod menu</td><td><textarea name="all_podmenu" rows="3" cols="100" class="formfld">'.$settings['all_podmenu'].'</textarea></td></tr>
			<tr><td>tekst "O stronie"</td><td><textarea name="about" rows="8" cols="100" class="formfld">'.$settings['about'].'</textarea></td></tr>
			<tr><td>Pozycji na stronę w Edytuj</td><td><input type="text" name="edit_pp" size="3" maxlength="3" value="'.$settings['edit_pp'].'" class="formfld"></td></tr>
			<tr><td>Pozycji na stronę w Szukajce</td><td><input type="text" name="search_pp" size="3" maxlength="3" value="'.$settings['search_pp'].'" class="formfld"</td></tr>
			<tr><td colspan="2" align="center"><input type="submit" name="submit" value="Zapisz" id="settzapisz" class="formbtn" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></td></tr></table></form>');
		}
		else{
			mysql_query('insert into logs set user="niezalogowany", action="Próba dostępu do Ustawień, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">Najpierw się <a href="index.php?login">zaloguj</a></p>');
		}
		html_end();
		break;
	}
	case 'logs':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			$res=mysql_query('select * from logs');
			echo('<table border="1"><tr><td>time</td><td>user</td><td>action</td></tr>');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				echo('<tr><td>'.$row['time'].'</td><td>'.$row['user'].'</td><td>'.$row['action'].'</td></tr>');
			}
			echo('</table>');
		}
		else echo('<p class="alert">Najpierw się <a href="index.php?login">zaloguj</a></p>');
		html_end();
		break;
	}
	case 'md5':{
		html_start();
		echo('<p class="ok">'.md5($id2.'dupa').'</p>');
		html_end();
		break;
	}
	default:{
		html_start();
		echo('<p class="alert">404: Nie ma takiej strony</p>');
		html_end();
	}
}
mysql_close();
?>
