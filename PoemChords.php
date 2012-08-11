<?php
# MediaWiki Poem extension v1.0cis
#
# Based on example code from
# http://meta.wikimedia.org/wiki/Write_your_own_MediaWiki_extension
#
# All other code is copyright © 2005 Nikola Smolenski <smolensk@eunet.yu>
# (with modified parser callback and attribute additions)
#
# Anyone is allowed to use this code for any purpose.
# 
# To install, copy the extension to your extensions directory and add line
# include("extensions/PoemChords.php");
# to the bottom of your LocalSettings.php
#
# To use, put some text between <poem></poem> tags
#
# For more information see its page at
# http://meta.wikimedia.org/wiki/Poem_Extension

if ( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
	$wgHooks['ParserFirstCallInit'][] = 'wfPoemChordsExtension';
} else {
	$wgExtensionFunctions[] = 'wfPoemChordsExtension';
}
$wgExtensionCredits['parserhook'][] = array(
	'name'           => 'PoemChords',
	'author'         => array( 'Nikola Smolenski', 'Brion Vibber', 'Steve Sanbeg', 'Vitaly Lipatov' ),
	'url'            => 'http://www.mediawiki.org/wiki/Extension:PoemChords',
	'svn-date'       => '$LastChangedDate: 2008-12-20 09:32:47 +0000 (Sat, 20 Dec 2008) $',
	'svn-revision'   => '$LastChangedRevision: 44839 $',
	'description'    => 'Adds <tt>&lt;poemchords&gt;</tt> tag for poem with guitar chords formatting',
	'descriptionmsg' => 'poem-desc',
);
$wgParserTestFiles[] = dirname( __FILE__ ) . "/poemParserTests.txt";
$wgExtensionMessagesFiles['PoemChords'] =  dirname(__FILE__) . '/PoemChords.i18n.php';

function wfPoemChordsExtension() {
	$GLOBALS['wgParser']->setHook("poemchords","PoemChordsExtension");
	return true;
}

function fancyChords1( $text )
{
	// Lav 31.01.10 TODO: foreach, scan for all chords and see for transpon here
	// UNIQ...QINU
	$text = preg_replace(
		array( "/([CDEFGAH][m679#b\+35]*)/m" ),
		array( "<b>\\1</b>" ),
		$text );

	$text = preg_replace(
		array( "/([CDEFGAH])([679])/m" ),
		array( "\\1<SUB>\\2</SUB>" ),
		$text );

	$text = preg_replace(
		array( "/([CDEFGAH][#b]*)/m" ),
		array( "<span class='abcdefgh'>\\1</span>" ),
		$text );

	return $text;
}

function fancyChords( $text )
{
	// Lav 31.01.10 TODO: foreach, scan for all chords and see for transpon here
	// UNIQ...QINU
	// always insert SUB
	$text = preg_replace(
		//array( "/([CDEFGAH])b/m", "/([CDEFGAH])#/m", "!([ ;:>(/]|^)([CDEFGAH])([#♯b♭]*)([m]*)([679]*)([\+-935]*)([^A-Za-z]|$)!m" ),
		array( "/([CDEFGAH])b/m", "/([CDEFGAH])#/m", "!([ ;:>(/]|^)([CDEFGAH])([#♯b♭]*)([m]*)([679]*)([\+-935]*)!m" ),
		array( "\\1♭", "\\1♯", "\\1<b><span class='guitarchord'>\\2\\3</span>\\4<SUB>\\5</SUB>\\6</b>" ),
		$text );

	// Ужасный хак для Am/F (учитываем, что уже расставили тэги
	$text = preg_replace(
		array( "!(/</b>)([CDEFGAH])([♯♭]*)!m" ),
		array( "\\1<b><span class='guitarchord'>\\2\\3</span></b>" ),
		$text );

	return $text;
}

