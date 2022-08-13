<?php

/**
 * This class handles formatting poems in WikiText, specifically anything within
 * <PoemChords></PoemChords> tags.
 *
 * To use, put poetry text in {{PoemChords|Name|Text text|date}}, add follow template for it:
 * https://soulibre.ru/index.php?title=Template:PoemChords
 *
 * @license CC0-1.0
 * @author Nikola Smolenski <smolensk@eunet.yu>
 */

class PoemChords {
	/**
	 * Bind the renderPoemChords function to the <poemchords> tag
	 * @param Parser $parser
	 */
	public static function init( Parser $parser ) {
		$parser->setHook( 'poemchords', [ self::class, 'renderPoemChords' ] );
	}

protected static function fancyChords1( $text )
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

protected static function fancyChords( $text )
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

protected static function fancyChordsLine( $text )
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

protected static function fancyChords0( $text )
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


	/**
	 * Parse the text into proper poem format
	 * @param string|null $in The text inside the poem tag
	 * @param string[] $param
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 */
	public static function renderPoemChords( $in, array $param, Parser $parser, PPFrame $frame ) {
		// using newlines in the text will cause the parser to add <p> tags,
		// which may not be desired in some cases
		$newline = isset( $param['compact'] ) ? '' : "\n";

		$tag = $parser->insertStripItem( "<br />" );

		// load javascript
		$parserOutput = $parser->getOutput();
		$parserOutput->addModules( 'ext.PoemChords' );

		// replace colons with indented spans
		$text = preg_replace_callback( '/^(:+)(.+)$/m', [ self::class, 'indentVerse' ], $in );

		// replace newlines with <br /> tags unless they are at the beginning or end
		// of the poem, or would directly follow exactly 4 dashes. See Parser::internalParse() for
		// the exact syntax for horizontal rules.
		$text = preg_replace(
			[ '/^\n/', '/\n$/D', '/(?<!^----)\n/m' ],
			[ "", "", "$tag\n" ],
			$text
		);

		// replace spaces at the beginning of a line with non-breaking spaces
		$text = preg_replace_callback( ['/^( +)/m', '/(  +)/m'], [ self::class, 'replaceSpaces' ], $text );

		$text = $parser->recursiveTagParse( $text, $frame );

		// Because of limitations of the regular expression above, horizontal rules with more than 4
		// dashes still need special handling.
		$text = str_replace( '<hr />' . $tag, '<hr />', $text );

		$text = self::fancyChords ($text);

		$attribs = Sanitizer::validateTagAttributes( $param, 'div' );

		// Wrap output in a <div> with "poem" class.
		if ( isset( $attribs['class'] ) ) {
			$attribs['class'] = 'poemchords ' . $attribs['class'];
		} else {
			$attribs['class'] = 'poemchords';
		}

	// Lav: How I can set attrs in #tag poem construction?
	// set in MediaWiki:Common.css div.poemchords
	//$attribs['style'] = 'font-size:120%;font-family:monospace';

		return Html::rawElement( 'div', $attribs, $newline . trim( $text ) . $newline );
	}

	/**
	 * Callback for preg_replace_callback() that replaces spaces with non-breaking spaces
	 * @param string[] $m Matches from the regular expression
	 *   - $m[1] consists of 1 or more spaces
	 * @return string
	 */
	protected static function replaceSpaces( array $m ) {
		return str_replace( ' ', '&#160;', $m[1] );
	}

	/**
	 * Callback for preg_replace_callback() that wraps content in an indented span
	 * @param string[] $m Matches from the regular expression
	 *   - $m[1] consists of 1 or more colons
	 *   - $m[2] consists of the text after the colons
	 * @return string
	 */
	protected static function indentVerse( array $m ) {
		$attribs = [
			'class' => 'mw-poem-indented',
			'style' => 'display: inline-block; margin-left: ' . strlen( $m[1] ) . 'em;'
		];
		// @todo Should this really be raw?
		return Html::rawElement( 'span', $attribs, $m[2] );
	}

  public static function onBeforePageDisplay(&$wgOut, &$sk) {
//error_log("Hello");
      $wgOut->addModules('ext.PoemChords');
  }
}

