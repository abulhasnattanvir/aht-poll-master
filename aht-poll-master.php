<?php

/**
 * Plugin Name: AHT Poll Master
 * Description: A plugin to create and manage polls.
 * Version: 1.0.0
 * Author: Abul Hasnat Tanvir
 * Author URI: https://github.com/abulhasnattanvir/aht-poll-master
 * Requires at least: 5.6
 * Requires PHP: 8.0
 * Text Domain: aht-poll-master
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (! defined('ABSPATH')) {
    exit;
}

// Constants (main file is in the plugin root)
define('AHTPOMA_VERSION', '1.0.0');
define('AHTPOMA_PLUGIN_FILE', __FILE__);
define('AHTPOMA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AHTPOMA_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include files
require_once AHTPOMA_PLUGIN_DIR . 'admin/class-wp-admin.php';
require_once AHTPOMA_PLUGIN_DIR . 'includes/class-frontend.php';

/**
 * Enqueue frontend scripts and styles
 */
function ahtpoma_enqueue_scripts()
{
    wp_enqueue_style(
        'ahtpoma-progress-bar',
        AHTPOMA_PLUGIN_URL . 'assets/css/progress-bar.css',
        array(),
        AHTPOMA_VERSION
    );

    wp_enqueue_style(
        'ahtpoma-frontend-style',
        AHTPOMA_PLUGIN_URL . 'assets/css/frontend-style.css',
        array(),
        AHTPOMA_VERSION
    );

    wp_enqueue_script(
        'ahtpoma-progress-bar',
        AHTPOMA_PLUGIN_URL . 'assets/js/progress-bar.js',
        array('jquery'),
        AHTPOMA_VERSION,
        true
    );

    wp_enqueue_script(
        'ahtpoma-main',
        AHTPOMA_PLUGIN_URL . 'assets/js/main.js',
        array('jquery'),
        AHTPOMA_VERSION,
        true
    );

    wp_localize_script('ahtpoma-main', 'ahtpoma_vars', array(
        'ajaxurl'            => admin_url('admin-ajax.php'),
        'ahtpoma_vote_nonce' => wp_create_nonce('ahtpoma_vote_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'ahtpoma_enqueue_scripts');

/**
 * Enqueue admin scripts and styles
 */
function ahtpoma_enqueue_admin_scripts($hook)
{

    wp_enqueue_style(
        'ahtpoma-admin-dashboard',
        AHTPOMA_PLUGIN_URL . 'assets/css/admin-dashboard.css',
        array(),
        AHTPOMA_VERSION
    );

    wp_enqueue_style(
        'ahtpoma-dashboard-style',
        AHTPOMA_PLUGIN_URL . 'assets/css/dashboard-style.css',
        array(),
        AHTPOMA_VERSION
    );

    wp_enqueue_style(
        'ahtpoma-style',
        AHTPOMA_PLUGIN_URL . 'assets/css/style.css',
        array(),
        AHTPOMA_VERSION
    );

    // Create Poll page
    if ('poll-system_page_ahtpoma_create_poll' === $hook) {
        wp_register_script('ahtpoma-create-poll', '', array(), AHTPOMA_VERSION, true);
        wp_enqueue_script('ahtpoma-create-poll');

        $create_js = "
            (function() {
                const colorBtn = document.getElementById('ahtpoma-color-picker-btn');
                if (colorBtn) {
                    colorBtn.addEventListener('click', function() {
                        document.getElementById('PollListDropdown').classList.toggle('show');
                    });
                }

                window.addEventListener('click', function(event) {
                    if (!event.target.matches('.cdropbtn')) {
                        document.querySelectorAll('.dropdown-content').forEach(function(el) {
                            el.classList.remove('show');
                        });
                    }
                });

                const addBtn = document.getElementById('ahtpoma-add-option');
                if (addBtn) {
                    addBtn.addEventListener('click', function() {
                        const container = document.getElementById('poll-options');
                        const div = document.createElement('div');
                        div.className = 'option';
                        const optionNumber = container.getElementsByClassName('option').length + 1;
                        div.innerHTML = '<input type=\"text\" name=\"options[]\" placeholder=\"Poll Option ' + optionNumber + '\" /><button type=\"button\" class=\"remove-option button\"></button>';
                        container.appendChild(div);
                    });
                }

                const optionsContainer = document.getElementById('poll-options');
                if (optionsContainer) {
                    optionsContainer.addEventListener('click', function(e) {
                        if (e.target.classList.contains('remove-option')) {
                            e.target.parentElement.remove();
                        }
                    });
                }
            })();
        ";
        wp_add_inline_script('ahtpoma-create-poll', $create_js);
    }

    // Hide Edit and View pages from the menu
    wp_add_inline_style('ahtpoma-admin-dashboard', '
    #toplevel_page_ahtpoma_polls .wp-submenu li a[href*="ahtpoma_edit_poll"],
    #toplevel_page_ahtpoma_polls .wp-submenu li a[href*="ahtpoma_view_poll"] {
        display: none !important;
    }
');

    // Polls List page
    if ('toplevel_page_ahtpoma_polls' === $hook) {
        wp_register_script('ahtpoma-polls-list', '', array(), AHTPOMA_VERSION, true);
        wp_enqueue_script('ahtpoma-polls-list');

        $list_js = "
            (function() {
                document.querySelectorAll('.ahtpoma-dropbtn').forEach(function(btn) {
                    btn.addEventListener('click', function(event) {
                        event.preventDefault();
                        event.stopPropagation();

                        const index = this.getAttribute('data-index');
                        const dropdown = document.getElementById('dropdown_' + index);

                        document.querySelectorAll('.dropdown-content').forEach(function(el) {
                            if (el !== dropdown) el.classList.remove('show');
                        });

                        if (dropdown) dropdown.classList.toggle('show');
                    });
                });

                window.addEventListener('click', function(event) {
                    if (!event.target.matches('.ahtpoma-dropbtn')) {
                        document.querySelectorAll('.dropdown-content').forEach(function(el) {
                            el.classList.remove('show');
                        });
                    }
                });
            })();
        ";
        wp_add_inline_script('ahtpoma-polls-list', $list_js);
    }

    // View Poll page
    if ('admin_page_ahtpoma_view_poll' === $hook) {
        wp_enqueue_script('jquery');
        wp_register_script('ahtpoma-view-poll', '', array('jquery'), AHTPOMA_VERSION, true);
        wp_enqueue_script('ahtpoma-view-poll');

        $view_js = "
            jQuery(document).ready(function($) {
                $('#bars li .bar').each(function() {
                    var percentage = $(this).data('percentage');
                    $(this).animate({ height: percentage + '%' }, 1000);
                });
            });
        ";
        wp_add_inline_script('ahtpoma-view-poll', $view_js);
    }
}
add_action('admin_enqueue_scripts', 'ahtpoma_enqueue_admin_scripts');