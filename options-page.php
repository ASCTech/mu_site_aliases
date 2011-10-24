<?php

class MuSiteAliasesOptionsPage {

  function MuSiteAliasesOptionsPage() {
    add_action('admin_menu', array(&$this, 'add_options_page'));
    
    if (!empty($_POST['alias'])) {
    $mu_site_aliases_plugin = new MuSiteAliases;
    $mu_site_aliases_plugin->createAlias($_POST['alias']);
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
</style>
<div class="wrap">
  <h2>Manage your site's aliases</h2>
  
  <?php if (!empty($this->message)) { ?>
  <div class="mu_site_alias_flash">
    <div><?php echo $this->message; ?></div>
  </div>
  <?php } ?>
  
  <form method="post">
    <h4>Create a new alias</h4>
    <?php echo get_blog_details(1)->domain . get_blog_details(1)->path; ?><input type="text" name="alias" />
  <button type="submit">Create Alias</button>
  </form>
  
  <div>
    <h4>Current aliases</h4>
    <ul>
      <?php
        global $blog_id;
        $mu_site_aliases_plugin = new MuSiteAliases;
        $aliases = $mu_site_aliases_plugin->getAliases($blog_id);
        foreach ($aliases as $alias) {
          echo '<li>' . $alias->alias . '</li>';
        }
      ?>
    </ul>
  </div>
  
</div>
<?php
  }
}
