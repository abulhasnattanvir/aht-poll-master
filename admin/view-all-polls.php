<?php
if (!defined('ABSPATH')) {
    exit;
}

$polls = get_option('mpp_polls', array());

if (!is_array($polls)) {
    $polls = array();
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$search_query = isset($_GET['s'])
    ? sanitize_text_field(wp_unslash($_GET['s']))
    : '';

/**
 * FILTER SEARCH FIRST
 */
if (!empty($search_query)) {
    $polls = array_filter($polls, function ($poll) use ($search_query) {
        return stripos($poll['question'], $search_query) !== false;
    });
}

/**
 * PAGINATION SETTINGS
 */
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $current_page = isset($_GET['paged'])
    ? max(1, intval($_GET['paged']))
    : 1;

$polls_per_page = 4;

$total_polls = count($polls);

$total_pages = ceil($total_polls / $polls_per_page);

$start_index = ($current_page - 1) * $polls_per_page;

$paginated_polls = array_slice(
    $polls,
    $start_index,
    $polls_per_page,
    true
);

?>

<div class="wrap">

    <h1><?php esc_html_e('View All Polls', 'aht-poll-master'); ?></h1>

    <!-- SEARCH -->
    <form method="get" style="margin-bottom:20px;">

        <input type="hidden" name="page" value="view_all_polls">

        <input class="search_box" type="text" name="s" value="<?php echo esc_attr($search_query); ?>"
            placeholder="<?php esc_attr_e('Search polls...', 'aht-poll-master'); ?>">

        <input type="submit" class="button" value="<?php esc_attr_e('Search', 'aht-poll-master'); ?>">

    </form>

    <?php if (empty($paginated_polls)) : ?>

    <p><?php esc_html_e('No polls found.', 'aht-poll-master'); ?></p>

    <?php else : ?>

    <?php foreach ($paginated_polls as $poll_id => $poll) : ?>

    <?php
            $total_votes = !empty($poll['votes'])
                ? array_sum($poll['votes'])
                : 0;
            ?>

    <h2>
        <?php echo esc_html__('Q:', 'aht-poll-master'); ?>
        <?php echo esc_html($poll['question']); ?>
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

            <?php if (!empty($poll['options'])) : ?>

            <?php foreach ($poll['options'] as $index => $option) : ?>

            <?php
                            $votes = isset($poll['votes'][$index])
                                ? $poll['votes'][$index]
                                : 0;

                            $percentage = $total_votes > 0
                                ? round(($votes / $total_votes) * 100, 2)
                                : 0;
                            ?>

            <tr>
                <td><?php echo esc_html($option); ?></td>
                <td><?php echo esc_html($votes); ?></td>
                <td><?php echo esc_html($percentage); ?>%</td>
            </tr>

            <?php endforeach; ?>

            <?php endif; ?>

        </tbody>

    </table>

    <br>

    <?php endforeach; ?>

    <?php endif; ?>

    <!-- PAGINATION -->
    <?php if ($total_pages > 1) : ?>

    <div class="pagination">

        <?php
            echo wp_kses_post(paginate_links(array(
                'base'      => add_query_arg('paged', '%#%'),
                'format'    => '',
                'current'   => $current_page,
                'total'     => $total_pages,
                'prev_text' => __('« Previous', 'aht-poll-master'),
                'next_text' => __('Next »', 'aht-poll-master'),
            )));
            ?>

    </div>

    <?php endif; ?>

</div>