function StartGameTimer () {
var di = new Date(); 
var ci = di.getTime(); 
UpdateDisplayTimer(0);
return ci;
}

function UpdateDisplayTimer(ms) {
  if (CountActive)
  {
  UpdateDisplay(ms)
  setTimeout("UpdateDisplayTimer(" + (ms+1000) + ")", 1000);
  }
}

function EndGameTimer (ci) {
    CountActive = false;
    var df = new Date();
    var cf = df.getTime();
    var cnt = cf - ci;
    UpdateDisplay(cnt)
return cnt;
}

function UpdateDisplay(ms) {
  DisplayStr = DisplayFormat.replace(/%%M%%/g, calcage(ms,60000,100));
  DisplayStr = DisplayStr.replace(/%%S%%/g, calcage(ms,1000,60));
  if (CountActive) { DisplayStr = DisplayStr.replace(/%%N%%/g, ""); }
  else { DisplayStr = DisplayStr.replace(/%%N%%/g, calcage(ms,1,1000)); }
  document.getElementById("gametime").innerHTML = DisplayStr;
}

function calcage(ms, incr, rlvr) {
  ms = ((Math.floor(ms / incr)) % rlvr).toString();
  if (ms.length < 2)
    ms = "0" + ms;
  if (incr == 1)
  {
    if (ms.length < 3)
       ms = "0" + ms;
    ms = "." + ms;
  }
  return "<b>" + ms + "</b>";
}
