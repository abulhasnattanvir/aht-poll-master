<?php
if (! defined('ABSPATH')) {
    exit;
}

// Retrieve polls from database
$polls = get_option('ahtpoma_polls', array());

if (! is_array($polls)) {
    $polls = array();
}
?>

<div class="wrap">
    <?php
    // Show admin notices
    if (isset($_GET['message'])) {
        $message = sanitize_text_field(wp_unslash($_GET['message']));

        if ('deleted' === $message) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Poll deleted successfully.', 'aht-poll-master') . '</p></div>';
        }

        if ('updated' === $message) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Poll updated successfully.', 'aht-poll-master') . '</p></div>';
        }

        if ('status_updated' === $message) {
            $status = isset($_GET['status']) ? sanitize_text_field(wp_unslash($_GET['status'])) : '';
            if ('activated' === $status) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Poll activated successfully.', 'aht-poll-master') . '</p></div>';
            } else {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Poll deactivated successfully.', 'aht-poll-master') . '</p></div>';
            }
        }

        if ('error' === $message) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Something went wrong. Please try again.', 'aht-poll-master') . '</p></div>';
        }
    }
    ?>
    <div class="pool_ques_list">

        <div class="create_btn">
            <a href="<?php echo esc_url(admin_url('admin.php?page=ahtpoma_create_poll')); ?>">
                <?php esc_html_e('Create Poll +', 'aht-poll-master'); ?>
            </a>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('SL NO', 'aht-poll-master'); ?></th>
                    <th><?php esc_html_e('Title', 'aht-poll-master'); ?></th>
                    <th><?php esc_html_e('Options', 'aht-poll-master'); ?></th>
                    <th><?php esc_html_e('Action', 'aht-poll-master'); ?></th>
                </tr>
            </thead>

            <tbody>
                <?php if (! empty($polls)) : ?>

                <?php foreach ($polls as $index => $poll) : ?>

                <?php
                        // Skip invalid poll data
                        if (! is_array($poll)) {
                            continue;
                        }

                        $question = isset($poll['question']) ? $poll['question'] : '';
                        $options  = isset($poll['options']) && is_array($poll['options']) ? $poll['options'] : array();
                        $status   = isset($poll['status']) ? absint($poll['status']) : 0;

                        $nonce = wp_create_nonce('ahtpoma_poll_action');

                        $status_text = $status
                            ? esc_html__('Deactivate', 'aht-poll-master')
                            : esc_html__('Activate', 'aht-poll-master');
                        ?>

                <tr>
                    <td>
                        <span class="sl_no_cur">
                            <?php echo esc_html($index + 1); ?>
                        </span>
                    </td>

                    <td>
                        <?php echo esc_html($question); ?>
                    </td>

                    <td class="option_title">
                        <span class="option_text">
                            <?php
                                    $formatted_options = array();

                                    foreach ($options as $key => $option) {
                                        $formatted_options[] = chr(65 + $key) . '. ' . sanitize_text_field($option);
                                    }

                                    echo esc_html(implode(', ', $formatted_options));
                                    ?>
                        </span>
                    </td>

                    <td>
                        <div class="action_btn">
                            <div class="dropdown">
                                <button type="button" class="dropbtn ahtpoma-dropbtn"
                                    data-index="<?php echo esc_attr($index); ?>">
                                    <?php esc_html_e('Action', 'aht-poll-master'); ?>
                                </button>

                                <div id="dropdown_<?php echo esc_attr($index); ?>" class="dropdown-content">

                                    <!-- Toggle Status -->
                                    <a href="<?php echo esc_url(
                                                            add_query_arg(
                                                                array(
                                                                    'action'   => 'toggle_status',
                                                                    'poll_id'  => $index,
                                                                    '_wpnonce' => $nonce,
                                                                ),
                                                                admin_url('admin.php?page=ahtpoma_polls')
                                                            )
                                                        ); ?>">
                                        <?php echo esc_html($status_text); ?>
                                    </a>

                                    <!-- View -->
                                    <a href="<?php echo esc_url(
                                                            add_query_arg(
                                                                array(
                                                                    'page'     => 'ahtpoma_view_poll',
                                                                    'poll_id'  => absint($index),
                                                                    '_wpnonce' => wp_create_nonce('ahtpoma_view_poll_action'),
                                                                ),
                                                                admin_url('admin.php')
                                                            )
                                                        ); ?>">
                                        <?php esc_html_e('View', 'aht-poll-master'); ?>
                                    </a>

                                    <!-- Edit -->
                                    <a href="<?php echo esc_url(
                                                            add_query_arg(
                                                                array(
                                                                    'page'     => 'ahtpoma_edit_poll',
                                                                    'poll_id'  => absint($index),
                                                                    '_wpnonce' => wp_create_nonce('ahtpoma_edit_poll_action'),
                                                                ),
                                                                admin_url('admin.php')
                                                            )
                                                        ); ?>">
                                        <?php esc_html_e('Edit', 'aht-poll-master'); ?>
                                    </a>

                                    <!-- Delete -->
                                    <a href="<?php echo esc_url(
                                                            add_query_arg(
                                                                array(
                                                                    'action'   => 'delete',
                                                                    'poll_id'  => $index,
                                                                    '_wpnonce' => $nonce,
                                                                ),
                                                                admin_url('admin.php?page=ahtpoma_polls')
                                                            )
                                                        ); ?>"
                                        onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this poll?', 'aht-poll-master')); ?>');">
                                        <?php esc_html_e('Delete', 'aht-poll-master'); ?>
                                    </a>

                                </div>
                            </div>
                        </div>
                    </td>
                </tr>

                <?php endforeach; ?>

                <?php else : ?>

                <tr>
                    <td colspan="4">
                        <?php esc_html_e('No polls found.', 'aht-poll-master'); ?>
                    </td>
                </tr>

                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>