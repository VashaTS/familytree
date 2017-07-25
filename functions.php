<?php

function mysqlquerryc($inp){
	global $qc;
	$qc+=1;
	return mysql_query($inp);
}

function checkname(){ //login check
	$res=mysqlquerryc('select name from users;');
	$pasuje=false;
	$pasuje2=false;
	for($i=0;$i<mysql_num_rows($res);$i+=1){
		$row=mysql_fetch_assoc($res);
		if($_COOKIE['zal']==$row['name']) $pasuje=true;
	}
	if($pasuje==true){
		$row2=mysql_fetch_assoc(mysqlquerryc('select ssid,ssid_time from users where name="'.$_COOKIE['zal'].'";'));
		if(($_COOKIE['ssid']=$row2['ssid'])) $pasuje2=true;
	}
	return $pasuje2;
}


function iso2utf(){ //for pdf
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
		case 'ni': $new=$imie.'ego'; break; //antoni
		case 'ty': //walenty
		case 'zy': //alojzy
		case 'cy': //ignacy
		case 'ry': $new=substr($imie,0,strlen($imie)-1).'ego'; break; //cezary
		case 'ek': $new=substr($imie,0,strlen($imie)-2).'ka'; break; //marek
		case 'er': $new=substr($imie,0,strlen($imie)-2).'ra'; break; //kacper
		default: $new=$imie.'a'; break;
	}}
	return $new;
}

