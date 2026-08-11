<?php
if (! defined('ABSPATH')) {
    exit;
}

// Get all polls
$polls = get_option('ahtpoma_polls', array());

if (! is_array($polls)) {
    $polls = array();
}

// Handle form submission for new polls
if (
    isset($_SERVER['REQUEST_METHOD']) &&
    'POST' === $_SERVER['REQUEST_METHOD'] &&
    isset($_POST['new_poll'])
) {

    // Verify the nonce
    if (
        ! isset($_POST['ahtpoma_poll_nonce']) ||
        ! wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['ahtpoma_poll_nonce'])),
            'ahtpoma_create_poll'
        )
    ) {
        wp_die(esc_html__('Security check failed.', 'aht-poll-master'));
    }

    // Sanitize and validate input
    $question = isset($_POST['question'])
        ? sanitize_text_field(wp_unslash($_POST['question']))
        : '';

    $options = isset($_POST['options']) && is_array($_POST['options'])
        ? array_map('sanitize_text_field', wp_unslash($_POST['options']))
        : array();

    $bgcolor = isset($_POST['bgcolor'])
        ? sanitize_hex_color(wp_unslash($_POST['bgcolor']))
        : '';

    $status = isset($_POST['status']) ? 1 : 0;

    // Generate a unique poll ID
    $poll_id = count($polls);
    while (isset($polls[$poll_id])) {
        $poll_id++;
    }

    // Save the poll
    $polls[$poll_id] = array(
        'question' => $question,
        'options'  => $options,
        'votes'    => array_fill(0, count($options), 0),
        'bgcolor'  => $bgcolor,
        'status'   => $status,
    );

    update_option('ahtpoma_polls', $polls);

    // Display success message with shortcode
    $shortcode = '[ahtpoma_poll id="' . $poll_id . '"]';
    echo '<div class="notice notice-success is-dismissible"><p>' .
        esc_html__('Poll created successfully! Use the following shortcode to display the poll:', 'aht-poll-master') .
        ' <strong>' . esc_html($shortcode) . '</strong></p></div>';
}
?>

<div class="create_page">
    <div class="wrap inner_container">
        <form method="post" action="">
            <?php wp_nonce_field('ahtpoma_create_poll', 'ahtpoma_poll_nonce'); ?>

            <table class="form-table">
                <tr valign="top">
                    <td>
                        <label for="question_name" class="question_name">
                            <?php esc_html_e('Add Poll +', 'aht-poll-master'); ?>
                        </label>
                        <input type="text" id="question_name" name="question"
                            placeholder="<?php esc_attr_e('Poll Title', 'aht-poll-master'); ?>" required />
                    </td>
                </tr>

                <tr>
                    <td class="btn_box">
                        <div class="btn-box-inner">
                            <div class="color_pic">
                                <button type="button" id="ahtpoma-color-picker-btn" class="cdropbtn button">
                                    <?php esc_html_e('Pick Color', 'aht-poll-master'); ?>
                                </button>
                            </div>

                            <div class="dropdown">
                                <div id="PollListDropdown" class="dropdown-content">
                                    <div class="cpic_box">
                                        <input type="color" id="bgcolor" name="bgcolor" value="#000000">
                                    </div>
                                </div>
                            </div>

                            <div class="add_option">
                                <button type="button" id="ahtpoma-add-option" class="button">
                                    <?php esc_html_e('Add Option +', 'aht-poll-master'); ?>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>

                <tr valign="top">
                    <td id="poll-options">
                        <div class="option">
                            <input type="text" name="options[]"
                                placeholder="<?php esc_attr_e('Poll Option 1', 'aht-poll-master'); ?>" />
                            <button type="button" class="remove-option button"></button>
                        </div>
                        <div class="option">
                            <input type="text" name="options[]"
                                placeholder="<?php esc_attr_e('Poll Option 2', 'aht-poll-master'); ?>" />
                            <button type="button" class="remove-option button"></button>
                        </div>
                        <div class="option">
                            <input type="text" name="options[]"
                                placeholder="<?php esc_attr_e('Poll Option 3', 'aht-poll-master'); ?>" />
                            <button type="button" class="remove-option button"></button>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="save_darf_box">
                <?php submit_button(__('Save Changes', 'aht-poll-master'), 'primary', 'new_poll'); ?>
                <?php submit_button(__('Draft', 'aht-poll-master'), 'secondary', 'draft_poll'); ?>
            </div>
        </form>
    </div>
</div>