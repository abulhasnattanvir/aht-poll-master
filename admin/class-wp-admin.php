<?php
if (!defined('ABSPATH')) {
    exit;
}

class MPP_Admin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'handle_poll_action'));
        add_action('admin_init', array($this, 'handle_edit_poll'));
    }

    /**
     * Admin Menu
     */
    public function add_admin_menu()
    {
        add_menu_page(
            'Poll System',
            'Poll System',
            'manage_options',
            'mpp_polls',
            array($this, 'polls_list_page'),
            'dashicons-admin-comments'
        );

        add_submenu_page(
            'mpp_polls',
            'Poll Question List',
            'Poll Question List',
            'manage_options',
            'mpp_polls',
            array($this, 'polls_list_page')
        );

        add_submenu_page(
            'mpp_polls',
            'Create Poll',
            'Create Poll',
            'manage_options',
            'mpp_create_poll',
            array($this, 'create_poll_page')
        );

        add_submenu_page(
            'mpp_polls',
            'View All Polls',
            'View All Polls',
            'manage_options',
            'view_all_polls',
            array($this, 'view_all_poll')
        );

        // Hidden edit page
        add_submenu_page(
            null,
            'Edit Poll',
            'Edit Poll',
            'manage_options',
            'mpp_edit_poll',
            array($this, 'edit_poll_page')
        );

        // Hidden view page
        add_submenu_page(
            null,
            'View Poll',
            'View Poll',
            'manage_options',
            'mpp_view_poll',
            array($this, 'view_poll_page')
        );
    }

    /**
     * Register Settings
     */
    public function register_settings()
    {
        register_setting(
            'mpp_settings_group',
            'mpp_polls',
            array(
                'sanitize_callback' => array($this, 'sanitize_polls'),
            )
        );
    }

    /**
     * Sanitize Poll Data
     */
    public function sanitize_polls($input)
    {
        if (!is_array($input)) {
            return array();
        }

        $sanitized = array();

        foreach ($input as $key => $poll) {

            $sanitized[$key] = array(
                'question' => isset($poll['question'])
                    ? sanitize_text_field($poll['question'])
                    : '',

                'options' => isset($poll['options']) && is_array($poll['options'])
                    ? array_map('sanitize_text_field', $poll['options'])
                    : array(),

                'votes' => isset($poll['votes']) && is_array($poll['votes'])
                    ? array_map('absint', $poll['votes'])
                    : array(),

                'bgcolor' => isset($poll['bgcolor'])
                    ? sanitize_hex_color($poll['bgcolor'])
                    : '',

                'status' => isset($poll['status'])
                    ? absint($poll['status'])
                    : 0,
            );
        }

        return $sanitized;
    }

    /**
     * Poll List Page
     */
    public function polls_list_page()
    {
        include plugin_dir_path(__FILE__) . 'polls-list.php';
    }

    /**
     * Handle Actions
     */
    public function handle_poll_action()
    {
        if (!is_admin()) {
            return;
        }

        if (!isset($_GET['page']) || $_GET['page'] !== 'mpp_polls') {
            return;
        }

        if (!isset($_GET['action'], $_GET['poll_id'])) {
            return;
        }

        $poll_id = absint($_GET['poll_id']);

        $action = sanitize_text_field(
            wp_unslash($_GET['action'])
        );

        if (
            !isset($_GET['_wpnonce']) ||
            !wp_verify_nonce(
                sanitize_text_field(wp_unslash($_GET['_wpnonce'])),
                'mpp_poll_action'
            )
        ) {
            wp_die(esc_html__('Security check failed.', 'aht-poll-master'));
        }

        switch ($action) {

            case 'delete':
                $this->delete_poll($poll_id);
                break;

            case 'toggle_status':
                $this->toggle_poll_status($poll_id);
                break;
        }
    }

    /**
     * Toggle Status
     */
    private function toggle_poll_status($poll_id)
    {
        $polls = get_option('mpp_polls', array());

        if (isset($polls[$poll_id])) {

            $polls[$poll_id]['status'] =
                !empty($polls[$poll_id]['status']) ? 0 : 1;

            update_option('mpp_polls', $polls);
        }

        wp_safe_redirect(
            admin_url('admin.php?page=mpp_polls')
        );

        exit;
    }

    /**
     * Delete Poll
     */
    private function delete_poll($poll_id)
    {
        $polls = get_option('mpp_polls', array());

        if (isset($polls[$poll_id])) {

            unset($polls[$poll_id]);

            update_option('mpp_polls', $polls);
        }

        wp_safe_redirect(
            admin_url('admin.php?page=mpp_polls')
        );

        exit;
    }

    /**
     * View Poll Page
     */
    public function view_poll_page()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view
        if (! isset($_GET['poll_id'], $_GET['_wpnonce'])) {
            return;
        }

        $nonce   = isset($_GET['_wpnonce']) ? wp_unslash($_GET['_wpnonce']) : '';
        $poll_id = absint($_GET['poll_id']);

        if (! wp_verify_nonce($nonce, 'view_poll_action')) {
            return;
        }

        $this->view_poll($poll_id);
    }


    /**
     * Edit Poll Page
     */
    public function edit_poll_page()
    {
        if (!isset($_GET['poll_id'])) {
            return;
        }

        $poll_id = absint($_GET['poll_id']);

        $this->edit_poll($poll_id);
    }

    /**
     * View Poll
     */
    private function view_poll($poll_id)
    {
        include plugin_dir_path(__FILE__) . 'view-poll.php';
    }

    /**
     * View All Polls
     */
    public function view_all_poll()
    {
        include plugin_dir_path(__FILE__) . 'view-all-polls.php';
    }

