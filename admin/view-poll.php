<?php
if (! defined('ABSPATH')) {
    exit;
}

$ahtpoma_polls = get_option('ahtpoma_polls', array());

if (isset($ahtpoma_polls[$poll_id])) :

    $ahtpoma_poll         = $ahtpoma_polls[$poll_id];
    $ahtpoma_total_votes  = array_sum($ahtpoma_poll['votes']);
    $ahtpoma_az_range     = range('A', 'Z');
    $ahtpoma_max_char_limit = 10;
?>
<div class="wrap">
    <div class="view_single">
        <div class="q_box">
            <h2><?php esc_html_e('View Poll', 'aht-poll-master'); ?></h2>
            <p>
                <strong><?php esc_html_e('Question:', 'aht-poll-master'); ?></strong>
                <?php echo esc_html($ahtpoma_poll['question']); ?>
            </p>
            <p>
                <strong><?php esc_html_e('Options Graph Results:', 'aht-poll-master'); ?></strong>
            </p>
        </div>

        <div id="chart">
            <ul id="numbers">
                <?php foreach (array(100, 90, 80, 70, 60, 50, 40, 30, 20, 10, 0) as $ahtpoma_pct) : ?>
                <li><span><?php echo esc_html($ahtpoma_pct); ?>%</span></li>
                <?php endforeach; ?>
            </ul>

            <ul id="bars">
                <?php foreach ($ahtpoma_poll['options'] as $ahtpoma_index => $ahtpoma_option) :
                        $ahtpoma_votes           = isset($ahtpoma_poll['votes'][$ahtpoma_index]) ? absint($ahtpoma_poll['votes'][$ahtpoma_index]) : 0;
                        $ahtpoma_percent         = $ahtpoma_total_votes > 0 ? ($ahtpoma_votes / $ahtpoma_total_votes) * 100 : 0;
                        $ahtpoma_truncated_option = strlen($ahtpoma_option) > $ahtpoma_max_char_limit
                            ? substr($ahtpoma_option, 0, $ahtpoma_max_char_limit) . '...'
                            : $ahtpoma_option;
                    ?>
                <li>
                    <div data-percentage="<?php echo esc_attr(round($ahtpoma_percent, 2)); ?>" class="bar">
                        <?php if ($ahtpoma_votes > 0) : ?>
                        <p class="vote_pqua">
                            <?php
                                        printf(
                                            /* translators: %d: number of votes */
                                            esc_html__('QV : %d', 'aht-poll-master'),
                                            esc_html($ahtpoma_votes)
                                        );
                                        ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <span><?php echo esc_html($ahtpoma_az_range[$ahtpoma_index] . ' : ' . $ahtpoma_truncated_option); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="text_box">
            <p class="total_Vot">
                <strong><?php esc_html_e('Total Votes:', 'aht-poll-master'); ?></strong>
                <?php echo esc_html($ahtpoma_total_votes); ?>
            </p>
            <p class="total_per">
                <strong><?php esc_html_e('Total Percentage:', 'aht-poll-master'); ?></strong> 100%
            </p>
            <p class="short_code">
                <strong><?php esc_html_e('Shortcode:', 'aht-poll-master'); ?></strong>
                <code>[ahtpoma_poll id="<?php echo esc_attr($poll_id); ?>"]</code>
            </p>

            <div class="back_btn">
                <a href="<?php echo esc_url(admin_url('admin.php?page=ahtpoma_polls')); ?>">
                    <?php esc_html_e('Back to Poll List', 'aht-poll-master'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
<?php
else :
?>
<div class="wrap">
    <h1><?php esc_html_e('Poll not found', 'aht-poll-master'); ?></h1>
</div>
<?php
endif;