function plfirstup($string, $e ='utf-8') { // żak, łuczak, etc
        if (function_exists('mb_strtoupper') && function_exists('mb_substr') && !empty($string)) { 
            $string = mb_strtolower($string, $e); 
            $upper = mb_strtoupper($string, $e); 
            preg_match('#(.)#us', $upper, $matches); 
            $string = $matches[1] . mb_substr($string, 1, mb_strlen($string, $e), $e); 
        } else { 
            $string = ucfirst($string); 
        } 
        return $string; 
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
function linkujludzia($uid,$style=1){ // inline - do szukajki, zdjęć, rodziców, żon/mężów, dzieci
	global $lang,$lng,$currentuser,$qc;
	if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $row=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$uid.';'));
	else $row=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where visible=1 and id='.$uid.';'));
	switch($style){
		case 1:{
			if($row['ur']==0) $rur='?';
				else $rur=$row['ur'];
				if($row['zm']==0) $rzm='?';
				else $rzm=$row['zm'];
				if($row['sex']=='k') $rse=$lang[$lng][65];
				else $rse=$lang[$lng][66];
				$wynik='<a';
				if($row['visible']==0) $wynik.=' class="nvperson"';
				$wynik.=' href="'.$thisfile.'?pokaz,one,'.$row['id'].'">'.$row['imie'].' '.$row['nazwisko'];
				if($rzm=='?'){
					if($rur!='?') $wynik.=' ('.$rur.')';
				}
				else{
				$wynik.=' ('.$rur.'-'.$rzm.')';	
				}
				$wynik.='</a>';
				$visr1=mysql_fetch_assoc(mysqlquerryc('select visible from ludzie where id='.$row['rodzic1'].';'));
				$visr2=mysql_fetch_assoc(mysqlquerryc('select visible from ludzie where id='.$row['rodzic2'].';'));
				if(($row['rodzic1']!=0)&($row['rodzic2']!=0)){
					if((($visr1['visible']==1)&($visr2['visible']==1))|((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags'])))){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r1=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where id='.$row['rodzic1'].';'));
						else $r1=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where visible=1 and id='.$row['rodzic1'].';'));
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r2=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where id='.$row['rodzic2'].';'));
						else $r2=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where visible=1 and id='.$row['rodzic2'].';'));
						$wynik.=', '.$rse.' '.odmiana_m($r1['imie']).' '.$lang[$lng][135].' '.odmiana_k($r2['imie']);
					}
					else if(($visr1['visible']==1)|((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags'])))){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r1=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where id='.$row['rodzic1'].';'));
						else $r1=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where visible=1 and id='.$row['rodzic1'].';'));
						$wynik.=', '.$rse.' '.odmiana_m($r1['imie']);
					}
					else if(($visr2['visible']==1)|((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags'])))){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r2=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where id='.$row['rodzic2'].';'));
						else $r2=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where visible=1 and id='.$row['rodzic2'].';'));
						$wynik.=', '.$rse.' '.odmiana_k($r2['imie']);
					}
				}
				else if($row['rodzic1']!=0){
					$visr1=mysql_fetch_assoc(mysqlquerryc('select visible from ludzie where id='.$row['rodzic1'].';'));
					if(($visr1['visible']==1)|((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags'])))){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r1=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where id='.$row['rodzic1'].';'));
						else $r1=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where visible=1 and id='.$row['rodzic1'].';'));
						$wynik.=', '.$rse.' '.odmiana_m($r1['imie']);
					}
				}
				else if($row['rodzic2']!=0){
					$visr2=mysql_fetch_assoc(mysqlquerryc('select visible from ludzie where id='.$row['rodzic2'].';'));
					if(($visr2['visible']==1)|((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags'])))){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r2=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where id='.$row['rodzic2'].';'));
						else $r2=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where visible=1 and id='.$row['rodzic2'].';'));
						$wynik.=', '.$rse.' '.odmiana_k($r2['imie']);
					}
				}
			break;
		}
		case 2:{ 
			if($row['ur']==0) $rur='?';
				else $rur=$row['ur'];
				if($row['zm']==0) $rzm='?';
				else $rzm=$row['zm'];
				if($row['sex']=='k') $rse=$lang[$lng][65];
				else $rse=$lang[$lng][66];
				$wynik='<a';
				if($row['visible']==0) $wynik.=' class="nvperson"';
				$wynik.=' href="'.$thisfile.'?pokaz,one,'.$row['id'].'">'.$row['imie'].' '.$row['nazwisko'].'</a>';
				if($rzm=='?'){
					if($rur!='?') $wynik.=' ('.$rur.')';
				}
				else{
				$wynik.=' ('.$rur.'-'.$rzm.')';	
				} 
			break;
		}
		case 3:{
			if($row['ur']==0) $rur='?';
				else $rur=$row['ur'];
				if($row['zm']==0) $rzm='?';
				else $rzm=$row['zm'];
				if($row['sex']=='k') $rse=$lang[$lng][65];
				else $rse=$lang[$lng][66];
				$wynik='<a href="'.$thisfile.'?pokaz,one,'.$row['id'].'">'.$row['imie'].' '.$row['nazwisko'];
				if($rzm=='?'){
					if($rur!='?') $wynik.=' ('.$rur.')';
				}
				else{
				$wynik.=' ('.$rur.'-'.$rzm.')';	
				}
				$wynik.='</a>';
				$visr1=mysql_fetch_assoc(mysqlquerryc('select visible from ludzie where id='.$row['rodzic1'].';'));
				$visr2=mysql_fetch_assoc(mysqlquerryc('select visible from ludzie where id='.$row['rodzic2'].';'));
				if(($row['rodzic1']!=0)&($row['rodzic2']!=0)){
					if((($visr1['visible']==1)&($visr2['visible']==1))|((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags'])))){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r1=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where id='.$row['rodzic1'].';'));
						else $r1=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where visible=1 and id='.$row['rodzic1'].';'));
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r2=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where id='.$row['rodzic2'].';'));
						else $r2=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where visible=1 and id='.$row['rodzic2'].';'));
						$wynik.=', '.$rse.' '.odmiana_m($r1['imie']).' '.$lang[$lng][135].' '.odmiana_k($r2['imie']);
					}
					else if(($visr1['visible']==1)|((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags'])))){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r1=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where id='.$row['rodzic1'].';'));
						else $r1=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where visible=1 and id='.$row['rodzic1'].';'));
						$wynik.=', '.$rse.' '.odmiana_m($r1['imie']);
					}
					else if(($visr2['visible']==1)|((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags'])))){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r2=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where id='.$row['rodzic2'].';'));
						else $r2=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where visible=1 and id='.$row['rodzic2'].';'));
						$wynik.=', '.$rse.' '.odmiana_k($r2['imie']);
					}
					//seme else as in case 1
				}
				else if($row['rodzic1']!=0){
					$visr1=mysql_fetch_assoc(mysqlquerryc('select visible from ludzie where id='.$row['rodzic1'].';'));
					if(($visr1['visible']==1)|((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags'])))){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r1=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where id='.$row['rodzic1'].';'));
						else $r1=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where visible=1 and id='.$row['rodzic1'].';'));
						$wynik.=', '.$rse.' '.odmiana_m($r1['imie']);
					}
				}
				else if($row['rodzic2']!=0){
					$visr2=mysql_fetch_assoc(mysqlquerryc('select visible from ludzie where id='.$row['rodzic2'].';'));
					if(($visr2['visible']==1)|((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags'])))){
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r2=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where id='.$row['rodzic2'].';'));
						else $r2=mysql_fetch_assoc(mysqlquerryc('select imie from ludzie where visible=1 and id='.$row['rodzic2'].';'));
						$wynik.=', '.$rse.' '.odmiana_k($r2['imie']);
					}
				}
				if(strlen($row['adres'])>4) $wynik.=', '.$row['adres'];
			break;
		}
	}
	return $wynik;
}
function dzieciizona($uid,$ws){ //do famuły
	global $byl,$qc;
	if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $row=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$uid.';'));
	else $row=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where visible=1 and id='.$uid.';'));
	$aa=rand(1000,9999);
	mysqlquerryc('update ludzie set byl=1 where id='.$uid.';');
	if($row['sex']=='m'){
		if($row['zona1']!=0){
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $mz=mysql_fetch_assoc(mysqlquerryc('select id from ludzie where id='.$row['zona1'].';'));
			else $mz=mysql_fetch_assoc(mysqlquerryc('select id from ludzie where visible=1 and id='.$row['zona1'].';'));
		}
	}
	else{
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $mz=mysql_fetch_assoc(mysqlquerryc('select id from ludzie where zona1='.$row['id'].' limit 1;'));
		else $mz=mysql_fetch_assoc(mysqlquerryc('select id from ludzie where visible=1 and zona1='.$row['id'].' limit 1;'));
	}
	if($mz){
		mysqlquerryc('update ludzie set byl=1 where id='.$mz['id'].';');
		if($row['sex']=='k'){
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysqlquerryc('select id from ludzie where rodzic2='.$row['id'].' or rodzic1='.$mz['id'].' order by ur,imie;');
			else $dzieci=mysqlquerryc('select id from ludzie where visible=1 and (rodzic2='.$row['id'].' or rodzic1='.$mz['id'].') order by ur,imie;');
		}
		else{
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysqlquerryc('select id from ludzie where rodzic1='.$row['id'].' or rodzic2='.$mz['id'].' order by ur,imie;');
			else $dzieci=mysqlquerryc('select id from ludzie where visible=1 and (rodzic1='.$row['id'].' or rodzic2='.$mz['id'].') order by ur,imie;');
		}
	}
	else{
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysqlquerryc('select id,byl from ludzie where rodzic1='.$row['id'].' or rodzic2='.$row['id'].' order by ur,imie;');
		else $dzieci=mysqlquerryc('select id,byl from ludzie where visible=1 and (rodzic1='.$row['id'].' or rodzic2='.$row['id'].') order by ur,imie;');
	}
	echo('<div class="box0"><p>');
	for($w=0;$w<$ws;$w+=1) echo('<img src="icon-none.png" width="30" height="30">');
	if(mysql_num_rows($dzieci)>0){
		echo('<img name="obo'.$row['id'].'_'.$aa.'" id="obo'.$row['id'].'_'.$aa.'" src="icon-plus.png" onclick="ps(\'bo'.$row['id'].'_'.$aa.'\',this.id)" width="30" height="30">');
	}
	else echo('<img src="icon-none.png" width="30" height="30">');
	echo(linkujludzia($row['id'],2));
	if($mz) echo(' + '.linkujludzia($mz['id'],2));
	echo('<div id="bo'.$row['id'].'_'.$aa.'" class="box1">');
	for($i=0;$i<mysql_num_rows($dzieci);$i+=1){
		$dz=mysql_fetch_assoc($dzieci);
		dzieciizona($dz['id'],($ws));
	}
	echo('</div></div>');
}
function ilupot($person_id,$pokolen_wstecz){ 
	global $qc;
	$pot=0;
	if($pokolen_wstecz==1){
		$res1=mysqlquerryc('select id from ludzie where rodzic1='.$person_id.' or rodzic2='.$person_id.';');
		$pot=mysql_num_rows($res1);
		mysql_free_result($res1);
	}
	else if($pokolen_wstecz>=2){
		$wn=0;
		$res1=mysqlquerryc('select id from ludzie where rodzic1='.$person_id.' or rodzic2='.$person_id.';');
		for($i=0;$i<mysql_num_rows($res1);$i+=1){
			$row=mysql_fetch_assoc($res1);
			$numqq=ilupot($row['id'],$pokolen_wstecz-1);
			$wn+=$numqq;			
		}
		mysql_free_result($res1);
		$pot=$wn;
	}
	return $pot;
}
function ilupotmax($person_id,$pokolen_wstecz){ 
	global $qc;
	$res=mysqlquerryc('select id from ludzie where rodzic1='.$person_id.' or rodzic2='.$person_id.';');
	$pot=mysql_num_rows($res);
	$potList=Array();
	if($pokolen_wstecz==1) return $pot;
	else{
		for($i=0;$i<mysql_num_rows($res);$i+=1){
			$row=mysql_fetch_assoc($res);
			$potList[($row['id'])]=ilupotmax($row['id'],1);
		}
		if($pokolen_wstecz==2) return max($potList);
		else{
			$pl2=Array();
			foreach($potList as $k => $v){
				$res2=mysqlquerryc('select id from ludzie where rodzic1='.$k.' or rodzic2='.$k.';');
				for($j=0;$j<mysql_num_rows($res2);$j+=1){
					$row2=mysql_fetch_assoc($res2);
					$pl2[($row2['id'])]=ilupotmax($row2['id'],1);
				}
			}
			if($pokolen_wstecz==3) return max($pl2);
			else{
				$pl3=Array();
				foreach($pl2 as $k => $v){
					$res3=mysqlquerryc('select id from ludzie where rodzic1='.$k.' or rodzic2='.$k.';');
					for($j=0;$j<mysql_num_rows($res3);$j+=1){
						$row3=mysql_fetch_assoc($res3);
						$pl3[($row3['id'])]=ilupotmax($row3['id'],1);
					}
				}
				if($pokolen_wstecz==4) return max($pl3);
				else return 'error';
			}
		}
	}
}
function pokrewienstwo($a,$b){
	global $jestans,$currentuser,$qc;
	$jestans=0;
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $self=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where id='.$a.';'));
		else $self=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where visible=1 and id='.$a.';'));
		if($self['byl']==0){
			mysqlquerryc('update ludzie set byl=1 where id='.$a.';');
			if($self['rodzic1']!=0){
				if($self['rodzic1']==$b) $ans[($self['rodzic1'])]=' ojca.end';
				else $ans[($self['rodzic1'])]=' ojca';
			}
			if($self['rodzic2']!=0){
				if($self['rodzic2']==$b) $ans[($self['rodzic2'])]=' matki.end';
				else $ans[($self['rodzic2'])]=' matki';
			}
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysqlquerryc('select id,sex from ludzie where rodzic1='.$a.' or rodzic2='.$a.';');
			else $dzieci=mysqlquerryc('select id,sex from ludzie where visible=1 and (rodzic1='.$a.' or rodzic2='.$a.');');
			for($i=0;$i<mysql_num_rows($dzieci);$i+=1){
				$dz=mysql_fetch_assoc($dzieci);
				if($dz['sex']=='k'){
					if($dz['id']==$b) $ans[($dz['id'])]=' córki.end';
					else $ans[($dz['id'])]=' córki';
				}
				else{
					if($dz['id']==$b) $ans[($dz['id'])]=' syna.end';
					else $ans[($dz['id'])]=' syna';
				}
			}
			if($self['sex']=='k'){
				if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $maz=mysqlquerryc('select id from ludzie where zona1='.$a.' or zona2='.$a.' or zona3='.$a.';');
				else $maz=mysqlquerryc('select id from ludzie where visible=1 and (zona1='.$a.' or zona2='.$a.' or zona3='.$a.');');
				for($i=0;$i<mysql_num_rows($maz);$i+=1){
					$mz=mysql_fetch_assoc($maz);
					if($mz['id']==$b) $ans[($mz['id'])]=' męża.end';
					else $ans[($mz['id'])]=' męża';
				}
			}
			else{
				if($self['zona1']!=0){
					if($self['zona1']==$b) $ans[($self['zona1'])]=' żony.end';
					else $ans[($self['zona1'])]=' żony';
				}
				if($self['zona2']!=0){
					if($self['zona2']==$b) $ans[($self['zona2'])]=' żony.end';
					else $ans[($self['zona2'])]=' żony';
				}
				if($self['zona3']!=0){
					if($self['zona3']==$b) $ans[($self['zona2'])]=' żony.end';
					else $ans[($self['zona2'])]=' żony';
				}
			}
		}
		foreach($ans as $rt) if(strstr($rt,'.end')!=FALSE){
			$jestans=1;
			return $rt;
		}
		for($i=0;$i<15;$i+=1){
			if($jestans==0){
				foreach($ans as $k1 => $v1){
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $self=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where id='.$k1.';'));
					else $self=mysql_fetch_assoc(mysqlquerryc('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where visible=1 and id='.$k1.';'));
					if($self['byl']==0){
						mysqlquerryc('update ludzie set byl=1 where id='.$k1.';');
						if($self['rodzic1']!=0){
							if($self['rodzic1']==$b) $ans1[($self['rodzic1'])]=$v1.' ojca.end';
							else $ans1[($self['rodzic1'])]=$v1.' ojca';
						}
						if($self['rodzic2']!=0){
							if($self['rodzic2']==$b) $ans1[($self['rodzic2'])]=$v1.' matki.end';
							else $ans1[($self['rodzic2'])]=$v1.' matki';
						}
						if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysqlquerryc('select id,sex from ludzie where rodzic1='.$k1.' or rodzic2='.$k1.';');
						else $dzieci=mysqlquerryc('select id,sex from ludzie where visible=1 and (rodzic1='.$k1.' or rodzic2='.$k1.');');
						for($i=0;$i<mysql_num_rows($dzieci);$i+=1){
							$dz=mysql_fetch_assoc($dzieci);
							if($dz['sex']=='k'){
								if($dz['id']==$b) $ans1[($dz['id'])]=$v1.' córki.end';
								else $ans1[($dz['id'])]=$v1.' córki';
							}
							else{
								if($dz['id']==$b) $ans1[($dz['id'])]=$v1.' syna.end';
								else $ans1[($dz['id'])]=$v1.' syna';
							}
						}
						if($self['sex']=='k'){
							if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $maz=mysqlquerryc('select id from ludzie where zona1='.$k1.' or zona2='.$k1.' or zona3='.$k1.';');
							else $maz=mysqlquerryc('select id from ludzie where visible=1 and (zona1='.$k1.' or zona2='.$k1.' or zona3='.$k1.');');
							for($i=0;$i<mysql_num_rows($maz);$i+=1){
								$mz=mysql_fetch_assoc($maz);
								if($mz['id']==$b) $ans1[($mz['id'])]=$v1.' męża.end';
								else $ans1[($mz['id'])]=$v1.' męża';
							}
						}
						else{
							if($self['zona1']!=0){
								if($self['zona1']==$b) $ans1[($self['zona1'])]=$v1.' żony.end';
								else $ans1[($self['zona1'])]=$v1.' żony';
							}
							if($self['zona2']!=0){
								if($self['zona2']==$b) $ans1[($self['zona2'])]=$v1.' żony.end';
								else $ans1[($self['zona2'])]=$v1.' żony';
							}
							if($self['zona3']!=0){
								if($self['zona3']==$b) $ans1[($self['zona3'])]=$v1.' żony.end';
								else $ans1[($self['zona3'])]=$v1.' żony';
							}
						}
					}
				}
			}
			$ans=$ans1;
			foreach($ans as $rt) if(strstr($rt,'.end')!=FALSE){
				$jestans=1;
				return $rt;
			}
		}
		if($jestans==0) return 'NIE ZNALEZIONO';
	
}

