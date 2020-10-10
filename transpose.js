// Transpose Chords
// TODO: fix bemole missing

// Diese rules
// Step to next (upper) halftone
var abcdefgh_ud = {
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
// for incorrect cases
	'D♭' : 'D',
	'E♭' : 'E',
	'G♭' : 'G',
	'A♭' : 'A',
	'H♭' : 'H'
};


// Step to prev (lower) halftone
var abcdefgh_dd = {
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
// for incorrect cases
	'D♭' : 'C',
	'E♭' : 'D',
	'G♭' : 'F',
	'A♭' : 'G',
	'H♭' : 'A'
};

// Bemol rules
// Step to next (upper) halftone
var abcdefgh_ub = {
	 'A' : 'H♭',
	 'H' : 'C',
	 'C' : 'D♭',
	 'D' : 'E♭',
	 'E' : 'F',
	 'F' : 'G♭',
	 'G' : 'A♭',
	'D♭' : 'D',
	'E♭' : 'E',
	'G♭' : 'G',
	'A♭' : 'A',
	'H♭' : 'H',
// for incorrect cases
	'C♯' : 'D',
	'D♯' : 'E',
	'F♯' : 'G',
	'G♯' : 'A',
	'A♯' : 'H',
};


// Step to prev (lower) halftone
var abcdefgh_db = {
	 'A' : 'A♭',
	 'H' : 'H♭',
	 'C' : 'H',
	 'D' : 'D♭',
	 'E' : 'E♭',
	 'F' : 'E',
	 'G' : 'G♭',
	'D♭' : 'C',
	'E♭' : 'D',
	'G♭' : 'F',
	'A♭' : 'G',
	'H♭' : 'A',
// for incorrect cases
	'C♯' : 'C',
	'D♯' : 'D',
	'A♯' : 'A',
	'F♯' : 'F',
	'G♯' : 'G',
};


var diese_list = [ "C♯", "D♯", "A♯", "F♯", "G♯" ];
var bemol_list = [ "D♭", "E♭", "G♭", "A♭", "H♭" ];

function convert_accords(chords, cnv)
{
  for (var c in chords) {
    if (chords[c].innerHTML == undefined)
      continue;
    if ( chords[c].innerHTML in cnv)
      chords[c].innerHTML = cnv[ chords[c].innerHTML ];
    else {
      //chords[c].innerHTML = '_E-'+chords[c].innerHTML+'-E_';
      console.log('Missed '+chords[c].innerHTML);
    }
  }
}

function abcdefgh_up()
{
  var chords = document.getElementsByClassName("guitarchord");

  // Определим текущие знаки при ключе
  var cnv = abcdefgh_ud;
  for (var c in chords) {
    if ( bemol_list.indexOf(chords[c].innerHTML) !== -1 ) {
      cnv = abcdefgh_ub;
      break;
    }
  }

  convert_accords(chords, cnv);
}

function abcdefgh_down()
{
  var chords = document.getElementsByClassName("guitarchord");
  // Определим текущие знаки при ключе
  var cnv = abcdefgh_db;
  for (var c in chords) {
    //if (chords[c].innerHTML in diese_list) {
    if ( diese_list.indexOf(chords[c].innerHTML) !== -1 ) {
      cnv = abcdefgh_dd;
      break;
    }
  }

  convert_accords(chords, cnv);
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

  var po = $j('#floatchordsanchor').offset();

  $j('#floatchords').html(ct);
  $j('#floatchords').makeFloat({
      x: po.left-150,
      y: 150
  });
}

