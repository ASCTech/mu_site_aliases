<?php
/*
Plugin Name: Wordpress MU Site Aliases
Version: 0.1
Description: Allows users to create alias paths for their sites
Author: John Colvin
*/
global $wpdb;
define('MU_SITE_ALIAS_TABLE', $wpdb->get_blog_prefix(1) . 'mu_site_aliases');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'options-page.php');

class MuSiteAliases {

  function MuSiteAliases() {
    $this->flash = '';
  }

  function createAlias($path) {
    if ($this->aliasExists($path)) {
      $this->flash = 'The alias "' . $path . '" has already been taken';
      return false; 
    }
    else {
      global $wpdb;
      global $blog_id;
      $row = array('alias' => $path, 'blog_id' => $blog_id);
      if (false === $wpdb->insert(MU_SITE_ALIAS_TABLE, $row)) {
        $this->flash = 'Site alias could not be created';
        return false;
      }
      else {
        $this->flash = 'Site alias created successfully';
        return true;  
      } 
    }
  }

  function aliasExists($path) {
  $alias_row = $this->findAlias($path);
    return empty($alias_row) ? false : true;
  }

  function deleteAlias($path) {
    global $wpdb;
    $sql = 'DELETE FROM ' . MU_SITE_ALIAS_TABLE . ' WHERE `alias` = "' . $path . '"';
    $wpdb->query($sql);
  }
  
  function resolveAlias($path) {
    global $wpdb;
    
  $alias_row = $this->findAlias($path);
  
  $sql = 'SELECT * FROM ' . $wpdb->prefix . 'blogs WHERE blog_id = ' . $alias_row->blog_id . '';
  $results = $wpdb->get_results($sql);
  
  if (empty($results)) {
    return false; 
  }
  
    return $results[0]->path;
  }
  
  function getAliases($blog_id) {
    global $wpdb;
    $sql = 'SELECT * FROM ' . MU_SITE_ALIAS_TABLE . ' WHERE `blog_id` = ' . $blog_id;
    $results = $wpdb->get_results($sql);
    if (empty($results)) {
      return array();
    }
    return $results;
  }
  
  private function findAlias($path) {
  global $wpdb;
    $sql = 'SELECT * FROM ' . MU_SITE_ALIAS_TABLE . ' WHERE `alias` = "' . $path . '" LIMIT 1';
    $results = $wpdb->get_results($sql);
    if (empty($results)) {
      return false;
    }
    else {
      return current($results); 
    }
  }
}

function mu_site_aliases_install() {
    global $wpdb;

    $sql = "CREATE TABLE IF NOT EXISTS " . MU_SITE_ALIAS_TABLE . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      alias varchar(100) DEFAULT '' NOT NULL,
      blog_id mediumint(9) DEFAULT 1 NOT NULL,
      UNIQUE KEY id (id)
    );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

$mu_site_aliases_plugin = new MuSiteAliases;
register_activation_hook(__FILE__,'mu_site_aliases_install');

if (is_admin()) {
  $options_page = new MuSiteAliasesOptionsPage();
}

add_filter('template_redirect', 'my_404_override');
function my_404_override() {
  global $wp_query;

  $mu_site_aliases = new MuSiteAliases;

  $path = trim($_SERVER['REQUEST_URI'], '/');

  if ($mu_site_aliases->aliasExists($path)) {
    status_header(200);
    $wp_query->is_404 = false;
    $site_url = get_bloginfo('url') . $mu_site_aliases->resolveAlias($path);
    header('Location:' . $site_url);
    exit;
  }
}