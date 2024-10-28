<?php
/*
Plugin Name: Account Lock Lite
Plugin URI: http://dennishoppe.de/en/wordpress-plugins/account-lock
Description: This plugin enables WordPress Administrator to lock and unlock user accounts without deleting them.
Version: 1.1.1
Author: Dennis Hoppe
Author URI: http://DennisHoppe.de
*/

If (!Class_Exists('wp_plugin_account_lock')){
class wp_plugin_account_lock {
  var $base_url; # url to the plugin directory

  function __construct(){
    # Read base
    $this->Load_Base_Url();

    # Handle Activation and Deactivation of the plugin
    Register_Activation_Hook(__FILE__, Array($this, 'Plugin_Activation'));
    Register_Deactivation_Hook(__FILE__, Array($this, 'Plugin_Deactivation'));

    # Register Filter and Actions
    Add_Action('init', Array($this, 'Load_TextDomain'));
    Add_Action('edit_user_profile', Array($this, 'Print_Status_Edit_Field'));
    Add_Action('edit_user_profile_update', Array($this, 'Save_User_Account_Status'));
    Add_filter('manage_users_columns', Array($this, 'User_Table_Columns'));
    Add_Filter('manage_users_custom_column', Array($this, 'User_Table_Row'), 10, 3);
    Add_Action('load-users.php', Array($this, 'Enqueue_Backend_Scripts'));
    Add_Action('load-users.php', Array($this, 'Handle_Lock_Unlock_Request'));
    Add_Filter('authenticate', Array($this, 'User_Authenticate'), 999);
    Add_Filter('allow_password_reset', Array($this, 'Allow_Password_Reset'), 999, 2);
    Add_Filter('views_users', Array($this, 'Print_Lock_Limit_Notice'));
    Add_Action('time_to_reset_account_locks', Array($this, 'Reset_Account_Locks'));

    # Add to GLOBALs
    $GLOBALS[__CLASS__] = $this;
  }

  function Load_Base_Url(){
    $absolute_plugin_folder = RealPath(DirName(__FILE__));

    If (StrPos($absolute_plugin_folder, ABSPATH) === 0)
      $this->base_url = Get_Bloginfo('wpurl').'/'.SubStr($absolute_plugin_folder, Strlen(ABSPATH));
    Else
      $this->base_url = Plugins_Url(BaseName(DirName(__FILE__)));

    $this->base_url = Str_Replace("\\", '/', $this->base_url); # Windows Workaround
  }

  function Load_TextDomain(){
    $locale = Apply_Filters( 'plugin_locale', get_locale(), __CLASS__ );
    Load_TextDomain (__CLASS__, DirName(__FILE__).'/language/' . $locale . '.mo');
  }

  function t ($text, $context = Null){
    # Translates the string $text with context $context
    If (Empty($context))
      return Translate ($text, __CLASS__);
    Else
      return Translate_With_GetText_Context ($text, $context, __CLASS__);
  }

  function Plugin_Activation(){
    If (!WP_Next_Scheduled('time_to_reset_account_locks'))
      WP_Schedule_Event(Time() + 24 * 3600, 'daily', 'time_to_reset_account_locks');
  }

  function Plugin_Deactivation(){
    WP_Clear_Scheduled_Hook('time_to_reset_account_locks');
  }

  function Enqueue_Backend_Scripts(){
    WP_Enqueue_Style('account-lock-backend', $this->base_url.'/css/backend.css');
  }

  function Handle_Lock_Unlock_Request(){
    If (Current_User_Can('edit_users')){
      If (IsSet($_GET['lock'])){
        $this->Lock_Account($_GET['lock']);
        WP_Redirect(Remove_Query_Arg('lock'));
        Exit;
      }

      If (IsSet($_GET['unlock'])){
        $this->Unlock_Account($_GET['unlock']);
        WP_Redirect(Remove_Query_Arg('unlock'));
        Exit;
      }
    }
  }

  function Lock_Account($user_id){
    Update_User_Meta($user_id, 'account_locked', True);
    Do_Action('lock_account', $user_id);
  }

  function Unlock_Account($user_id){
    Delete_User_Meta($user_id, 'account_locked');
    Do_Action('unlock_account', $user_id);
  }

  function Is_Account_Locked($user_id){
    return Get_User_Meta($user_id, 'account_locked', True);
  }

  function Print_Status_Edit_Field($user){
    $locked = $this->Is_Account_Locked($user->data->ID);
    ?>
    <h3><?php Echo $this->t('Lock User Account') ?></h3>
    <table class="form-table">
    <tr>
      <th><label for="account_status"><?php Echo $this->t('Account status') ?></label></th>
      <td>
        <select name="account_status" id="account_status">
          <option value="unlocked" <?php Selected(!$locked) ?> ><?php Echo $this->t('Unlocked') ?></option>
          <option value="locked" <?php Selected($locked) ?> ><?php Echo $this->t('Locked') ?></option>
        </select>
        <p class="error-message"><?php Echo $this->Get_Lock_Limit_Notice() ?></p>
      </td>
    </tr>
    </table>
    <?php
  }

  function Save_User_Account_Status($user_id){
    If (IsSet($_REQUEST['account_status'])){
      If ($_REQUEST['account_status'] == 'locked'){
        $this->Lock_Account($user_id);
      }
      Else {
        $this->Unlock_Account($user_id);
      }
    }
  }

  function User_Table_Columns($arr_columns){
    $arr_columns['account_status'] = $this->t('Account status');
    return $arr_columns;
  }

  function User_Table_Row($value, $column, $user_id){
    If ($column == 'account_status' && $user_id != Get_Current_User_ID()){
      If ($this->Is_Account_Locked($user_id)){
        $return = SPrintF('<div class="user-account-status locked">%s</div>', $this->t('Locked'));
        If (Current_User_Can('edit_users'))
          $return .= SPrintF('<div class="row-actions"><span class="unlock"><a href="%s">%s</a></span></div>', Add_Query_Arg('unlock', $user_id), $this->t('Unlock'));
      }
      Else {
        $return = SPrintF('<div class="user-account-status unlocked">%s</div>', $this->t('Unlocked'));
        If (Current_User_Can('edit_users'))
          $return .= SPrintF('<div class="row-actions"><span class="lock"><a href="%s">%s</a></span></div>', Add_Query_Arg('lock', $user_id), $this->t('Lock'));
      }
    }
    return $return;
  }

  function User_Authenticate($user){
    If ($this->Is_Account_Locked($user->data->ID)){
      $message = Apply_Filters('account_lock_message', SPrintF('<strong>%s</strong> %s', $this->t('Error:'), $this->t('Your account is locked.')), $user);
      return New WP_Error('authentication_failed', $message);
    }
    Else
      return $user;
  }

  function Allow_Password_Reset($allowed, $user_id){
    return $this->Is_Account_Locked($user_id) ? False : $allowed;
    /*
    If ($this->Is_Account_Locked($user_id))
      return False;
    Else
      return $allowed;
    */
  }

  function Print_Lock_Limit_Notice($views){
    ?>
    <div id="message" class="error">
      <p><?php Echo $this->Get_Lock_Limit_Notice() ?></p>
    </div>
    <?php
    return $views;
  }

  function Get_Lock_Limit_Notice(){
    return SPrintF('%s %s %s',
      $this->t('Please notice:'),
      $this->t('In the Lite Version all accounts will be unlocked daily.'),
      $this->t('Why not switching to <a href="http://dennishoppe.de/en/wordpress-plugins/account-lock" target="_blank">Account Lock Pro</a>? :)')
    );
  }

  function Reset_Account_Locks(){
    $arr_locked_users = Get_Users(Array('meta_key' => 'account_locked'));
    ForEach ($arr_locked_users AS $locked_user){
      $this->Unlock_Account($locked_user->ID);
    }
  }

} /* End of the Class */
New wp_plugin_account_lock;
} /* End of the If-Class-Exists-Condition */