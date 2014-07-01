<?php

/**
 * Theme Wrapper
 *
 * @link http://scribu.net/wordpress/theme-wrappers.html
 */

function main_template_path() {
  return GLP_Wrapper::$main_template;
}
function main_template_base() {
  echo GLP_Wrapper::$base;
}

class GLP_Wrapper {

  static $main_template;
  static $base;

  static function wrap($template) {
    self::$main_template = $template;
    self::$base = substr(basename(self::$main_template), 0, -4);

    if (self::$base === 'index') {
      self::$base = false;
    }

    $templates = array('layout.php');

    if (self::$base) {
      array_unshift($templates, sprintf('layout-%s.php', self::$base ));
    }

    return locate_template($templates);
  }

}
add_filter('template_include', array('GLP_Wrapper', 'wrap'), 99);

?>