function szukajZony($inp_id){
	global $qc;
	$res=mysql_fetch_assoc(mysqlquerryc('select * from ludzie where id='.$inp_id.';'));
					if($res['sex']=='m'){
						if($res['zona3']!='0') $zid=$res['zona3'];
						else if($res['zona2']!='0') $zid=$res['zona2'];
						else if($res['zona1']!='0') $zid=$res['zona1'];
						else $zid='0';
					}
					else{
						$res1z=mysqlquerryc('select * from ludzie where zona3='.$inp_id.';');
						if(mysql_num_rows($res1z)>0){
							$rowz=mysql_fetch_assoc($res1z);
							$zid=$rowz['id'];
							mysql_free_result($res1z);
						}
						else{
							$res1z=mysqlquerryc('select * from ludzie where zona2='.$inp_id.';');
							if(mysql_num_rows($res1z)>0){
								$rowz=mysql_fetch_assoc($res1z);
								$zid=$rowz['id'];
								mysql_free_result($res1z);
							}
							else{
								$res1z=mysqlquerryc('select * from ludzie where zona1='.$inp_id.';');
								if(mysql_num_rows($res1z)>0){
									$rowz=mysql_fetch_assoc($res1z);
									$zid=$rowz['id'];
									mysql_free_result($res1z);
								}
								else $zid='0';
							}
						}
					}
	return $zid;
}

function jejjego($sex,$lang){
	switch($lang){
		case 'pl':{
			if($sex=='m') return 'jego';
			else return 'jej';
			break;
		}
		case 'en':{
			if($sex=='m') return 'his';
			else return 'her';
			break;
		}
	}
}

?>
