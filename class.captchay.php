<?php
/**
 *
 * Captchay library
 * Creating captcha challenges easily with support
 * for Arabic/Persian/English languages
 *
 * @author Mohd Mansour Seraj <mmaseraj@gmail.com>
 * @copyright (c) 2015, Mohammed Seraj
 * @version 1.3.1
 * @license MIT
 *
 * PLEASE DON'T REMOVE THIS COPYRIGHT NOTICE
 * If you decide to use this library, a message with the LINK
 * of your project to <mmaseraj@gmail.com> IS highly appreciated.
 *
 * Proudly made in YEMEN
 *
 */
class Captchay {
  private $_image;

  const DRAW_BEFORE_TEXT   = 1;
  const DRAW_AFTER_TEXT    = 2;
  const SESSION_INDEX_NAME = 'captchay_session';

  private $_canvas  = array (
      'width'            => 175,
      'height'           => 80,
      'background-color' => '#fff',
      'border-width'     => 1,
      'border-style'     => 'dotted',
      'border-color'     => '#aaa',
      'save-path'        => './_cache/',
      'auto-delete'      => 1,
      'words-list'       => './libs/words.txt',
      'words-count'      => 2
  );
  private $_font    = array (
      'file-path'  => './libs/fonts/Kharabeesh Font.ttf',
      'color'      => '#555',
      'size'       => 20,
      'min-rotate' => 0,
      'max-rotate' => 5
  );
  private $_texture = array (
      'place'         => Captchay::DRAW_BEFORE_TEXT,
      'arcs-count'    => 20,
      'arcs-color'    => '#aaa',
      'lines-count'   => 20,
      'lines-color'   => '#aaa',
      'dotts-count'   => 20,
      'dotts-color'   => '#aaa',
      'circles-count' => 20,
      'circles-color' => '#aaa'
  );
  /**
   *
   * @var string store the generated string by unicodeLetter()
   */
  private $_captchay_string;
  /**
   *
   * @var string store the orginal captchay word
   */
  private $_normal_string;
  private $_savedColors;
  private $_created = array ();

  /**
   *
   * @param int $width Width of captchay canvas
   * @param int $height Height of captchay canvas
   * @param init $string Captchay content text
   *
   */
  public function __construct ( $width = NULL, $height = NULL, $string = null ) {
    if (in_array ( session_status (), array (PHP_SESSION_DISABLED, PHP_SESSION_NONE) )) {
      trigger_error ( 'Capachy needs a session to run, you have to start_session().', E_USER_ERROR );
    }

    if ($width && is_numeric ( $width )) {
      $this->_canvas[ 'width' ] = $width;
    }

    if ($height && is_numeric ( $height )) {
      $this->_canvas[ 'height' ] = $height;
    }

    if ($string && strlen ( $string ) > 0) {
      $this->_captchay_string = $this->_unicodeLetters ( $string );
    }
  }

  /**
   * (captchay 1.3.1)<br/>
   * Set new settings of captchay
   * @param string $key setting property name
   * @param array $value new array of configs
   */
  public function setConfig ( $key, Array $value ) {
    $name = '_' . $key;
    if (!isset ( $this->$name )) {
      trigger_error ( 'unvalid ' . $key . ' config parameter.', E_USER_ERROR );
    }

    $this->$name = array_merge ( $this->$name, $value );
  }

  /**
   * (captchay 1.3.1) <br />
   * get current captchay settings
   * @param string $key Property name which holds the settings which will be returned
   * @return array array of current settings
   */
  public function getConfig ( $key ) {
    $name = '_' . $key;
    if (!isset ( $this->$name )) {
      trigger_error ( 'unvalid ' . $key . ' config parameter.', E_USER_ERROR );
    }
    return $this->$name;
  }

  /**
   * (captchay 1.3.1) <br />
   * set canvas drawing string
   * @param string $string String that will be placed in the generated image
   */
  public function setString ( $string = NULL ) {
    if (is_null ( $string )) {
      return;
    }
    $this->_captchay_string = $string;
  }

  /**
   * (captchay 1.3.1) <br />
   * get the current drawn string
   * @return string
   */
  public function getString () {
    return $this->_captchay_string;
  }

