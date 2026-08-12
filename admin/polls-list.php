<?php
if (! defined('ABSPATH')) {
    exit;
}

// Retrieve polls from database
$ahtpoma_polls = get_option('ahtpoma_polls', array());

if (! is_array($ahtpoma_polls)) {
    $ahtpoma_polls = array();
}
?>

<div class="wrap">
    <?php
    // Show admin notices
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ( isset( $_GET['message'] ) ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $ahtpoma_message = sanitize_text_field( wp_unslash( $_GET['message'] ) );

        if ( 'deleted' === $ahtpoma_message ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Poll deleted successfully.', 'aht-poll-master' ) . '</p></div>';
        }

        if ( 'updated' === $ahtpoma_message ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Poll updated successfully.', 'aht-poll-master' ) . '</p></div>';
        }

        if ( 'status_updated' === $ahtpoma_message ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $ahtpoma_status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
            if ( 'activated' === $ahtpoma_status ) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Poll activated successfully.', 'aht-poll-master' ) . '</p></div>';
            } else {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Poll deactivated successfully.', 'aht-poll-master' ) . '</p></div>';
            }
        }

        if ( 'error' === $ahtpoma_message ) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Something went wrong. Please try again.', 'aht-poll-master' ) . '</p></div>';
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
                <?php if (! empty($ahtpoma_polls)) : ?>

                <?php foreach ($ahtpoma_polls as $ahtpoma_index => $ahtpoma_poll) : ?>

                <?php
                        // Skip invalid poll data
                        if (! is_array($ahtpoma_poll)) {
                            continue;
                        }

                        $ahtpoma_question = isset($ahtpoma_poll['question']) ? $ahtpoma_poll['question'] : '';
                        $ahtpoma_options  = isset($ahtpoma_poll['options']) && is_array($ahtpoma_poll['options']) ? $ahtpoma_poll['options'] : array();
                        $ahtpoma_status   = isset($ahtpoma_poll['status']) ? absint($ahtpoma_poll['status']) : 0;

                        $ahtpoma_nonce = wp_create_nonce('ahtpoma_poll_action');

                        $ahtpoma_status_text = $ahtpoma_status
                            ? esc_html__('Deactivate', 'aht-poll-master')
                            : esc_html__('Activate', 'aht-poll-master');
                        ?>

                <tr>
                    <td>
                        <span class="sl_no_cur">
                            <?php echo esc_html($ahtpoma_index + 1); ?>
                        </span>
                    </td>

                    <td>
                        <?php echo esc_html($ahtpoma_question); ?>
                    </td>

                    <td class="option_title">
                        <span class="option_text">
                            <?php
                                    $ahtpoma_formatted_options = array();

                                    foreach ($ahtpoma_options as $ahtpoma_key => $ahtpoma_option) {
                                        $ahtpoma_formatted_options[] = chr(65 + $ahtpoma_key) . '. ' . sanitize_text_field($ahtpoma_option);
                                    }

                                    echo esc_html(implode(', ', $ahtpoma_formatted_options));
                                    ?>
                        </span>
                    </td>

                    <td>
                        <div class="action_btn">
                            <div class="dropdown">
                                <button type="button" class="dropbtn ahtpoma-dropbtn"
                                    data-index="<?php echo esc_attr($ahtpoma_index); ?>">
                                    <?php esc_html_e('Action', 'aht-poll-master'); ?>
                                </button>

                                <div id="dropdown_<?php echo esc_attr($ahtpoma_index); ?>" class="dropdown-content">

                                    <!-- Toggle Status -->
                                    <a href="<?php echo esc_url(
                                                            add_query_arg(
                                                                array(
                                                                    'action'   => 'toggle_status',
                                                                    'poll_id'  => $ahtpoma_index,
                                                                    '_wpnonce' => $ahtpoma_nonce,
                                                                ),
                                                                admin_url('admin.php?page=ahtpoma_polls')
                                                            )
                                                        ); ?>">
                                        <?php echo esc_html($ahtpoma_status_text); ?>
                                    </a>

                                    <!-- View -->
                                    <a href="<?php echo esc_url(
                                                            add_query_arg(
                                                                array(
                                                                    'page'     => 'ahtpoma_view_poll',
                                                                    'poll_id'  => absint($ahtpoma_index),
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
                                                                    'poll_id'  => absint($ahtpoma_index),
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
                                                                    'poll_id'  => $ahtpoma_index,
                                                                    '_wpnonce' => $ahtpoma_nonce,
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