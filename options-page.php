<?php

class MuSiteAliasesOptionsPage {

  function MuSiteAliasesOptionsPage() {
    add_action('admin_menu', array(&$this, 'add_options_page'));
    
    if (!empty($_POST)) {
      $mu_site_aliases_plugin = new MuSiteAliases;
      if ($_POST['action'] === 'delete') {
        if (!empty($_POST['alias'])) {
        $mu_site_aliases_plugin->deleteAlias($_POST['alias']);
        }
      }
      else if (!empty($_POST['alias'])) {
      $mu_site_aliases_plugin->createAlias($_POST['alias']);
    }
    
        $this->message = $mu_site_aliases_plugin->flash;
  }
  }

  function add_options_page() {
    add_options_page('Site Aliases', 'Site Aliases', 'manage_options', 'mu_site_aliases', array(&$this, '_display_options_page'));
  }
  
  function _display_options_page() {
    if (! current_user_can('manage_options')) {
      wp_die(__('You do not have sufficient permissions to access this page.'));
    }
?>
<style>
  .mu_site_alias_flash {
    overflow: auto; 
  }
  
  .mu_site_alias_flash div {
    background-color: #FFFF09;
    float: left;
    padding: 10px;
  }
  
  ul#mu_current_aliases_list {
    list-style-type: square;
    margin-left: 25px;
  }
  
  ul#mu_current_aliases_list form {
    display: inline;  
  }
  
  #new-alias-form {
    margin-left: 25px;  
  }
</style>
<div class="wrap">
  <h2>Manage your site's aliases</h2>
  
  <?php if (!empty($this->message)) { ?>
  <div class="mu_site_alias_flash">
    <div><?php echo $this->message; ?></div>
  </div>
  <?php } ?>
  
  <h4>Create a new alias</h4>
  <form id="new-alias-form" method="post">
    <?php echo get_blog_details(1)->domain . get_blog_details(1)->path; ?><input type="text" name="alias" />
    <input type="hidden" name="action" value="create" />
    <button type="submit">Create Alias</button>
  </form>
  
  <div>
    <h4>Current aliases</h4>
    <ul id="mu_current_aliases_list">
      <?php
        global $blog_id;
        $mu_site_aliases_plugin = new MuSiteAliases;
        $aliases = $mu_site_aliases_plugin->getAliases($blog_id);
        foreach ($aliases as $alias) { ?>
          <li>
          <?php echo get_blog_details(1)->domain . get_blog_details(1)->path . $alias->alias; ?>
          <form method="post">
            <input type="hidden" name="action" value="delete" />
            <input type="hidden" name="alias" value="<?php echo $alias->alias; ?>" />
            <button type="submit">Delete</button>
          </form>
          </li>
          <?php
        }
      ?>
    </ul>
  </div>
  
</div>
<?php
  }
}