  /**
   * (captchay 1.3.1) <br />
   * check if captchay input string is same as the drawn string
   * @param string $user_string input string that will be compared with the drawn string
   * @return type
   */
  public function isValid () {
    return $_SESSION[ self::SESSION_INDEX_NAME ] == htmlentities ( trim ( $_POST[ 'captchay_input' ] ) );
  }

  /**
   * (captchay 1.3.1) <br />
   * Create and draw captchay image
   * @return array(orginal_word, width, height, img_tag)
   * @access public
   *
   */
  public function create () {
    if (!file_exists ( $this->_font[ 'file-path' ] )) {
      trigger_error ( "Could not find the font file.", E_USER_ERROR );
    }

    $extn = explode ( '.', $this->_font[ 'file-path' ] );
    if (end ( $extn ) !== 'ttf') {
      trigger_error ( "Font file must be a TTF type and have .ttf extension.", E_USER_ERROR );
    }

    $this->_image = imagecreatetruecolor ( $this->_canvas[ 'width' ], $this->_canvas[ 'height' ] );

    imagefilltoborder ( $this->_image, 0, 0, 1, $this->_createColor ( $this->_canvas[ 'background-color' ] ) );

    if ($this->_texture[ 'place' ] == Captchay::DRAW_BEFORE_TEXT) {
      $this->_drawTexture ();
    }

    $this->_setString ();

    if ($this->_texture[ 'place' ] == Captchay::DRAW_AFTER_TEXT) {
      $this->_drawTexture ();
    }

    if ($this->_canvas[ 'border-width' ]) {
      $this->_setBorder ();
    }

    // saving generated image
    $this->_canvas[ 'save-path' ] = rtrim ( $this->_canvas[ 'save-path' ], '/' );
    $save_place                   = $this->_canvas[ 'save-path' ] . '/' .
        substr ( str_shuffle ( uniqid () ), 0, 9 ) . '_' . time () . '.png';

    if (!is_dir ( $this->_canvas[ 'save-path' ] ) || !is_writable ( $this->_canvas[ 'save-path' ] )) {
      trigger_error ( "Cache folder must be exists and writeable", E_USER_ERROR );
    }

    //$delete_before = 60 * $this->_canvas[ 'auto-delete' ]; // in minutes
    $delete_before = 60 * $this->_canvas[ 'auto-delete' ];

    foreach (glob ( $this->_canvas[ 'save-path' ] . '*.png' ) as $image) {
      $explodeName = explode ( '_', $image );
      $image_time  = end ( $explodeName );

      if (time () - $image_time <= $delete_before) {
        continue;
      }
      unlink ( $image );
    }
    $content = imagepng ( $this->_image, $save_place );
    if (isset ( $_GET[ 'captchay' ] ) && !empty ( $_GET[ 'captchay' ] )) {
      header ( 'content-type: image/png' );
      echo file_get_contents ( $save_place );
      exit;
    }
    imagedestroy ( $this->_image );
    $this->_created = array (
        'word'   => $this->_normal_string,
        'path'   => $save_place,
        'width'  => $this->_canvas[ 'width' ],
        'height' => $this->_canvas[ 'height' ],
        'img'    => '<img src="?captchay=' . rand ( 1111, 9999 ) . '" width=" ' . $this->_canvas[ 'width' ] . '"' .
        ' height="' . $this->_canvas[ 'height' ] . '" />'
    );
    return $this->_created;
  }

  public function random ( $max = 2 ) {
    $path = $this->_canvas[ 'words-list' ];
    if (!file_exists ( $this->_canvas[ 'words-list' ] )) {
      trigger_error ( "Words list file doesn't exists", E_USER_ERROR );
    }

    $content_array = explode ( ' ', preg_replace ( '#\n|\s{3,}|\t#', ' ', file_get_contents ( $path ) ) );
    foreach ($content_array as $key => $val) {
      if ($val == '') {
        unset ( $content_array[ $key ] );
        continue;
      }
      $content_array[ $key ] = trim ( $val );
    }

    $unique_array = array_unique ( $content_array );
    shuffle ( $unique_array );
    return join ( ' ', array_slice ( $unique_array, 0, $max ) );
  }

