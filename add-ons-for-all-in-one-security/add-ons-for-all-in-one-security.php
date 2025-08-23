<?php
/*
 * Plugin Name: Add-ons for All-In-One Security (AIOS)
 * Plugin URI: https://github.com/vincentbitter/add-ons-for-all-in-one-security
 * Description: Extend AIOS with additional features.
 * Version: 0.1.0
 * Requires at least: 5.0
 * Requires PHP: 5.6
 * Author: Vincent Bitter
 * Author URI: https://vincentbitter.nl
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: add-ons-for-all-in-one-security
 * Domain Path: /languages
 * Requires Plugins: all-in-one-wp-security-and-firewall
 */

add_filter('simba_tfa_after_user_roles', function ($output) {
    $simba_tfa = $GLOBALS['simba_two_factor_authentication'];

    $tfa_requireafter = get_option("tfa_requireafter");
    if (!$tfa_requireafter)
        $tfa_requireafter = 30;
    $tfa_trusted_for = get_option("tfa_trusted_for");
    if (!$tfa_trusted_for)
        $tfa_trusted_for = 30;
    ob_start();
?>

    <form method="post" action="options.php" style="margin-top: 12px">
        <?php settings_fields('tfa_user_roles_required_group'); ?>
        <p>
            <?php $simba_tfa->list_user_roles_checkboxes('required_'); ?>
        </p>
        <p>
            <input type="text" id="tfa_requireafter" class="tfa_requireafter" name="tfa_requireafter" size="6" style="height" value="<?php echo $tfa_requireafter  ?>">
            <?php sprintf(__('Require enabling two-factor authentication after %s days', 'add-ons-for-all-in-one-security'), '<input type="text" id="tfa_requireafter" class="tfa_requireafter" name="tfa_requireafter" size="6" style="height">'); ?>
        </p>
        <p>
            <input type="text" id="tfa_trusted_for" class="tfa_trusted_for" name="tfa_trusted_for" size="6" style="height" value="<?php echo $tfa_trusted_for  ?>">
            <?php sprintf(__('Remember two-factor authentiation for %s days', 'add-ons-for-all-in-one-security'), '<input type="text" id="tfa_trusted_for" class="tfa_trusted_for" name="tfa_trusted_for" size="6" style="height">'); ?>
        </p>

        <?php submit_button(); ?>
    </form>

<?php
    $html = ob_get_contents();
    ob_end_clean();

    return $html;
});
