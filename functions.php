<?php
function checkname(){ //login check
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

function plfirstup($string, $e ='utf-8') { 
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
	global $lang,$lng;
	if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $row=mysql_fetch_assoc(mysql_query('select * from ludzie where id='.$uid.';'));
	else $row=mysql_fetch_assoc(mysql_query('select * from ludzie where visible=1 and id='.$uid.';'));
	switch($style){
		case 1:{
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
				if(($row['rodzic1']!=0)&($row['rodzic2']!=0)){
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r1=mysql_fetch_assoc(mysql_query('select imie from ludzie where id='.$row['rodzic1'].';'));
					else $r1=mysql_fetch_assoc(mysql_query('select imie from ludzie where visible=1 and id='.$row['rodzic1'].';'));
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r2=mysql_fetch_assoc(mysql_query('select imie from ludzie where id='.$row['rodzic2'].';'));
					else $r2=mysql_fetch_assoc(mysql_query('select imie from ludzie where visible=1 and id='.$row['rodzic2'].';'));
					$wynik.=', '.$rse.' '.odmiana_m($r1['imie']).' '.$lang[$lng][135].' '.odmiana_k($r2['imie']);
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
				$wynik='<a href="'.$thisfile.'?pokaz,one,'.$row['id'].'">'.$row['imie'].' '.$row['nazwisko'].'</a>';
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
				if(($row['rodzic1']!=0)&($row['rodzic2']!=0)){
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r1=mysql_fetch_assoc(mysql_query('select imie from ludzie where id='.$row['rodzic1'].';'));
					else $r1=mysql_fetch_assoc(mysql_query('select imie from ludzie where visible=1 and id='.$row['rodzic1'].';'));
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $r2=mysql_fetch_assoc(mysql_query('select imie from ludzie where id='.$row['rodzic2'].';'));
					else $r2=mysql_fetch_assoc(mysql_query('select imie from ludzie where visible=1 and id='.$row['rodzic2'].';'));
					$wynik.=', '.$rse.' '.odmiana_m($r1['imie']).' '.$lang[$lng][135].' '.odmiana_k($r2['imie']);
				}
				if(strlen($row['adres'])>4) $wynik.=', '.$row['adres'];
			break;
		}
	}
	return $wynik;
}
function dzieciizona($uid,$ws){ //do famuły
	global $byl;
	if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $row=mysql_fetch_assoc(mysql_query('select * from ludzie where id='.$uid.';'));
	else $row=mysql_fetch_assoc(mysql_query('select * from ludzie where visible=1 and id='.$uid.';'));
	$aa=rand(1000,9999);
	mysql_query('update ludzie set byl=1 where id='.$uid.';');
	if($row['sex']=='m'){
		if($row['zona1']!=0){
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $mz=mysql_fetch_assoc(mysql_query('select id from ludzie where id='.$row['zona1'].';'));
			else $mz=mysql_fetch_assoc(mysql_query('select id from ludzie where visible=1 and id='.$row['zona1'].';'));
		}
	}
	else{
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $mz=mysql_fetch_assoc(mysql_query('select id from ludzie where zona1='.$row['id'].' limit 1;'));
		else $mz=mysql_fetch_assoc(mysql_query('select id from ludzie where visible=1 and zona1='.$row['id'].' limit 1;'));
	}
	if($mz){
		mysql_query('update ludzie set byl=1 where id='.$mz['id'].';');
		if($row['sex']=='k'){
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysql_query('select id from ludzie where rodzic2='.$row['id'].' or rodzic1='.$mz['id'].' order by ur,imie;');
			else $dzieci=mysql_query('select id from ludzie where visible=1 and (rodzic2='.$row['id'].' or rodzic1='.$mz['id'].') order by ur,imie;');
		}
		else{
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysql_query('select id from ludzie where rodzic1='.$row['id'].' or rodzic2='.$mz['id'].' order by ur,imie;');
			else $dzieci=mysql_query('select id from ludzie where visible=1 and (rodzic1='.$row['id'].' or rodzic2='.$mz['id'].') order by ur,imie;');
		}
	}
	else{
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysql_query('select id,byl from ludzie where rodzic1='.$row['id'].' or rodzic2='.$row['id'].' order by ur,imie;');
		else $dzieci=mysql_query('select id,byl from ludzie where visible=1 and (rodzic1='.$row['id'].' or rodzic2='.$row['id'].') order by ur,imie;');
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
function ilupot($person_id,$pokolen_wstecz){ // MASSIVE MEMORY USAGE! <- maybe no more?
	$pot=0;
	if($pokolen_wstecz==1){
		$res1=mysql_query('select id from ludzie where rodzic1='.$person_id.' or rodzic2='.$person_id.';');
		$pot=mysql_num_rows($res1);
		mysql_free_result($res1);
	}
	else if($pokolen_wstecz==2){
		$wn=0;
		$res1=mysql_query('select id from ludzie where rodzic1='.$person_id.' or rodzic2='.$person_id.';');
		for($i=0;$i<mysql_num_rows($res1);$i+=1){
			$row=mysql_fetch_assoc($res1);
			$res2=mysql_query('select id from ludzie where rodzic1='.$row['id'].' or rodzic2='.$row['id'].';');
			$numqq=mysql_num_rows($res2);
				if($numqq>0){
					//echo($numqq);
					$wn+=$numqq;
					//for($j=0;$j<$numqq;$j+=1){
					//	$row2=mysql_fetch_assoc($res2);
					//	$wn+=ilupot($row2['id'],($pokolen_wstecz-1));
					//}
				}
				
			}
		mysql_free_result($res1);
		$pot=$wn;
	}
	return $pot;
}
function pokrewienstwo($a,$b){
	global $jestans;
	$jestans=0;
		if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where id='.$a.';'));
		else $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where visible=1 and id='.$a.';'));
		if($self['byl']==0){
			mysql_query('update ludzie set byl=1 where id='.$a.';');
			if($self['rodzic1']!=0){
				if($self['rodzic1']==$b) $ans[($self['rodzic1'])]=' ojca.end';
				else $ans[($self['rodzic1'])]=' ojca';
			}
			if($self['rodzic2']!=0){
				if($self['rodzic2']==$b) $ans[($self['rodzic2'])]=' matki.end';
				else $ans[($self['rodzic2'])]=' matki';
			}
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysql_query('select id,sex from ludzie where rodzic1='.$a.' or rodzic2='.$a.';');
			else $dzieci=mysql_query('select id,sex from ludzie where visible=1 and (rodzic1='.$a.' or rodzic2='.$a.');');
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
				if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $maz=mysql_query('select id from ludzie where zona1='.$a.' or zona2='.$a.' or zona3='.$a.';');
				else $maz=mysql_query('select id from ludzie where visible=1 and (zona1='.$a.' or zona2='.$a.' or zona3='.$a.');');
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
		if($jestans==0){
		foreach($ans as $k1 => $v1){
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where id='.$k1.';'));
			else $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where visible=1 and id='.$k1.';'));
			if($self['byl']==0){
				mysql_query('update ludzie set byl=1 where id='.$k1.';');
				if($self['rodzic1']!=0){
					if($self['rodzic1']==$b) $ans1[($self['rodzic1'])]=$v1.' ojca.end';
					else $ans1[($self['rodzic1'])]=$v1.' ojca';
				}
				if($self['rodzic2']!=0){
					if($self['rodzic2']==$b) $ans1[($self['rodzic2'])]=$v1.' matki.end';
					else $ans1[($self['rodzic2'])]=$v1.' matki';
				}
				if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysql_query('select id,sex from ludzie where rodzic1='.$k1.' or rodzic2='.$k1.';');
				else $dzieci=mysql_query('select id,sex from ludzie where visible=1 and (rodzic1='.$k1.' or rodzic2='.$k1.');');
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
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $maz=mysql_query('select id from ludzie where zona1='.$k1.' or zona2='.$k1.' or zona3='.$k1.';');
					else $maz=mysql_query('select id from ludzie where visible=1 and (zona1='.$k1.' or zona2='.$k1.' or zona3='.$k1.');');
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
		}}
		foreach($ans1 as $rt) if(strstr($rt,'.end')!=FALSE){
			$jestans=1;
			return $rt;
		}
		if($jestans==0){
		foreach($ans1 as $k2 => $v2){
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where id='.$k2.';'));
			else $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where visible=1 and id='.$k2.';'));
			if($self['byl']==0){
				mysql_query('update ludzie set byl=1 where id='.$k2.';');
				if($self['rodzic1']!=0){
					if($self['rodzic1']==$b) $ans2[$self['rodzic1']]=$v2.' ojca.end';
					else $ans2[$self['rodzic1']]=$v2.' ojca';
				}
				if($self['rodzic2']!=0){
					if($self['rodzic2']==$b) $ans2[$self['rodzic2']]=$v2.' matki.end';
					else $ans2[$self['rodzic2']]=$v2.' matki';
				}
				if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysql_query('select id,sex from ludzie where rodzic1='.$k2.' or rodzic2='.$k2.';');
				else $dzieci=mysql_query('select id,sex from ludzie where visible=1 and (rodzic1='.$k2.' or rodzic2='.$k2.');');
				for($i=0;$i<mysql_num_rows($dzieci);$i+=1){
					$dz=mysql_fetch_assoc($dzieci);
					if($dz['sex']=='k'){
						if($dz['id']==$b) $ans2[$dz['id']]=$v2.' córki.end';
						else $ans2[$dz['id']]=$v2.' córki';
					}
					else{
						if($dz['id']==$b) $ans2[$dz['id']]=$v2.' syna.end';
						else $ans2[$dz['id']]=$v2.' syna';
					}
				}
				if($self['sex']=='k'){
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $maz=mysql_query('select id from ludzie where zona1='.$k2.' or zona2='.$k2.' or zona3='.$k2.';');
					else $maz=mysql_query('select id from ludzie where visible=1 and (zona1='.$k2.' or zona2='.$k2.' or zona3='.$k2.');');
					for($i=0;$i<mysql_num_rows($maz);$i+=1){
						$mz=mysql_fetch_assoc($maz);
						if($mz['id']==$b) $ans2[$mz['id']]=$v2.' męża.end';
						else $ans2[$mz['id']]=$v2.' męża';
					}
				}
				else{
					if($self['zona1']!=0){
						if($self['zona1']==$b) $ans2[$self['zona1']]=$v2.' żony.end';
						else $ans2[$self['zona1']]=$v2.' żony';
					}
					if($self['zona2']!=0){
						if($self['zona2']==$b) $ans2[$self['zona2']]=$v2.' żony.end';
						else $ans2[$self['zona2']]=$v2.' żony';
					}
					if($self['zona3']!=0){
						if($self['zona3']==$b) $ans2[$self['zona3']]=$v2.' żony.end';
						else $ans2[$self['zona3']]=$v2.' żony';
					}
				}
			}
		}}
		foreach($ans2 as $rt) if(strstr($rt,'.end')!=FALSE){
			$jestans=1;
			return $rt;
		}
		if($jestans==0){
		foreach($ans2 as $k3 => $v3){
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where id='.$k3.';'));
			else $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where visible=1 and id='.$k3.';'));
			if($self['byl']==0){
				mysql_query('update ludzie set byl=1 where id='.$k3.';');
				if($self['rodzic1']!=0){
					if($self['rodzic1']==$b) $ans3[$self['rodzic1']]=$v3.' ojca.end';
					else $ans3[$self['rodzic1']]=$v3.' ojca';
				}
				if($self['rodzic2']!=0){
					if($self['rodzic2']==$b) $ans3[$self['rodzic2']]=$v3.' matki.end';
					else $ans3[$self['rodzic2']]=$v3.' matki';
				}
				if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysql_query('select id,sex from ludzie where rodzic1='.$k3.' or rodzic2='.$k3.';');
				else $dzieci=mysql_query('select id,sex from ludzie where visible=1 and (rodzic1='.$k3.' or rodzic2='.$k3.');');
				for($i=0;$i<mysql_num_rows($dzieci);$i+=1){
					$dz=mysql_fetch_assoc($dzieci);
					if($dz['sex']=='k'){
						if($dz['id']==$b) $ans3[$dz['id']]=$v3.' córki.end';
						else $ans3[$dz['id']]=$v3.' córki';
					}
					else{
						if($dz['id']==$b) $ans3[$dz['id']]=$v3.' syna.end';
						else $ans3[$dz['id']]=$v3.' syna';
					}
				}
				if($self['sex']=='k'){
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $maz=mysql_query('select id from ludzie where zona1='.$k3.' or zona2='.$k3.' or zona3='.$k3.';');
					else $maz=mysql_query('select id from ludzie where visible=1 and (zona1='.$k3.' or zona2='.$k3.' or zona3='.$k3.');');
					for($i=0;$i<mysql_num_rows($maz);$i+=1){
						$mz=mysql_fetch_assoc($maz);
						if($mz['id']==$b) $ans3[$mz['id']]=$v3.' męża.end';
						else $ans3[$mz['id']]=$v3.' męża';
					}
				}
				else{
					if($self['zona1']!=0){
						if($self['zona1']==$b) $ans3[$self['zona1']]=$v3.' żony.end';
						else $ans3[$self['zona1']]=$v3.' żony';
					}
					if($self['zona2']!=0){
						if($self['zona2']==$b) $ans3[$self['zona2']]=$v3.' żony.end';
						else $ans3[$self['zona2']]=$v3.' żony';
					}
					if($self['zona3']!=0){
						if($self['zona3']==$b) $ans3[$self['zona3']]=$v3.' żony.end';
						else $ans3[$self['zona3']]=$v3.' żony';
					}
				}
			}
		}}
		foreach($ans3 as $rt) if(strstr($rt,'.end')!=FALSE){
			$jestans=1;
			return $rt;
		}
		if($jestans==0){
			$ans4=Array();
		foreach($ans3 as $k4 => $v4){
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where id='.$k4.';'));
			else $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where visible=1 and id='.$k4.';'));
			if($self['byl']==0){
				mysql_query('update ludzie set byl=1 where id='.$k4.';');
				if($self['rodzic1']!=0){
					if($self['rodzic1']==$b) $ans4[$self['rodzic1']]=$v4.' ojca.end';
					else $ans4[$self['rodzic1']]=$v4.' ojca';
				}
				if($self['rodzic2']!=0){
					if($self['rodzic2']==$b) $ans4[$self['rodzic2']]=$v4.' matki.end';
					else $ans4[$self['rodzic2']]=$v4.' matki';
				}
				if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysql_query('select id,sex from ludzie where rodzic1='.$k4.' or rodzic2='.$k4.';');
				else $dzieci=mysql_query('select id,sex from ludzie where visible=1 and (rodzic1='.$k4.' or rodzic2='.$k4.');');
				for($i=0;$i<mysql_num_rows($dzieci);$i+=1){
					$dz=mysql_fetch_assoc($dzieci);
					if($dz['sex']=='k'){
						if($dz['id']==$b) $ans4[$dz['id']]=$v4.' córki.end';
						else $ans4[$dz['id']]=$v4.' córki';
					}
					else{
						if($dz['id']==$b) $ans4[$dz['id']]=$v4.' syna.end';
						else $ans4[$dz['id']]=$v4.' syna';
					}
				}
				if($self['sex']=='k'){
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $maz=mysql_query('select id from ludzie where zona1='.$k4.' or zona2='.$k4.' or zona3='.$k4.';');
					else $maz=mysql_query('select id from ludzie where visible=1 and (zona1='.$k4.' or zona2='.$k4.' or zona3='.$k4.');');
					for($i=0;$i<mysql_num_rows($maz);$i+=1){
						$mz=mysql_fetch_assoc($maz);
						if($mz['id']==$b) $ans4[$mz['id']]=$v4.' męża.end';
						else $ans4[$mz['id']]=$v4.' męża';
					}
				}
				else{
					if($self['zona1']!=0){
						if($self['zona1']==$b) $ans4[$self['zona1']]=$v4.' żony.end';
						else $ans4[$self['zona1']]=$v4.' żony';
					}
					if($self['zona2']!=0){
						if($self['zona2']==$b) $ans4[$self['zona2']]=$v4.' żony.end';
						else $ans4[$self['zona2']]=$v4.' żony';
					}
					if($self['zona3']!=0){
						if($self['zona3']==$b) $ans4[$self['zona3']]=$v4.' żony.end';
						else $ans4[$self['zona3']]=$v4.' żony';
					}
				}
			}
		}}
		foreach($ans4 as $rt) if(strstr($rt,'.end')!=FALSE){
			$jestans=1;
			return $rt;
		}
		if($jestans==0){
		foreach($ans4 as $k5 => $v5){
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where id='.$k5.';'));
			else $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where visible=1 and id='.$k5.';'));
			if($self['byl']==0){
				mysql_query('update ludzie set byl=1 where id='.$k5.';');
				if($self['rodzic1']!=0){
					if($self['rodzic1']==$b) $ans5[$self['rodzic1']]=$v5.' ojca.end';
					else $ans5[$self['rodzic1']]=$v5.' ojca';
				}
				if($self['rodzic2']!=0){
					if($self['rodzic2']==$b) $ans5[$self['rodzic2']]=$v5.' matki.end';
					else $ans5[$self['rodzic2']]=$v5.' matki';
				}
				if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysql_query('select id,sex from ludzie where rodzic1='.$k5.' or rodzic2='.$k5.';');
				else $dzieci=mysql_query('select id,sex from ludzie where visible=1 and (rodzic1='.$k5.' or rodzic2='.$k5.');');
				for($i=0;$i<mysql_num_rows($dzieci);$i+=1){
					$dz=mysql_fetch_assoc($dzieci);
					if($dz['sex']=='k'){
						if($dz['id']==$b) $ans5[$dz['id']]=$v5.' córki.end';
						else $ans5[$dz['id']]=$v5.' córki';
					}
					else{
						if($dz['id']==$b) $ans5[$dz['id']]=$v5.' syna.end';
						else $ans5[$dz['id']]=$v5.' syna';
					}
				}
				if($self['sex']=='k'){
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $maz=mysql_query('select id from ludzie where zona1='.$k5.' or zona2='.$k5.' or zona3='.$k5.';');
					else $maz=mysql_query('select id from ludzie where visible=1 and (zona1='.$k5.' or zona2='.$k5.' or zona3='.$k5.');');
					for($i=0;$i<mysql_num_rows($maz);$i+=1){
						$mz=mysql_fetch_assoc($maz);
						if($mz['id']==$b) $ans5[$mz['id']]=$v5.' męża.end';
						else $ans5[$mz['id']]=$v5.' męża';
					}
				}
				else{
					if($self['zona1']!=0){
						if($self['zona1']==$b) $ans5[$self['zona1']]=$v5.' żony.end';
						else $ans5[$self['zona1']]=$v5.' żony';
					}
					if($self['zona2']!=0){
						if($self['zona2']==$b) $ans5[$self['zona2']]=$v5.' żony.end';
						else $ans5[$self['zona2']]=$v5.' żony';
					}
					if($self['zona3']!=0){
						if($self['zona3']==$b) $ans5[$self['zona3']]=$v5.' żony.end';
						else $ans5[$self['zona3']]=$v5.' żony';
					}
				}
			}
		}}
		foreach($ans5 as $rt) if(strstr($rt,'.end')!=FALSE){
			$jestans=1;
			return $rt;
		}
		if($jestans==0){
		foreach($ans5 as $k6 => $v6){
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where id='.$k6.';'));
			else $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where visible=1 and id='.$k6.';'));
			if($self['byl']==0){
				mysql_query('update ludzie set byl=1 where id='.$k6.';');
				if($self['rodzic1']!=0){
					if($self['rodzic1']==$b) $ans6[$self['rodzic1']]=$v6.' ojca.end';
					else $ans6[$self['rodzic1']]=$v6.' ojca';
				}
				if($self['rodzic2']!=0){
					if($self['rodzic2']==$b) $ans6[$self['rodzic2']]=$v6.' matki.end';
					else $ans6[$self['rodzic2']]=$v6.' matki';
				}
				if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysql_query('select id,sex from ludzie where rodzic1='.$k6.' or rodzic2='.$k6.';');
				else $dzieci=mysql_query('select id,sex from ludzie where visible=1 and (rodzic1='.$k6.' or rodzic2='.$k6.');');
				for($i=0;$i<mysql_num_rows($dzieci);$i+=1){
					$dz=mysql_fetch_assoc($dzieci);
					if($dz['sex']=='k'){
						if($dz['id']==$b) $ans6[$dz['id']]=$v6.' córki.end';
						else $ans6[$dz['id']]=$v6.' córki';
					}
					else{
						if($dz['id']==$b) $ans6[$dz['id']]=$v6.' syna.end';
						else $ans6[$dz['id']]=$v6.' syna';
					}
				}
				if($self['sex']=='k'){
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $maz=mysql_query('select id from ludzie where zona1='.$k6.' or zona2='.$k6.' or zona3='.$k6.';');
					else $maz=mysql_query('select id from ludzie where visible=1 and (zona1='.$k6.' or zona2='.$k6.' or zona3='.$k6.');');
					for($i=0;$i<mysql_num_rows($maz);$i+=1){
						$mz=mysql_fetch_assoc($maz);
						if($mz['id']==$b) $ans6[$mz['id']]=$v6.' męża.end';
						else $ans6[$mz['id']]=$v6.' męża';
					}
				}
				else{
					if($self['zona1']!=0){
						if($self['zona1']==$b) $ans6[$self['zona1']]=$v6.' żony.end';
						else $ans6[$self['zona1']]=$v6.' żony';
					}
					if($self['zona2']!=0){
						if($self['zona2']==$b) $ans6[$self['zona2']]=$v6.' żony.end';
						else $ans6[$self['zona2']]=$v6.' żony';
					}
					if($self['zona3']!=0){
						if($self['zona3']==$b) $ans6[$self['zona3']]=$v6.' żony.end';
						else $ans6[$self['zona3']]=$v6.' żony';
					}
				}
			}
		}}
		foreach($ans6 as $rt) if(strstr($rt,'.end')!=FALSE){
			$jestans=1;
			return $rt;
		}
		if($jestans==0){
		foreach($ans6 as $k7 => $v7){
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where id='.$k7.';'));
			else $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where visible=1 and id='.$k7.';'));
			if($self['byl']==0){
				mysql_query('update ludzie set byl=1 where id='.$k7.';');
				if($self['rodzic1']!=0){
					if($self['rodzic1']==$b) $ans7[$self['rodzic1']]=$v7.' ojca.end';
					else $ans7[$self['rodzic1']]=$v7.' ojca';
				}
				if($self['rodzic2']!=0){
					if($self['rodzic2']==$b) $ans7[$self['rodzic2']]=$v7.' matki.end';
					else $ans7[$self['rodzic2']]=$v7.' matki';
				}
				if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysql_query('select id,sex from ludzie where rodzic1='.$k7.' or rodzic2='.$k7.';');
				else $dzieci=mysql_query('select id,sex from ludzie where visible=1 and (rodzic1='.$k7.' or rodzic2='.$k7.');');
				for($i=0;$i<mysql_num_rows($dzieci);$i+=1){
					$dz=mysql_fetch_assoc($dzieci);
					if($dz['sex']=='k'){
						if($dz['id']==$b) $ans7[$dz['id']]=$v7.' córki.end';
						else $ans7[$dz['id']]=$v7.' córki';
					}
					else{
						if($dz['id']==$b) $ans7[$dz['id']]=$v7.' syna.end';
						else $ans7[$dz['id']]=$v7.' syna';
					}
				}
				if($self['sex']=='k'){
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $maz=mysql_query('select id from ludzie where zona1='.$k7.' or zona2='.$k7.' or zona3='.$k7.';');
					else $maz=mysql_query('select id from ludzie where visible=1 and (zona1='.$k7.' or zona2='.$k7.' or zona3='.$k7.');');
					for($i=0;$i<mysql_num_rows($maz);$i+=1){
						$mz=mysql_fetch_assoc($maz);
						if($mz['id']==$b) $ans7[$mz['id']]=$v7.' męża.end';
						else $ans7[$mz['id']]=$v7.' męża';
					}
				}
				else{
					if($self['zona1']!=0){
						if($self['zona1']==$b) $ans7[$self['zona1']]=$v7.' żony.end';
						else $ans7[$self['zona1']]=$v7.' żony';
					}
					if($self['zona2']!=0){
						if($self['zona2']==$b) $ans7[$self['zona2']]=$v7.' żony.end';
						else $ans7[$self['zona2']]=$v7.' żony';
					}
					if($self['zona3']!=0){
						if($self['zona3']==$b) $ans7[$self['zona3']]=$v7.' żony.end';
						else $ans7[$self['zona3']]=$v7.' żony';
					}
				}
			}
		}}
		foreach($ans7 as $rt) if(strstr($rt,'.end')!=FALSE){
			$jestans=1;
			return $rt;
		}
		if($jestans==0){
		foreach($ans7 as $k8 => $v8){
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where id='.$k8.';'));
			else $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where visible=1 and id='.$k8.';'));
			if($self['byl']==0){
				mysql_query('update ludzie set byl=1 where id='.$k8.';');
				if($self['rodzic1']!=0){
					if($self['rodzic1']==$b) $ans8[$self['rodzic1']]=$v8.' ojca.end';
					else $ans8[$self['rodzic1']]=$v8.' ojca';
				}
				if($self['rodzic2']!=0){
					if($self['rodzic2']==$b) $ans8[$self['rodzic2']]=$v8.' matki.end';
					else $ans8[$self['rodzic2']]=$v8.' matki';
				}
				if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysql_query('select id,sex from ludzie where rodzic1='.$k8.' or rodzic2='.$k8.';');
				else $dzieci=mysql_query('select id,sex from ludzie where visible=1 and (rodzic1='.$k8.' or rodzic2='.$k8.');');
				for($i=0;$i<mysql_num_rows($dzieci);$i+=1){
					$dz=mysql_fetch_assoc($dzieci);
					if($dz['sex']=='k'){
						if($dz['id']==$b) $ans8[$dz['id']]=$v8.' córki.end';
						else $ans8[$dz['id']]=$v8.' córki';
					}
					else{
						if($dz['id']==$b) $ans8[$dz['id']]=$v8.' syna.end';
						else $ans8[$dz['id']]=$v8.' syna';
					}
				}
				if($self['sex']=='k'){
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $maz=mysql_query('select id from ludzie where zona1='.$k8.' or zona2='.$k8.' or zona3='.$k8.';');
					else $maz=mysql_query('select id from ludzie where visible=1 and (zona1='.$k8.' or zona2='.$k8.' or zona3='.$k8.');');
					for($i=0;$i<mysql_num_rows($maz);$i+=1){
						$mz=mysql_fetch_assoc($maz);
						if($mz['id']==$b) $ans8[$mz['id']]=$v8.' męża.end';
						else $ans8[$mz['id']]=$v8.' męża';
					}
				}
				else{
					if($self['zona1']!=0){
						if($self['zona1']==$b) $ans8[$self['zona1']]=$v8.' żony.end';
						else $ans8[$self['zona1']]=$v8.' żony';
					}
					if($self['zona2']!=0){
						if($self['zona2']==$b) $ans8[$self['zona2']]=$v8.' żony.end';
						else $ans8[$self['zona2']]=$v8.' żony';
					}
					if($self['zona3']!=0){
						if($self['zona3']==$b) $ans8[$self['zona3']]=$v8.' żony.end';
						else $ans8[$self['zona3']]=$v8.' żony';
					}
				}
			}
		}}
		foreach($ans8 as $rt) if(strstr($rt,'.end')!=FALSE){
			$jestans=1;
			return $rt;
		}
		if($jestans==0){
		foreach($ans8 as $k9 => $v9){
			if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where id='.$k9.';'));
			else $self=mysql_fetch_assoc(mysql_query('select id,imie,nazwisko,byl,sex,rodzic1,rodzic2,zona1,zona2,zona3 from ludzie where visible=1 and id='.$k9.';'));
			if($self['byl']==0){
				mysql_query('update ludzie set byl=1 where id='.$k9.';');
				if($self['rodzic1']!=0){
					if($self['rodzic1']==$b) $ans9[$self['rodzic1']]=$v9.' ojca.end';
					else $ans9[$self['rodzic1']]=$v9.' ojca';
				}
				if($self['rodzic2']!=0){
					if($self['rodzic2']==$b) $ans9[$self['rodzic2']]=$v9.' matki.end';
					else $ans9[$self['rodzic2']]=$v9.' matki';
				}
				if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $dzieci=mysql_query('select id,sex from ludzie where rodzic1='.$k9.' or rodzic2='.$k9.';');
				else $dzieci=mysql_query('select id,sex from ludzie where visible=1 and (rodzic1='.$k9.' or rodzic2='.$k9.');');
				for($i=0;$i<mysql_num_rows($dzieci);$i+=1){
					$dz=mysql_fetch_assoc($dzieci);
					if($dz['sex']=='k'){
						if($dz['id']==$b) $ans9[$dz['id']]=$v9.' córki.end';
						else $ans9[$dz['id']]=$v9.' córki';
					}
					else{
						if($dz['id']==$b) $ans9[$dz['id']]=$v9.' syna.end';
						else $ans9[$dz['id']]=$v9.' syna';
					}
				}
				if($self['sex']=='k'){
					if((isset($_COOKIE['zal'])&checkname())&(preg_match('#,menu2view,#',$currentuser['flags']))) $maz=mysql_query('select id from ludzie where zona1='.$k9.' or zona2='.$k9.' or zona3='.$k9.';');
					else $maz=mysql_query('select id from ludzie where visible=1 and (zona1='.$k9.' or zona2='.$k9.' or zona3='.$k9.');');
					for($i=0;$i<mysql_num_rows($maz);$i+=1){
						$mz=mysql_fetch_assoc($maz);
						if($mz['id']==$b) $ans9[$mz['id']]=$v9.' męża.end';
						else $ans9[$mz['id']]=$v9.' męża';
					}
				}
				else{
					if($self['zona1']!=0){
						if($self['zona1']==$b) $ans9[$self['zona1']]=$v9.' żony.end';
						else $ans9[$self['zona1']]=$v9.' żony';
					}
					if($self['zona2']!=0){
						if($self['zona2']==$b) $ans9[$self['zona2']]=$v9.' żony.end';
						else $ans9[$self['zona2']]=$v9.' żony';
					}
					if($self['zona3']!=0){
						if($self['zona3']==$b) $ans9[$self['zona3']]=$v9.' żony.end';
						else $ans9[$self['zona3']]=$v9.' żony';
					}
				}
			}
		}}
		foreach($ans9 as $rt) if(strstr($rt,'.end')!=FALSE){
			$jestans=1;
			return $rt;
		}
		if($jestans==0) return 'NIE ZNALEZIONO';
	
}

?>