  public function output () {
    return '<style>
  .capt-wrapper{
    display: inline-block;
    font-family: "Droid Arabic Kufi";
    font-size: 12px;
    padding: 10px;
    direction: rtl;
  }
  .capt-detail{
    display: block;
    margin-bottom: 10px;
    text-align: left;
  }
  .capt-detail a{
    color: #fff;
    background: #0eaaa6;
    display: inline-block;
    font-size: 12px;
    padding: 5px 10px;
    border-right: 1px solid #1cbab6;
  }
  .capt-detail a:hover{
    background-color: #138885;
  }
  .capt-detail a:first-child{
    border-left: 1px solid #118e8b;
    border-right: 0;
  }
  .capt-wrapper label{
    display: block;
  }
  .capt-wrapper input[type=text]{
    font: inherit;
    width: 100%; 
    background: #fff;
    border: 1px solid #ccc;
    padding: 5 10px;
    color: #515151;
  }
  .capt-wrapper .capt-desc label{
    font-size: 12px;
    font-weight: bold;
    color: #74736f;
    margin-bottom: 10px;
  }
</style>

<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<script>
  jQuery( function () {
    jQuery( \'[href="#refresh"]\' ).click( function ( e ) {
      var round = Math.round( Math.random() * 100000 );
      var imgUrl = jQuery( \'#captchay-wrapper img\' ).attr( \'src\', \'?captchay=\' + round );
      e.preventDefault();
    } );
  } );
</script>
<div class="capt-wrapper">
  <div class="capt-desc">
    <div class="capt-detail">
      <a href="#refresh"><img style="width: 12px; height: auto" src="assets/redo.svg" /></i></a><a href="http://github.com/mmaseraj" target="_blank" rel="nofollow">
		<img style="width: 12px; height: auto" src="assets/info-circle.svg" />
	  </a>
    </div>
  </div>
  <div id="captchay-wrapper">' . $this->_created[ 'img' ] . '</div>
  <div class="capt-desc">
    <input type="text" name="captchay_input" placeholder="أدخل الكلمتين التي داخل الصورة" id="captchay_input" />
  </div>
</div>
';
  }

  /**
   * (captchay 1.0) <br />
   * Drawing borders to canvas
   * @return void
   * @access private
   *
   */
  private function _setBorder () {
    if (!in_array ( $this->_canvas[ 'border-style' ], array ('dashed',
            'solid', 'dotted') )) {
      $this->_canvas[ 'border-style' ] = 'solid';
    }

    if ($this->_canvas[ 'border-style' ] == 'dashed') {
      imagesetstyle ( $this->_image, array (
          $this->_createColor ( $this->_canvas[ 'border-color' ] ),
          $this->_createColor ( $this->_canvas[ 'border-color' ] ),
          $this->_createColor ( $this->_canvas[ 'border-color' ] ),
          $this->_createColor ( $this->_canvas[ 'border-color' ] ),
          $this->_createColor ( '#fff' ),
          $this->_createColor ( '#fff' ),
          $this->_createColor ( '#fff' ),
          $this->_createColor ( '#fff' )
      ) );
    } else if ($this->_canvas[ 'border-style' ] == 'dotted') {
      imagesetstyle ( $this->_image, array (
          $this->_createColor ( $this->_canvas[ 'border-color' ] ),
          $this->_createColor ( $this->_canvas[ 'border-color' ] ),
          $this->_createColor ( '#fff' ),
          $this->_createColor ( '#fff' )
      ) );
    } else {
      imagesetstyle ( $this->_image, array (
          $this->_createColor ( $this->_canvas[ 'border-color' ] )
      ) );
    }

    imagesetthickness ( $this->_image, $this->_canvas[ 'border-width' ] );

    $position = 0;
    if ($this->_canvas[ 'border-width' ] > 1) {
      $position = $this->_canvas[ 'border-width' ] / 2;
    }


    // top
    $analyze = 0;
    if ($this->_canvas[ 'border-width' ] == 1) {
      $analyze = 1;
    }
    imageline ( $this->_image, 0, $this->_canvas[ 'border-width' ] - $analyze - $position, $this->_canvas[ 'width' ], $this->_canvas[ 'border-width' ]
        - $analyze - $position, IMG_COLOR_STYLED );

    // left
    imageline ( $this->_image, $this->_canvas[ 'border-width' ] - $analyze - $position, $this->_canvas[ 'border-width' ], $this->_canvas[ 'border-width' ]
        - $analyze - $position, $this->_canvas[ 'height' ], IMG_COLOR_STYLED );

    // bottom
    imageline ( $this->_image, $this->_canvas[ 'width' ], $this->_canvas[ 'height' ]
        - $this->_canvas[ 'border-width' ] + $position, $this->_canvas[ 'border-width' ], $this->_canvas[ 'height' ]
        - $this->_canvas[ 'border-width' ] + $position, IMG_COLOR_STYLED );

    // right
    imageline ( $this->_image, $this->_canvas[ 'width' ] - $this->_canvas[ 'border-width' ]
        + $position, $this->_canvas[ 'border-width' ], $this->_canvas[ 'width' ]
        - $this->_canvas[ 'border-width' ] + $position, $this->_canvas[ 'height' ], IMG_COLOR_STYLED );
  }

