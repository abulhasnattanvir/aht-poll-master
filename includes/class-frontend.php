<?php
if (! defined('ABSPATH')) {
    exit;
}

class AHTPOMA_Frontend
{

    public function __construct()
    {
        add_shortcode('ahtpoma_poll', array($this, 'poll_shortcode'));

        add_action('wp_ajax_ahtpoma_vote', array($this, 'handle_vote'));
        add_action('wp_ajax_nopriv_ahtpoma_vote', array($this, 'handle_vote'));
    }

    /**
     * Shortcode: [ahtpoma_poll id="1"]
     */
    public function poll_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts,
            'ahtpoma_poll'
        );

        $poll_id = absint($atts['id']);
        $polls   = get_option('ahtpoma_polls', array());

        if (! isset($polls[$poll_id]) || empty($polls[$poll_id]['status'])) {
            return '';
        }

        $poll        = $polls[$poll_id];
        $total_votes = array_sum($poll['votes']);

        // Calculate percentages
        $poll['votes_percent'] = array();
        if ($total_votes > 0) {
            foreach ($poll['votes'] as $vote) {
                $poll['votes_percent'][] = round(($vote / $total_votes) * 100, 2);
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
                    <span style="width: <?php echo esc_attr($poll['votes_percent'][$index]); ?>%;">
                        <?php echo esc_html(round($poll['votes_percent'][$index])); ?>%
                    </span>
                </div>
            </div>

            <div class="total_vote">
                <span><?php echo esc_html($poll['votes'][$index]); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
        return ob_get_clean();
    }

    /**
     * Handle AJAX vote
     */
    public function handle_vote()
    {
        // Check required fields
        if (! isset($_POST['poll_id']) || ! isset($_POST['option_index'])) {
            wp_send_json_error(array('message' => 'Invalid data.'));
        }

        // Verify nonce
        $nonce = isset($_POST['ahtpoma_vote_nonce'])
            ? sanitize_text_field(wp_unslash($_POST['ahtpoma_vote_nonce']))
            : '';

        if (empty($nonce) || ! wp_verify_nonce($nonce, 'ahtpoma_vote_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce.'));
        }

        $poll_id      = absint($_POST['poll_id']);
        $option_index = absint($_POST['option_index']);
        $polls        = get_option('ahtpoma_polls', array());

        if (! isset($polls[$poll_id]) || ! isset($polls[$poll_id]['options'][$option_index])) {
            wp_send_json_error(array('message' => 'Poll or option not found.'));
        }

        // Ensure votes arrays exist
        if (! isset($polls[$poll_id]['votes'])) {
            $polls[$poll_id]['votes'] = array_fill(0, count($polls[$poll_id]['options']), 0);
        }

        $user_id = get_current_user_id();

        if ($user_id) {
            // Logged-in user
            $user_votes = get_user_meta($user_id, 'ahtpoma_votes', true);
            if (! is_array($user_votes)) {
                $user_votes = array();
            }

            if (isset($user_votes[$poll_id])) {
                $previously_selected = absint($user_votes[$poll_id]);

                if ($previously_selected !== $option_index) {
                    if ($polls[$poll_id]['votes'][$previously_selected] > 0) {
                        $polls[$poll_id]['votes'][$previously_selected]--;
                    }
                    $polls[$poll_id]['votes'][$option_index]++;
                    $user_votes[$poll_id] = $option_index;
                    update_user_meta($user_id, 'ahtpoma_votes', $user_votes);
                }
            } else {
                $user_votes[$poll_id] = $option_index;
                update_user_meta($user_id, 'ahtpoma_votes', $user_votes);
                $polls[$poll_id]['votes'][$option_index]++;
            }
        } else {
            // Guest user (cookie based)
            $cookie_name = 'ahtpoma_voted_' . $poll_id;

            if (isset($_COOKIE[$cookie_name])) {
                $previously_selected = absint($_COOKIE[$cookie_name]);

                if ($previously_selected !== $option_index) {
                    if ($polls[$poll_id]['votes'][$previously_selected] > 0) {
                        $polls[$poll_id]['votes'][$previously_selected]--;
                    }
                    $polls[$poll_id]['votes'][$option_index]++;
                    setcookie($cookie_name, $option_index, time() + (30 * DAY_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN);
                }
            } else {
                setcookie($cookie_name, $option_index, time() + (30 * DAY_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN);
                $polls[$poll_id]['votes'][$option_index]++;
            }
        }

        // Recalculate percentages
        $total_votes                   = array_sum($polls[$poll_id]['votes']);
        $polls[$poll_id]['votes_percent'] = array();

        foreach ($polls[$poll_id]['votes'] as $vote) {
            $polls[$poll_id]['votes_percent'][] = $total_votes > 0
                ? round(($vote / $total_votes) * 100, 2)
                : 0;
        }

        update_option('ahtpoma_polls', $polls);

        wp_send_json_success(array(
            'new_percent'    => $polls[$poll_id]['votes_percent'][$option_index],
            'new_vote_count' => $polls[$poll_id]['votes'][$option_index],
            'total_votes'    => $total_votes,
            'votes'          => $polls[$poll_id]['votes'],
            'votes_percent'  => $polls[$poll_id]['votes_percent'],
        ));
    }
}

new AHTPOMA_Frontend();