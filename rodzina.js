
function highl(iid) {
	document.getElementById(iid).className="mboxsel";
	document.getElementById(iid+"_p").className="menusel";
//	document.getElementById("allm").className="allmenuh";
}
function downl(iid) {
	document.getElementById(iid).className="mbox";
	document.getElementById(iid+"_p").className="menu";
//	document.getElementById("allm").className="allmenu";
}

function menuclick(iid) { 
	document.location="index.php?" + iid;
}
function rokclick(iid) {
	document.location="index.php?rocznik," + document.rocznik.rok.value;
}
function btnh(iid) {
	document.getElementById(iid).className="formbtnh";
}
function btnd(iid) {
	document.getElementById(iid).className="formbtn";
}
function scrwdth() {
	document.getElementById("scrsize").value=window.screen.width;
}
function ps(iid,iid2) {   
     var el1=document.getElementById(iid);
     var obr=document.getElementById(iid2);
         if(el1.style.display=="block"){
             el1.style.display="none";
             obr.src="icon-plus.png";
            
          } else {
             el1.style.display="block";
             obr.src="icon-minus.png";
          }
   }

function point_it(event){
	pos_x = event.offsetX?(event.offsetX):event.pageX-document.getElementById("pointer_div").offsetLeft;
	pos_y = event.offsetY?(event.offsetY):event.pageY-document.getElementById("pointer_div").offsetTop;
	if(document.stg2.posx0.value==0) document.stg2.posx0.value=pos_x;
	else if(document.stg2.posx1.value==0) document.stg2.posx1.value=pos_x;
	else if(document.stg2.posx2.value==0) document.stg2.posx2.value=pos_x;
	else if(document.stg2.posx3.value==0) document.stg2.posx3.value=pos_x;
	else if(document.stg2.posx4.value==0) document.stg2.posx4.value=pos_x;
	else if(document.stg2.posx5.value==0) document.stg2.posx5.value=pos_x;
	else if(document.stg2.posx6.value==0) document.stg2.posx6.value=pos_x;
	else if(document.stg2.posx7.value==0) document.stg2.posx7.value=pos_x;
	if(document.stg2.posy0.value==0) document.stg2.posy0.value=pos_y;
	else if(document.stg2.posy1.value==0) document.stg2.posy1.value=pos_y;
	else if(document.stg2.posy2.value==0) document.stg2.posy2.value=pos_y;
	else if(document.stg2.posy3.value==0) document.stg2.posy3.value=pos_y;
	else if(document.stg2.posy4.value==0) document.stg2.posy4.value=pos_y;
	else if(document.stg2.posy5.value==0) document.stg2.posy5.value=pos_y;
	else if(document.stg2.posy6.value==0) document.stg2.posy6.value=pos_y;
	else if(document.stg2.posy7.value==0) document.stg2.posy7.value=pos_y;
}
