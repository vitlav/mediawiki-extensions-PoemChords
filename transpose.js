// Transpose Chords
// TODO: fix bemole missing

// Step to next (upper) halftone
var abcdefgh_u = {
	 'A' : 'A♯',
	 'H' : 'C',
	 'C' : 'C♯',
	 'D' : 'D♯',
	 'E' : 'F',
	 'F' : 'F♯',
	 'G' : 'G♯',
	'C♯' : 'D',
	'D♯' : 'E',
	'F♯' : 'G',
	'G♯' : 'A',
	'A♯' : 'H',
	'D♭' : 'D',
	'E♭' : 'E',
	'G♭' : 'G',
	'A♭' : 'A',
	'H♭' : 'H'
};


// Step to prev (lower) halftone
var abcdefgh_d = {
	 'A' : 'G♯',
	 'H' : 'A♯',
	 'C' : 'H',
	 'D' : 'C♯',
	 'E' : 'D♯',
	 'F' : 'E',
	 'G' : 'F♯',
	'C♯' : 'C',
	'D♯' : 'D',
	'A♯' : 'A',
	'F♯' : 'F',
	'G♯' : 'G',
	'D♭' : 'C',
	'E♭' : 'D',
	'G♭' : 'F',
	'A♭' : 'G',
	'H♭' : 'A'
};

function abcdefgh_up(){
  var chords = document.getElementsByClassName("guitarchord");
  for (var c in chords) {
    chords[c].innerHTML =  abcdefgh_u[ chords[c].innerHTML ];
  }
}

function abcdefgh_down(){
  var chords = document.getElementsByClassName("guitarchord");
  for (var c in chords) {
    chords[c].innerHTML =  abcdefgh_d[ chords[c].innerHTML ];
  }
}

function enable_floatchords(){
  //var chords = document.getElementsByClassName("fullguitarchord");
  var ct = '';
  //for (var c in chords) {
  //  if (chords[c].innerHTML)
  //  ct = ct + ' ' + chords[c].innerHTML;
  //}
  // http://www.softcomplex.com/docs/get_window_size_and_scrollbar_position.html
  var chords = document.getElementsByClassName("poemchords");
  var pt = chords[0].innerHTML;
  var at = pt.split(/<br[^>]*?>/g);
  // Хак - пропускаем обычный текст
  var re = /[а-яА-Я]/;
  for (var c in at) {
    if (!at[c].match(re))
      ct = ct + at[c] + '<br>';
  }

  var po = $j('#floatchordsanchor').position();

  $j('#floatchords').html(ct);
  $j('#floatchords').makeFloat({
      x: po.left,
      y: po.top+10
  });
}

