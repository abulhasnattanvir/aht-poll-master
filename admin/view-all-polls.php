<?php
if (! defined('ABSPATH')) {
    exit;
}

$ahtpoma_polls = get_option('ahtpoma_polls', array());

if (! is_array($ahtpoma_polls)) {
    $ahtpoma_polls = array();
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended
$ahtpoma_search_query = isset($_GET['s'])
    ? sanitize_text_field(wp_unslash($_GET['s']))
    : '';

/**
 * FILTER SEARCH FIRST
 */
if (! empty($ahtpoma_search_query)) {
    $ahtpoma_polls = array_filter($ahtpoma_polls, function ($ahtpoma_poll) use ($ahtpoma_search_query) {
        return isset($ahtpoma_poll['question']) && stripos($ahtpoma_poll['question'], $ahtpoma_search_query) !== false;
    });
}

/**
 * PAGINATION SETTINGS
 */
$ahtpoma_current_page = isset($_GET['paged'])
    ? max(1, absint($_GET['paged']))
    : 1;
// phpcs:enable

    $ahtpoma_polls_per_page = 4;
$ahtpoma_total_polls    = count($ahtpoma_polls);
$ahtpoma_total_pages    = ceil($ahtpoma_total_polls / $ahtpoma_polls_per_page);
$ahtpoma_start_index    = ($ahtpoma_current_page - 1) * $ahtpoma_polls_per_page;

$ahtpoma_paginated_polls = array_slice(
    $ahtpoma_polls,
    $ahtpoma_start_index,
    $ahtpoma_polls_per_page,
    true
);
?>

<div class="wrap">

    <h1><?php esc_html_e('View All Polls', 'aht-poll-master'); ?></h1>

    <!-- SEARCH -->
    <form method="get" style="margin-bottom:20px;">

        <input type="hidden" name="page" value="ahtpoma_view_all_polls">

        <input class="search_box" type="text" name="s" value="<?php echo esc_attr($ahtpoma_search_query); ?>"
            placeholder="<?php esc_attr_e('Search polls...', 'aht-poll-master'); ?>">

        <input type="submit" class="button" value="<?php esc_attr_e('Search', 'aht-poll-master'); ?>">

    </form>

    <?php if (empty($ahtpoma_paginated_polls)) : ?>

    <p><?php esc_html_e('No polls found.', 'aht-poll-master'); ?></p>

    <?php else : ?>

    <?php foreach ($ahtpoma_paginated_polls as $ahtpoma_poll_id => $ahtpoma_poll) : ?>

    <?php
            $ahtpoma_total_votes = ! empty($ahtpoma_poll['votes']) && is_array($ahtpoma_poll['votes'])
                ? array_sum($ahtpoma_poll['votes'])
                : 0;
            ?>

    <h2>
        <?php echo esc_html__('Q:', 'aht-poll-master'); ?>
        <?php echo esc_html($ahtpoma_poll['question'] ?? ''); ?>
    </h2>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Option', 'aht-poll-master'); ?></th>
                <th><?php esc_html_e('Votes', 'aht-poll-master'); ?></th>
                <th><?php esc_html_e('Percentage', 'aht-poll-master'); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php if (! empty($ahtpoma_poll['options']) && is_array($ahtpoma_poll['options'])) : ?>

            <?php foreach ($ahtpoma_poll['options'] as $ahtpoma_index => $ahtpoma_option) : ?>

            <?php
                            $ahtpoma_votes = isset($ahtpoma_poll['votes'][$ahtpoma_index])
                                ? absint($ahtpoma_poll['votes'][$ahtpoma_index])
                                : 0;

                            $ahtpoma_percentage = $ahtpoma_total_votes > 0
                                ? round(($ahtpoma_votes / $ahtpoma_total_votes) * 100, 2)
                                : 0;
                            ?>

            <tr>
                <td><?php echo esc_html($ahtpoma_option); ?></td>
                <td><?php echo esc_html($ahtpoma_votes); ?></td>
                <td><?php echo esc_html($ahtpoma_percentage); ?>%</td>
            </tr>

            <?php endforeach; ?>

            <?php endif; ?>
        </tbody>
    </table>

    <br>

    <?php endforeach; ?>

    <?php endif; ?>

    <!-- PAGINATION -->
    <?php if ($ahtpoma_total_pages > 1) : ?>

    <div class="pagination">
        <?php
            echo wp_kses_post(
                paginate_links(
                    array(
                        'base'      => add_query_arg('paged', '%#%'),
                        'format'    => '',
                        'current'   => $ahtpoma_current_page,
                        'total'     => $ahtpoma_total_pages,
                        'prev_text' => __('« Previous', 'aht-poll-master'),
                        'next_text' => __('Next »', 'aht-poll-master'),
                    )
                )
            );
            ?>
    </div>

    <?php endif; ?>

</div>