  /**
   * (captchay 1.0) <br />
   * Draw string to canvas
   * @access private
   * @return void
   *
   */
  private function _setString () {
    if (empty ( $this->_captchay_string ) || is_null ( $this->_captchay_string )) {
      $random                 = $this->random ( $this->_canvas[ 'words-count' ] );
      $this->_captchay_string = $random;
    }

    $this->_captchay_string = self::_unicodeLetters ( $this->_captchay_string );
    $angle                  = array_rand ( range ( $this->_font[ 'min-rotate' ], $this->_font[ 'max-rotate' ] ) );
    $stringBox              = imagettfbbox ( $this->_font[ 'size' ], $angle, $this->_font[ 'file-path' ], $this->_captchay_string );

    $text_color = $this->_font[ 'color' ];

    if (is_array ( $this->_font[ 'color' ] )) {
      $text_color = $this->_font[ 'color' ][ array_rand ( $this->_font[ 'color' ] ) ];
    } else {
      $text_color = $this->_font[ 'color' ];
    }

    imagettftext ( $this->_image, $this->_font[ 'size' ], $angle, ($this->_canvas[ 'width' ]
        / 2) - ($stringBox[ 2 ] / 2), ($this->_canvas[ 'height' ] / 2) - ($stringBox[ 5 ]
        / 2), $this->_createColor ( $text_color ), $this->_font[ 'file-path' ], $this->_captchay_string );
  }

  /**
   * (captchay 1.0) <br />
   * Create color, covert hexadecimal color, to its right equivlant
   * @param string $hexCode
   * @return image color source
   *
   */
  private function _createColor ( $hexCode ) {
    if (isset ( $this->_savedColors[ $hexCode ] )) {
      return $this->_savedColors[ $hexCode ];
    }

    $color                          = $this->_hex2rgb ( $hexCode );
    $this->_savedColors[ $hexCode ] = imagecolorallocate ( $this->_image, $color[ 0 ], $color[ 1 ], $color[ 2 ] );

    return $this->_savedColors[ $hexCode ];
  }

