<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Esther Brunner <wikidesign@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class helper_plugin_discussion extends DokuWiki_Plugin {

  function getInfo(){
    return array(
      'author' => 'Esther Brunner',
      'email'  => 'wikidesign@gmail.com',
      'date'   => '2007-02-21',
      'name'   => 'Avatar Plugin (helper class)',
      'desc'   => 'Functions to get info about comments to a wiki page',
      'url'    => 'http://www.wikidesign/en/plugin/avatar/start',
    );
  }
  
  function getMethods(){
    $result = array();
    $result[] = array(
      'name'   => 'getXHTML',
      'desc'   => 'returns the XHTML to display an avatar',
      'params' => array(
        'user or mail'     => 'string',
        'title (optional)' => 'string',
        'align (optional)' => 'string',
        'size (optional)'  => 'integer'),
      'return' => array('xhtml' => 'string'),
    );
    return $result;
  }
  
  function getXHTML($user, $title = '', $align = '', $size = null){
    
    // determine the URL of the avatar image
    $src = $this->_getAvatarURL($user, $title, $size);
    
    // output with vcard photo microformat
    return '<span class="vcard">'.
      '<img src="'.$src.'" class="media'.$align.' photo fn" title="'.hsc($title).'" '.
      'alt="'.hsc($title).'" width="'.$size.'" height="'.$size.'" />'.
      '</span>';
  }
  
  /**
   * Main function to determine the avatar to use
   */
  function _getAvatarURL($user, &$title, &$size){
    global $auth;
    
    if (!$size || !is_int($size)) $size = $this->getConf('size');
    
    // check first if a local image for the given user exists
    $userinfo = $auth->getUserData($user);
    if (is_array($userinfo)){
      if (($userinfo['name']) && (!$title)) $title = $userinfo['name'];
      $avatar = $this->getConf('namespace').':'.$user;
      $formats = array('.png', '.jpg', '.gif');
      foreach ($formats as $format){
        $img = mediaFN($avatar.$format);
        if (!@file_exists($img)) continue;
        $src = ml($avatar.$format, array('w' => $size, 'h' => $size));
        break;
      }
      if (!$src) $mail = $userinfo['mail'];
    } else {
      $mail = $user;
    }
    
    if (!$src){
      $seed = md5($user);
    
      // we take the monster ID as default
      $default = ml(DOKU_URL.'lib/plugins/avatar/monsterid.php?'.
        'seed='.$seed.
        '&size='.$size.
        '&.png', 'cache=recache', true, '&');
      
      // do not pass invalid or empty emails to gravatar site...
      if (isvalidemail($mail) && ($size <= 80)){
        $src = ml('http://www.gravatar.com/avatar.php?'.
          'gravatar_id='.$seed.
          '&default='.urlencode(DOKU_URL.$default).
          '&size='.$size.
          '&rating='.$this->getConf('rating').
          '&.jpg', 'cache=recache');
      
      // show only default image if invalid or empty email given
      } else {
        $src = $default;
      }
    }
    
    if (!$title) $title = obfuscate($mail);
    
    return $src;
  }
        
}
  
//Setup VIM: ex: et ts=4 enc=utf-8 :