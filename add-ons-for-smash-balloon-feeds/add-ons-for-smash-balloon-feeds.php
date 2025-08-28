<?php
/*
 * Plugin Name: Add-ons for Smash Balloon Feeds
 * Plugin URI: https://github.com/vincentbitter/add-ons-for-smash-balloon-feeds
 * Description: Extend Smash Balloon plugins like Custom Facebook Feed with additional features.
 * Version: 0.2.0
 * Requires at least: 5.0
 * Requires PHP: 5.6
 * Author: Vincent Bitter
 * Author URI: https://vincentbitter.nl
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

$sbfeeds_supported_plugins = [
    'custom-facebook-feed/custom-facebook-feed.php',
    'custom-twitter-feeds/custom-twitter-feed.php',
];

register_activation_hook(__FILE__, function () use ($sbfeeds_supported_plugins) {
    foreach ($sbfeeds_supported_plugins as $plugin_file) {
        $plugin_name = explode(DIRECTORY_SEPARATOR, $plugin_file)[0];
        sbfeeds_patch($plugin_name);
    }
});

foreach ($sbfeeds_supported_plugins as $plugin_file) {
    register_activation_hook($plugin_file, function () use ($plugin_file) {
        $plugin_name = explode(DIRECTORY_SEPARATOR, $plugin_file)[0];
        sbfeeds_patch($plugin_name);
    });
}

add_action('upgrader_process_complete', function ($upgrader, $options) {
    if ($options['type'] !== 'plugin' || empty($options['plugins'])) return;

    foreach ($options['plugins'] as $pluginPath) {
        $plugin_name = explode(DIRECTORY_SEPARATOR, $pluginPath)[0];
        sbfeeds_patch($plugin_name);
    }
}, 10, 2);

function sbfeeds_patch($plugin_name)
{
    $source_dir = plugin_dir_path(__FILE__) . $plugin_name . DIRECTORY_SEPARATOR;
    $dest_dir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_name . DIRECTORY_SEPARATOR;

    if (is_dir($source_dir) && is_dir($dest_dir)) {
        sbfeeds_patch_dir($source_dir, $dest_dir);
    }
}

function sbfeeds_patch_dir($source, $destination)
{
    if (!is_dir($source) || !is_dir($destination)) return;

    $dir = opendir($source);
    @mkdir($destination, 0755, true);

    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;

        $srcFile = $source . $file;
        $destFile = $destination . $file;

        if (is_dir($srcFile)) {
            sbfeeds_patch_dir($srcFile . DIRECTORY_SEPARATOR, $destFile . DIRECTORY_SEPARATOR);
        } else {
            if (str_ends_with($srcFile, '.patch'))
                sbfeeds_patch_file($srcFile, substr($destFile, 0, -6));
            else
                copy($srcFile, $destFile);
        }
    }

    closedir($dir);
}

function sbfeeds_patch_file($patchFile, $targetFile)
{
    copy($targetFile, $targetFile . '.bak');

    $patch = json_decode(file_get_contents($patchFile), true);
    if (json_last_error() !== JSON_ERROR_NONE) return;

    $content = file_get_contents($targetFile);
    foreach ($patch as $change) {
        if ($change['op'] === 'replace') {
            $content = str_replace($change['find'], $change['value'], $content);
        }
    }

    file_put_contents($targetFile, $content);
}
