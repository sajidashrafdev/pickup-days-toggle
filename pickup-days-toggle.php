<?php
/*
Plugin Name: Pickup Days Toggle (Nested Tabs Support)
Plugin URI: https://github.com/sajidashrafdev/pickup-days-toggle
Description: Toggle pickup days and control Elementor Nested Tabs visibility with cutoff logic.
Version: 3.0
Author: Sajid Ashraf
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// ===============================
// ADMIN MENU & SETTINGS
// ===============================
add_action('admin_menu', function () {
    add_menu_page('Pickup Days', 'Pickup Days', 'manage_options', 'pickup-days-settings', 'pdt_settings_page', 'dashicons-calendar', 25);
});

function pdt_settings_page() {
    if (isset($_POST['pdt_save'])) {
        $config = $_POST['pdt_config'] ?? [];

        // sanitize cutoff time
        if (!empty($config['cutoff_time'])) {
            $config['cutoff_time'] = sanitize_text_field($config['cutoff_time']);
        }

        update_option('pdt_config', $config);
        echo '<div class="updated"><p>Settings Saved!</p></div>';
    }

    $config = get_option('pdt_config', []);
    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    ?>
    <div class="wrap">
        <h1>Pickup Days & Locations</h1>

        <form method="post">
            <table class="form-table">

                <!-- Cutoff Time -->
                <tr>
                    <th>Order Cutoff Time</th>
                    <td>
                        <input type="time" name="pdt_config[cutoff_time]" 
                        value="<?php echo esc_attr($config['cutoff_time'] ?? '10:00'); ?>">
                        <p class="description">After this time, next day's menu will be shown.</p>
                    </td>
                </tr>

                <?php foreach ($days as $day): 
                    $is_active = isset($config[$day]['active']) ? 'checked' : '';
                    $loc = $config[$day]['location'] ?? '';
                ?>
                <tr>
                    <th><?php echo ucfirst($day); ?></th>
                    <td>
                        <input type="checkbox" name="pdt_config[<?php echo $day; ?>][active]" value="1" <?php echo $is_active; ?>> Active
                    </td>
                    <td>
                        <input type="text" name="pdt_config[<?php echo $day; ?>][location]" 
                        value="<?php echo esc_attr($loc); ?>" 
                        class="regular-text" 
                        placeholder="Enter location...">
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
add_action('init', function() {
    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

    foreach ($days as $day) {
        add_shortcode('location_' . $day, function() use ($day) {
            $config = get_option('pdt_config', []);
            return (!empty($config[$day]['active']) && !empty($config[$day]['location'])) 
                ? esc_html($config[$day]['location']) 
                : '';
        });
    }
});

// ===============================
// FRONTEND SCRIPT
// ===============================
add_action('wp_footer', function () {

    if (is_admin()) return;

    $config = get_option('pdt_config', []);
    $active_days = [];

    foreach ($config as $day => $data) {
        if (in_array($day, ['monday','tuesday','wednesday','thursday','friday','saturday','sunday']) 
            && !empty($data['active'])) {
            $active_days[] = $day;
        }
    }

    $cutoff_time = !empty($config['cutoff_time']) ? $config['cutoff_time'] : '10:00';
?>
<script>
(function () {

    try {

        const activeDays = <?php echo json_encode($active_days); ?>;
        const cutoffTime = "<?php echo esc_js($cutoff_time); ?>";

        const allDays = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

        const dayMap = {
            0: "sunday", 1: "monday", 2: "tuesday", 3: "wednesday",
            4: "thursday", 5: "friday", 6: "saturday"
        };

        const dayToIndex = {
            "monday": 1, "tuesday": 2, "wednesday": 3,
            "thursday": 4, "friday": 5, "saturday": 6, "sunday": 7
        };

        function getCurrentDayWithCutoff() {
            const now = new Date();
            const parts = cutoffTime.split(":");

            if (parts.length !== 2) return dayMap[now.getDay()];

            const cutHour = parseInt(parts[0]);
            const cutMin = parseInt(parts[1]);

            let dayIndex = now.getDay();

            if (
                now.getHours() > cutHour ||
                (now.getHours() === cutHour && now.getMinutes() >= cutMin)
            ) {
                dayIndex = (dayIndex + 1) % 7;
            }

            return dayMap[dayIndex];
        }

        function getValidDay() {
            let today = getCurrentDayWithCutoff();

            if (activeDays.includes(today)) return today;

            let index = Object.keys(dayMap).find(key => dayMap[key] === today);

            for (let i = 0; i < 7; i++) {
                let nextIndex = (parseInt(index) + i) % 7;
                let nextDay = dayMap[nextIndex];

                if (activeDays.includes(nextDay)) return nextDay;
            }

            return null;
        }

        function syncTabs() {
            allDays.forEach(day => {
                const index = dayToIndex[day];

                const selectors = [
                    "#tab-" + day,
                    '.e-n-tab-title[data-tab-index="' + index + '"]'
                ];

                selectors.forEach(selector => {
                    document.querySelectorAll(selector).forEach(el => {
                        el.style.display = activeDays.includes(day) ? "" : "none";
                    });
                });
            });
        }

        function activateTab(day) {
            if (!day) return;

            const index = dayToIndex[day];

            const btn =
                document.getElementById("tab-" + day) ||
                document.querySelector('.e-n-tab-title[data-tab-index="' + index + '"]');

            if (btn) btn.click();
        }

        window.addEventListener("load", function () {

            if (!activeDays.length) {
                document.querySelectorAll('.e-n-tab-title').forEach(el => el.style.display = "none");
                return;
            }

            let interval = setInterval(function () {
                if (document.querySelectorAll('.e-n-tab-title').length > 0) {
                    clearInterval(interval);

                    syncTabs();

                    let params = new URLSearchParams(window.location.search);
                    let dayParam = params.get("day");

                    let target = (dayParam && activeDays.includes(dayParam))
                        ? dayParam
                        : getValidDay();

                    activateTab(target);
                }
            }, 300);

            // Update Today Menu Button
            let btnWrap = document.getElementById("today-menu-btn");
            if (btnWrap) {
                let a = btnWrap.querySelector("a");
                let valid = getValidDay();
                if (a && valid) {
                    a.href = "/menu/?day=" + valid;
                }
            }

        });

    } catch (e) {
        console.error("Pickup Days Plugin Error:", e);
    }

})();
</script>
<?php
});
