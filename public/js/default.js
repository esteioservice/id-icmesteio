
function oAuth2GoogleLog() {
  const source = document.getElementById("googleLog");
  const text   = source.childNodes[2].nodeValue.trim();
  const url    = document.URL;
  const piece  =  url.split('/');
  var last   = piece.at(3);
  for (var i = 4; i < piece.length; i++) {
    last += ("/" + piece.at(i));
  }
  var query    = '';
  if (last) {
    query = "?part=/" + last;
  }
  if (text == "Logout") {
    location.href = "/oauth2/logout" + query;
  } else if (text == "Login") {
    location.href = "/oauth2" + query;
  }
}

function lockScreen() {
  $(window).scrollTop(0);
  $(window).scrollLeft(0);
  $('html').css({
    'overflow': 'hidden',
    'height': '100%',
    'width' : '100%'
  });
  $("body").append('<div id="overlay" style="background-color:rgba(255, 255, 255, 0.5);position:absolute;top:0;left:0;height:100%;width:100%;z-index:999;backdrop-filter:blur(2px);"></div>');
}

function removeAcentos(t) {
  text = t.value;
  var semAcento = text.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
  t.value = semAcento;
}

function closeAlert(id) {
  var obj = document.getElementById(id);
  $(obj).css({
    'display' : 'none'
  })
}