  /**
   * (captchay 1.0) <br />
   * Draw Textures (arcs, lines, circles, dots)
   * @return void
   * @access private
   */
  private function _drawTexture () {
    for ($i = 0; $i < $this->_texture[ 'arcs-count' ]; $i++) {
      if (is_array ( $this->_texture[ 'arcs-color' ] )) {
        $color = $this->_texture[ 'arcs-color' ][ array_rand ( $this->_texture[ 'arcs-color' ] ) ];
      } else {
        $color = $this->_texture[ 'arcs-color' ];
      }

      imagearc ( $this->_image, rand ( 0, $this->_canvas[ 'width' ] ), rand ( 0, $this->_canvas[ 'height' ] ), rand ( $this->_canvas[ 'width' ]
              / 4, $this->_canvas[ 'width' ] * 2 ), rand ( $this->_canvas[ 'width' ]
              / 4, $this->_canvas[ 'width' ] * 2 ), rand ( 0, 360 ), rand ( 0, 360 ), $this->_createColor ( $color ) );
    }

    for ($i = 0; $i < $this->_texture[ 'lines-count' ]; $i++) {
      if (is_array ( $this->_texture[ 'lines-color' ] )) {
        $color = $this->_texture[ 'lines-color' ][ array_rand ( $this->_texture[ 'lines-color' ] ) ];
      } else {
        $color = $this->_texture[ 'lines-color' ];
      }

      imageline ( $this->_image, rand ( 0, $this->_canvas[ 'width' ] ), rand ( 0, $this->_canvas[ 'height' ] ), rand ( 0, $this->_canvas[ 'width' ] ), rand ( 0, $this->_canvas[ 'height' ] ), $this->_createColor ( $color ) );
    }

    for ($i = 0; $i < $this->_texture[ 'dotts-count' ]; $i++) {
      if (is_array ( $this->_texture[ 'dotts-color' ] )) {
        $color = $this->_texture[ 'dotts-color' ][ array_rand ( $this->_texture[ 'dotts-color' ] ) ];
      } else {
        $color = $this->_texture[ 'dotts-color' ];
      }

      imagesetpixel ( $this->_image, rand ( 0, $this->_canvas[ 'width' ] ), rand ( 0, $this->_canvas[ 'height' ] ), $this->_createColor ( $color ) );
    }

    for ($i = 0; $i < $this->_texture[ 'circles-count' ]; $i++) {
      if (is_array ( $this->_texture[ 'circles-color' ] )) {
        $color = $this->_texture[ 'circles-color' ][ array_rand ( $this->_texture[ 'circles-color' ] ) ];
      } else {
        $color = $this->_texture[ 'circles-color' ];
      }

      $max_heightWidth = 10;
      $min_heightWidth = 2;
      $demin           = rand ( $min_heightWidth, $max_heightWidth );
      imagefilledarc ( $this->_image, rand ( 5, $this->_canvas[ 'width' ] ), rand ( 5, $this->_canvas[ 'height' ] ), $demin, $demin, 0, 360, $this->_createColor ( $color ), rand ( 1, 4 ) );

      imagesetpixel ( $this->_image, rand ( 0, $this->_canvas[ 'width' ] ), rand ( 0, $this->_canvas[ 'height' ] ), $this->_createColor ( $color ) );
    }
  }

  /**
   * (captchay 1.0) <br />
   * Convert hexadecimal color to rgb array
   * @param string $color
   * @access private
   * @return RGB array
   *
   */
  private function _hex2rgb ( $color ) {
    $color = ltrim ( $color, '#' );

    if (strlen ( $color ) == 3) {
      $splitting      = str_split ( $color );
      $splitting[ 0 ] = str_repeat ( $splitting[ 0 ], 2 );
      $splitting[ 1 ] = str_repeat ( $splitting[ 1 ], 2 );
      $splitting[ 2 ] = str_repeat ( $splitting[ 2 ], 2 );
    } else {
      $splitting = str_split ( $color, 2 );
    }

    return array (hexdec ( $splitting[ 0 ] ),
        hexdec ( $splitting[ 1 ] ),
        hexdec ( $splitting[ 2 ] ));
  }

  /**
   * (captchay 1.0) <br />
   * Covert non-ASCII letters to their right ASCII values
   * this METHOD uses bidi library which fix drawing arabic/persian/urdo in images in php
   *
   * @param string $str ARABIC/PERSIAN/URDO string
   * @return fixed string
   * @access private
   *
   */
  private function _unicodeLetters ( $str ) {
    require_once str_replace ( '\\', '/', dirname ( __FILE__ ) ) . '/libs/bidi.php';
    $this->_normal_string                 = $str;
    $_SESSION[ self::SESSION_INDEX_NAME ] = $str;
    $bidi                                 = new bidi();
    $text                                 = explode ( "\n", $str );

    $str = array ();

    foreach ($text as $line) {
      $chars = $bidi->utf8Bidi ( $bidi->UTF8StringToArray ( $line ), 'R' );
      $line  = '';
      foreach ($chars as $char) {
        $line .= $bidi->unichr ( $char );
      }

      $str[] = $line;
    }

    return implode ( "\n", $str );
  }

}

/*
* End of file ./class.Captchay.php
*/
