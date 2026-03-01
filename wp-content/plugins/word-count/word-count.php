<?php

/*
    Plugin Name: Word Count
    Description: A simple plugin that adds a word count, character count, and time to read box to posts.
    Version: 1.0
    Author: Mr Joshers
    Author URI: https://www.joshcoast.com/
    Text Domain: wcp_domain
    Domain Path: /languages
*/

class JcWordCountAndTimePlugin {

    function __construct() {
        add_action('admin_menu', array($this, 'adminPage') );
        add_action('admin_init', array($this, 'settings'));
        add_filter('the_content', array( $this, 'if_wrap'));
    }

    function if_wrap( $content ) {
        // Default value is important because these options might not exist yet in the database.
        if ( is_main_query() AND is_single() AND ( get_option('wcp_wordCount', '1') OR get_option('wcp_characterCount', '1') OR get_option('wcp_readTime', '1') ) ) {
            return $this->create_html( $content );
        }
        return $content;
    }

    function create_html( $content ) {
        $html = '<h3>' . esc_html(get_option('wcp_headline', 'Post Stats')) . '</h3><p>';

        if ( get_option('wcp_wordCount', '1') OR get_option('wcp_readTime', '1')) {
            $wordCount = str_word_count(strip_tags($content));
        }

        if ( get_option('wcp_wordCount', '1') ) {
            $html .= esc_html__('This post has', 'wcp_domain') . ' ' . $wordCount . ' ' . esc_html__('words', 'wcp_domain') . '.<br>';
        }

        if ( get_option('wcp_characterCount', '1') ) {
            $html .= esc_html__('This post has', 'wcp_domain') . ' ' . strlen(strip_tags($content)) . ' ' . esc_html__('characters', 'wcp_domain') . '.<br>';
        }

        if ( get_option('wcp_readTime', '1') ) {
            $time_to_read = round($wordCount / 200);
            if ( $time_to_read == '0' OR $time_to_read == '1' ) {
                $time_to_read = '1'  . ' ' . esc_html__('minute to read', 'wcp_domain');
            } else {
                $time_to_read = $time_to_read . ' ' . esc_html__('minutes to read', 'wcp_domain');
            }
            $html .= esc_html__('This post will take about ', 'wpc_domain') . ' ' . $time_to_read . '.<br>';
        }

        if ( get_option('wcp_location', '0') === '0' ) {
            return $html . $content;
        } else {
            return $content . $html;
        }

    }

    function settings() {
        add_settings_section('wcp_first_section', null, null, 'word-count-settings-page');

        // Location Select list.
        add_settings_field('wcp_location', 'Display Location', array($this, 'location_html'), 'word-count-settings-page', 'wcp_first_section' );
        register_setting( 'wordCountPlugin', 'wcp_location', array('sanitize_callback' => array($this, 'sanitize_location'), 'default' => '0') );

        // Headline.
        add_settings_field('wcp_headline', 'Headline Text', array($this, 'headline_html'), 'word-count-settings-page', 'wcp_first_section' );
        register_setting( 'wordCountPlugin', 'wcp_headline', array('sanitize_text_field', 'default' => 'Post Statistics') );

        // Word Count Checkbox.
        add_settings_field('wcp_wordCount', 'Word Count', array($this, 'checkbox_html'), 'word-count-settings-page', 'wcp_first_section', array( 'theName' => 'wcp_wordCount' ) );
        register_setting( 'wordCountPlugin', 'wcp_wordCount', array('sanitize_text_field', 'default' => '1') );

        // Character Count Checkbox.
        add_settings_field('wcp_characterCount', 'Character Count', array($this, 'checkbox_html'), 'word-count-settings-page', 'wcp_first_section', array( 'theName' => 'wcp_characterCount' ) );
        register_setting( 'wordCountPlugin', 'wcp_characterCount', array('sanitize_text_field', 'default' => '1') );

        // Read Time Checkbox.
        add_settings_field('wcp_readTime', 'Read Time', array($this, 'checkbox_html'), 'word-count-settings-page', 'wcp_first_section', array( 'theName' => 'wcp_readTime' ) );
        register_setting( 'wordCountPlugin', 'wcp_readTime', array('sanitize_text_field', 'default' => '1') );
    }

    function sanitize_location( $input ) {
        if ( $input != '0' AND $input != '1' ) {
            add_settings_error('wcp_location', 'wcp_location_error', 'Display location must be either Beginning or End.');
            return get_option('wcp_location');
        }
        return $input;
    }

    // Location select list.
    function location_html() { ?>
        <select name="wcp_location">
            <option value="0" <?php selected( get_option('wcp_location', '0') ) ?> >Beginning of Post</option>
            <option value="1" <?php selected( get_option('wcp_location', '1') ) ?>>End of Post</option>
        </select>
    <?php }

    // Headline Input.
    function headline_html() { ?>
        <input type="text" name="wcp_headline" value="<?php echo esc_attr( get_option('wcp_headline') )  ?> ">
    <?php }

    // Reusable Checkbox.
    function checkbox_html( $args ) { ?>
        <input type="checkbox" name="<?php echo $args['theName'] ?>" value="1" <?php checked( get_option( $args['theName'] ), '1') ?> >
    <?php }

    /*
    // Word Count Checkbox.
    function wordCount_html() { ?>
        <input type="checkbox" name="wcp_wordCount" value="1" <?php checked( get_option( 'wcp_wordCount'), '1') ?> >
    <?php }

    // Character Count Checkbox.
    function characterCount_html() { ?>
        <input type="checkbox" name="wcp_characterCount" value="1" <?php checked( get_option( 'wcp_characterCount'), '1') ?> >
    <?php }

    // Read Time Checkbox.
    function readTime_html() { ?>
        <input type="checkbox" name="wcp_readTime" value="1" <?php checked( get_option( 'wcp_readTime'), '1') ?> >
    <?php }
    */



function adminPage() {
    add_options_page( 'Word Count Settings', esc_html__('Word Count', 'wcp_domain'), 'manage_options', 'word-count-settings-page', array($this, 'our_html') );
    }

    function our_html() { ?>
     <div class="wrap">
        <h1>Word Count Settings</h1>
        <form action="options.php" method="POST">
            <?php
                settings_fields('wordCountPlugin');
                do_settings_sections('word-count-settings-page');
                submit_button();
            ?>
        </form>
     </div>

    <?php }
}

$jcWordCountAndTimePlugin = new jcWordCountAndTimePlugin();



