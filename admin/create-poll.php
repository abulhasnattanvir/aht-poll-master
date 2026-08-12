<?php
if (! defined('ABSPATH')) {
    exit;
}

// Get all polls
$ahtpoma_polls = get_option('ahtpoma_polls', array());

if (! is_array($ahtpoma_polls)) {
    $ahtpoma_polls = array();
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
    $ahtpoma_question = isset($_POST['question'])
        ? sanitize_text_field(wp_unslash($_POST['question']))
        : '';

    $ahtpoma_options = isset($_POST['options']) && is_array($_POST['options'])
        ? array_map('sanitize_text_field', wp_unslash($_POST['options']))
        : array();

    $ahtpoma_bgcolor = isset($_POST['bgcolor'])
        ? sanitize_hex_color(wp_unslash($_POST['bgcolor']))
        : '';

    $ahtpoma_status = isset($_POST['status']) ? 1 : 0;

    // Generate a unique poll ID
    $ahtpoma_poll_id = count($ahtpoma_polls);
    while (isset($ahtpoma_polls[$ahtpoma_poll_id])) {
        $ahtpoma_poll_id++;
    }

    // Save the poll
    $ahtpoma_polls[$ahtpoma_poll_id] = array(
        'question' => $ahtpoma_question,
        'options'  => $ahtpoma_options,
        'votes'    => array_fill(0, count($ahtpoma_options), 0),
        'bgcolor'  => $ahtpoma_bgcolor,
        'status'   => $ahtpoma_status,
    );

    update_option('ahtpoma_polls', $ahtpoma_polls);

    // Display success message with shortcode
    $ahtpoma_shortcode = '[ahtpoma_poll id="' . $ahtpoma_poll_id . '"]';
    echo '<div class="notice notice-success is-dismissible"><p>' .
        esc_html__('Poll created successfully! Use the following shortcode to display the poll:', 'aht-poll-master') .
        ' <strong>' . esc_html($ahtpoma_shortcode) . '</strong></p></div>';
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