/**
 * Handle Edit Poll Submit
 */
public function handle_edit_poll()
{
    if (!is_admin()) {
        return;
    }

    if (
        !isset($_GET['page']) ||
        'mpp_edit_poll' !== sanitize_text_field(wp_unslash($_GET['page']))
    ) {
        return;
    }

    if (
        !isset($_SERVER['REQUEST_METHOD']) ||
        'POST' !== sanitize_text_field(
            wp_unslash($_SERVER['REQUEST_METHOD'])
        )
    ) {
        return;
    }

    if (!isset($_POST['edit_poll'])) {
        return;
    }

    if (!isset($_GET['poll_id'])) {
        return;
    }

    $poll_id = absint($_GET['poll_id']);

    // Verify nonce
    if (
        !isset($_POST['edit_poll_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(
                wp_unslash($_POST['edit_poll_nonce'])
            ),
            'edit_poll_action'
        )
    ) {
        wp_die(
            esc_html__('Security check failed.', 'aht-poll-master')
        );
    }

    $polls = get_option('mpp_polls', array());

    if (!isset($polls[$poll_id])) {
        return;
    }

    $question = isset($_POST['question'])
        ? sanitize_text_field(
            wp_unslash($_POST['question'])
        )
        : '';

    $options = isset($_POST['options']) && is_array($_POST['options'])
        ? array_map(
            'sanitize_text_field',
            wp_unslash($_POST['options'])
        )
        : array();

    $status = isset($_POST['status']) ? 1 : 0;

    $polls[$poll_id] = array(
        'question' => $question,

        'options' => $options,

        'votes' => isset($polls[$poll_id]['votes'])
            ? $polls[$poll_id]['votes']
            : array_fill(0, count($options), 0),

        'bgcolor' => isset($polls[$poll_id]['bgcolor'])
            ? $polls[$poll_id]['bgcolor']
            : '',

        'status' => $status,
    );

    update_option('mpp_polls', $polls);

    wp_safe_redirect(
        admin_url('admin.php?page=mpp_polls')
    );

    exit;
}

/**
 * Edit Poll
 */
private function edit_poll($poll_id)
{
    $polls = get_option('mpp_polls', array());

    if (
        !isset($polls[$poll_id]) ||
        !is_array($polls[$poll_id])
    ) {

        echo '<div class="error"><p>' .
            esc_html__('Poll not found.', 'aht-poll-master') .
            '</p></div>';

        return;
    }

    $poll = $polls[$poll_id];

    $question = isset($poll['question'])
        ? $poll['question']
        : '';

    $options = isset($poll['options']) && is_array($poll['options'])
        ? $poll['options']
        : array();

    $status = isset($poll['status'])
        ? absint($poll['status'])
        : 0;

?>

<div class="wrap">

    <h1>
        <?php esc_html_e('Edit Poll', 'aht-poll-master'); ?>
    </h1>

    <form method="post">

        <?php
        wp_nonce_field(
            'edit_poll_action',
            'edit_poll_nonce'
        );
        ?>

        <table class="form-table">

            <tr>

                <th scope="row">
                    <?php esc_html_e('Poll Title', 'aht-poll-master'); ?>
                </th>

                <td>

                    <input type="text" name="question" value="<?php echo esc_attr($question); ?>" class="regular-text">

                </td>

            </tr>

            <tr>

                <th scope="row">
                    <?php esc_html_e('Options', 'aht-poll-master'); ?>
                </th>

                <td>

                    <?php if (!empty($options)) : ?>

                    <?php foreach ($options as $option) : ?>

                    <input type="text" name="options[]" value="<?php echo esc_attr($option); ?>" class="regular-text">

                    <br><br>

                    <?php endforeach; ?>

                    <?php else : ?>

                    <input type="text" name="options[]" class="regular-text">

                    <?php endif; ?>

                </td>

            </tr>

            <tr>

                <th scope="row">
                    <?php esc_html_e('Status', 'aht-poll-master'); ?>
                </th>

                <td>

                    <label>

                        <input type="checkbox" name="status" value="1" <?php checked($status, 1); ?>>

                        <?php esc_html_e('Active', 'aht-poll-master'); ?>

                    </label>

                </td>

            </tr>

        </table>

        <?php
        submit_button(
            __('Update Poll', 'aht-poll-master'),
            'primary',
            'edit_poll'
        );
        ?>

    </form>

</div>

<?php
    }

    /**
     * Create Poll Page
     */
    public function create_poll_page()
    {
        include plugin_dir_path(__FILE__) . 'create-poll.php';
    }
}

new MPP_Admin();