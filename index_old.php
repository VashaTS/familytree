<?php
// drzewo genealogiczne
$lng='pl'; //default language
if(isset($_COOKIE['lan'])) $lng=$_COOKIE['lan']; 
include('lang.php');
$ver='1.5d';
// 2017-11-11
ini_set( 'display_errors', 'Off' );
ini_set('memory_limit','300M'); //mostly used by treegen2
error_reporting( E_ALL );
include('db_connection.php');
mysql_query('SET NAMES utf-8');
$settings=mysql_fetch_assoc(mysql_query('select * from settings'));
if(isset($_COOKIE['zal'])){
	setcookie('zal',$_COOKIE['zal'],(time()+60*10));
	$currentuser=mysql_fetch_assoc(mysql_query('select id,flags from users where name="'.$_COOKIE['zal'].'" limit 1;')); //this is a global varrible used by functions!
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
$thisfile='index_old.php';
$vars=explode(',',$request[1]);
if(strlen($vars[0])>2) $id=$vars[0];
else $id='main';
$id2=$vars[1];
$id3=$vars[2];
$id4=$vars[3];
$id5=$vars[4];
if($id=='pokr') for($k=0;$k<2000;$k+=1) $byl[$k]=0;
$qc=0;
include('functions.php');
$banned=mysqlquerryc('select * from banip;');
for($i=0;$i<mysql_num_rows($banned);$i+=1){
	$rbn=mysql_fetch_assoc($banned);
	if(strstr($_SERVER['REMOTE_ADDR'],$rbn['ip'])!=FALSE) $id='404';
}
$width_duz=1200; //only for new pics
$width_min=200;	
$jestans=0; // global var for pokr function


function html_start(){
	global $ver,$settings,$currentuser,$lang,$lng,$id,$id2,$id3,$qc;
	header("Content-Type: text/html; charset=UTF-8");
	echo('<html><head><title>'.$lang[$lng][1]);
	if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) echo(' v'.$ver);
	echo('</title><link rel="stylesheet" type="text/css" href="rodzina.css" />
		<script type="text/javascript" src="rodzina.js"></script>
		<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>');
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) echo('<script type="text/javascript">
		$(document).ready(function(){
		$(\'img[usemap]\').maphilight();
		});
		');
		echo('</script></head><body>');
			echo('<div style="float:right; border:none; vertical-align:center;">');
			foreach($lang as $k1 => $v1){
				echo('<a href="'.$thisfile.'?set-lang,'.$k1.','.$id.','.$id2.','.$id3.'"><img border="1" src="flags/'.$k1.'.png"></a>');
			}
			echo('</div><br><br><br>');
		echo('<p>');
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) echo('<h1><a href="'.$thisfile.'?main">'.$settings['site_name'].': '.$lang[$lng][2].'</a></h1>'); 
		else echo('<img usemap="#logomap" src="logo.png"><br></p><div class="all">
		<map name="logomap" id="logomap"><area title="'.$settings['site_name'].'" shape="poly" href="'.$thisfile.'?main" coords="11,177,632,177,633,211,686,211,685,181,792,176,778,15,738,31,742,65,666,67,634,81,636,135,576,138,561,68,475,62,463,14,431,29,439,67,87,63,87,12,22,15,1,80"></map>');
	$menus=Array();
	$menus['search']=$lang[$lng][3]; //search
	$menus['stats']=$lang[$lng][4]; //stats
	$menus['pokaz,all']=$lang[$lng][5]; // family tree
	$menus['rocznik,'.date("Y")]=$lang[$lng][6];
	$menus['zdjgru']=$lang[$lng][7]; //photos
	$menus['info']=$lang[$lng][8]; //about
	$menus2=Array();
	$menus2['add']=$lang[$lng][9]; //add new person to db
	$menus2['edit']=$lang[$lng][10]; //edit db in bulk
	$menus2['todo']=$lang[$lng][11]; // todo for website maintainer
	$menus2['settings']=$lang[$lng][12]; //settings
	$menus2['users']=$lang[$lng][13]; //website users
	$menus2['logs']=$lang[$lng][14]; //logs
	$menus2['messages']=$lang[$lng][15]; //read guestbook entries
	$menus3=Array();
    $menus3['ipban']='Ban IP';
	$menus3['md5']='MD5';
	$menus3['404']='404';
	$menus3['files']=$lang[$lng][16];  
	if(isset($_COOKIE['zal'])&checkname()) $menus['logout']=$lang[$lng][17];
	else $menus['login']=$lang[$lng][18];
	echo('<div id="men1" class="menu" style="border:hidden; width:'.((count($menus)*110)+20).'px; background-color: #ffcc99; margin:auto; padding:0px; height: 30px; text-align:center; ">');
	foreach($menus as $k => $v) echo('<div class="mbox" onmouseover="highl(this.id)" onmouseout="downl(this.id)" onclick="menuclick(this.id)" id="'.$k.'"><p id="'.$k.'_p" class="menu">'.$v.'</p></div>');
	echo('</div>');
	if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))){
		echo('<div id="men2" class="menu" style="border:hidden; width:'.((count($menus2)*110)+20).'px; background-color: #ffcc99; margin:auto; padding:0px; height: 30px; text-align:center; ">');
		foreach($menus2 as $k => $v) echo('<div class="mbox" onmouseover="highl(this.id)" onmouseout="downl(this.id)" onclick="menuclick(this.id)" id="'.$k.'"><p id="'.$k.'_p" class="menu">'.$v.'</p></div>');
		echo('</div>');
		if(preg_match('#,menu3view,#',$currentuser['flags'])){
			echo('<div id="men3" class="menu" style="border:hidden; width:'.((count($menus3)*110)+20).'px; background-color: #ffcc99; margin:auto; padding:0px; height: 30px; text-align:center; ">');
			foreach($menus3 as $k => $v) echo('<div class="mbox" onmouseover="highl(this.id)" onmouseout="downl(this.id)" onclick="menuclick(this.id)" id="'.$k.'"><p id="'.$k.'_p" class="menu">'.$v.'</p></div>');
			echo('</div><br>');
		}
	}
	echo('<p><a href="index.php?kontakt">'.$lang[$lng][112].'</a></p><hr>');
	if(isset($_COOKIE['pokr'])&($_COOKIE['pokr']!=0)) echo('<p class="ok">'.$lang[$lng][19].' <a href="'.$thisfile.'?pokr,del">'.$lang[$lng][20].'</a></p>');
	unset($menus,$menus2,$menus3);
}
function html_end(){ //+google ad & analytics
	global $ver,$lang,$lng,$qc;
	echo('<hr><font size="1">'.$lang[$lng][1].' v'.$ver.' Copyleft 2012-'.date('Y').'. <a href="https://github.com/VashaTS/familytree">GitHub</a> | <a href="'.$thisfile.'?cookies">'.$lang[$lng][195].'</a> | '.$lang[$lng][226].': '.round(memory_get_peak_usage()/1024/1024,2).' MiB | Zapytań SQL: '.$qc.'</font><br></div><p>');
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
echo("
<script type=\"text/javascript\">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-44979032-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>");
	echo('</p></body></html>');
}

