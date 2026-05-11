<?php
if (!defined('ABSPATH')) {
    exit;
}

$polls = get_option('mpp_polls', array());
if (isset($polls[$poll_id])) : 
    $poll = $polls[$poll_id];
    $total_votes = array_sum($poll['votes']);
    $azRange = range('A', 'Z');
    $maxCharLimit = 10;
    ?>
<div class="wrap">
    <div class="view_single">
        <div class="q_box">
            <h2><?php esc_html_e('View Poll', 'aht-poll-master'); ?></h2>
            <p><strong><?php esc_html_e('Question:', 'aht-poll-master'); ?></strong>
                <?php echo esc_html($poll['question']); ?></p>
            <p><strong><?php esc_html_e('Options Graph Results:', 'aht-poll-master'); ?></strong></p>
        </div>

        <div id="chart">
            <ul id="numbers">
                <?php foreach (array(100, 90, 80, 70, 60, 50, 40, 30, 20, 10, 0) as $pct) : ?>
                <li><span><?php echo esc_html($pct); ?>%</span></li>
                <?php endforeach; ?>
            </ul>
            <ul id="bars">
                <?php foreach ($poll['options'] as $index => $option) : 
                        $votes = $poll['votes'][$index];
                        $percent = $total_votes > 0 ? ($votes / $total_votes) * 100 : 0;
                        $truncatedOption = strlen($option) > $maxCharLimit ? substr($option, 0, $maxCharLimit) . '...' : $option;
                        ?>
                <li>
                    <div data-percentage="<?php echo esc_attr(round($percent, 2)); ?>" class="bar">
                        <?php if ($votes != 0) : ?>
                        <p class="vote_pqua">
                            <?php printf(esc_html('QV : %d', 'aht-poll-master'), esc_html($votes)); ?></p>
                        <?php endif; ?>
                    </div>
                    <span><?php echo esc_html($azRange[$index] . ' : ' . $truncatedOption); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="text_box">
            <p class="total_Vot"><strong><?php esc_html_e('Total Votes:', 'aht-poll-master'); ?></strong>
                <?php echo esc_html($total_votes); ?></p>
            <p class="total_per"><strong><?php esc_html_e('Total Percentage:', 'aht-poll-master'); ?></strong> 100%</p>
            <p class="short_code"><strong><?php esc_html_e('Shortcode:', 'aht-poll-master'); ?></strong>
                <code>[mpp_poll id="<?php echo esc_attr($poll_id); ?>"]</code>
            </p>
            <div class="back_btn">
                <a
                    href="<?php echo esc_url(admin_url('admin.php?page=view_all_polls')); ?>"><?php esc_html_e('Back to Poll List', 'aht-poll-master'); ?></a>
            </div>
        </div>
    </div>
</div>
<script>
jQuery(document).ready(function($) {
    $("#bars li .bar").each(function(key, bar) {
        var percentage = $(this).data('percentage');
        $(this).animate({
            'height': percentage + '%'
        }, 1000);
    });
});
</script>
<?php else : ?>
<div class="wrap">
    <h1><?php esc_html_e('Poll not found', 'aht-poll-master'); ?></h1>
</div>
<?php endif; ?>