function fancyChordsLine( $text )
{
	// Lav 31.01.10 TODO: foreach, scan for all chords and see for transpon here
	// UNIQ...QINU
	// always insert SUB
	$text = preg_replace(
		//array( "/([CDEFGAH])( +)([#♯b♭m679\+-935]*)/m" ),
		// Тут остаётся ::
		array( "/([ :>(]|^)([CDEFGAH])(.+)/m", "/(<\/p>)/" ),
		array( "<span class='fullguitarchord'>\\1\\2\\3</span>", "\\1<span class='fullguitarchord'></span>" ),
		$text );

	return $text;
}

function fancyChords0( $text )
{
	// Lav 31.01.10 TODO: foreach, scan for all chords and see for transpon here
	// UNIQ...QINU
	// always insert SUB
	$text = preg_replace(
		array( "/([CDEFGAH])([#bm679\+35]*)/m" ),
		array( "<span class='guitarchord'>\\1\\2</span>\\3<SUB>\\4</SUB>\\5</b>" ),
		$text );

	return $text;
}

function PoemChordsExtension( $in, $param=array(), $parser=null ) {

	/* using newlines in the text will cause the parser to add <p> tags,
 	 * which may not be desired in some cases
	 */
	$nl = isset( $param['compact'] ) ? '' : "\n";
  
	if( method_exists( $parser, 'recursiveTagParse' ) ) {
		//new methods in 1.8 allow nesting <nowiki> in <poem>.
		$tag = $parser->insertStripItem( "<br />", $parser->mStripState );
		$text = preg_replace(
			array( "/^\n/", "/\n$/D", "/\n/", "/^( +)/me", "/(  +)/me" ),
			array( "",      "",      "$tag\n", "str_replace(' ','&nbsp;','\\1')", "str_replace(' ','&nbsp;','\\1')" ),
			$in );

		//$text = fancyChordsLine ($text);
		$text = fancyChords ($text);

		$text = $parser->recursiveTagParse( $text );
	} else {
		$text = preg_replace(
			array( "/^\n/", "/\n$/D", "/\n/", "/(^ +)/me", "/(  +)/me" ),
			array( "", "", "<br />\n", "str_replace(' ','&nbsp;','\\1')", "str_replace(' ','&nbsp;','\\1')" ),
			$in );

		$text = fancyChords ($text);

		$ret = $parser->parse(
			$text,
			$parser->getTitle(),
			$parser->getOptions(),
			// We begin at line start
			true,
			// Important, otherwise $this->clearState()
			// would get run every time <ref> or
			// <references> is called, fucking the whole
			// thing up.
			false
		);

		$text = $ret->getText();
	}

	global $wgVersion;
	if( version_compare( $wgVersion, "1.7alpha" ) >= 0 ) {
		// Pass HTML attributes through to the output.
		$attribs = Sanitizer::validateTagAttributes( $param, 'div' );
	} else {
		// Can't guarantee safety on 1.6 or older.
		$attribs = array();
	}

	// Wrap output in a <div> with "poemchords" class.
	if( isset( $attribs['class'] ) ) {
		$attribs['class'] = 'poemchords ' . $attribs['class'];
	} else {
		$attribs['class'] = 'poemchords';
	}

	// Lav: How I can set attrs in #tag poem construction?
	// set in MediaWiki:Common.css div.poemchords
	//$attribs['style'] = 'font-size:120%;font-family:monospace';

	global $wgScriptPath, $wgJsMimeType;
// FIXME: более глобально
	$jsFile = htmlspecialchars( "$wgScriptPath/extensions/PoemChords/transpose.js" );
	$jsFile1 = htmlspecialchars( "$wgScriptPath/extensions/PoemChords/jquery.floatobject.js" );

	$newscript = sprintf (<<<EOT
<script type="$wgJsMimeType" src="$jsFile"></script>
<script type="$wgJsMimeType" src="$jsFile1"></script>
EOT
	);
	$text = $newscript.$text;

	return Xml::openElement( 'div', $attribs ) .
		$nl .
		trim( $text ) .
		"$nl</div>";
}
