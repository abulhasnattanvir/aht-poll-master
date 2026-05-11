<?php
if (!defined('ABSPATH')) {
    exit;
}

class MPP_Frontend {
    public function __construct() {
        add_shortcode('mpp_poll', array($this, 'mpp_poll_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_mpp_vote', array($this, 'mpp_vote'));
        add_action('wp_ajax_nopriv_mpp_vote', array($this, 'mpp_vote'));
    }

    public function mpp_poll_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts,
            'mpp_poll'
        );

        $poll_id = intval($atts['id']);
        $polls = get_option('mpp_polls', array());

        if (!isset($polls[$poll_id]) || !$polls[$poll_id]['status']) {
            return '';
        }

        $poll = $polls[$poll_id];
        $total_votes = array_sum($poll['votes']);
        $poll['votes_percent'] = array();

        if ($total_votes > 0) {
            foreach ($poll['votes'] as $vote) {
                $vote_percentage = round(($vote / $total_votes) * 100, 2);
                $poll['votes_percent'][] = $vote_percentage;
            }
        } else {
            $poll['votes_percent'] = array_fill(0, count($poll['votes']), 0);
        }

        ob_start();
        ?>
<div class="contant_box">
    <div class="title_Box quotation">
        <p class="h3"><?php echo esc_html($poll['question']); ?></p>
    </div>
    <div class="Option_text_box">
        <?php foreach ($poll['options'] as $index => $option) : ?>
        <div class="option_answer container" data-poll-id="<?php echo esc_attr($poll_id); ?>"
            data-option-index="<?php echo esc_attr($index); ?>">
            <div class="sl_no">
                <div class="number_title">
                    <span class="number"><?php echo esc_html(chr(65 + $index)); ?></span>
                </div>
                <div class="option_title">
                    <p><?php echo esc_html($option); ?></p>
                </div>
            </div>
            <div class="progress-bar">
                <div class="progress" data-percent="<?php echo esc_attr($poll['votes_percent'][$index]); ?>%"
                    style="width: <?php echo esc_attr($poll['votes_percent'][$index]); ?>%; background-color: <?php echo isset($poll['bgcolor']) ? esc_attr($poll['bgcolor']) : ''; ?>;">
                    <span
                        style="width: <?php echo esc_attr($poll['votes_percent'][$index]); ?>%;"><?php echo esc_html(round($poll['votes_percent'][$index])); ?>%</span>
                </div>
            </div>
            <div class="total_vote"><span><?php echo esc_html($poll['votes'][$index]); ?></span></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
        return ob_get_clean();
    }

    public function enqueue_scripts()
    {
        $css_file = plugin_dir_path(__FILE__) . 'assets/css/frontend-style.css';

        $js_file = plugin_dir_path(__FILE__) . 'assets/js/main.js';

        $css_version = file_exists($css_file)
            ? filemtime($css_file)
            : '1.0.0';

        $js_version = file_exists($js_file)
            ? filemtime($js_file)
            : '1.0.0';

        wp_enqueue_style(
            'mpp-style',
            plugin_dir_url(__FILE__) . 'assets/css/frontend-style.css',
            array(),
            $css_version
        );

        wp_enqueue_script(
            'mpp-script',
            plugin_dir_url(__FILE__) . 'assets/js/main.js',
            array('jquery'),
            $js_version,
            true
        );

        wp_localize_script(
            'mpp-script',
            'mpp_vars',
            array(
                'ajaxurl'        => admin_url('admin-ajax.php'),
                'mpp_vote_nonce' => wp_create_nonce('mpp_vote_nonce'),
            )
        );
    }

    public function mpp_vote() {
        if (!isset($_POST['poll_id']) || !isset($_POST['option_index'])) {
            wp_send_json_error(array('message' => 'Invalid data.'));
        }

        $nonce = isset($_POST['mpp_vote_nonce'])
            ? sanitize_text_field(wp_unslash($_POST['mpp_vote_nonce']))
            : '';

        if (empty($nonce) || !wp_verify_nonce($nonce, 'mpp_vote_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce.'));
        }

        $poll_id = isset($_POST['poll_id']) ? intval($_POST['poll_id']) : 0;
        $option_index = isset($_POST['option_index']) ? intval($_POST['option_index']) : 0;
        $polls = get_option('mpp_polls', array());

        if (!isset($polls[$poll_id]) || !isset($polls[$poll_id]['options'][$option_index])) {
            wp_send_json_error(array('message' => 'Poll or option not found.'));
        }

        $user_id = get_current_user_id();

        if (!isset($polls[$poll_id]['votes'])) {
            $polls[$poll_id]['votes'] = array_fill(0, count($polls[$poll_id]['options']), 0);
        }

        if (!isset($polls[$poll_id]['votes_percent'])) {
            $polls[$poll_id]['votes_percent'] = array_fill(0, count($polls[$poll_id]['options']), 0);
        }

        if ($user_id) {
            $user_votes = get_user_meta($user_id, 'mpp_votes', true);
            if (!is_array($user_votes)) {
                $user_votes = array();
            }
            if (isset($user_votes[$poll_id])) {
                $previously_selected_option = $user_votes[$poll_id];
                if ($previously_selected_option !== $option_index) {
                    if ($polls[$poll_id]['votes'][$previously_selected_option] > 0) {
                        $polls[$poll_id]['votes'][$previously_selected_option]--;
                    }
                    $polls[$poll_id]['votes'][$option_index]++;
                    $user_votes[$poll_id] = $option_index;
                    update_user_meta($user_id, 'mpp_votes', $user_votes);
                }
            } else {
                $user_votes[$poll_id] = $option_index;
                update_user_meta($user_id, 'mpp_votes', $user_votes);
                $polls[$poll_id]['votes'][$option_index]++;
            }
        } else {
            if (isset($_COOKIE['mpp_voted_' . $poll_id])) {
                $cookie_key = 'mpp_voted_' . $poll_id;
                $previously_selected_option = isset($_COOKIE[$cookie_key])
                    ? sanitize_text_field(wp_unslash($_COOKIE[$cookie_key]))
                    : '';
                if ($previously_selected_option !== $option_index) {
                    if ($polls[$poll_id]['votes'][$previously_selected_option] > 0) {
                        $polls[$poll_id]['votes'][$previously_selected_option]--;
                    }
                    $polls[$poll_id]['votes'][$option_index]++;
                    setcookie('mpp_voted_' . $poll_id, $option_index, time() + 3600 * 24 * 30, COOKIEPATH, COOKIE_DOMAIN);
                }
            } else {
                setcookie('mpp_voted_' . $poll_id, $option_index, time() + 3600 * 24 * 30, COOKIEPATH, COOKIE_DOMAIN);
                $polls[$poll_id]['votes'][$option_index]++;
            }
        }

        $total_votes = array_sum($polls[$poll_id]['votes']);
        $polls[$poll_id]['votes_percent'] = array();
        foreach ($polls[$poll_id]['votes'] as $vote) {
            $vote_percentage = $total_votes > 0 ? ($vote / $total_votes) * 100 : 0;
            $polls[$poll_id]['votes_percent'][] = $vote_percentage;
        }

        update_option('mpp_polls', $polls);

        wp_send_json_success(array(
            'new_percent' => $polls[$poll_id]['votes_percent'][$option_index],
            'new_vote_count' => $polls[$poll_id]['votes'][$option_index],
            'total_votes' => $total_votes,
            'votes' => $polls[$poll_id]['votes'],
            'votes_percent' => $polls[$poll_id]['votes_percent']
        ));
    }
}

new MPP_Frontend();