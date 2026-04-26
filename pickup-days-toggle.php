<?php
/*
Plugin Name: Pickup Days Toggle (Nested Tabs Support)
Plugin URI: https://github.com/sajidashrafdev/pickup-days-toggle
Description: Pickup days control + Elementor tabs + cutoff logic + dynamic messages.
Version: 3.2
Author: Sajid Ashraf
*/

if (!defined('ABSPATH')) exit;

// ===============================
// ADMIN MENU
// ===============================
add_action('admin_menu', function () {
    add_menu_page(
        'Pickup Days',
        'Pickup Days',
        'manage_options',
        'pickup-days-settings',
        'pdt_settings_page',
        'dashicons-calendar',
        25
    );
});

// ===============================
// SETTINGS PAGE
// ===============================
function pdt_settings_page() {

    if (isset($_POST['pdt_save'])) {

        $config = $_POST['pdt_config'] ?? [];

        $config['cutoff_time'] = sanitize_text_field($config['cutoff_time'] ?? '10:00');
        $config['msg_before']  = wp_kses_post($config['msg_before'] ?? '');
        $config['msg_after']   = wp_kses_post($config['msg_after'] ?? '');

        update_option('pdt_config', $config);

        echo '<div class="updated"><p>Settings Saved!</p></div>';
    }

    $config = get_option('pdt_config', []);
    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
?>
<div class="wrap">
    <h1>Pickup Days Settings</h1>

    <form method="post">
        <table class="form-table">

            <!-- Cutoff -->
            <tr>
                <th>Order Cutoff Time</th>
                <td>
                    <input type="time" name="pdt_config[cutoff_time]"
                    value="<?php echo esc_attr($config['cutoff_time'] ?? '10:00'); ?>">
                </td>
            </tr>

            <!-- Messages -->
            <tr>
                <th>Message Before Cutoff</th>
                <td>
                    <textarea name="pdt_config[msg_before]" rows="3" class="large-text"><?php
                    echo esc_textarea($config['msg_before'] ?? 'Order before 10 AM for same-day pickup');
                    ?></textarea>
                </td>
            </tr>

            <tr>
                <th>Message After Cutoff</th>
                <td>
                    <textarea name="pdt_config[msg_after]" rows="3" class="large-text"><?php
                    echo esc_textarea($config['msg_after'] ?? 'Same-day orders closed, please select a future day.');
                    ?></textarea>
                </td>
            </tr>

            <!-- DAYS -->
            <?php foreach ($days as $day):
                $is_active = isset($config[$day]['active']) ? 'checked' : '';
                $loc = $config[$day]['location'] ?? '';
            ?>
            <tr>
                <th><?php echo ucfirst($day); ?></th>
                <td>
                    <input type="checkbox"
                        name="pdt_config[<?php echo $day; ?>][active]"
                        value="1" <?php echo $is_active; ?>>
                    Active
                </td>
                <td>
                    <input type="text"
                        name="pdt_config[<?php echo $day; ?>][location]"
                        value="<?php echo esc_attr($loc); ?>"
                        class="regular-text"
                        placeholder="Pickup location">
                </td>
            </tr>
            <?php endforeach; ?>

        </table>

        <input type="submit" name="pdt_save" class="button button-primary" value="Save Settings">
    </form>
</div>
<?php
}

// ===============================
// SHORTCODES
// ===============================

// Location shortcodes
add_action('init', function () {
    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

    foreach ($days as $day) {
        add_shortcode('location_' . $day, function () use ($day) {
            $config = get_option('pdt_config', []);
            return (!empty($config[$day]['active']) && !empty($config[$day]['location']))
                ? esc_html($config[$day]['location'])
                : '';
        });
    }
});

// Pickup message shortcode
add_shortcode('pickup_message', function () {
    return '<div id="pickup-message"></div>';
});

// ===============================
// FRONTEND JS
// ===============================
add_action('wp_footer', function () {

    if (is_admin()) return;

    $config = get_option('pdt_config', []);

    $active_days = [];

    foreach (['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day) {
        if (!empty($config[$day]['active'])) {
            $active_days[] = $day;
        }
    }

    $cutoff_time = $config['cutoff_time'] ?? '10:00';
    $msg_before  = $config['msg_before'] ?? 'Order before 10 AM for same-day pickup';
    $msg_after   = $config['msg_after'] ?? 'Same-day orders closed, please select a future day.';
?>

<script>
(function () {

    try {

        const activeDays = <?php echo json_encode($active_days); ?>;
        const cutoffTime = "<?php echo esc_js($cutoff_time); ?>";
        const msgBefore = <?php echo json_encode($msg_before); ?>;
        const msgAfter  = <?php echo json_encode($msg_after); ?>;

        const dayMap = {
            0:"sunday",1:"monday",2:"tuesday",
            3:"wednesday",4:"thursday",
            5:"friday",6:"saturday"
        };

        const dayToIndex = {
            "monday":1,"tuesday":2,"wednesday":3,
            "thursday":4,"friday":5,"saturday":6,"sunday":7
        };

        // ===============================
        // TAB CONTROL (FIXED)
        // ===============================
        function syncTabs() {

            const allDays = Object.keys(dayToIndex);

            allDays.forEach(day => {

                const index = dayToIndex[day];

                const selectors = [
                    "#tab-" + day,
                    '.e-n-tab-title[data-tab-index="' + index + '"]'
                ];

                selectors.forEach(sel => {
                    document.querySelectorAll(sel).forEach(el => {

                        if (!activeDays.includes(day)) {
                            el.style.display = "none";
                        } else {
                            el.style.display = "";
                        }

                    });
                });
            });
        }

        // ===============================
        // CUT-OFF LOGIC (IRELAND TIME)
        // ===============================
        function getValidDay() {

            const now = new Date();
            const ireland = new Date(
                now.toLocaleString("en-US", { timeZone: "Europe/Dublin" })
            );

            const [h,m] = cutoffTime.split(":").map(Number);

            let dayIndex = ireland.getDay();

            const currentMinutes = ireland.getHours()*60 + ireland.getMinutes();
            const cutoffMinutes = h*60 + m;

            if (currentMinutes >= cutoffMinutes) {
                dayIndex = (dayIndex + 1) % 7;
            }

            let today = dayMap[dayIndex];

            if (activeDays.includes(today)) return today;

            for (let i=0;i<7;i++) {
                let next = dayMap[(dayIndex+i)%7];
                if (activeDays.includes(next)) return next;
            }

            return null;
        }

        // ===============================
        // MESSAGE UPDATE
        // ===============================
        function updatePickupMessage() {

            const el = document.getElementById("pickup-message");
            if (!el) return;

            const now = new Date();
            const ireland = new Date(
                now.toLocaleString("en-US", { timeZone: "Europe/Dublin" })
            );

            const [h,m] = cutoffTime.split(":").map(Number);

            const current = ireland.getHours()*60 + ireland.getMinutes();
            const cutoff = h*60 + m;

            el.innerHTML = (current < cutoff) ? msgBefore : msgAfter;
        }

        // ===============================
        // INIT
        // ===============================
        window.addEventListener("load", function () {

            syncTabs();
            updatePickupMessage();

            let btn = document.getElementById("today-menu-btn");
            if (btn) {
                let a = btn.querySelector("a");
                let valid = getValidDay();
                if (a && valid) {
                    a.href = "/menu/?day=" + valid;
                }
            }

        });

    } catch (e) {
        console.error("Pickup Plugin Error:", e);
    }

})();
</script>

<?php
});