//IF($_SERVER['REMOTE_ADDR']!='81.15.212.181') $id='404';  // UNCOMMENT TO MAKE LOCAL -- EMERGENCY USE ONLY
if((!(isset($_COOKIE['zal'])&checkname()))&($id!='login-do')&($id!='main')&($id!='kontakt')&($id!='info')&($id!='set-lang')&($id!='cookies')) $id='login'; // niezalogowani widzą: main, login, login-do, kontakt
switch($id){
	case 'main':{
		html_start();
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $odroku=mysql_fetch_assoc(mysqlquerryc('select min(ur) as minur from ludzie where ur!=0;'));
		else $odroku=mysql_fetch_assoc(mysqlquerryc('select min(ur) as minur from ludzie where visible=1 and ur!=0;'));
		echo('<h2>'.$settings['main_opis'].', '.$lang[$lng][21].' '.$odroku['minur'].'</h2>');
		$ile_l=mysql_fetch_assoc(mysqlquerryc('select count(*) as li from ludzie;'));
		$ile_lnv=mysql_fetch_assoc(mysqlquerryc('select count(*) as li from ludzie where visible=0;'));
		$ile_z=mysql_fetch_assoc(mysqlquerryc('select count(*) as li from zdjecia;'));
		echo('<h3>'.$lang[$lng][22].' '.$ile_l['li'].' '.$lang[$lng][23]); //people in db
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) echo(' ('.$lang[$lng][24].' '.$ile_lnv['li'].' '.$lang[$lng][25].')'); // # of hidden
		echo(' '.$lang[$lng][26].' '.$ile_z['li'].' '.$lang[$lng][27].'</h3>');
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))){
			$rw=mysql_fetch_assoc(mysqlquerryc('select count(*) as cnt from ludzie where lastedit="'.$_COOKIE['zal'].'";'));
			$lt=mysql_fetch_assoc(mysqlquerryc('select time from logs where user="'.$_COOKIE['zal'].'" order by time desc limit 1,1;'));
			$lt0=explode(' ',$lt['time']);
			$lt1=explode('-',$lt0[0]);
			$lastvis=mktime(12,0,0,$lt1[1],$lt1[2],$lt1[0]);
			$today=time();
			$tdiff=$today-$lastvis;
			echo('<h3>'.$lang[$lng][28].' '.$_COOKIE['zal'].'! </h3>
			<p>'.$lang[$lng][29].' '.floor($tdiff/60/60/24).' '.$lang[$lng][30].'!</p>
			<p>'.$lang[$lng][31].': '.$rw['cnt'].'</p><br>');
			
			if(preg_match('#,menu2view,#',$currentuser['flags'])) echo('<h4>&#10004; '.$lang[$lng][32].'</h4>');
			else echo('<h4>&#10008; '.$lang[$lng][33].'</h4>');
			if(preg_match('#,personadd,#',$currentuser['flags'])) echo('<h4>&#10004; '.$lang[$lng][34].'</h4>');
			else echo('<h4>&#10008; '.$lang[$lng][35].'</h4>');
			if(preg_match('#,persondel,#',$currentuser['flags'])) echo('<h4>&#10004; '.$lang[$lng][36].'</h4>');
			else echo('<h4>&#10008; '.$lang[$lng][37].'</h4>');
			if(preg_match('#,personedit,#',$currentuser['flags'])) echo('<h4>&#10004; '.$lang[$lng][38].'</h4>');
			else echo('<h4>&#10008; '.$lang[$lng][39].'</h4>');
			if(preg_match('#,picadd,#',$currentuser['flags'])) echo('<h4>&#10004; '.$lang[$lng][40].'</h4>');
			else echo('<h4>&#10008; '.$lang[$lng][41].'</h4>');
			if(preg_match('#,picdel,#',$currentuser['flags'])) echo('<h4>&#10004; '.$lang[$lng][42].'</h4>');
			else echo('<h4>&#10008; '.$lang[$lng][43].'</h4>');
			if(preg_match('#,picedit,#',$currentuser['flags'])) echo('<h4>&#10004; '.$lang[$lng][44].'</h4>');
			else echo('<h4>&#10008; '.$lang[$lng][45].'</h4>');
			if(preg_match('#,grupersonadd,#',$currentuser['flags'])) echo('<h4>&#10004; '.$lang[$lng][46].'</h4>');
			else echo('<h4>&#10008; '.$lang[$lng][47].'</h4>');
			if(preg_match('#,grupersondel,#',$currentuser['flags'])) echo('<h4>&#10004; '.$lang[$lng][48].'</h4>');
			else echo('<h4>&#10008; '.$lang[$lng][49].'</h4>');
			if(preg_match('#,useredit,#',$currentuser['flags'])) echo('<h4>&#10004; '.$lang[$lng][50].'</h4>');
			else echo('<h4>&#10008; '.$lang[$lng][51].'</h4>');
			if(preg_match('#,menu3view,#',$currentuser['flags'])) echo('<h4>&#10004; '.$lang[$lng][52].'</h4>');
			else echo('<h4>&#10008; '.$lang[$lng][53].'</h4>');
		}
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Wyświetlenie strony głównej", time="'.date("Y-m-d H:i:s").'"');
		else mysqlquerryc('insert into logs set user="niezalogowany", action="Wyświetlenie strony głównej, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'"');
		html_end();
		break;
	}
	case 'login':{ // ALL CAN SEE
		html_start();
		echo('<form name="login" action="'.$thisfile.'?login-do" method="POST"><label>login:<input class="formfld" type="text" name="login"></label><br>
		<label>'.$lang[$lng][54].':<input class="formfld" type="password" name="pass"></label><br><input class="formbtn" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)" id="loginbtn" type="submit" name="submit" value="'.$lang[$lng][18].'"></form>');
		html_end();
		break;
	}
	case 'set-lang':{
		if(isset($lang[$id2])){
			setcookie('lan',$id2,(time()+60*60*24*61));
			html_start();
			echo('<p class="ok">Language change OK</p><script type="text/javascript">
			document.location="'.$thisfile.'?'.$id3.','.$id4.','.$id5.'";
			</script>');
			html_end();
		}
		else{
			html_start();
			echo('<p class="alert">'.$lang[$lng][186].'</p>');
			html_end();
		}
		break;
	}
	case 'login-do':{ // ALL CAN SEE
		if(isset($_POST['login'])&isset($_POST['pass'])){
			$res=mysqlquerryc('select * from users where name="'.htmlspecialchars($_POST['login']).'";');
			if(mysql_num_rows($res)==1){
				$row=mysql_fetch_assoc($res);
				if(md5($_POST['pass'].'dupa')==$row['pass']){
					$randval=md5(md5(rand(100,99999999)));
					if(mysqlquerryc('update users set ssid="'.$randval.'" where id='.$row['id'].';')){
						setcookie('zal',$row['name'],(time()+60*5));
						setcookie('ssid',$randval);
						mysqlquerryc('insert into logs set user="'.$row['name'].'", action="Zalogował się", time="'.date("Y-m-d H:i:s").'"');
					}
					html_start();
					echo('<p class="ok">Login OK</p><script type="text/javascript">
					document.location="'.$thisfile.'?main";
					</script>');
					html_end();
				}
				else{
					html_start();
					echo('<p class="alert">'.$lang[$lng][55].'</p>');
					mysqlquerryc('insert into logs set user="niezalogowany", action="Nieudane logowanie - hasło, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'"');
					html_end();
				}
			}
			else{
				html_start();
				echo('<p class="alert">'.$lang[$lng][55].'</p>');
				mysqlquerryc('insert into logs set user="niezalogowany", action="Nieudane logowanie - login, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'"');
				html_end();
			}
		}
		else{
			html_start();
			echo('<p class="alert">'.$lang[$lng][55].'</p>');
			mysqlquerryc('insert into logs set user="niezalogowany", action="Nieudane logowanie - puste pole, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'"');
			html_end();
		}
		break;
	}
	case 'logout':{
		mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Wylogował się", time="'.date("Y-m-d H:i:s").'"');
		setcookie("zal","null",date('U')-500);
		unset($_COOKIE['zal']);
		unset($_COOKIE['ssid']);
		html_start();
		echo('<p class="ok">'.$lang[$lng][56].'</p>');
		html_end();
		break;
	}
	case 'add':{ //add a person to db
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(preg_match('#,personadd,#',$currentuser['flags'])){
				if(isset($_POST['submit'])){
					if(strlen($_POST['imie'])>=2){
						if(strlen($_POST['nazwisko'])>=2){
							if(is_numeric($_POST['ur'])&((($_POST['ur']>999)&($_POST['ur']<=date("Y")))|($_POST['ur']==0))){
								if(is_numeric($_POST['zm'])&(($_POST['zm']==0)|(($_POST['zm']>=$_POST['ur'])&($_POST['zm']<=date("Y"))))){
									$r1=mysql_fetch_assoc(mysqlquerryc('select ur,sex from ludzie where id='.$_POST['rodzic1']));
									$r2=mysql_fetch_assoc(mysqlquerryc('select ur,sex from ludzie where id='.$_POST['rodzic2']));
									if(((($r1['sex']!=$r2['sex'])&($r1['ur']<$_POST['ur'])&($r2['ur']<$_POST['ur']))&($_POST['rodzic1']!=0)&($_POST['rodzic2']!=0))|(($_POST['rodzic1']==0)&($_POST['rodzic2']!=0)&($r2['ur']<$_POST['ur']))|($_POST['rodzic2']==0)|($_POST['ur']==0)){
										if(($_POST['visible']==1)|($_POST['visible']==0)){
											$q='insert into ludzie set imie="'.trim(htmlspecialchars($_POST['imie'])).'", nazwisko="'.htmlspecialchars($_POST['nazwisko']).'", ur='.$_POST['ur'].', zm='.$_POST['zm'].', sex="'.$_POST['sex'].'", pok='.$_POST['pok'].', rodzic1='.$_POST['rodzic1'].', rodzic2='.$_POST['rodzic2'];
											if($_POST['sex']=='m') $q.=', zona1='.$_POST['zona'];
											$q.=', uwagi="'.htmlspecialchars($_POST['uwagi']).'", lastedit="'.$_COOKIE['zal'].'", adres="'.htmlspecialchars($_POST['adres']).'", visible='.htmlspecialchars($_POST['visible']).';';
											if(mysqlquerryc($q)){
												echo('<p class="ok">OK, '.$_POST['imie'].' '.$_POST['nazwisko'].' '.$lang[$lng][174].'!</p>');
												mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Dodano '.htmlspecialchars($_POST['imie']).' '.htmlspecialchars($_POST['nazwisko']).'", time="'.date("Y-m-d H:i:s").'"');
											}
											else echo('<p class="alert">'.$lang[$lng][175].'</p>');
										}
										else echo('<p class="alert">'.$lang[$lng][176].'</p>');
									}
									else echo('<p class="alert">'.$lang[$lng][177].'</p>');
								}
								else echo('<p class="alert">'.$lang[$lng][178].'</p>');
							}
							else echo('<p class="alert">'.$lang[$lng][121].' '.date("Y").'</p>');
						}
						else echo('<p class="alert">'.$lang[$lng][187].'</p>');
					}
					else echo('<p class="alert">'.$lang[$lng][188].'</p>');
				}
			echo('<script>
				function pokolenie(iid){
				var ludzie=new Array();
				var zonaindex=new Array();
				');
			$res=mysqlquerryc('select id,pok from ludzie;');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				echo('ludzie['.$row['id'].']="'.($row['pok']+1).'";');
			}
			$res=mysqlquerryc('select id from ludzie where sex="k" order by id;');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				$row2=mysql_fetch_assoc(mysqlquerryc('select id,zona1 from ludzie where zona1='.$row['id'].' limit 1;'));
				if($row2['zona1']!=0) echo('zonaindex['.$row2['id'].']="'.($i+1).'";');
			}
			$nsurn='';
			if(isset($id2)){
				$tbr1=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.htmlspecialchars($id2).';'));
				$tbr2=$tbr1['zona1'];
				if($tbr1['zona2']!=0) $tbr2=$tbr1['zona2'];
				if($tbr1['zona3']!=0) $tbr2=$tbr1['zona3'];
				$npok=($tbr1['pok']+1);
				$nsurn=$tbr1['nazwisko'];
			}
			echo('document.dodaj.pok.value=ludzie[iid];
				document.getElementById(\'r2\').selectedIndex=zonaindex[iid];
				}</script><form name="dodaj" method="POST" action="'.$thisfile.'?add"><label>'.$lang[$lng][59].':<input class="formfld" type="text" name="imie" maxlength="20" size="20"></label> <label>'.$lang[$lng][60].':<input class="formfld" type="text" name="nazwisko" size="30" maxlength="40" value="'.$nsurn.'"></label><br>
				<label>'.$lang[$lng][116].':<input class="formfld" type="text" name="ur" size="4" value="0" maxlength="4"></label> <label>'.$lang[$lng][117].':<input class="formfld" type="text" name="zm" value="0" size="4" maxlength="4"></label> <label>'.$lang[$lng][118].':</label><label><input class="formfld" type="radio" name="sex" value="m" checked="checked">'.$lang[$lng][214].'</label><label><input class="formfld" type="radio" name="sex" value="k">'.$lang[$lng][215].'</label> <label>'.$lang[$lng][198].':<input type="text" name="adres" class="formfld" size="12"></label><br>
				<label>'.$lang[$lng][79].':<select class="formfld" id="r1" name="rodzic1" onchange="pokolenie(this.options[this.selectedIndex].value)"><option value="0">'.$lang[$lng][113].'</option>');
			$res=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie where sex="m" order by id;');
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
			echo('</select><script type=\'text/javascript\'>$(\'#r1\').select2();</script>
			<select class="formfld" id="r2" name="rodzic2"><option value="0">'.$lang[$lng][113].'</option>');
			$res=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie where sex="k" order by id;');
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
			echo('</select><script type=\'text/javascript\'>$(\'#r2\').select2();</script>
			</label> <label>'.$lang[$lng][114].':<input class="formfld" type="text" id="pok" name="pok" value="');
			if(isset($id2)) echo($npok);
			else echo('0');
			echo('" size="3" title="W papierowych zapiskach:'."\n".'0 - Czarni'."\n".'1 - Fioletowi'."\n".'2 - Niebiescy'."\n".'3 - Zieloni'."\n".'4 - Czerwoni'."\n".'5 - Pomarańczowi"></label> <label>'.$lang[$lng][75].': <select class="formfld" name="zona" id="zona"><option value="0">'.$lang[$lng][115].'</option>');
			if(strlen($id2)>2) $res=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie where sex="k" and imie="'.htmlspecialchars($id2).'" order by id;');
			else $res=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie where sex="k" order by imie,nazwisko;');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				echo('<option value="'.$row['id'].'">');
				for($j=0;$j<$row['pok'];$j+=1) echo('-');
				echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
			}
			echo('</select><script type=\'text/javascript\'>$(\'#zona\').select2();</script>
			<label>'.$lang[$lng][119].'<input type="text" class="formfld" size="2" maxlength="2" name="visible" value="1"></label></label><br><textarea class="formfld" name="uwagi" rows="5" cols="60"></textarea>
			<input class="formbtn" id="dodaj" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)" type="submit" name="submit" value="'.$lang[$lng][173].'"></form>');
		}
		else{
			echo('<p class="alert">'.$lang[$lng][35].'</p>');
			mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba dodania nowego ludzia, mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'"');
		}
	}
	else{
		mysqlquerryc('insert into logs set user="niezalogowany", action="Próba dostępu do Dodaj, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
		echo('<p class="alert">'.$lang[$lng][179].'<a href="'.$thisfile.'?login">'.$lang[$lng][180].'</a></p>');
	}
		html_end();
		break;
	}
	case 'edit':{ //edit all, do not use / emergency use
		html_start();
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))){
			$edit_ipp=$settings['edit_pp'];
			$colspan='12';
			$actp_s='<font color="red">';
			$actp_e='</font>';
			if((isset($id2))&($id2>0)&(strlen($id2)>0)) $str=$id2;
			else $str=1;
			$pcount=mysql_fetch_array(mysqlquerryc('select count(*) from ludzie;'));
			$nop=floor($pcount[0]/$edit_ipp)+1;
			if(isset($_POST['edit'])){
				if(preg_match('#,personedit,#',$currentuser['flags'])){
					if(strlen($_POST['imie'])>=2){
						if(strlen($_POST['nazwisko'])>=2){
							if(is_numeric($_POST['ur'])&((($_POST['ur']>999)&($_POST['ur']<=date("Y")))|($_POST['ur']==0))){
								if(is_numeric($_POST['zm'])&(($_POST['zm']==0)|(($_POST['zm']>=$_POST['ur'])&($_POST['zm']<=date("Y"))))){
									$r1=mysql_fetch_assoc(mysqlquerryc('select ur,sex from ludzie where id='.$_POST['rodzic1']));
									$r2=mysql_fetch_assoc(mysqlquerryc('select ur,sex from ludzie where id='.$_POST['rodzic2']));
									if(((($r1['sex']!=$r2['sex'])&($r1['ur']<$_POST['ur'])&($r2['ur']<$_POST['ur']))&($_POST['rodzic1']!=0)&($_POST['rodzic2']!=0))|(($_POST['rodzic1']==0)&($_POST['rodzic2']!=0)&($r2['ur']<$_POST['ur']))|($_POST['rodzic2']==0)|($_POST['ur']==0)){
										$q='update ludzie set imie="'.htmlspecialchars($_POST['imie']).'", nazwisko="'.htmlspecialchars($_POST['nazwisko']).'", ur='.$_POST['ur'].', zm='.$_POST['zm'].', sex="'.$_POST['sex'].'", pok='.$_POST['pok'].', rodzic1='.$_POST['rodzic1'].', rodzic2='.$_POST['rodzic2'].', adres="'.htmlspecialchars($_POST['adres']).'", uwagi="'.htmlspecialchars($_POST['uwagi']).'"';
										if($_POST['sex']=='m') $q.=', zona1='.$_POST['zona1'].', zona2='.$_POST['zona2'].', zona3='.$_POST['zona3'];
										$q.=' where id='.$_POST['id'].';';
										mysqlquerryc($q);
										echo('<p class="ok">OK, '.$_POST['imie'].' '.$_POST['nazwisko'].' '.$lang[$lng][120].'!</p>');
										mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Zmieniono '.htmlspecialchars($_POST['imie']).' '.htmls($_POST['nazwisko']).'", time="'.date("Y-m-d H:i:s").'"');
									}
									else echo('<p class="alert">'.$lang[$lng][177].'</p>');
								}
								else echo('<p class="alert">'.$lang[$lng][178].'</p>');
							}
							else echo('<p class="alert">'.$lang[$lng][121].' '.date("Y").'</p>');
						}
						else echo('<p class="alert">'.$lang[$lng][187].'</p>');
					}
					else echo('<p class="alert">'.$lang[$lng][188].'</p>');	
				}
				else{
					echo('<p class="alert">'.$lang[$lng][39].'</p>');
					mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba edycji '.htmlspecialchars($_POST['imie']).' '.htmlspecialchars($_POST['nazwisko']).', mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'"');
				}
			}
			if(isset($_POST['del'])){
				if(preg_match('#,persondel,#',$currentuser['flags'])){
					$res=mysqlquerryc('select * from ludzie where id='.htmlspecialchars($_POST['id']).';');
					if(mysql_num_rows($res)==1){
						$row=mysql_fetch_assoc($res);
						if(mysqlquerryc('delete from ludzie where id='.htmlspecialchars($_POST['id']).';')){
							mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Usunięto '.$row['imie'].' '.$row['nazwisko'].'", time="'.date("Y-m-d H:i:s").'"');
							echo('<p class="ok">'.$row['imie'].' '.$row['nazwisko'].' '.$lang[$lng][200].'</p>');
						}
						else echo('<p class="alert">'.$lang[$lng][189].'</p>');
					}
					else echo('<p class="alert">'.$lang[$lng][154].'</p>');
				}
				else{
					echo('<p class="alert">'.$lang[$lng][37].'</p>');
					mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba usunięcia '.$row['imie'].' '.$row['nazwisko'].', mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'"');
				}
			}
			echo('<table border="1"><tr><th colspan="'.$colspan.'">'.$lang[$lng][199].'</th></tr><tr><th colspan="'.$colspan.'">');
			if($str>1) echo('<a href="'.$thisfile.'?edit,'.($str-1).'">&lt;&lt;'.$lang[$lng][164].'</a> | ');
			if($nop<20){
				for($i=1;$i<=$nop;$i+=1){
					echo('<a href="'.$thisfile.'?edit,'.$i.'">');
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
						echo('<a href="'.$thisfile.'?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($nop-3);$i<=$nop;$i+=1){
						echo('<a href="'.$thisfile.'?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
				}
				else if($str>($nop-6)){
					for($i=1;$i<=3;$i+=1){
						echo('<a href="'.$thisfile.'?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($nop-15);$i<=$nop;$i+=1){
						echo('<a href="'.$thisfile.'?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
				}
				else{
					for($i=1;$i<=3;$i+=1){
						echo('<a href="'.$thisfile.'?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($str-5);$i<=($str+5);$i+=1){
						echo('<a href="'.$thisfile.'?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($nop-3);$i<=$nop;$i+=1){
						echo('<a href="'.$thisfile.'?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
				}
			}
			if($str<$nop) echo(' | <a href="'.$thisfile.'?edit,'.($str+1).'">'.$lang[$lng][165].'&gt;&gt;</a>');
			echo('</th></tr><tr><td>id</td><td>'.$lang[$lng][59].'</td><td>'.$lang[$lng][60].'</td><td>ur</td><td>zm</td><td>rodzice</td><td>zony</td><td>pł</td><td>pok</td><td>adres</td><td>uwagi</td><td>akcje</td></tr>');
			$res=mysqlquerryc('select * from ludzie order by id limit '.(($str-1)*$edit_ipp).','.$edit_ipp.';');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				echo('<form name="f'.$row['id'].'" method="POST" action="'.$thisfile.'?edit,'.$str.'#n'.$row['id'].'"><input type="hidden" name="id" value="'.$row['id'].'"><tr');
				if($row['id']==$_POST['id']) echo(' class="zazn"');
				echo('><td>'.$row['id'].'<a name="n'.$row['id'].'"></a></td><td><input class="formfld" type="text" name="imie" value="'.$row['imie'].'" maxlength="20" size="15"></td>
				<td><input class="formfld" type="text" name="nazwisko" value="'.$row['nazwisko'].'" maxlength="40" size="20"></td>
				<td><input class="formfld" type="text" name="ur" size="4" maxlength="4" value="'.$row['ur'].'"></td>
				<td><input class="formfld" type="text" name="zm" size="4" maxlength="4" value="'.$row['zm'].'"></td>
				<td><select class="formfld" name="rodzic1"><option value="0">'.$lang[$lng][113].'</option>');
			$res2=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie where sex="m";');
			for($j=0;$j<mysql_num_rows($res2);$j+=1){
				$row2=mysql_fetch_assoc($res2);
				echo('<option value="'.$row2['id'].'"');
				if($row2['id']==$row['rodzic1']) echo(' selected="selected"');
				echo('>');
				for($ji=0;$ji<$row2['pok'];$ji+=1) echo('-');
				echo($row2['imie'].' '.$row2['nazwisko'].' ('.$row2['ur'].')</option>');
			}
			echo('</select><select class="formfld" name="rodzic2"><option value="0">'.$lang[$lng][113].'</option>');
			$res2=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie where sex="k";');
			for($j=0;$j<mysql_num_rows($res2);$j+=1){
				$row2=mysql_fetch_assoc($res2);
				echo('<option value="'.$row2['id'].'"');
				if($row2['id']==$row['rodzic2']) echo(' selected="selected"');
				echo('>');
				for($ji=0;$ji<$row2['pok'];$ji+=1) echo('-');
				echo($row2['imie'].' '.$row2['nazwisko'].' ('.$row2['ur'].')</option>');
			}
			echo('</select></td><td><select class="formfld" name="zona1"><option value="0">Brak</option>');
			$res2=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie where sex="k";');
			for($j=0;$j<mysql_num_rows($res2);$j+=1){
				$row2=mysql_fetch_assoc($res2);
				echo('<option value="'.$row2['id'].'"');
				if($row2['id']==$row['zona1']) echo(' selected="selected"');
				echo('>');
				for($ji=0;$ji<$row2['pok'];$ji+=1) echo('-');
				echo($row2['imie'].' '.$row2['nazwisko'].' ('.$row2['ur'].')</option>');
			}
			echo('</select><select class="formfld" name="zona2"><option value="0">Brak</option>');
			$res2=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie;');
			for($j=0;$j<mysql_num_rows($res2);$j+=1){
				$row2=mysql_fetch_assoc($res2);
				echo('<option value="'.$row2['id'].'"');
				if($row2['id']==$row['zona2']) echo(' selected="selected"');
				echo('>');
				for($ji=0;$ji<$row2['pok'];$ji+=1) echo('-');
				echo($row2['imie'].' '.$row2['nazwisko'].' ('.$row2['ur'].')</option>');
			}
			echo('</select><select class="formfld" name="zona3"><option value="0">Brak</option>');
			$res2=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie;');
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
			if($str>1) echo('<a href="'.$thisfile.'?edit,'.($str-1).'">&lt;&lt;'.$lang[$lng][164].'</a> | ');
			if($nop<20){
				for($i=1;$i<=$nop;$i+=1){
					echo('<a href="'.$thisfile.'?edit,'.$i.'">');
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
						echo('<a href="'.$thisfile.'?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($nop-3);$i<=$nop;$i+=1){
						echo('<a href="'.$thisfile.'?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
				}
				else if($str>($nop-6)){
					for($i=1;$i<=3;$i+=1){
						echo('<a href="'.$thisfile.'?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($nop-15);$i<=$nop;$i+=1){
						echo('<a href="'.$thisfile.'?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
				}
				else{
					for($i=1;$i<=3;$i+=1){
						echo('<a href="'.$thisfile.'?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($str-5);$i<=($str+5);$i+=1){
						echo('<a href="'.$thisfile.'?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
					echo('... | ');
					for($i=($nop-3);$i<=$nop;$i+=1){
						echo('<a href="'.$thisfile.'?edit,'.$i.'">');
						if($i==$str) echo($actp_s);
						echo($i);
						if($i==$str) echo($actp_e);
						echo('</a>');
						if($i!=$nop) echo(' | ');
					}
				}
			}
			if($str<$nop) echo(' | <a href="'.$thisfile.'?edit,'.($str+1).'">'.$lang[$lng][165].'&gt;&gt;</a>');
			echo('</th></tr></table>');
		}
		else{
			mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba dostępu do Edytuj, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">Nie masz dostępu do tej strony</a></p>');
		}
		html_end();
		break;
	}
	case 'edit1':{
		html_start();
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))){
		$logs_in=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko from ludzie where id='.htmlspecialchars($id2).' limit 1;'));
			echo('<a href="'.$thisfile.'?pokaz,one,'.$id2.'">'.$lang[$lng][190].'</a>');
			if(isset($_POST['zdjdod'])){
				if(preg_match('#,picadd,#',$currentuser['flags'])){
					if(is_uploaded_file($_FILES['zdj']['tmp_name'])){
						$newname='zdj'.date('U');
						if($_FILES['zdj']['size']<=$_POST['MAX_FILE_SIZE']){	 
							move_uploaded_file($_FILES['zdj']['tmp_name'], 'gfx/'.$newname.'.jpg');
							mysqlquerryc('insert into zdjecia set path="gfx/'.$newname.'.jpg", osoby="'.htmlspecialchars($id2).'", rok='.htmlspecialchars($_POST['rok']).';');
							$im=imagecreatefromjpeg('gfx/'.$newname.'.jpg');
							$imsize=getimagesize('gfx/'.$newname.'.jpg');
							$nih=($imsize[1]*200)/$imsize[0];
							$nim=imagecreatetruecolor(200,$nih);
							imagecopyresampled($nim,$im,0,0,0,0,200,$nih,$imsize[0],$imsize[1]);
							imagejpeg($nim,'gfx/'.$newname.'.jpg');
							echo('<p class="ok">'.$lang[$lng][191].' '.$_FILES['zdj']['name'].' '.$lang[$lng][192].'</p>');
							mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Dodano zdjęcie '.$logs_in['imie'].' '.$logs_in['nazwisko'].'", time="'.date("Y-m-d H:i:s").'";');
						}
						else{
							echo($lang[$lng][191].': <strong>'.$_FILES['zdj']['name'].'</strong> '.$lang[$lng][193].' '.($_POST['MAX_FILE_SIZE']/1024/1024).' MB<br>');
							mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba dodania zbyt dużego zdjęcia  '.$logs_in['imie'].' '.$logs_in['nazwisko'].'", time="'.date("Y-m-d H:i:s").'";');
						}	
					}
					else echo('<p class="alert">'.$lang[$lng][201].'</p>');
				}
				else{
					mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba dodania zdjęcia '.$logs_in['imie'].' '.$logs_in['nazwisko'].' mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
					echo('<p class="alert">'.$lang[$lng][41].'</p>');
				}
			}
			if(isset($_POST['del'])){
				if(preg_match('#,persondel,#',$currentuser['flags'])){
					echo('<p class="alert">Napewno usunąć '.$_POST['imie'].' '.$_POST['nazwisko'].' [id:'.$_POST['id'].'] ?</p>
					<form name="reallydel" action="'.$thisfile.'?edit1" method="POST"><input type="hidden" name="del_id" value="'.$_POST['id'].'">
					<input type="submit" name="del_really_yes" id="del2" value="TAK, NAPEWNO USUNĄĆ '.$_POST['imie'].' '.$_POST['nazwisko'].' !!!" class="formbtn" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></form>
					<br><a href="'.$thisfile.'?edit1,'.$_POST['id'].'">Nie usuwać</a>');
				}
			}
			if(isset($_POST['del_really_yes'])){
				if(preg_match('#,persondel,#',$currentuser['flags'])){
					if(mysqlquerryc('delete from ludzie where id='.htmlspecialchars($_POST['del_id']).';')){
						echo('<p class="ok">Poprawnie usunięto!</p>');
						mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Usunięcie '.htmlspecialchars($_POST['del_id']).'", time="'.date('Y-m-d H:i:s').'";');
					}
				}
			}
			if(isset($_POST['submit'])){
				if(preg_match('#,personedit,#',$currentuser['flags'])){
					if(strlen($_POST['imie'])>=2){
						if(strlen($_POST['nazwisko'])>=2){
							if(is_numeric($_POST['ur'])&((($_POST['ur']>999)&($_POST['ur']<=date("Y")))|($_POST['ur']==0))){
								if(is_numeric($_POST['zm'])&(($_POST['zm']==0)|(($_POST['zm']>=$_POST['ur'])&($_POST['zm']<=date("Y"))))){
									$r1=mysql_fetch_assoc(mysqlquerryc('select ur,sex from ludzie where id='.$_POST['rodzic1']));
									$r2=mysql_fetch_assoc(mysqlquerryc('select ur,sex from ludzie where id='.$_POST['rodzic2']));
									if(((($r1['sex']!=$r2['sex'])&($r1['ur']<$_POST['ur'])&($r2['ur']<$_POST['ur']))&($_POST['rodzic1']!=0)&($_POST['rodzic2']!=0))|(($_POST['rodzic1']==0)&($_POST['rodzic2']!=0)&($r2['ur']<$_POST['ur']))|($_POST['rodzic2']==0)|($_POST['ur']==0)){
										$rc1=mysql_fetch_assoc(mysqlquerryc('select ur,sex from ludzie where id='.$_POST['rodzicch1']));
										$rc2=mysql_fetch_assoc(mysqlquerryc('select ur,sex from ludzie where id='.$_POST['rodzicch2']));
										if(((($rc1['sex']!=$rc2['sex'])&($rc1['ur']<$_POST['ur'])&($rc2['ur']<$_POST['ur']))&($_POST['rodzic1']!=0)&($_POST['rodzicch2']!=0))|(($_POST['rodzicch1']==0)&($_POST['rodzicch2']!=0)&($rc2['ur']<$_POST['ur']))|($_POST['rodzicch2']==0)|($_POST['ur']==0)){
											if(($_POST['visible']==0)|($_POST['visible']==1)){
												$q='update ludzie set imie="'.htmlspecialchars($_POST['imie']).'", nazwisko="'.htmlspecialchars($_POST['nazwisko']).'", ur='.$_POST['ur'].', zm='.$_POST['zm'].', sex="'.$_POST['sex'].'", pok='.$_POST['pok'].', rodzic1='.$_POST['rodzic1'].', rodzic2='.$_POST['rodzic2'].', rch1='.$_POST['rodzicch1'].', rch2='.$_POST['rodzicch2'].', adres="'.htmlspecialchars($_POST['adres']).'", uwagi="'.htmlspecialchars($_POST['uwagi']).'", lastedit="'.$_COOKIE['zal'].'", rnazw="'.$_POST['rnazw'].'", z1s="'.$_POST['z1s'].'", z2s="'.$_POST['z2s'].'", z3s="'.$_POST['z3s'].'", visible='.$_POST['visible'];
												if($_POST['sex']=='m') $q.=', zona1='.$_POST['zona1'].', zona2='.$_POST['zona2'].', zona3='.$_POST['zona3'];
												$q.=' where id='.htmlspecialchars($id2).';';
												if(mysqlquerryc($q)){
													echo('<p class="ok">OK, '.$_POST['imie'].' '.$_POST['nazwisko'].' zmienione!</p>');
													mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Edycja '.htmlspecialchars($_POST['imie']).' '.htmlspecialchars($_POST['nazwisko']).'", time="'.date("Y-m-d H:i:s").'";');
												}
												else echo('<p class="alert">'.$lang[$lng][223].'</p>');
											}
											else echo('<p class="alert">'.$lang[$lng][176].'</p>');
										}
										else echo('<p class="alert">'.$lang[$lng][224].'</p>');
									}
									else echo('<p class="alert">'.$lang[$lng][177].'</p>');
								}
								else echo('<p class="alert">'.$lang[$lng][178].'</p>');
							}
							else echo('<p class="alert">'.$lang[$lng][121].' '.date("Y").'</p>');
						}
						else echo('<p class="alert">'.$lang[$lng][228].'</p>');
					}
					else echo('<p class="alert">'.$lang[$lng][229].'</p>');	
				}
				else{
					mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba edycji '.$logs_in['imie'].' '.$logs_in['nazwisko'].' mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
					echo('<p class="alert">Nie masz uprawnień do edytowania ludzi</p>');
				}
			}
			$theone=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.htmlspecialchars($id2).';'));
			if($theone){
				echo('<table width="100%" border="0"><tr><td width="50%" align="right">');
				$zdjecia=mysqlquerryc('select * from zdjecia where osoby="'.$theone['id'].'";');
				for($z=0;$z<mysql_num_rows($zdjecia);$z+=1){
					$zdjeciar=mysql_fetch_assoc($zdjecia);
					echo('<p>'.$zdjeciar['path'].'</p>');
				}
				echo('<form enctype="multipart/form-data" action="'.$thisfile.'?edit1,'.$id2.'" method="POST">
				<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
				<table border="0"><tr><td>Dodaj zdjęcie:</td><td>Rok</td><td>&nbsp;</td></tr><tr><td><input class="formfld" name="zdj" type="file" /></td><td><input type="text" name="rok" class="formfld" size="4" maxlength="4"></td>
				<td><input class="formbtn" id="zdjdod" name="zdjdod" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)" type="submit" value="Wyślij" /></td></tr></table></form>
				</td><td width="50%">');
				echo('<form name="edit1" method="POST" action="'.$thisfile.'?edit1,'.$id2.'">');
				echo('<label>'.$lang[$lng][59].':<input type="text" name="imie" value="'.$theone['imie'].'" class="formfld" maxlength="20" size="20"></label> <label>'.$lang[$lng][60].':<input type="text" name="nazwisko" value="'.$theone['nazwisko'].'" class="formfld" maxlength="40"></label><label title="wpisać tylko jeżeli inne niż nazwisko ojca lub inne niż nazwisko po mężu"><br>prawdziwe nazwisko: <input name="rnazw" type="text" size="20" name="rnazw" class="formfld" value="'.$theone['rnazw'].'"></label><br>');
				echo('<label>ur:<input type="text" name="ur" value="'.$theone['ur'].'" size="4" maxlength="4" class="formfld"></label> <label>zm:<input type="text" name="zm" value="'.$theone['zm'].'" size="4" maxlength="4" class="formfld"</label> <label>płeć:<input type="text" name="sex" value="'.$theone['sex'].'" class="formfld" size="1" maxlength="1"></label> <label>pokolenie: <input type="text" class="formfld" name="pok" value="'.$theone['pok'].'" size="2" maxlength="2"></label><label>widoczniść:<input type="text" class="formfld" size="2" maxlength="2" name="visible" value="'.$theone['visible'].'"></label><br>');
				echo('<label>adres:<input type="text" name="adres" value="'.$theone['adres'].'" class="formfld"></label>');
				echo('</td></tr><tr><td>');
				$res=mysqlquerryc('select * from ludzie where rodzic1='.$theone['id'].' or rodzic2='.$theone['id'].' order by ur;');
				if(mysql_num_rows($res)>0){
					echo('<h3>Dzieci ('.mysql_num_rows($res).'):</h3>');
					for($i=0;$i<mysql_num_rows($res);$i+=1){
						$row=mysql_fetch_assoc($res);
						echo('<p><a href="'.$thisfile.'?pokaz,one,'.$row['id'].'">'.$row['imie'].' '.$row['nazwisko'].'</a> ('.$row['ur'].')</p>');
					}
				}
				echo('</td><td>');
				echo('<h3>'.$lang[$lng][75].':</h3>');
				echo('<select class="formfld" id="z1" name="zona1"><option value="0">'.$lang[$lng][113].'</option>');
				$res=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie where sex="k" order by nazwisko,imie;');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<option value="'.$row['id'].'"');
					if($row['id']==$theone['zona1']) echo(' selected="selected"');
					echo('>');
					for($j=0;$j<$row['pok'];$j+=1) echo('-');
					echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
				}
				echo('</select><input type="text" name="z1s" size="10" maxlength="10" class="formfld" value="'.$theone['z1s'].'"><br><select class="formfld" id="z2" name="zona2"><option value="0">Nieznany</option>');
				$res=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie where sex="k" order by nazwisko,imie;');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<option value="'.$row['id'].'"');
					if($row['id']==$theone['zona2']) echo(' selected="selected"');
					echo('>');
					for($j=0;$j<$row['pok'];$j+=1) echo('-');
					echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
				}
				echo('</select><input type="text" name="z2s" size="10" maxlength="10" class="formfld" value="'.$theone['z2s'].'"><br><select class="formfld" id="z3" name="zona3"><option value="0">Nieznany</option>');
				$res=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie where sex="k" order by nazwisko,imie;');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<option value="'.$row['id'].'"');
					if($row['id']==$theone['zona3']) echo(' selected="selected"');
					echo('>');
					for($j=0;$j<$row['pok'];$j+=1) echo('-');
					echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
				}
				echo('</select><input type="text" name="z3s" size="10" maxlength="10" class="formfld" value="'.$theone['z3s'].'"><br><h3>Rodzice:</h3>');
				echo('<select class="formfld" id="r1" name="rodzic1"><option value="0">Nieznany</option>');
				$res=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie where sex="m" order by nazwisko,imie;');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<option value="'.$row['id'].'"');
					if($row['id']==$theone['rodzic1']) echo(' selected="selected"');
					echo('>');
					for($j=0;$j<$row['pok'];$j+=1) echo('-');
					echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
				}
				echo('</select><br><select class="formfld" id="r2" name="rodzic2"><option value="0">Nieznany</option>');
				$res=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie where sex="k" order by nazwisko,imie;');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<option value="'.$row['id'].'"');
					if($row['id']==$theone['rodzic2']) echo(' selected="selected"');
					echo('>');
					for($j=0;$j<$row['pok'];$j+=1) echo('-');
					echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
				}
				echo('</select>');
				
				echo('<h3>Chrzestni:</h3>');
				echo('<select class="formfld" id="rch1" name="rodzicch1"><option value="0">Nieznany</option>');
				$res=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie where sex="m" order by id;');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<option value="'.$row['id'].'"');
					if($row['id']==$theone['rch1']) echo(' selected="selected"');
					echo('>');
					for($j=0;$j<$row['pok'];$j+=1) echo('-');
					echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
				}
				echo('</select><br><select class="formfld" id="rch2" name="rodzicch2"><option value="0">Nieznany</option>');
				$res=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie where sex="k" order by id;');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<option value="'.$row['id'].'"');
					if($row['id']==$theone['rch2']) echo(' selected="selected"');
					echo('>');
					for($j=0;$j<$row['pok'];$j+=1) echo('-');
					echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
				}
				echo('</select>');
				
				echo('</td></tr><tr><td colspan="2" align="center">');
				echo('<textarea name="uwagi" rows="5" cols="80" class="formfld">'.$theone['uwagi'].'</textarea>');
				echo('</td></tr></table><input type="submit" name="submit" value="Zapisz" class="formbtn" id="edit1btn" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"><input type="submit" name="del" value="USUŃ" class="formbtn" id="del1btn" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></form>');
			}
		}
		else{
			mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba dostępu do edycji '.$logs_in['imie'].' '.$logs_in['nazwisko'].', z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class"alert">'.$lang[$lng][89].'</a></p>');
		}
		html_end();
		break;
	}
	case 'search':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(strlen($id2)>2) $_POST['q1']=$id2;
			if(strlen($id3)>2) $_POST['q2']=$id3;
			echo('<p><b>'.$lang[$lng][57].'</b><form name="search" method="POST" action="'.$thisfile.'?search"><center><table border="0"><tr><td>'.$lang[$lng][59].'</td><td>'.$lang[$lng][60].'</td><td>&nbsp;</td></tr><tr><td><input class="formfld" type="text" name="q1" value="'.$_POST['q1'].'"></td><td><input class="formfld" type="text" name="q2" value="'.$_POST['q2'].'"></td><td><input class="formbtn" id="szukaj" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)" type="submit" name="submit" value="'.$lang[$lng][3].'"></td></tr><tr><td align="center" colspan="2"><label><input class="formfld" type="checkbox" name="exact" value="1"');
			if(isset($_POST['exact'])) echo(' checked="checked"');
			echo('>'.$lang[$lng][58].'</label></td><td>&nbsp;</td></tr></table></center></form></p><br>');
			
			if(isset($_POST['q1'])|isset($_POST['q2'])){
				if(isset($_POST['exact'])){
					if((strlen($_POST['q1'])>0)&(strlen($_POST['q2'])>0)){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $res=mysqlquerryc('select id from ludzie where imie="'.htmlspecialchars(plfirstup($_POST['q1'])).'" and nazwisko="'.htmlspecialchars(plfirstup($_POST['q2'])).'" order by imie,nazwisko;');
						else $res=mysqlquerryc('select id from ludzie where visible=1 and imie="'.htmlspecialchars(plfirstup($_POST['q1'])).'" and nazwisko="'.htmlspecialchars(plfirstup($_POST['q2'])).'" order by imie,nazwisko;');			
					}
					else if(strlen($_POST['q1'])>0){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $res=mysqlquerryc('select id from ludzie where imie="'.htmlspecialchars(plfirstup($_POST['q1'])).'" order by imie,nazwisko;');
						else $res=mysqlquerryc('select id from ludzie where visible=1 and imie="'.htmlspecialchars(plfirstup($_POST['q1'])).'" order by imie,nazwisko;');
					}
					else if(strlen($_POST['q2'])>0){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $res=mysqlquerryc('select id from ludzie where nazwisko="'.htmlspecialchars(plfirstup($_POST['q2'])).'" order by imie,nazwisko;');
						else $res=mysqlquerryc('select id from ludzie where visible=1 and nazwisko="'.htmlspecialchars(plfirstup($_POST['q2'])).'" order by imie,nazwisko;');
					}
				}
				else{
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $res=mysqlquerryc('select id from ludzie where imie like "%'.htmlspecialchars(plfirstup($_POST['q1'])).'%" and nazwisko like "%'.htmlspecialchars(plfirstup($_POST['q2'])).'%" order by imie,nazwisko;');
					else $res=mysqlquerryc('select id from ludzie where visible=1 and (imie like "%'.htmlspecialchars(plfirstup($_POST['q1'])).'%" and nazwisko like "%'.htmlspecialchars(plfirstup($_POST['q2'])).'%") order by imie,nazwisko;');
				}
				echo('<h2>'.$lang[$lng][61].' '.mysql_num_rows($res).' '.$lang[$lng][62]);
				if(isset($_COOKIE['zal'])&checkname()) mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Szukanie '.htmlspecialchars($_POST['q1']).' '.htmlspecialchars($_POST['q2']).'", time="'.date("Y-m-d H:i:s").'";');
				else mysqlquerryc('insert into logs set user="niezalogowany", action="Szukanie <a href="'.$thisfile.'?search,'.$_POST['q1'].','.$_POST['q2'].'">'.htmlspecialchars($_POST['q1']).' '.htmlspecialchars($_POST['q2']).'</a>, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
				if($lng=='pl'){
					if(mysql_num_rows($res)==1) echo('obę');
					else if(((substr(mysql_num_rows($res),-1,1)=='2')|(substr(mysql_num_rows($res),-1,1)=='3')|(substr(mysql_num_rows($res),-1,1)=='4'))&(substr(mysql_num_rows($res),-2,1)!='1')) echo ('oby');
					else echo('ób');
				}
				echo('</h2>');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<p>'.linkujludzia($row['id'],3).'</p>');
				}
				if(mysql_num_rows($res)==0){
					echo('<h3>'.$lang[$lng][63].', <a href="'.$thisfile.'?kontakt">'.$lang[$lng][64].'</a></h3>');
				}
			}
		}
		html_end();
		break;
	}
	case 'stats':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Wyświetlenie ciekowostek, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'"');
			echo('<h3>'.$lang[$lng][92].':</h3>'); //length of life
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $maxl=mysqlquerryc('select id,imie,nazwisko,zm,ur,(zm-ur) as wiek from ludzie where ur>0 and zm>0 order by wiek desc,ur asc limit 5;');
			else $maxl=mysqlquerryc('select id,imie,nazwisko,zm,ur,(zm-ur) as wiek from ludzie where visible=1 and ur>0 and zm>0 order by wiek desc,ur asc limit 5;');
			for($i=0;$i<mysql_num_rows($maxl);$i+=1){
				$maxlength=mysql_fetch_assoc($maxl);
				echo('<p><a href="'.$thisfile.'?pokaz,one,'.$maxlength['id'].'">'.$maxlength['imie'].' '.$maxlength['nazwisko'].'</a> ('.$maxlength['ur'].'-'.$maxlength['zm'].') - '.$maxlength['wiek'].' '.$lang[$lng][94].'</p>');
			}
			echo('<h3>'.$lang[$lng][93].':</h3>'); //most children
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $res=mysqlquerryc('select distinct(rodzic1) as ro1 from ludzie where rodzic1!=0;');
			else $res=mysqlquerryc('select distinct(rodzic1) as ro1 from ludzie where visible=1 and rodzic1!=0;');
			$max=0;
			$mid=0;
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				$ldzieci=mysql_fetch_assoc(mysqlquerryc('select count(*) as ile from ludzie where rodzic1='.$row['ro1'].';'));
				if($ldzieci['ile']>$max){
					$max=$ldzieci['ile'];
					$mid=$row['ro1'];
				}
			}
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $zona=mysql_fetch_assoc(mysqlquerryc('select zona1 from ludzie where id='.$mid.';'));
			else $zona=mysql_fetch_assoc(mysqlquerryc('select zona1 from ludzie where visible=1 and id='.$mid.';'));
			echo('<p>'.linkujludzia($mid,2).' i '.linkujludzia($zona['zona1'],2).' - '.$max.' '.strtolower($lang[$lng][76]).'</p>');
			echo('<h3>'.$lang[$lng][95].':</h3>'); //most grand children
			$maxwn=0;
			$maxwn_id=0;
			$chi1=mysqlquerryc('select id from ludzie where ur<'.(date('Y')-15).';'); //older than 15
			for($i=0;$i<mysql_num_rows($chi1);$i+=1){
				$r1=mysql_fetch_assoc($chi1);
				$actwn=ilupot($r1['id'],2);
				if($actwn>$maxwn){
					$maxwn_id=$r1['id'];
					$maxwn=$actwn;
				}
			}
			echo(linkujludzia($maxwn_id,2).' - '.$maxwn.' '.strtolower($lang[$lng][77]));
			echo('<h3>'.$lang[$lng][96].':</h3>'); //most frequent name
			$imm=mysqlquerryc('select distinct(imie) as im from ludzie where sex="m" and imie!="???";');
			$imk=mysqlquerryc('select distinct(imie) as im from ludzie where sex="k" and imie!="???";');
			for($i=0;$i<mysql_num_rows($imm);$i+=1){
				$row=mysql_fetch_assoc($imm);
				$li=mysql_fetch_assoc(mysqlquerryc('select count(*) as lim from ludzie where imie="'.$row['im'].'";'));
				$imiona[$row['im']]=$li['lim'];
			}
			for($i=0;$i<mysql_num_rows($imk);$i+=1){
				$row=mysql_fetch_assoc($imk);
				$li=mysql_fetch_assoc(mysqlquerryc('select count(*) as lim from ludzie where imie="'.$row['im'].'";'));
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
			echo('<p>'.$lang[$lng][97].': <a href="'.$thisfile.'?search,'.$immax.'">'.$immax.'</a> ('.$immaxc.')</p>');
			$kimmax='';
			$kimmaxc=0;
			foreach($imionak as $k => $v){
				if($v>$kimmaxc){
					$kimmaxc=$v;
					$kimmax=$k;
				}
			}
			echo('<p>'.$lang[$lng][98].': <a href="'.$thisfile.'?search,'.$kimmax.'">'.$kimmax.'</a> ('.$kimmaxc.')</p>');
		}
		//life expectancy normal distribution
		echo('<h3>'.$lang[$lng][99].'</h3>');
		$q1=mysqlquerryc('select ur,zm from ludzie where ur>0 and zm>0;'); //known date of birth and death
		$fnam='norm/normaldist'.mysql_num_rows($q1).'.png';
		echo('<p>'.$lang[$lng][100].' '.mysql_num_rows($q1).' '.$lang[$lng][101].'</p>');
		if(!file_exists($fnam)){  //use existing file if number of people didnt change
			$a1=Array();
			for($dz=0;$dz<=100;$dz+=1) $a1[$dz]=0;
			for($i1=0;$i1<mysql_num_rows($q1);$i1+=1){
				$r1=mysql_fetch_assoc($q1);
				$latc=($r1['zm']-$r1['ur']);
				$a1[$latc]=$a1[$latc]+1;
			}
			ksort($a1);
			$colwidth=15;
			$imgw=50+(100*$colwidth);
			$imgh=250;
			$img=imagecreatetruecolor($imgw,$imgh);
			$black=imagecolorallocate($img,0,0,0);
			$white=imagecolorallocate($img,255,255,255);
			$blue=imagecolorallocate($img,20,20,240);
			imagefilledrectangle($img,0,0,$imgw,$imgh,$black); // whole image black
			imageline($img,50,0,50,($imgh-20),$white); //vertical line
			imageline($img,50,($imgh-20),$imgw,($imgh-20),$white); //horizontal line
			imagestring($img,2,10,5,max($a1),$white); //max number on Y axis
			imagestring($img,2,10,((($imgh-50)/2)+5),round(max($a1)/2,0),$white); //half number on Y axis
			foreach($a1 as $k => $v){
				imagestring($img,1,(50+($k*$colwidth)+($colwidth/2)),($imgh-19),$k,$white); //number on X axis
				imagefilledrectangle($img,(51+($k*$colwidth)),($imgh-21),(49+(($k+1)*$colwidth)),(($imgh-20)-(($v/max($a1))*($imgh-20))-1),$blue); //actual number of people
			}
			imagepng($img,$fnam);
		}
		echo('<img src="'.$fnam.'">');
		html_end();
		break;
	}
	case 'todo':{ // auto-generated data completion check
		html_start();
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))){
			print_r($currentuser);
			echo('<h3>'.$lang[$lng][102].'</h3>');
			$res=mysqlquerryc('select id from ludzie where imie="???" or nazwisko="???";');
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
			echo('<h3>'.$lang[$lng][103].'</h3>');
			$res=mysqlquerryc('select id,ur from ludzie where ur<'.(date('Y')-95).' and ur!=0 and zm=0 order by ur asc;');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				echo('<p>'.linkujludzia($row['id'],1).' (ma '.(date('Y')-$row['ur']).' lat)</p>');
			}
			$res=mysqlquerryc('select id,pok,zona1,zona2,zona3 from ludzie where sex="m";');
			if(mysql_num_rows($res)>0) echo('<h3>'.$lang[$lng][104].'</h3>');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				if($row['zona1']!=0){
					$z1=mysql_fetch_assoc(mysqlquerryc('select pok from ludzie where id='.$row['zona1'].' limit 1;'));
					if($row['pok']!=$z1['pok']) echo('<p>'.linkujludzia($row['id'],2).' jest z pokolenia '.$row['pok'].', a jego żona '.linkujludzia($row['zona1'],2).' jest z pokolenia '.$z1['pok'].'</p>');
				}
				if($row['zona2']!=0){
					$z2=mysql_fetch_assoc(mysqlquerryc('select pok from ludzie where id='.$row['zona2'].' limit 1;'));
					if($row['pok']!=$z2['pok']) echo('<p>'.linkujludzia($row['id'],2).' jest z pokolenia '.$row['pok'].', a jego żona '.linkujludzia($row['zona1'],2).' jest z pokolenia '.$z2['pok'].'</p>');
				}
				if($row['zona3']!=0){
					$z3=mysql_fetch_assoc(mysqlquerryc('select pok from ludzie where id='.$row['zona3'].' limit 1;'));
					if($row['pok']!=$z3['pok']) echo('<p>'.linkujludzia($row['id'],2).' jest z pokolenia '.$row['pok'].', a jego żona '.linkujludzia($row['zona1'],2).' jest z pokolenia '.$z3['pok'].'</p>');
				}
			}
			echo('<h3>'.$lang[$lng][105].'</h3>');
			$res=mysqlquerryc('select id,rodzic1,rodzic2,ur from ludzie;');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$one=mysql_fetch_assoc($res);
				if($one['ur']!=0){
					if($one['rodzic1']!=0){
						$r1=mysql_fetch_assoc(mysqlquerryc('select ur from ludzie where id='.$one['rodzic1'].';'));
						if(($r1['ur']!=0)&($one['ur']-$r1['ur'])<18) echo('<p>'.linkujludzia($one['rodzic1'],2).' był ojcem '.linkujludzia($one['id'],2).' w wieku '.($one['ur']-$r1['ur']).' lat</p>');
					}
					if($one['rodzic2']!=0){
						$r2=mysql_fetch_assoc(mysqlquerryc('select ur from ludzie where id='.$one['rodzic2'].';')); 
						if(($r2['ur']!=0)&($one['ur']-$r2['ur'])<18) echo('<p>'.linkujludzia($one['rodzic2'],2).' urodziła '.linkujludzia($one['id'],2).' w wieku '.($one['ur']-$r2['ur']).' lat</p>');
					}
				}
			}
			echo('<h3>'.$lang[$lng][106].':</h3>'); //invisible for non-admins
			$res6=mysqlquerryc('select id from ludzie where visible=0;');
			for($i=0;$i<mysql_num_rows($res6);$i+=1){
				$one=mysql_fetch_assoc($res6);
				echo(linkujludzia($one['id'],2));
			}
			mysql_free_result($res6);
			echo('<h3>'.$lang[$lng][107].'</h3>');
			foreach($lang as $kk => $vv){
				if(count($vv)==count($lang['pl'])) echo(count($vv).' '.$kk.' OK, ');
				else echo($kk.' Bad, ');
			}
			
			echo('<h3>max length of bounding box of people names:</h3>');
			$res=mysqlquerryc('select imie,nazwisko from ludzie');
			$maxbb=0;
			putenv('GDFONTPATH=' . realpath('.'));
			$font='calibri';
			$fsiz=14;
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				$bbox=imagettfbbox($fsiz,0,$font,'+ '+$row['imie'].' '.$row['nazwisko']);
				if($bbox[2]>$maxbb){
					$maxbb=$bbox[2];
					$maxin=$row['imie'].' '.$row['nazwisko'];
				}
			}
			echo('<p>'.$maxbb.' ('.$maxin.')</p>');
			
		}
		else{
			mysqlquerryc('insert into logs set user="niezalogowany", action="Próba dostępu do Do zrobienia, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">'.$lang[$lng][179].' <a href="'.$thisfile.'?login">'.$lang[$lng][180].'</a></p>');
		}
		html_end();
		break;
	}
	case 'pokaz':{
		if(strlen($id2)>0) $co=$id2;
		else $co='all';
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			switch($co){
				case 'all':{ //famuła menu item
					$ipk=mysql_fetch_assoc(mysqlquerryc('select min(pok) as s,max(pok) as e from ludzie'));
					mysqlquerryc('update ludzie set byl=0;');
					$li=0;
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Wyświetlenie Famuły", time="'.date("Y-m-d H:i:s").'";');
					else mysqlquerryc('insert into logs set user="niezalogowany", action="Wyświetlenie Famuły, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');	
					for($j=$ipk['s'];$j<$ipk['e'];$j+=1){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $res=mysqlquerryc('select id,byl from ludzie where pok='.$j.' and sex="m" order by nazwisko;');
						else $res=mysqlquerryc('select id,byl from ludzie where visible=1 and pok='.$j.' and sex="m" order by nazwisko;');
						for($i=0;$i<mysql_num_rows($res);$i+=1){
							$row=mysql_fetch_assoc($res);
							if($row['byl']==0){
								dzieciizona($row['id'],$li);
							}
						}
						echo('<br><br>');
						$li+=1;
					}
					break;
				}
				case 'one':{
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $theone=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$id3.';'));
					else $theone=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where visible=1 and id='.$id3.';'));
					if($theone){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Wyświetlenie '.$theone['imie'].' '.$theone['nazwisko'].'", time="'.date("Y-m-d H:i:s").'";');
						else mysqlquerryc('insert into logs set user="niezalogowany", action="Wyświetlenie '.$theone['imie'].' '.$theone['nazwisko'].', z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
						echo('<center><b><a href="'.$thisfile.'?tree,'.$id3.'">'.$lang[$lng][67].'</a> | <a href="'.$thisfile.'?pokr,'.$id3.'">'.$lang[$lng][68].'</a></b>');
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) echo(' | <a href="'.$thisfile.'?edit1,'.$id3.'">'.$lang[$lng][69].'</a> | <a href="'.$thisfile.'?add,'.$theone['id'].'">'.$lang[$lng][70].'</a>');
						echo('<table width="80%" border="0"><tr><td width="50%" align="right" colspan="2">');
						$zdjnum=mysql_fetch_assoc(mysqlquerryc('select count(*) as num from zdjecia where osoby="'.$id3.'";'));
						$zdjnumall=mysql_fetch_assoc(mysqlquerryc('select count(*) as num from zdjecia where osoby like "%'.$id3.'%";'));
						$slu=mysql_fetch_assoc(mysqlquerryc('select * from zdjecia where osoby="'.$id3.'" and slub=1 limit 1;'));
						if($zdjnum['num']==0) echo('<img src="brakzdj_'.$lng.'.png" class="lud" border="4" title="'.$lang[$lng][71].'">');
						if($slu){
							echo('<img src="'.$slu['path'].'" class="lud" border="4" title="'.$lang[$lng][72].' '.$slu['rok'].'">');
							$zdj=mysqlquerryc('select * from zdjecia where osoby="'.$id3.'" and slub=0 order by rok desc,id desc limit 1;');
						}
						else{
							$zdj=mysqlquerryc('select * from zdjecia where osoby="'.$id3.'" order by rok desc,id desc limit 2;');
						}
						for($iz=0;$iz<mysql_num_rows($zdj);$iz+=1){
							$zdjrow=mysql_fetch_assoc($zdj);
							echo('<img src="'.$zdjrow['path'].'" class="lud" border="4" title="'.$lang[$lng][72].' '.$zdjrow['rok'].'">');
						}
						if(($zdjnumall['num']>0)|isset($_COOKIE['zal'])) echo('<br><a href="'.$thisfile.'?zdj,'.$theone['id'].'">'.$lang[$lng][73].'</a>');
						echo('</td><td width="50%">');
						echo('<h1>'.$theone['imie'].' ');
						if($theone['sex']=='k'){
							if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $zony=mysqlquerryc('select * from ludzie where zona1='.$theone['id'].' or zona2='.$theone['id'].' or zona3='.$theone['id'].';');
							else $zony=mysqlquerryc('select * from ludzie where visible=1 and (zona1='.$theone['id'].' or zona2='.$theone['id'].' or zona3='.$theone['id'].');');
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
						if($theone['ur']!=0) echo($lang[$lng][122].'. <a href="'.$thisfile.'?rocznik,'.$theone['ur'].'">'.$theone['ur'].'</a>'.$lang[$lng][124].' ');
						if($theone['zm']!=0) echo($lang[$lng][123].'. <a href="'.$thisfile.'?rocznik,'.$theone['zm'].'">'.$theone['zm'].'</a>'.$lang[$lng][124].' ');
						if(($theone['ur']!=0)&($theone['zm']!=0)){
							echo($lang[$lng][125]);
							if($theone['sex']=='k') echo($lang[$lng][126]);
							echo(' '.($theone['zm']-$theone['ur']).' '.$lang[$lng][127]);
						}
						if(strlen($theone['adres'])>1) echo('<br>'.$theone['adres']);
						if($theone['sex']=='k'){
							if(mysql_num_rows($zony)>0){
								echo('<h3>'.$lang[$lng][74].':</h3>');
								if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $zony=mysqlquerryc('select * from ludzie where zona1='.$theone['id'].' or zona2='.$theone['id'].' or zona3='.$theone['id'].';');
								else $zony=mysqlquerryc('select * from ludzie where visible=1 and (zona1='.$theone['id'].' or zona2='.$theone['id'].' or zona3='.$theone['id'].');');
								for($i=0;$i<mysql_num_rows($zony);$i+=1){
									$maz=mysql_fetch_assoc($zony);
									echo('<p><a href="'.$thisfile.'?pokaz,one,'.$maz['id'].'">'.$maz['imie'].' '.$maz['nazwisko'].'</a> ('.$maz['ur'].')</p>');
								}
							}
						}
						else{
							if($theone['zona1']!=0){
								echo('<h3>'.$lang[$lng][75].':</h3>');
								if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $zon1=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$theone['zona1'].';'));
								else $zon1=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where visible=1 and id='.$theone['zona1'].';'));
								echo('<p><a href="'.$thisfile.'?pokaz,one,'.$zon1['id'].'">'.$zon1['imie'].' '.$zon1['nazwisko'].'</a> ('.$zon1['ur'].')</p>');
								if($theone['zona2']!=0){
									if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $zon2=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$theone['zona2'].';'));
									else $zon2=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where visible=1 and id='.$theone['zona2'].';'));
									echo('<p><a href="'.$thisfile.'?pokaz,one,'.$zon2['id'].'">'.$zon2['imie'].' '.$zon2['nazwisko'].'</a> ('.$zon2['ur'].')</p>');
									if($theone['zona3']!=0){
										if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $zon3=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$theone['zona3'].';'));
										else $zon3=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where visible=1 and id='.$theone['zona3'].';'));
										echo('<p><a href="'.$thisfile.'?pokaz,one,'.$zon3['id'].'">'.$zon3['imie'].' '.$zon3['nazwisko'].'</a> ('.$zon3['ur'].')</p>');
									}
								}
							}
						}
						echo('</td></tr><tr><td>');
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $res=mysqlquerryc('select * from ludzie where rodzic1='.$theone['id'].' or rodzic2='.$theone['id'].' order by ur,imie;');
						else $res=mysqlquerryc('select * from ludzie where visible=1 and (rodzic1='.$theone['id'].' or rodzic2='.$theone['id'].') order by ur,imie;');
						$praw_number=0;
						if(mysql_num_rows($res)>0){
							echo('<h3>'.$lang[$lng][76].' ('.mysql_num_rows($res).'):</h3>');
							for($i=0;$i<mysql_num_rows($res);$i+=1){
								$row=mysql_fetch_assoc($res);
								$praw_number+=ilupot($row['id'],2);
								echo('<p><a href="'.$thisfile.'?pokaz,one,'.$row['id'].'">'.$row['imie'].' '.$row['nazwisko'].'</a> ('.$row['ur'].')</p>');
							}
						}
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $res=mysqlquerryc('select * from ludzie where rch1='.$theone['id'].' or rch2='.$theone['id'].' order by ur,imie;');
						else $res=mysqlquerryc('select * from ludzie where visible=1 and (rch1='.$theone['id'].' or rch2='.$theone['id'].') order by ur,imie;');
						if(mysql_num_rows($res)>0){
							echo('<br><h3>'.$lang[$lng][128].' ('.mysql_num_rows($res).'):</h3>');
							for($i=0;$i<mysql_num_rows($res);$i+=1){
								$row=mysql_fetch_assoc($res);
								echo('<p><a href="'.$thisfile.'?pokaz,one,'.$row['id'].'">'.$row['imie'].' '.$row['nazwisko'].'</a> ('.$row['ur'].')</p>');
							}
						}
						echo('</td><td>');
						$l_wn=ilupot($theone['id'],2);
						if($l_wn>0) echo($lang[$lng][77].': '.$l_wn);
						else echo('&nbsp;');
						if($praw_number>0) echo('<br>'.$lang[$lng][78].': '.$praw_number);
						echo('</td><td>');
						echo('<h3>'.$lang[$lng][79].':</h3>');
						if($theone['rodzic1']==0) echo('<p>'.$lang[$lng][80].'</p>');
						else echo('<p>'.linkujludzia($theone['rodzic1'],2).'</p>');
						if($theone['rodzic2']==0) echo('<p>'.$lang[$lng][80].'</p>');
						else echo('<p>'.linkujludzia($theone['rodzic2'],2).'</p>');
						if(($theone['rch1']!=0)|($theone['rch2']!=0)){
							echo('<br><h3>'.$lang[$lng][81].':</h3>');
							if($theone['rch1']==0) echo('<p>'.$lang[$lng][80].'</p>');
							else echo('<p>'.linkujludzia($theone['rch1'],2).'</p>');
							if($theone['rch2']==0) echo('<p>'.$lang[$lng][80].'</p>');
							else echo('<p>'.linkujludzia($theone['rch2'],2).'</p>');
						}
						echo('</td></tr><tr><td colspan="2" align="center">');
						echo('<p>'.$theone['uwagi'].'</p>');
						echo('</td></tr></table></center>');
					}
					else{
						echo('<p class="alert">'.$lang[$lng][82].'</p>');
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba wyświetlenia nieistniejącej osoby (id '.$theone['id'].')", time="'.date("Y-m-d H:i:s").'";');
						else mysqlquerryc('insert into logs set user="niezalogowany", action="Próba wyświetlenia nieistniejącej osoby (id '.$theone['id'].'), z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
					}
					break;
				}
			}
		}
		html_end();
		break;
	}
	case 'pokr':{
		if(isset($_COOKIE['zal'])&checkname()){
			if(isset($id2)){
				mysqlquerryc('update ludzie set byl=0;'); //temp column
				if($id2=='del'){
					setcookie('pokr',0,date("U")-500);
					unset($_COOKIE['pokr']);
					html_start();
					echo('<p class="ok">'.$lang[$lng][129].'</p>');
					html_end();
				}
				else{
					if(isset($_COOKIE['pokr'])&($_COOKIE['pokr']!=0)){
						$a=htmlspecialchars($_COOKIE['pokr']);
						$b=htmlspecialchars($id2);
						setcookie('pokr',0,date("U")-500);
						unset($_COOKIE['pokr']);
						html_start();
						if($a!=$b){	
							$p=pokrewienstwo($a,$b);
							$p=str_replace('matki córki','siostry',$p);
							$p=str_replace('ojca córki','siostry',$p);
							$p=str_replace('matki syna','brata',$p);
							$p=str_replace('ojca syna','brata',$p);
							//pass0
							$p=str_replace('ojca ojca ojca','pradziadka',$p);
							$p=str_replace('matki ojca ojca','pradziadka',$p);
							$p=str_replace('ojca matki ojca','pradziadka',$p);
							$p=str_replace('matki matki ojca','pradziadka',$p);
							$p=str_replace('ojca ojca matki','prababci',$p);
							$p=str_replace('ojca matki matki','prababci',$p);
							$p=str_replace('matki ojca matki','prababci',$p);
							$p=str_replace('matki matki matki','prababci',$p);
							$p=str_replace('syna syna syna','prawnuka',$p);
							$p=str_replace('córki syna syna','prawnuka',$p);
							$p=str_replace('syna córki syna','prawnuka',$p);
							$p=str_replace('córki córki syna','prawnuka',$p);
							$p=str_replace('córki córki córki','prawnuczki',$p);
							$p=str_replace('syna córki córki','prawnuczki',$p);
							$p=str_replace('córki syna córki','prawnuczki',$p);
							$p=str_replace('syna syna córki','prawnuczki',$p);
							//pass1
							$p=str_replace('córki córki','wnuczki',$p);
							$p=str_replace('syna córki','wnuczki',$p);
							$p=str_replace('córki syna','wnuka',$p);
							$p=str_replace('syna syna','wnuka',$p);
							$p=str_replace('ojca ojca','dziadka',$p);
							$p=str_replace('ojca matki','babci',$p);
							$p=str_replace('matki ojca','dziadka',$p);
							$p=str_replace('matki matki','babci',$p);
							//final pass
							$p=str_replace(' matki.end',' matka',$p);
							$p=str_replace(' ojca.end',' ojciec',$p);
							$p=str_replace(' syna.end',' syn',$p);
							$p=str_replace(' córki.end',' córka',$p);
							$p=str_replace(' męża.end',' mąż',$p);
							$p=str_replace(' żony.end',' żona',$p);
							$p=str_replace(' brata.end',' brat',$p);
							$p=str_replace(' siostry.end',' siostra',$p);
							$p=str_replace(' babci.end',' babcia',$p);
							$p=str_replace(' dziadka.end',' dziadek',$p);
							$p=str_replace(' wnuka.end',' wnuk',$p);
							$p=str_replace(' wnuczki.end',' wnuczka',$p);
							$p=str_replace(' pradziadka.end',' pradziadek',$p);
							$p=str_replace(' prababci.end',' prababcia',$p);
							$p=str_replace(' bratowej.end',' bratowa',$p);		
							$p=str_replace(' prawnuka.end',' prawnuk',$p);
							$p=str_replace(' prawnuczki.end',' prawnuczka',$p);
							
							if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $oa=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko,sex from ludzie where id='.$a.';'));
							else $oa=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko,sex from ludzie where visible=1 and id='.$a.';'));
							if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $za=mysql_fetch_assoc(mysqlquerryc('select path from zdjecia where osoby="'.$a.'" order by rok desc;'));
							else $za=mysql_fetch_assoc(mysqlquerryc('select path from zdjecia where osoby="'.$a.'" order by rok desc;'));
							if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $ob=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko,sex from ludzie where id='.$b.';'));
							else $ob=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko,sex from ludzie where visible=1 and id='.$b.';'));
							if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $zb=mysql_fetch_assoc(mysqlquerryc('select path from zdjecia where osoby="'.$b.'" order by rok desc;'));
							else $zb=mysql_fetch_assoc(mysqlquerryc('select path from zdjecia where osoby="'.$b.'" order by rok desc;'));
							echo('<table border="0" width="100%"><tr><td align="center"><a href="'.$thisfile.'?pokaz,one,'.$a.'"><img class="lud" border="4" src="');
							if(strlen($za['path'])>4) echo($za['path']);
							else echo('brakzdj_'.$lng.'.png');
							echo('"><br><p class="inspokr">'.$oa['imie'].' '.$oa['nazwisko'].'</p></a></td><td width="60%"><h2>');
							if($p=='NIE ZNALEZIONO') echo($lang[$lng][130]);
							else{
								if($oa['sex']=='k') echo(odmiana_k($oa['imie']));
								else echo(odmiana_m($oa['imie']));
								echo(' '.str_replace('_',' ',$p).' to jest '.$ob['imie']);
							}
							echo('</h2></td><td align="center"><a href="'.$thisfile.'?pokaz,one,'.$b.'"><img class="lud" border="4" src="');
							if(strlen($zb['path'])>4) echo($zb['path']);
							else echo('brakzdj_'.$lng.'.png');
							echo('"><br><p class="inspokr">'.$ob['imie'].' '.$ob['nazwisko'].'</p></a></td></tr></table>');
							if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Sprawdzenie pokrewieństwa pomiędzy '.$a.' a '.$b.'", time="'.date("Y-m-d H:i:s").'";');
							else mysqlquerryc('insert into logs set user="niezalogowany", action="Sprawdzenie pokrewieństwa pomiędzy '.$a.' a '.$b.', z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
						}
						else{
							echo('<p class="alert">'.$lang[$lng][227].'</p>');
							if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="próba porównania tej samej osoby ze sobą", time="'.date("Y-m-d H:i:s").'";');
							else mysqlquerryc('insert into logs set user="niezalogowany", action="próba porównania tej samej osoby ze sobą, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
						}
						html_end();
					}
					else{
						setcookie('pokr',$id2);
						html_start();
						echo('<p class="ok">'.$lang[$lng][132].'</p><br><p>'.$lang[$lng][131].'</p>');
						echo('<script type="text/javascript">
						document.location="'.$thisfile.'?search";
						</script>');
						html_end();
					}
				}
			}
			else{
				html_start();
				echo('<p class="alert">'.$lang[$lng][133].'</p>');
				html_end();
			}
			break;
		}
	}
	case 'tree':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(isset($id2)){
				if(isset($_POST['submit'])){ //pdf w dół
					system('rm pdfgen/*.pdf');
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $theone=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where id='.$id2.';'));
					else $theone=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where visible=1 and id='.$id2.';'));
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
					$onezdj=mysqlquerryc('select path from zdjecia where osoby='.$theone['id'].' order by rok desc,id desc limit 1;');
					if($_POST['pok']<5) $cellh=floor($h/($_POST['pok']+1));
					else $cellh=floor($h/5);
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
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $ro[0]=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where id='.$theone['rodzic1'].';'));
						else $ro[0]=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where visible=1 and id='.$theone['rodzic1'].';'));
						$rozdj[0]=mysqlquerryc('select path from zdjecia where osoby='.$theone['rodzic1'].' order by rok desc,id desc limit 1;');
					}
					if($theone['rodzic2']!=0){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $ro[1]=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where id='.$theone['rodzic2'].';'));
						else $ro[1]=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where visible=1 and id='.$theone['rodzic2'].';'));
						$rozdj[1]=mysqlquerryc('select path from zdjecia where osoby='.$theone['rodzic2'].' order by rok desc,id desc limit 1;');
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
							if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzia[$i4]=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where id='.$ro[floor($i4/2)]['rodzic'.(($i4%2)+1)].';'));
							else $dzia[$i4]=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where visible=1 and id='.$ro[floor($i4/2)]['rodzic'.(($i4%2)+1)].';'));
							$dzzdj[$i4]=mysqlquerryc('select path from zdjecia where osoby='.$ro[floor($i4/2)]['rodzic'.(($i4%2)+1)].' order by rok desc limit 1;');
						}
					}
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
								if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $pra[$i5]=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where id='.$dzia[floor($i5/2)]['rodzic'.(($i5%2)+1)].';'));
								else $pra[$i5]=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where visible=1 and id='.$dzia[floor($i5/2)]['rodzic'.(($i5%2)+1)].';'));
								$przdj[$i5]=mysqlquerryc('select path from zdjecia where osoby='.$dzia[floor($i5/2)]['rodzic'.(($i5%2)+1)].' order by rok desc limit 1;');
							}
						}
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
							$pdf->SetFont('arialpl','', 9);
							for($i6=0;$i6<16;$i6+=1){
								if($pra[floor($i6/2)]['rodzic'.(($i6%2)+1)]!=0){
									if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $prpr[$i6]=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where id='.$pra[floor($i6/2)]['rodzic'.(($i6%2)+1)].';'));
									else $prpr[$i6]=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where visible=1 and id='.$pra[floor($i6/2)]['rodzic'.(($i6%2)+1)].';'));
									$prprzdj[$i6]=mysqlquerryc('select path from zdjecia where osoby='.$pra[floor($i6/2)]['rodzic'.(($i6%2)+1)].' order by rok desc limit 1;');
								}
							}
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
							if($_POST['pok']>=5){
								//5 pra pra pra
								$pdf->SetXY($w-50,$h+15);
								$pdf->SetTextColor(100,100,100);
								$pdf->Write(0,UTF8_2_ISO88592($lang[$lng][134].': '.$settings['site_name']));
								$pdf->SetTextColor(0,0,0);
								$pdf->AddPage();
								$pdf->SetFont('arialpl','', 8);
								for($i7=0;$i7<32;$i7+=1){
									if($prpr[floor($i7/2)]['rodzic'.(($i7%2)+1)]!=0){
										if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $pr3[$i7]=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where id='.$prpr[floor($i7/2)]['rodzic'.(($i7%2)+1)].';'));
										else $pr3[$i7]=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,rodzic1,rodzic2 from ludzie where visible=1 and id='.$prpr[floor($i7/2)]['rodzic'.(($i7%2)+1)].';'));
										$pr3zdj[$i7]=mysqlquerryc('select path from zdjecia where osoby='.$prpr[floor($i7/2)]['rodzic'.(($i7%2)+1)].' order by rok desc limit 1;');
									}
								}
								foreach($pr3 as $key=>$val){
									if(isset($_POST['zdjecia'])&(mysql_num_rows($pr3zdj[$key])==1)){
										$pd3zdj=mysql_fetch_assoc($pr3zdj[$key]);
										$imsize=getimagesize($pd3zdj['path']);
										$pdf_zdj_w[$key]=$imsize[0]*($cellh/2)/$imsize[1];
										$pdf->Image($pd3zdj['path'],$x+($key*$w/32)-($pdf_zdj_w[$key]/2)+$w/64,($y+(5*$cellh)),$pdf_zdj_w[$key],($cellh/2));
									}
									//($y+5+(5*$cellh)), ($y+8*$cellh)
									$pdf->SetXY($x+$key*($w/32),10);
									$pdf->MultiCell($w/32,(1.5*$cellh),'',1,'C');	
									$pdf->SetXY($x+$key*($w/32)+3,(10+(1.5*$cellh)));
									$pdf->Rotate(90);
									if($val['nazwisko']=='̣̣???') $nazwisko=' ';
									else $nazwisko=$val['nazwisko'];
									$pdf->Write(0,UTF8_2_ISO88592($val['imie'].' '.$nazwisko));
									$pdf->Rotate(0);
								}
							}
						}
					}
					$pdf->SetXY($w-50,$h+15);
					$pdf->SetFont('arialpl','',9);
					$pdf->SetTextColor(100,100,100);
					$pdf->Write(0,UTF8_2_ISO88592($lang[$lng][134].': '.$settings['site_name']));
					$pdf->SetAuthor('Szymon Marciniak');
					$pdf->SetCreator(UTF8_2_ISO88592($settings['site_name']));
					$pdf->Output($filename);
					echo('<a href="'.$thisfile.'?pokaz,one,'.$theone['id'].'">'.$lang[$lng][202].' '.$theone['imie'].' '.$theone['nazwisko'].'</a><br><br>Plik PDF gotowy! <a href="'.$filename.'" target="blank"><b>Pokaż</b></a>');
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Wygenerowano drzewo dla '.$theone['imie'].' '.$theone['nazwisko'].'", time="'.date("Y-m-d H:i:s").'";');
					else mysqlquerryc('insert into logs set user="niezalogowany", action="Wygenerowano drzewo dla '.$theone['imie'].' '.$theone['nazwisko'].', z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
				}
				else if(isset($_POST['submit2'])){ //obrazek w górę
					$kids_already_done=Array();
					for($i=0;$i<2000;$i+=1){
						$kids_already_done[$i]=0;
					}
					$lwp[1]=1;
					for($i=2;$i<=$_POST['pok2'];$i+=1){
						$lwp[$i]=ilupot($id2,$i-1);
					}
					$mlwp[1]=1;
					for($i=2;$i<=($_POST['pok2']+1);$i+=1){
						$mlwp[$i]=ilupotmax($id2,$i-1);
					}
					//kolory
					/*
					 1szary 808080
					 2czerwony ff0000
					 3fioli 800080
					 4ziel 008000
					 5nieb 0000ff
					 6turk 008080
					 7róż ff00ff
					 8burgung 800000
					 9oliw 808000
					 10granat 000080
					 11czarny 000000
					 12 pomara ff6600
					 13 czloty cc9966
					 */
					$filenamepdf='pdfgen/gtr'.$id2.'_'.$_POST['pok2'].'_'.date("Y_W").'_'.$POST['compat'].'.pdf';
					if(!file_exists($filenamepdf)){
						$filename='pdfgen/gtree'.$id2;
						$res1=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.htmlspecialchars($id2).';'));
						$zid=szukajZony(htmlspecialchars($id2));
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $ziin=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko from ludzie where id='.$zid.';'));
						else $ziin=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko from ludzie where id='.$zid.' and visible=1;'));
						$w_pp=190;
						$h_pp=300;
						$imgh=$_POST['pok2']*$h_pp;
						putenv('GDFONTPATH=' . realpath('.'));
						$font='calibri';
						$fsiz=14;
						$bbox=imagettfbbox(32,0,$font,$res1['imie'].' '.$res1['nazwisko'].' + '.$ziin['imie'].' '.$ziin['nazwisko']);
						if(isset($_POST['compact'])) $imgw=max($lwp)*$w_pp;
						else{
							$imgw=$w_pp;
							for($i=2;$i<=$_POST['pok2'];$i+=1){
								$imgw*=$mlwp[$i];
							}
						}
						$page_wid=round((300*$imgh)/175,0);
						$pages=ceil($imgw/$page_wid);
						if(($pages%2)==0) $pages+=1;
						//echo(' pages: '.$pages.', page_dim: '.$page_wid.'x'.$imgh.'<br>');
						//echo('new page dim:'.round($imgw/$pages,0).'x'.round($imgh*round($imgw/$pages,0)/$page_wid,0).' <br>');
						$offset=round($imgw/$pages,0);
						if($bbox[2]>$imgw) $imgw=$bbox[2];
						$logo=imagecreatefrompng('logo.png');
						imagefilter($logo,IMG_FILTER_GRAYSCALE);
						imagefilter($logo,IMG_FILTER_CONTRAST,50);
						imagefilter($logo,IMG_FILTER_BRIGHTNESS,128);
						$logow=$h_pp*800/227;
						$logoh=$h_pp;
						if($logow>($imgw/4)){
							$logow=$imgw/4;
							$logoh=$logow*227/800;
						}
						for($pag=0;$pag<$pages;$pag+=1){ //split for pages
							unset($kids_already_done);
							$img=imagecreatetruecolor($offset,$imgh);
							$black=imagecolorallocate($img,0,0,0);
							$white=imagecolorallocate($img,255,255,255);
							imagefilledrectangle($img,0,0,$offset,$imgh,$white); // whole image white
							if($pag==0) imagecopyresampled($img,$logo,0,0,0-$offset*$pag,0,$logow,$logoh,800,227);
							imagerectangle($img,($imgw/2)-($bbox[2]/2)+$bbox[0]-10-$offset*$pag,50+$bbox[1]+10,($imgw/2)-($bbox[2]/2)+$bbox[4]+10-$offset*$pag,50+$bbox[5]-10,$black);
							imagettftext($img,32,0,($imgw/2)-($bbox[2]/2)-$offset*$pag,50,$black,$font,$res1['imie'].' '.$res1['nazwisko'].' + '.$ziin['imie'].' '.$ziin['nazwisko']);
							//imagefilter($img,IMG_FILTER_GRAYSCALE);
							//dzieci
							if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $res2=mysqlquerryc('select * from ludzie where rodzic1='.htmlspecialchars($id2).' or rodzic2='.htmlspecialchars($id2).';');
							else $res2=mysqlquerryc('select * from ludzie where rodzic1='.htmlspecialchars($id2).' or rodzic2='.htmlspecialchars($id2).' and visible=1;');
							$pok3=Array(); // of grand children and x positions of their parents
							$pok3r=Array();
							$pok3nok=Array();
							for($i=0;$i<mysql_num_rows($res2);$i+=1){
								$row2=mysql_fetch_assoc($res2);
								$bbox1=imagettfbbox($fsiz,0,$font,$row2['imie'].' '.$row2['nazwisko']);
								if(isset($_POST['compact'])) $centerx=(((max($lwp)*$w_pp)/mysql_num_rows($res2))*($i+0.5));
								else{
									$centerx=(($imgw/$w_pp)/mysql_num_rows($res2))*($kids_already_done[$id2]+0.5)*$w_pp;
									$kids_already_done[$id2]+=1;
								}
								imageline($img,($imgw/2)-($offset*$pag),70,$centerx-($offset*$pag),1.4*$h_pp,$black);
								imagettftext($img,$fsiz,0,$centerx-($bbox1[2]/2)-($offset*$pag),$h_pp*1.5,$black,$font,$row2['imie'].' '.$row2['nazwisko']);
								if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $ziid2=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko from ludzie where id='.szukajZony($row2['id']).';'));
								else $ziid2=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko from ludzie where id='.szukajZony($row2['id']).' and visible=1;'));
								$bbox1z=imagettfbbox($fsiz,0,$font,'+ '.$ziid2['imie'].' '.$ziid2['nazwisko']);
								if($ziid2) imagettftext($img,$fsiz,0,$centerx-($bbox1z[2]/2)-$offset*$pag,$h_pp*1.5-$bbox1[7],$black,$font,'+ '.$ziid2['imie'].' '.$ziid2['nazwisko']);
								//imagerectangle($img,(((max($lwp)*$w_pp)/mysql_num_rows($res2))*($i+0.5))-($bbox[2]/2)+$bbox[0]-10,$h_pp*1.5+$bbox[1]+10,(((max($lwp)*$w_pp)/mysql_num_rows($res2))*($i+0.5))-($bbox[2]/2)+$bbox[4]+10,$h_pp*1.5+$bbox[5]-10,$black);
								if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $p3prep=mysqlquerryc('select id from ludzie where rodzic1='.$row2['id'].' or rodzic2='.$row2['id'].';');
								else $p3prep=mysqlquerryc('select id from ludzie where rodzic1='.$row2['id'].' or rodzic2='.$row2['id'].' and visible=1;');
								$dzieci=mysql_num_rows($p3prep);
								$firstchild=(((($imgw/$mlwp[2])/$dzieci)*($dzieci-1))/2);
									for($j=0;$j<mysql_num_rows($p3prep);$j+=1){
									$p3r=mysql_fetch_assoc($p3prep);
									$pok3[($p3r['id'])]=$centerx; //save x for line drawing     
									$pok3r[($p3r['id'])]=$centerx-$firstchild+($j*($firstchild/(($dzieci-1)/2)));
								}
							}
							//unset($row2,$ziid2,$res1,$zid,$ziin);
							mysql_free_result($p3prep);
							//wnuki
							if($_POST['pok2']>=3){
								$i=0;
								$pok4=Array();
								$pok4r=Array();
								foreach($pok3 as $k3=>$v3){
									if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $row3=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$k3.';'));
									else $row3=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$k3.' and visible=1;'));
									$bbox1=imagettfbbox($fsiz,0,$font,$row3['imie'].' '.$row3['nazwisko']);
									if(isset($_POST['compact'])) $centerx=(((max($lwp)*$w_pp)/count($pok3))*($i+0.5));
									else $centerx=$pok3r[$k3];
									imagettftext($img,$fsiz,0,$centerx-($bbox1[2]/2)-$offset*$pag,$h_pp*2.5,$black,$font,$row3['imie'].' '.$row3['nazwisko']);
									imageline($img,$v3-$offset*$pag,$h_pp*1.7,$centerx-$offset*$pag,2.4*$h_pp,$black);
									if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $ziid3=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko from ludzie where id='.szukajZony($row3['id']).';'));
									else $ziid3=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko from ludzie where id='.szukajZony($row3['id']).' and visible=1;'));
									$bbox1z=imagettfbbox($fsiz,0,$font,'+ '.$ziid3['imie'].' '.$ziid3['nazwisko']);
									if($ziid3) imagettftext($img,$fsiz,0,$centerx-($bbox1z[2]/2)-$offset*$pag,$h_pp*2.5-$bbox1[7],$black,$font,'+ '.$ziid3['imie'].' '.$ziid3['nazwisko']);
									if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $p4prep=mysqlquerryc('select id from ludzie where rodzic1='.$row3['id'].' or rodzic2='.$row3['id'].';');
									else $p4prep=mysqlquerryc('select id from ludzie where rodzic1='.$row3['id'].' or rodzic2='.$row3['id'].' and visible=1;');
									$dzieci=mysql_num_rows($p4prep);
									$firstchild=((((($imgw/$mlwp[2])/$mlwp[3])/$dzieci)*($dzieci-1))/2);
									for($j=0;$j<mysql_num_rows($p4prep);$j+=1){
										$p4r=mysql_fetch_assoc($p4prep);
										$pok4[($p4r['id'])]=$centerx; //save x for line drawing
										$pok4r[($p4r['id'])]=$centerx-$firstchild+($j*($firstchild/(($dzieci-1)/2)));
									}
									$i++;
								}
							}
							//prawnuki
							if($_POST['pok2']>=4){
								$i=0;
								$pok5=Array();
								$pok5r=Array();
								foreach($pok4 as $k4=>$v4){
									if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $row4=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$k4.';'));
									else $row4=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$k4.' abd visible=1;'));
									$bbox1=imagettfbbox($fsiz,0,$font,$row4['imie'].' '.$row4['nazwisko']);
									if(isset($_POST['compact'])) $centerx=(((max($lwp)*$w_pp)/count($pok4))*($i+0.5));
									else $centerx=$pok4r[$k4];
									imagettftext($img,$fsiz,0,$centerx-($bbox1[2]/2)-$offset*$pag,$h_pp*3.5,$black,$font,$row4['imie'].' '.$row4['nazwisko']);
									imageline($img,$v4-$offset*$pag,$h_pp*2.7,$centerx-$offset*$pag,3.4*$h_pp,$black);
									if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $ziid4=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko from ludzie where id='.szukajZony($row4['id']).';'));
									else $ziid4=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko from ludzie where id='.szukajZony($row4['id']).' and visible=1;'));
									$bbox1z=imagettfbbox($fsiz,0,$font,'+ '.$ziid4['imie'].' '.$ziid4['nazwisko']);
									if($ziid4) imagettftext($img,$fsiz,0,$centerx-($bbox1z[2]/2)-$offset*$pag,$h_pp*3.5-$bbox1[7],$black,$font,'+ '.$ziid4['imie'].' '.$ziid4['nazwisko']);
									if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $p5prep=mysqlquerryc('select id from ludzie where rodzic1='.$row4['id'].' or rodzic2='.$row4['id'].';');
									else $p5prep=mysqlquerryc('select id from ludzie where rodzic1='.$row4['id'].' or rodzic2='.$row4['id'].' and visible=1;');
									$dzieci=mysql_num_rows($p5prep);
									$firstchild=(((((($imgw/$mlwp[2])/$mlwp[3])/$mlwp[4])/$dzieci)*($dzieci-1))/2);
									for($j=0;$j<mysql_num_rows($p5prep);$j+=1){
										$p5r=mysql_fetch_assoc($p5prep);
										$pok5[($p5r['id'])]=$centerx; //save x for line drawing
										$pok5r[($p5r['id'])]=$centerx-$firstchild+($j*($firstchild/(($dzieci-1)/2)));
									}
									$i++;
								}
							}
							//pra pra
							if($_POST['pok2']>=5){
								$i=0;
								$pok6=Array();
								$pok6r=Array();
								foreach($pok5 as $k5=>$v5){
									if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $row5=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$k5.';'));
									else $row5=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$k5.' and visible=1;'));
									$bbox1=imagettfbbox($fsiz,0,$font,$row5['imie'].' '.$row5['nazwisko']);
									if(isset($_POST['compact'])) $centerx=(((max($lwp)*$w_pp)/count($pok5))*($i+0.5));
									else{
										$centerx=$pok5r[$k5];
									}
									imagettftext($img,$fsiz,0,$centerx-($bbox1[2]/2)-$offset*$pag,$h_pp*4.5,$black,$font,$row5['imie'].' '.$row5['nazwisko']);
									imageline($img,$v5-$offset*$pag,$h_pp*3.7,$centerx-$offset*$pag,4.4*$h_pp,$black);
									if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $ziid5=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko from ludzie where id='.szukajZony($row5['id']).';'));
									else $ziid5=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko from ludzie where id='.szukajZony($row5['id']).' and visible=1;'));
									$bbox1z=imagettfbbox($fsiz,0,$font,'+ '.$ziid5['imie'].' '.$ziid5['nazwisko']);
									if($ziid5) imagettftext($img,$fsiz,0,$centerx-($bbox1z[2]/2)-$offset*$pag,$h_pp*4.5-$bbox1[7],$black,$font,'+ '.$ziid5['imie'].' '.$ziid5['nazwisko']);
									if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $p6prep=mysqlquerryc('select id from ludzie where rodzic1='.$row5['id'].' or rodzic2='.$row5['id'].';');
									else $p6prep=mysqlquerryc('select id from ludzie where rodzic1='.$row5['id'].' or rodzic2='.$row5['id'].' and visible=1;');
									$dzieci=mysql_num_rows($p6prep);
									$firstchild=((((((($imgw/$mlwp[2])/$mlwp[3])/$mlwp[4])/$mlwp[5])/$dzieci)*($dzieci-1))/2);
									for($j=0;$j<mysql_num_rows($p6prep);$j+=1){
										$p6r=mysql_fetch_assoc($p6prep);
										$pok6[($p6r['id'])]=$centerx; //save x for line drawing
										$pok6r[($p6r['id'])]=$centerx-$firstchild+($j*($firstchild/(($dzieci-1)/2)));
									}
									$i++;
								}
							}
							// 3x pra
							if($_POST['pok2']>=6){
								$i=0;
								foreach($pok6 as $k6=>$v6){
									if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $row6=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$k6.';'));
									else $row6=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$k6.' and visible=1;'));
									$bbox1=imagettfbbox($fsiz,0,$font,$row6['imie'].' '.$row6['nazwisko']);
									if(isset($_POST['compact'])) $centerx=(((max($lwp)*$w_pp)/count($pok6))*($i+0.5));
									else{
										$centerx=$pok6r[$k6];
									}
									imagettftext($img,$fsiz,0,$centerx-($bbox1[2]/2)-$offset*$pag,$h_pp*5.5,$black,$font,$row6['imie'].' '.$row6['nazwisko']);
									imageline($img,$v6-$offset*$pag,$h_pp*4.7,$centerx-$offset*$pag,5.4*$h_pp,$black);
									if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $ziid6=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko from ludzie where id='.szukajZony($row6['id']).';'));
									else $ziid6=mysql_fetch_assoc(mysqlquerryc('select imie,nazwisko from ludzie where id='.szukajZony($row6['id']).' and visible=1;'));
									$bbox1z=imagettfbbox($fsiz,0,$font,'+ '.$ziid6['imie'].' '.$ziid6['nazwisko']);
									if($ziid6) imagettftext($img,$fsiz,0,$centerx-($bbox1z[2]/2)-$offset*$pag,$h_pp*5.5-$bbox1[7],$black,$font,'+ '.$ziid6['imie'].' '.$ziid6['nazwisko']);
									$i++;
								}
							}
							//unset($row6,$bbox1,$bbox1z,$zzid6,$pok6);
							imagepng($img,$filename.'_'.$pag.'.png');
							unset($img);
						}
						$pdf=new PDF();
						$pdf->Open();
						$w=280; // wiredly, 266 works - [mm] - for A4 page
						$h=180; // [mm] - for A4 page
						$pdf->AddFont('arialpl','','arialpl.php');
						$pdf->SetFont('arialpl','', 9);
						for($pag=0;$pag<$pages;$pag+=1){
							$pdf->AddPage();
							$x=$pdf->GetX();
							$y=$pdf->GetY();
							$pdf->Image($filename.'_'.$pag.'.png',$x,$y,$w,$h);
							$pdf->Line($x,$y,$x,$y+$h);
							$pdf->Line($x+$w,$y,$x+$w,$$y+$h);
							$pdf->SetXY($w-20,$h+5);
							$pdf->SetTextColor(100,100,100);
							$pdf->Write(0,UTF8_2_ISO88592('strona '.($pag+1)));
					
						}
						$pdf->SetAuthor('Szymon Marciniak');
						$pdf->SetCreator(UTF8_2_ISO88592($settings['site_name']));
						$pdf->Output($filenamepdf);
					}
					else echo('<p>fast mode active</p>');
					echo('<b>Gotowe!<br><a href="'.$filenamepdf.'" target="blank">Pokaż</a></b><br>');
					//echo('<p><a href='.$thisfile.'?druk>Instrukcja jak wydrukować na wielu stronach</a></p>');
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Wygenerowano drzewo w górę dla '.$th_info['imie'].' '.$th_info['nazwisko'].'", time="'.date("Y-m-d H:i:s").'";');
					else mysqlquerryc('insert into logs set user="niezalogowany", action="Wygenerowano drzewo w górę dla '.$th_info['imie'].' '.$th_info['nazwisko'].', z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
				}
				else{
					$theone=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.htmlspecialchars($id2).';'));
					echo('<center><table berder="0"><tr><th colspan="2"><p>'.$lang[$lng][197].': '.linkujludzia($id2,2).'</p></th></tr><form action="'.$thisfile.'?tree,'.$id2.'" method="POST" name="treegen">');
					echo('<tr><td class="treeui">'.$theone['imie'].', '.jejjego($theone['sex'],$lng).' '.$lang[$lng][203].'</td>
					<td class="treeui">'.$theone['imie'].', '.jejjego($theone['sex'],$lng).' '.$lang[$lng][225].'</td></tr><tr><td class="treeui"><label><input type="radio" class="formfld" name="pok" value="2"> 2 ('.$lang[$lng][204].')</label><br>
					<label><input type="radio" class="formfld" name="pok" value="3"> 3 ('.$lang[$lng][219].')</label><br>
					<label><input type="radio" class="formfld" name="pok" value="4" checked="checked"> 4 ('.$lang[$lng][220].')</label><br>');
					echo('<label><input type="radio" class="formfld" name="pok" value="5"> 5 ('.$lang[$lng][221].'</label><br>');
					echo('<label><input type="checkbox" class="formfld" name="zdjecia" checked="checked"> '.$lang[$lng][222].'</label><br>');
					echo('<input type="submit" name="submit" value="'.$lang[$lng][218].'" class="formbtn" id="treegen" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)">');
					echo('</td><td class="treeui">');
					echo('<label><input type="radio" class="formfld" name="pok2" value="2">do dzieci</label><br>');
					echo('<label><input type="radio" class="formfld" name="pok2" value="3">do wnuków</label><br>');
					echo('<label><input type="radio" class="formfld" name="pok2" value="4" checked="checked">do pra wnuków</label><br>');
					echo('<p>Ze względu na duże zużycie pamięci, poniższe opcje opcje mogą zająć <br>
					         kilkanaście minut. Prosimy o cierpliwość.</p>');
					echo('<label><input type="radio" class="formfld" name="pok2" value="5">do pra pra wnuków</label><br>');
					echo('<label><input type="radio" class="formfld" name="pok2" value="6">do pra pra pra wnuków</label><br>');
					echo('<label><input type="checkbox" class="formfld" name="compact" checked="checked"> zmniejszona szerokość</label><br>');
					echo('<input type="submit" name="submit2" value="Generuj" class="formbtn" id="treegen2" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)">');
					echo('</form></td></tr></table></center>');
				}
			}
			else echo('<p class="alert">'.$lang[$lng][196].'?</p>');
		}
		html_end();
		break;
	}
	case 'rocznik':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(isset($_POST['rok'])&(!isset($id2))) $id2=$_POST['rok'];
			mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Wyświetlenie rocznika '.$id2.', z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'"');
			echo('<h2><a href="'.$thisfile.'?rocznik,'.($id2-1).'">◄ '.($id2-1).'</a> <big>'.$id2.'</big> ');
			if($id2==date('Y')) echo(($id2+1).' ►');
			else echo('<a href="'.$thisfile.'?rocznik,'.($id2+1).'">'.($id2+1).' ►</a>');
			echo('</h2>');
			echo('<form name="rocznik" action="'.$thisfile.'?rocznik" method="POST"><input class="formfld" type="text" id="rok" name="rok"><button class="formbtn" id="przejdz" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)" onclick="rokclick(document.rocznik.rok);" type="button" name="b1" value="Pokaż">'.$lang[$lng][111].'</button></form><br>');
			if(is_numeric($id2)){
				if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $res=mysqlquerryc('select id from ludzie where ur='.htmlspecialchars($id2).' order by imie,nazwisko;'); //born in year $id2
				else $res=mysqlquerryc('select id from ludzie where visible=1 and ur='.htmlspecialchars($id2).' order by imie,nazwisko;');
				if(mysql_num_rows($res)>0){
					echo('<h3>'.$lang[$lng][109].' '.$id2.'</h3>');
					for($i=0;$i<mysql_num_rows($res);$i+=1){
						$row=mysql_fetch_assoc($res);
						echo('<p>'.linkujludzia($row['id'],2).'</p>');
					}
				}
				if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $res=mysqlquerryc('select id from ludzie where zm='.htmlspecialchars($id2).' order by imie,nazwisko;'); //dead in year $id2
				else $res=mysqlquerryc('select id from ludzie where visible=1 and zm='.htmlspecialchars($id2).' order by imie,nazwisko;');
				if(mysql_num_rows($res)>0){
					echo('<h3>'.$lang[$lng][108].' '.$id2.'</h3>');
					for($i=0;$i<mysql_num_rows($res);$i+=1){
						$row=mysql_fetch_assoc($res);
						echo('<p>'.linkujludzia($row['id'],2).'</p>');
					}
				}
				$res=mysqlquerryc('select * from zdjecia where rok='.htmlspecialchars($id2).' and path like "%gru%";'); //pictures taken in year $id2
				if(mysql_num_rows($res)>0){
					echo('<h3>'.$lang[$lng][110].' '.$id2.'</h3>');
					for($i=0;$i<mysql_num_rows($res);$i+=1){
						$row=mysql_fetch_assoc($res);
						$pth=explode('.',$row['path']);
						echo('<a href="'.$thisfile.'?zdjgru1,'.$row['id'].'" title="'.$row['opis'].'"><img border="2" src="'.$pth[0].'m.jpg" class="lud"></a>');
					}
				}
			}
			else{
				$num_im=mysqlquerryc('select id from ludzie where imie like "%'.htmlspecialchars($id2).'%";');
				$num_naz=mysqlquerryc('select id from ludzie where nazwisko like "%'.htmlspecialchars($id2).'%";');
				//echo(mysql_num_rows($num_im).'_'.mysql_num_rows($num_naz));
				echo('<script type="text/javascript">
					document.location="'.$thisfile.'?search,');
				if(mysql_num_rows($num_im)>mysql_num_rows($num_naz)) echo($id2.',');
				else echo(','.$id2);	
				echo('";</script>');
			}
		}
		html_end();
		break;
	}
	case 'info':{  /// ALL CAN SEE
		html_start();
		if(isset($_COOKIE['zal'])&checkname()) mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Wyświetlenie O strinie", time="'.date("Y-m-d H:i:s").'"');
		else mysqlquerryc('insert into logs set user="niezalogowany", action="Wyświetlenie O strinie, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'"');
		echo('<h3>'.$lang[$lng][144].':</h3><a href="index.php?pokaz,one,78">Szymon Marciniak</a> ('.$lang[$lng][21].' 2012)<h3><a href="index.php?kontakt">'.$lang[$lng][145].'</a></h3> <h3>'.$lang[$lng][146].':</h3><a href="index.php?pokaz,one,76">Jolanta Marciniak</a> ('.$lang[$lng][21].' 2004)<br><br>');
		echo($settings['about']);  //change this in settings
		html_end();
		break;
	}
	case 'messages':{
		html_start();
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))){
			$res=mysqlquerryc('select * from opinie order by time desc;');
			echo('<table border="1"><tr><td>time</td><td>ip</td><td>tresc</td></tr>');
			for($o=0;$o<mysql_num_rows($res);$o+=1){
				$row=mysql_fetch_assoc($res);
				echo('<tr><td><nobr>'.$row['time'].'</nobr></td><td>'.$row['ip'].'</td><td>'.$row['tresc'].'</td></tr>');
			}
			echo('</table>');
		}
		else{
			mysqlquerryc('insert into logs set user="niezalogowany", action="Próba dostępu do wiadomości, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">'.$lang[$lng][179].' <a href="'.$thisfile.'?login">'.$lang[$lng][180].'</a></p>');
		}
		html_end();
		break;
	}
	case 'kontakt':{ // ALL CAN SEE
		html_start();
		if(isset($_COOKIE['zal'])&checkname()) mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Wyświetlenie Kontakt", time="'.date("Y-m-d H:i:s").'"');
		else mysqlquerryc('insert into logs set user="niezalogowany", action="Wyświetlenie Kontakt, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'"');
		if(isset($_POST['submit'])){
			if(mysqlquerryc('insert into opinie set tresc="Imie: '.htmlspecialchars($_POST['imie']).', email: '.htmlspecialchars($_POST['email']).', Wiadomość: '.htmlspecialchars($_POST['tresc']).'", time="'.date("Y-m-d H:i:s").'", ip="'.$_SERVER['REMOTE_ADDR'].'";')) echo('<p class="ok">'.$lang[$lng][147].'</p>');
			else echo('<p class="alert">'.$lang[$lng][148].'</p>');
			$headers ="MIME-Version: 1.0\n"; 
			$headers.="Content-type: text/html; charset=UTF-8\n"; 
			mail($settings['admin_mail'],'Nowy wpis na famule','Imie: '.htmlspecialchars($_POST['imie']).', email: '.htmlspecialchars($_POST['email']).', Wiadomość: '.htmlspecialchars($_POST['tresc']),$headers);
		}
		echo('<h3>'.$lang[$lng][136].':</h3>
		<p>'.$lang[$lng][137].':<center><table border="0"><tr><td><li>'.$lang[$lng][138].'</li><li>'.$lang[$lng][139].'</li> <li>'.$lang[$lng][140].'</li></td></tr></table> </center></p>
		<p><form name="kont" action="'.$thisfile.'?kontakt" method="POST"><label>'.$lang[$lng][141].': <input type="text" name="imie" size="40" class="formfld"></label><br><textarea name="tresc" rows="6" cols="60" class="formfld"></textarea><br><label>email: <input type="text" name="email" class="formfld"></label><br>
		<input type="submit" name="submit" value="'.$lang[$lng][142].'" class="formbtn" id="wyslijopinie" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)" onclick="rokclick(document.rocznik.rok);"></form></p>');
		html_end();
		break;
	}
	case 'zdjgru':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(isset($_COOKIE['zal'])&checkname()) mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Wyświetlenie Zdjęć grupowych", time="'.date("Y-m-d H:i:s").'"');
			else mysqlquerryc('insert into logs set user="niezalogowany", action="Wyświetlenie Zdjęć grupowych, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'"');
			echo('<h3>'.$lang[$lng][83].':</h3>');
			if(isset($_COOKIE['zal'])&checkname()) echo('<p><a href="'.$thisfile.'?zdjgru-add">'.$lang[$lng][84].'</a></p><hr>');
			if(isset($id2)){
				if(($id2=='s')|($id2=='l')|($id2=='k')) $cat=$id2;
				else $cat='s';
			}
			else $cat='s';
			$submenu=Array('s'=>$lang[$lng][85],'l'=>$lang[$lng][86],'k'=>$lang[$lng][87]);
			echo('<p>'.$lang[$lng][88].': ');
			foreach($submenu as $k=>$v){
				if($cat==$k) echo('<b>');
				echo('&nbsp;<a href="'.$thisfile.'?zdjgru,'.$k.'">'.$v.'</a>&nbsp;');
				if($cat==$k) echo('</b>');
			}
			echo('</p>');
			$res=mysqlquerryc('select * from zdjecia where osoby like "0,%" and cat="'.$cat.'" order by rok desc;');
			echo('<center><table border="0">');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				$pth=explode('.',$row['path']);
				echo('<tr><td><a name="gr'.$row['id'].'"></a><a href="'.$thisfile.'?zdjgru1,'.$row['id'].'"><img src="'.$pth[0].'m.'.$pth[1].'" usemap="#gru'.$row['id'].'"></a></td><td><font class="duzy">'.$row['rok'].'</font></td><td>'.$row['opis'].'</td></tr>');
			}
			echo('</table></center>');
		}
		else echo('<p class="alert">'.$lang[$lng][89].'!</p>');
		html_end();
		break;
	}
	case 'zdjgru-del':{
		html_start();
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))){
			if(preg_match('#,picdel,#',$currentuser['flags'])){
				if(isset($id2)){
					if(isset($id3)&($id3=='taknapewno')){
						$row=mysql_fetch_assoc(mysqlquerryc('select * from zdjecia where id='.htmlspecialchars($id2).' and osoby like "0,%" limit 1;'));
						if($row){
							$pth=explode('.',$row['path']);
							$min=$pth[0].'m.'.$pth[1];
							unlink($row['path']);
							unlink($min);
							if(mysqlquerryc('delete from zdjecia where id='.htmlspecialchars($id2).';')){
								mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Usunięto zdjęcie grupowe: '.$row['opis'].'", time="'.date("Y-m-d H:i:s").'";');
								echo('<p class="ok">Poprawnie usunięto zdjęcie "'.$row['opis'].'"</p><a href="'.$thisfile.'?zdjgru">'.$lang[$lng][90].'</a>');
							}
							else echo('<p class="alert">Nie udało sie usunąć zdjęcia nr '.$id2.'. Zgłoś ten błąd do adminsitratora.</p>');
						}
						else echo('<p class="alert">'.$lang[$lng][143].'</p>');
					}
					else{
						echo('<p>'.$lang[$lng][205].'?</p><p class="alert"><a href="'.$thisfile.'?zdjgru-del,'.$id2.',taknapewno">'.$lang[$lng][206].'</a></p><p class="ok"><a href="'.$thisfile.'?zdjgru1,'.$id2.'">'.$lang[$lng][207].'</a></p>');
					}
				}
				else echo('<p class="alert">'.$lang[$lng][208].'.</p>');
			}
			else{
				mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba usunięcia zdjęcia grupowego, mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
				echo('<p class="alert">'.$lang[$lng][43].'</p>');
			}
		}
		else{
			mysqlquerryc('insert into logs set user="niezalogowany", action="Próba dostępu do Usuwania zdjęć grupowych, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">'.$lang[$lng][89].'</a></p>');
		}
		html_end();
		break;
	}
	case 'zdjgru1':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(isset($id2)){
				$row=mysql_fetch_assoc(mysqlquerryc('select * from zdjecia where id='.htmlspecialchars($id2).';'));
				echo('<a name="gr'.$row['id'].'"></a><h1>'.$row['rok'].': '.$row['opis'].'</h1><a href="'.$thisfile.'?zdjgru,'.$row['cat'].'#gr'.$row['id'].'">'.$lang[$lng][90].'</a><br>
				<img src="'.$row['path'].'" usemap="#gru'.$row['id'].'"><br><p>'.$lang[$lng][91].': ');
				$os=explode(',',$row['osoby']);
				for($k=1;$k<(count($os)-1);$k+=1){
					if($k!=1) echo(', ');
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $rowo=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$os[$k].';'));
					else $rowo=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where visible=1 and id='.$os[$k].';'));
					if($rowo['ur']==0) $rur='?';
					else $rur=$rowo['ur'];
					if($rowo['zm']==0) $rzm='?';
					else $rzm=$rowo['zm'];
					if($rowo['sex']=='k') $rse=$lang[$lng][65];
					else $rse=$lang[$lng][66];
					$wynik='<nobr><a href="'.$thisfile.'?pokaz,one,'.$rowo['id'].'">'.$rowo['imie'].' '.$rowo['nazwisko'].'</a>';
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
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $osoba=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$os[$j].' limit 1'));
					else $osoba=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where visible=1 and id='.$os[$j].' limit 1'));
					echo('<area shape="poly" coords="'.$coo[($j-1)].'" href="'.$thisfile.'?pokaz,one,'.$os[$j].'" title="'.$osoba['imie'].' '.$osoba['nazwisko'].'">');
				}
				echo('</map>');
				if(isset($_COOKIE['zal'])&checkname()) mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Wyświetlenie zdjęcia'.htmlspecialchars($id2).': '.$row['opis'].'", time="'.date("Y-m-d H:i:s").'";');
				if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) echo('<a href="'.$thisfile.'?zdjgru-dodos,'.$row['path'].'">'.$lang[$lng][149].'</a> | <a href="'.$thisfile.'?zdjgru-del,'.$row['id'].'">'.$lang[$lng][150].'</a><br>');
			}
		}
		html_end();
		break;
	}
	case 'zdjgru-usos':{
		html_start();
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))){
			if(preg_match('#,grupersondel,#',$currentuser['flags'])){
				if(isset($id2)&isset($id3)){
					$zdjecie=mysql_fetch_assoc(mysqlquerryc('select id,osoby,coords from zdjecia where path like "%'.htmlspecialchars($id2).'%";'));
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
					if(mysqlquerryc('update zdjecia set osoby="'.$noso.'", coords="'.$ncrd.'" where id='.$zdjecie['id'].';')){
						mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Usunięto ludzia ze zdjęcia grupowego nr '.$zdjecie['id'].'", time="'.date("Y-m-d H:i:s").'";');
						echo('<p class="ok">'.$lang[$lng][151].'</p>');
					}
					else echo('<p class="ok">'.$lang[$lng][152].'</p>');
					echo('<a href="'.$thisfile.'?zdjgru-dodos,'.$id2.'">'.$lang[$lng][153].'</a>');
				}
				else echo('<p class="alert">'.$lang[$lng][154].'</p>');
			}
			else{
				mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba usuwania ludzi ze zdjęć grupowych, mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
				echo('<p class="alert">'.$lang[$lng][49].'</p>');
			}
		}
		else{
			mysqlquerryc('insert into logs set user="niezalogowany", action="Próba dostępu do Usuwania ludzi ze zdjęć grupowych, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">'.$lang[$lng][179].' <a href="'.$thisfile.'?login">'.$lang[$lng][180].'</a></p>');
		}
		html_end();
		break;
	}
	case 'zdjgru-edit':{
		html_start();
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))){
			if(preg_match('#,picedit,#',$currentuser['flags'])){
				if(isset($_POST['submit'])){
					if(mysqlquerryc('update zdjecia set rok='.htmlspecialchars($_POST['rok']).', opis="'.htmlspecialchars($_POST['opis']).'", cat="'.htmlspecialchars($_POST['cat']).'" where id='.htmlspecialchars($_POST['id']).';')){
						mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Edycja zdjęcia '.htmlspecialchars($_POST['opis']).'", time="'.date("Y-m-d H:i:s").'";');
						echo('<p class="ok">'.$lang[$lng][155].'</p>');
					}
					else echo('<p class="alert">'.$lang[$lng][157].'</p>');
					echo('<a href="'.$thisfile.'?zdjgru1,'.$_POST['id'].'">'.$lang[$lng][158].'</a>');
				}
				else echo('<p class="alert">'.$lang[$lng][156].'</p>');
			}
			else{
				mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba edycji zdjęcia grupowego, mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
				echo('<p class="alert">'.$lang[$lng][45].'</p>');
			}
		}
		else{
			mysqlquerryc('insert into logs set user="niezalogowany", action="Próba dostępu do Edycji zdjęć grupowych, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">'.$lang[$lng][179].' <a href="'.$thisfile.'?login">'.$lang[$lng][180].'</a></p>');
		}
		html_end();
		break;
	}
	case 'zdjgru-dodos':{
		html_start();
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))){
			if(isset($id2)){
				$actzdj=mysql_fetch_assoc(mysqlquerryc('select * from zdjecia where path like "%'.htmlspecialchars($id2).'%" limit 1;'));
				echo('<a href="'.$thisfile.'?zdjgru1,'.$actzdj['id'].'">Zobacz jak będzie wyglądać to zdjęcie</a>');
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
						if(mysqlquerryc($q)){
							mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Dodano ludzia do <a href=\"'.$thisfile.'?zdjgru1,'.$actzdj['id'].'\">zdjęcia nr '.$actzdj['id'].'</a>", time="'.date('Y-m-d H:i:s').'";');
							echo('<p class="ok">Poprawnie dodano</p>');
						}
						else echo('<p class="alert">Nie udało się dodać</p>');
					}
					else{
						mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba dodawania ludzi do zdjęć grupowych, mimo braku uprawnień", time="'.date('Y-m-d H:i:s').'";');
						echo('<p class="alert">'.$lang[$lng][47].'</p>');
					}
				}
				$actzdj=mysql_fetch_assoc(mysqlquerryc('select * from zdjecia where path like "%'.htmlspecialchars($id2).'%" limit 1;'));
				if($actzdj){
					$imsize=getimagesize($actzdj['path']);
					echo('<tr><td colspan="3"><form name=zdjgruedit" action="'.$thisfile.'?zdjgru-edit" method="POST"><label>rok:<input type="text" name="rok" size="4" class="formfld" maxlength="4" value="'.$actzdj['rok'].'"></label> <label>opis:<input type="text" name="opis" class="formfld" value="'.$actzdj['opis'].'" size="80"></label> <label title="s, l lub k">typ: <input type="tekst" name="cat" class="formfld" size="1" maxlength="1" value="'.$actzdj['cat'].'"></label><input type="hidden" name="id" value="'.$actzdj['id'].'"> <input type="submit" name="submit" value="Zapisz" class="formbtn" id="zdjeditgru" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></form></td></tr>');
					echo('<form name="stg2" action="'.$thisfile.'?zdjgru-dodos,'.$id2.'" method="POST"><table border="0" width="100%"><tr><td colspan="3" text-align="center">');
					//echo('<canvas id="theCanvas" width="'.$imsize[0].'" height="'.$imsize[1].'"></canvas>');
					echo('<div id="pointer_div" onclick="point_it(event)" style = "background-image:url(\''.$actzdj['path'].'\');width:'.$imsize[0].'px;height:'.$imsize[1].'px;"></td></tr>');
					echo('<input type="hidden" name="zdjname" value="'.$actzdj['path'].'"><input type="hidden" name="rok" value="'.$actzdj['rok'].'">');
					echo('<tr><td valign="top"><select class="selectspecial" id="kto" name="kto" width="50%"><option value="0">Nieznany</option>');
					$res=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie order by nazwisko,imie,ur;');
					for($i=0;$i<mysql_num_rows($res);$i+=1){
						$row=mysql_fetch_assoc($res);
						echo('<option value="'.$row['id'].'">');
						for($j=0;$j<$row['pok'];$j+=1) echo('-');
						echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
					}
					echo('</select><script type=\'text/javascript\'>$(\'#kto\').select2();</script><br>');
					for($i=0;$i<8;$i+=1){ //8 points should be enough for everyone...
						echo('<input type="text" name="posx'.$i.'" size="4" class="formfld" value="0"><input type="text" name="posy'.$i.'" size="4" class="formfld" value="0"><br>');
					}
					echo('<input type="submit" name="submit" class="formbtn" value="Dodaj" id="dodosdozdj" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></td><td>');
					$dous=mysql_fetch_assoc(mysqlquerryc('select osoby,coords from zdjecia where path like "%'.htmlspecialchars($id2).'%";'));
					$oso=explode(',',$dous['osoby']);
					for($i=0;$i<(floor(count($oso))+1);$i+=1){
						if($oso[$i]!=0){
							echo('<font size="2">'.linkujludzia($oso[$i],2).' <a href="'.$thisfile.'?zdjgru-usos,'.$id2.','.$oso[$i].'">Usuń</a></font><br>');
						}
					}
					echo('</td><td>');
					for($i=(floor(count($oso))+1);$i<count($oso);$i+=1){
						if($oso[$i]!=0){
							echo('<font size="2">'.linkujludzia($oso[$i],2).' <a href="'.$thisfile.'?zdjgru-usos,'.$id2.','.$oso[$i].'">Usuń</a></font><br>');
						}
					}
					echo('</td></tr></table></form>');
				}
				else echo('<p class="alert">'.$lang[$lng][209].'</p>');
			}
			else echo('<p class="alert">'.$lang[$lng][213].'</p>');
		}
		else{
			mysqlquerryc('insert into logs set user="niezalogowany", action="Próba dostępu do Dodawania ludzi do zdjęć grupowych, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">'.$lang[$lng][179].' <a href="'.$thisfile.'?login">'.$lang[$lng][180].'</a></p>');
		}
		html_end();
		break;
	}
	case 'zdjgru-add':{
		html_start();
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))){
			if(preg_match('#,picadd,#',$currentuser['flags'])){
				if(isset($_POST['stage1'])){
					if(is_uploaded_file($_FILES['zdj']['tmp_name'])){
						$newname='gru'.date('U'); //miliseconds for unique name
						if($_FILES['zdj']['size']<=$_POST['MAX_FILE_SIZE']){ 
							move_uploaded_file($_FILES['zdj']['tmp_name'], 'gfx/'.$newname.'.jpg');
							$im=imagecreatefromjpeg('gfx/'.$newname.'.jpg');
							$imsize=getimagesize('gfx/'.$newname.'.jpg');
							$minh=($imsize[1]*$width_min)/$imsize[0];
							$nih=($imsize[1]*$width_duz)/$imsize[0];
							$nim=imagecreatetruecolor($width_duz,$nih);
							$min=imagecreatetruecolor($width_min,$minh);
							imagecopyresampled($nim,$im,0,0,0,0,$width_duz,$nih,$imsize[0],$imsize[1]);
							imagecopyresampled($min,$im,0,0,0,0,$width_min,$minh,$imsize[0],$imsize[1]);
							imagejpeg($nim,'gfx/'.$newname.'.jpg');
							imagejpeg($min,'gfx/'.$newname.'m.jpg');
							echo('<form name="stg2" action="'.$thisfile.'?zdjgru-add" method="POST"><table border="0"><tr><td><div id="pointer_div" onclick="point_it(event)" style = "background-image:url(\'gfx/'.$newname.'.jpg\');width:'.$width_duz.'px;height:'.$nih.'px;"></td></tr>');
							echo('<input type="hidden" name="zdjname" value="'.$newname.'"><input type="hidden" name="rok" value="'.$_POST['rok'].'"><input type="hidden" name="opis" value="'.$_POST['opis'].'">');
							echo('<tr><td><select class="selectspecial" id="kto1" name="kto1"><option value="0">'.$lang[$lng][113].'</option>');
							$res=mysqlquerryc('select id,imie,nazwisko,ur,pok from ludzie order by nazwisko,imie');
							for($i=0;$i<mysql_num_rows($res);$i+=1){
								$row=mysql_fetch_assoc($res);
								echo('<option value="'.$row['id'].'">');
								for($j=0;$j<$row['pok'];$j+=1) echo('-');
								echo($row['imie'].' '.$row['nazwisko'].' ('.$row['ur'].')</option>');
							}
							echo('</select><script type=\'text/javascript\'>$(\'#kto1\').select2();</script><br>');
							for($i=0;$i<8;$i+=1){
								echo('<input type="text" name="posx'.$i.'" size="4" class="formfld" value="0"><input type="text" name="posy'.$i.'" size="4" class="formfld" value="0"><br>');
							}
							echo('<input type="submit" name="stage2" class="formbtn" value="'.$lang[$lng][173].'" id="dodozdj" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></td></tr></table></form>');
						}
						else{
							echo($lang[$lng][191].': <strong>'.$_FILES['zdj']['name'].'</strong> '.$lang[$lng][210].' '.($_POST['MAX_FILE_SIZE']/1024/1024).' MiB<br>');
						}	
					}
					else echo('<p class="alert">'.$lang[$lng][211].'</p>');
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
							if(mysqlquerryc($q)){
								mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Dodano zdjęcia grupowe '.htmlspecialchars($_POST['zdjname']).'", time="'.date("Y-m-d H:i:s").'";');
								echo('<p class="ok">Poprawnie dodano zdjęcie</p><a href="'.$thisfile.'?zdjgru-dodos,'.$_POST['zdjname'].'">Dodaj jeszcze jedną osobę do tego zdjęcia</a>');
							}
							else echo('<p class="alert">'.$lang[$lng][175].'</p>');
						}
						else echo('<p class="alert">'.$lang[$lng][216].'</p>');
					}
					else echo('<p class="alert">'.$lang[$lng][217].'</p>');
				}
				else{ //form
					echo('<form enctype="multipart/form-data" action="'.$thisfile.'?zdjgru-add" method="POST">
						<input type="hidden" name="MAX_FILE_SIZE" value="100000000" />
						<table border="0"><tr><td>'.$lang[$lng][212].':</td><td>Rok</td><td>&nbsp;</td></tr><tr><td><input class="formfld" name="zdj" type="file" /></td><td><input type="text" name="rok" class="formfld" size="4" maxlength="4"></td>
						<td><input class="formbtn" id="grudod" name="stage1" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)" type="submit" value="'.$lang[$lng][142].'" /></td></tr><tr><td colspan="2"><textarea rows="5" cols="60" name="opis" class="formfld"></textarea></td></tr></table></form>');
				}
			}
			else{
				mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba dodania zdjęcia grupowego, mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
				echo('<p class="alert">'.$lang[$lng][41].'</p>');
			}
		}
		else{
			mysqlquerryc('insert into logs set user="niezalogowany", action="Próba dostępu do Dodawania zdjęć grupowych, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">'.$lang[$lng][179].' <a href="'.$thisfile.'?login">'.$lang[$lng][180].'</a></p>');
		}
		html_end();
		break;
	}
	case 'zdj':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(isset($id2)){
				if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))){
					if(isset($_POST['del'])){
						if(preg_match('#,picdel,#',$currentuser['flags'])){
							$zdjdel=mysql_fetch_assoc(mysqlquerryc('select * from zdjecia where id='.$_POST['id'].';'));
							if(mysqlquerryc('delete from zdjecia where id='.$_POST['id'].';')){
								unlink($zdjdel['path']);
								echo('<p class="ok">'.$lang[$lng][159].'</p>');
								mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Usunięto zdjęcie", time="'.date("Y-m-d H:i:s").'";');
							}
							else echo('<p class="alert">'.$lang[$lng][160].'</p>');
						}
						else{
							mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba usunięcia zdjęcia '.$zdjdel['path'].', mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
							echo('<p class="alert">'.$lang[$lng][43].'</p>');
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
								if(mysqlquerryc($q)){
									mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Edycja zdjęcia", time="'.date("Y-m-d H:i:s").'";');
									echo('<p class="ok">'.$lang[$lng][162].'</p>');
								}
								else echo('<p class="alert">'.$lang[$lng][163].'</p>');
							}
							else echo('<p class="alert">'.$lang[$lng][161].'</p>');
						}
						else{
							mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Próba edycji zdjecia, mimo braku uprawnień", time="'.date("Y-m-d H:i:s").'";');
							echo('<p class="alert">'.$lang[$lng][45].'</p>');
						}
					}
				}
				mysqlquerryc('insert into logs set user="niezalogowany", action="Wyświetlenie wszystkich zdjęć '.linkujludzia($id2,2).', z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'"');
				echo('<h3>'.linkujludzia($id2,2).' - '.$lang[$lng][166].'</h3>');
				$res=mysqlquerryc('select * from zdjecia where osoby="'.htmlspecialchars($id2).'" order by rok desc,id desc;');
				echo('<table border="0">');
				for($i=0;$i<mysql_num_rows($res);$i+=1){
					$row=mysql_fetch_assoc($res);
					echo('<tr><td><img src="'.$row['path'].'"></td><td>');
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))){
						echo('<form name="zdj'.$id2.'" action="'.$thisfile.'?zdj,'.$id2.'" method="POST"><input type="text" class="formfld" name="rok" value="'.$row['rok'].'"></td><td>
						<input type="hidden" name="id" value="'.$row['id'].'">
						<label><input type="checkbox" name="slub" class="formfld"');
						if($row['slub']==1) echo(' checked="checked"');
						echo('> Ślubne</label> <label><input type="checkbox" name="komunia" class="formfld"');
						if($row['komunia']==1) echo(' checked="checked"');
						echo('> Komunia</label></td><td>
						<input type="submit" name="zmien" value="'.$lang[$lng][185].'" class="formbtn" id="zdjid'.$row['id'].'" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)">
						<input type="submit" name="del" value="'.$lang[$lng][184].'" class="formbtn" id="zdjdel'.$row['id'].'" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></form>');
					}
					else{
						echo('<h2>'.$row['rok']);
						if($row['slub']==1) echo(', '.$lang[$lng][181]);
						echo('</h2>');
					}
					echo('</td></tr>');
				}
				echo('</table>');
				$zdjgru=mysqlquerryc('select * from zdjecia where osoby like "%,'.htmlspecialchars($id2).',%" order by rok;');
				if(mysql_num_rows($zdjgru)>0){
					echo('<hr><h3>'.$lang[$lng][182].':</h3>');
					for($l=0;$l<mysql_num_rows($zdjgru);$l+=1){
						$row2=mysql_fetch_assoc($zdjgru);
						$pat=explode('.',$row2['path']);
						$min=$pat[0].'m.'.$pat[1];
						echo('<a href="'.$thisfile.'?zdjgru1,'.$row2['id'].'" title="'.$row2['opis'].' '.$row2['rok'].'"><img border="2" class="lud" src="'.$min.'"></a> ');
					}
				}
			}
			else echo('<p class="alert">'.$lang[$lng][183].'</p>');
		}
		html_end();
		break;
	}
	case 'users':{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()){
			if(($id2=='edit')&preg_match('#,useredit,#',$currentuser['flags'])){
				if(isset($_POST['edit'])){
					if(mysqlquerryc('update users set name="'.$_POST['name'].'", pass="'.$_POST['pass'].'", flags="'.$_POST['flags'].'", description="'.$_POST['descr'].'" where id='.$_POST['id'].';')) echo('<p class="ok">Poprawnie zmieniono</p>');
					else echo('<p class="alert">Nie udało się zmienić</p>');
				}
				if(isset($_POST['del'])){
					if($_POST['id']==$currentuser['id']) echo('<p class="alert">Nie możesz usunąć siebie!</p>');
					else{
						if(mysqlquerryc('delete from users where id='.htmlspecialchars($_POST['id']).';')) echo('<p class="ok">Poprawnie usunięto użytkownika</p>');
						else echo('<p class="alert">Nie udało się usunąć uzytkownika</p>');
					}
				}
			}
			if(($id2=='add')&preg_match('#,useradd,#',$currentuser['flags'])){
				if(isset($_POST['submit'])){
					if(mysqlquerryc('insert into users set name="'.htmlspecialchars($_POST['newname']).'", pass="'.md5(htmlspecialchars($_POST['newpass']).'dupa').'", flags="0,0", description="'.htmlspecialchars($_POST['descri']).'";')) echo('<p class="ok">Poprawnie dodano użytkownika</p>');
					else echo('<p class="alert">Nie udało się dodać użytkownika</p>');
				}
				else{
					echo('<form name="adduser" action="'.$thisfile.'?users,add" method="post"><label>nazwa:<input type="text" class="formfld" name="newname" size="15"></label><label>hasło:<input type="text" name="newpass" class="formfld" size="15"></label><label>descr:<input type="text" name="descri" value="no" class="formfld" size="15"></label><input type="submit" name="submit" value="Zapisz" class="formbtn" id="useradd-btn" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></form>');
				}
			}
			echo('<p><a href="'.$thisfile.'?users,add">Dodaj nowego użytkownika</a></p>');
			$res=mysqlquerryc('select * from users');
			echo('<table border="1"><tr><td>name</td><td>hash</td><td>flags</td><td>description</td><td>actions</td></tr>');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				if(preg_match('#,useredit,#',$currentuser['flags'])) echo('<tr><form method="POST" name="user-'.$row['name'].'" action="'.$thisfile.'?users,edit"><input type="hidden" name="id" value="'.$row['id'].'"><td><input type="text" name="name" class="formfld" value="'.$row['name'].'" size="10"></td><td><input type="text" name="pass" value="'.$row['pass'].'" size="32" class="formfld"></td><td><input type="text" name="flags" value="'.$row['flags'].'" class="formfld" size="120"></td><td><input type="text" name="descr" value="'.$row['description'].'" class="formfld" size="50"></td><td><input type="submit" name="edit" value="Edytuj" class="formbtn" id="user-'.$row['id'].'-edit" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"> <input type="submit" name="del" value="Usuń" class="formbtn" id="user-'.$row['id'].'-del" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></td></form></tr>');
				else echo('<tr><td>'.$row['name'].'</td><td>'.$row['pass'].'</td><td>'.$row['flags'].'</td><td>'.$row['description'].'</td><td>brak</td></tr>');
			}
			echo('</table>');
		}
		else{
			mysqlquerryc('insert into logs set user="niezalogowany", action="Próba dostępu do Użytkowników, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">'.$lang[$lng][179].' <a href="'.$thisfile.'?login">'.$lang[$lng][180].'</a></p>');
		}
		html_end();
		break;
	}
	case 'settings':{ 
		html_start();
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))){
			if(isset($_POST['submit'])){
				if(mysqlquerryc('update settings set edit_pp='.htmlspecialchars($_POST['edit_pp']).', site_name="'.htmlspecialchars($_POST['site_name']).'", main_opis="'.strip_tags($_POST['main_opis'],'<a><b><i><u>').'", all_podmenu="'.strip_tags($_POST['all_podmenu'],'<a><b><i><u>').'", about="'.strip_tags($_POST['about'],'<a><b><i><u><table><tr><td><img><li><center><p><h3><br>').'", admin_mail="'.$_POST['admin_mail'].'", stats_ll='.$_POST['stats_ll'].';')){
					echo('<p class="ok">'.$lang[$lng][155].'</p><script type="text/javascript">
					document.location="'.$thisfile.'?settings";
					</script>');
					mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Zmiana ustawień", time="'.date("Y-m-d H:i:s").'";');
				}
				else echo('<p class="alert">'.$lang[$lng][157].'</p>');
			}
			echo('<form name="setts" action="'.$thisfile.'?settings" method="POST"><table border="0"><tr><td>'.$lang[$lng][167].'</td><td><textarea name="main_opis" rows="3" cols="100" class="formfld">'.$settings['main_opis'].'</textarea></td></tr>');
			echo('<tr><td>'.$lang[$lng][168].'</td><td><textarea name="about" rows="8" cols="100" class="formfld">'.$settings['about'].'</textarea></td></tr>');
			echo('<tr><td>'.$lang[$lng][169].'</td><td><input type="text" name="edit_pp" size="3" maxlength="3" value="'.$settings['edit_pp'].'" class="formfld"></td></tr>');
			echo('<tr><td>'.$lang[$lng][170].'</td><td><input type="text" name="site_name" size="20" maxlength="20" value="'.$settings['site_name'].'" class="formfld"</td></tr>');
			echo('<tr><td>'.$lang[$lng][171].'</td><td><input type="text" name="admin_mail" size="30" maxlength="30" value="'.$settings['admin_mail'].'" class="formfld"></td></tr>');
			echo('<tr><td>'.$lang[$lng][172].'</td><td><input type="text" name="stats_ll" size="3" maxlength="2" value="'.$settings['stats_ll'].'" class="formfld"></td></tr>');
			echo('<tr><td colspan="2" align="center"><input type="submit" name="submit" value="'.$lang[$lng][173].'" id="settzapisz" class="formbtn" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></td></tr></table></form>');
		}
		else{
			mysqlquerryc('insert into logs set user="niezalogowany", action="Próba dostępu do Ustawień, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
			echo('<p class="alert">'.$lang[$lng][179].' <a href="'.$thisfile.'?login">'.$lang[$lng][180].'</a></p>');
		}
		html_end();
		break;
	}
	case 'logs':{
		html_start();
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))){
			if(strlen($id3)<1) $id3=0;
			$il=mysql_fetch_array(mysqlquerryc('select count(*) from logs where user like "%'.$id2.'%" and action not like "%66.249.%" and action not like "%81.15.212.181%";'));
			$ilstr=floor($il[0]/200)+1;
			$res=mysqlquerryc('select * from logs where user like "%'.$id2.'%" and action not like "%66.249.%" and action not like "%81.15.212.181%" order by time desc limit '.($id3*200).',200;');
			$res2=mysqlquerryc('select distinct(user) as users from logs;');
			echo('<center><table border="1"><tr><td colspan="3">');
			if($id3>=1) echo('<a href="'.$thisfile.'?logs,'.$id2.','.($id3-1).'">-◄'.$lang[$lng][164].'-</a>');
			if($id3==0) echo('<b>');
			echo('<a href="'.$thisfile.'?logs,'.$id2.',0">-1-</a>');
			if($id3==0) echo('</b>');
			if($id3>6) echo('...');
			if(($id3>6)&($id3<($ilstr-6))) for($i=($id3-5);$i<($id3+5);$i+=1){
				if($id3==$i) echo('<b>');
				echo('<a href="'.$thisfile.'?logs,'.$id2.','.$i.'">-'.($i+1).'-</a>');
				if($id3==$i) echo('</b>');
			}
			else if($id3<($ilstr/2))for($i=1;$i<($id3+5);$i+=1){
				if($id3==$i) echo('<b>');
				echo('<a href="'.$thisfile.'?logs,'.$id2.','.$i.'">-'.($i+1).'-</a>');
				if($id3==$i) echo('</b>');
			}
			echo('<a href="'.$thisfile.'?logs,'.$id2.','.($id3+1).'">'.$lang[$lng][165].'►</td></tr><tr><td colspan="3">');
			if(strlen($id2)<3) echo('<b>');
			echo('&nbsp;<a href="'.$thisfile.'?logs">Wszystkie</a>&nbsp;');
			if(strlen($id2)<3) echo('</b>');
			for($d=0;$d<mysql_num_rows($res2);$d+=1){
				$row2=mysql_fetch_assoc($res2);
				if($id2==$row2['users']) echo('<b>');
				echo('&nbsp;<a href="'.$thisfile.'?logs,'.$row2['users'].'">'.$row2['users'].'</a>&nbsp;');
				if($id2==$row2['users']) echo('</b>');
			}
			echo('</td></tr><tr><td>time</td><td>user</td><td>action</td></tr>');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				echo('<tr><td>'.$row['time'].'</td><td>'.$row['user'].'</td><td>'.$row['action'].'</td></tr>');
			}
			echo('</table></center>');
		}
		else{
			echo('<p class="alert">'.$lang[$lng][179].' <a href="'.$thisfile.'?login">'.$lang[$lng][180].'</a></p>');
			mysqlquerryc('insert into logs set user="niezalogowany", action="Próba dostępu do Logów, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
		}
		html_end();
		break;
	}
	case 'ipban':{
		html_start();
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu3view,#',$currentuser['flags']))){
			if(($id2=='edit')&preg_match('#,banedit,#',$currentuser['flags'])){
				if(isset($_POST['edit'])){
					if(mysqlquerryc('update banip set owner="'.$_POST['owner'].'", reason="'.$_POST['reason'].'" where ip='.$_POST['ip'].';')) echo('<p class="ok">Poprawnie zmieniono</p>');
					else echo('<p class="alert">Nie udało się zmienić</p>');
				}
				if(isset($_POST['del'])){
					if(mysqlquerryc('delete from ipban where ip='.htmlspecialchars($_POST['ip']).';')) echo('<p class="ok">Poprawnie usunięto bana z ip</p>');
					else echo('<p class="alert">Nie udało się usunąć uzytkownika</p>');
				}
			}
			if(($id2=='add')&preg_match('#,banadd,#',$currentuser['flags'])){
				if(isset($_POST['submit'])){
					if(mysqlquerryc('insert into banip set ip="'.htmlspecialchars($_POST['newip']).'", owner="'.htmlspecialchars($_POST['newowner']).'", reason="'.htmlspecialchars($_POST['newreason']).'", date="'.date('Y-m-d').'";')) echo('<p class="ok">Poprawnie dodano bana</p>');
					else echo('<p class="alert">Nie udało się dodać bana</p>');
				}
				else{
					echo('<form name="addban" action="'.$thisfile.'?ipban,add" method="post"><label>ip:<input type="text" class="formfld" name="newip" size="15"></label><label>owner:<input type="text" name="newowner" class="formfld" size="15"></label><label>reason:<input type="text" name="newreason" class="formfld" size="30"></label><input type="submit" name="submit" value="Zapisz" class="formbtn" id="banadd-btn" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></form>');
				}
			}
			echo('<p><a href="'.$thisfile.'?ipban,add">Dodaj nowego użytkownika</a></p>');
			$res=mysqlquerryc('select * from banip');
			echo('<table border="1"><tr><td>ip</td><td>owner</td><td>reason</td><td>date</td><td>actions</td></tr>');
			for($i=0;$i<mysql_num_rows($res);$i+=1){
				$row=mysql_fetch_assoc($res);
				if(preg_match('#,banedit,#',$currentuser['flags'])) echo('<tr><form method="POST" name="ban-'.$row['ip'].'" action="'.$thisfile.'?ipban,edit"><input type="hidden" name="date" value="'.$row['date'].'"><input type="hidden" name="ip" value="'.$row['ip'].'"><td>'.$row['ip'].'</td><td><input type="text" name="owner" class="formfld" value="'.$row['owner'].'" size="15"></td><td><input type="text" name="reason" value="'.$row['reason'].'" size="32" class="formfld"></td><td>'.$row['date'].'</td><td><input type="submit" name="edit" value="Edytuj" class="formbtn" id="ban-'.$row['id'].'-edit" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"> <input type="submit" name="del" value="Usuń" class="formbtn" id="ban-'.$row['id'].'-del" onmouseover="btnh(this.id)" onmouseout="btnd(this.id)"></td></form></tr>');
				else echo('<tr><td>'.$row['ip'].'</td><td>'.$row['owner'].'</td><td>'.$row['reason'].'</td><td>'.$row['date'].'</td><td>brak</td></tr>');
			}
			echo('</table>');
		}
		else{
			echo('<p class="alert">'.$lang[$lng][179].' <a href="'.$thisfile.'?login">'.$lang[$lng][180].'</a></p>');
			mysqlquerryc('insert into logs set user="niezalogowany", action="Próba dostępu do Listy Banów, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
		}
		html_end();
		break;
	}
	case 'md5':{
		html_start();
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu3view,#',$currentuser['flags']))) echo('<p class="ok">MD5: '.md5($id2.'dupa').'</p><p class="ok">SHA256: '.hash('sha256',$id2.'dupa').'</p>');
		else echo('<p class="alert">MD5: '.$lang[$lng][89].'</p>');
		html_end();
		break;
	}
	case 'files':{
		html_start();
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu3view,#',$currentuser['flags']))){
			echo('<a href="index.php">index.php</a><br>
				  <a href="rodzina.css">rodzina.css</a><br>
				  <a href="rodzina.js">rodzina.js</a><br>');
		}
		else echo('<p class="alert">'.$lang[$lng][89].'</p>');
		html_end();
		break;
	}
	case 'cookies':{
		html_start();
		echo('<p>'.$lang[$lng][194].'</p>');
		html_end();
		break;
	}
	case 'druk':{
		html_start();
		echo('<h2>Jak wydrukować drzewo genealogiczne na wielu stronach?</h2>
		<p>1. Otwieramy plik w programie Paint</p>
		<p>2. Wybieramy z menu Plik -> Drukuj -> Ustawienia strony<br><img src="druk1.png"></p>
		<p>3. W pulu Dopasuj do wpisujemy na ilu stronach ma być wydrukowane, n.p. 9<br><img src="druk2.png"></p>
		<p>4. Po kliknięciu OK, normalnie drukujemy.</p>');
		html_end();
		break;
	}
	default:{
		html_start();
		if(isset($_COOKIE['zal'])&checkname()) mysqlquerryc('insert into logs set user="'.$_COOKIE['zal'].'", action="Spowodował 404", time="'.date("Y-m-d H:i:s").'";');
		else mysqlquerryc('insert into logs set user="niezalogowany", action="Spowodował 404, z ip '.$_SERVER['REMOTE_ADDR'].'", time="'.date("Y-m-d H:i:s").'";');
		echo('<p class="alert">404: '.$lang[$lng][89].'</p>');
		html_end();
	}
}
mysql_close();
?>
