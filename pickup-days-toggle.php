<?php
/*
Plugin Name: Pickup Days Toggle
Plugin URI: https://github.com/sajidashrafdev/pickup-days-toggle
Description: Toggle pickup days from backend and control Elementor tabs visibility.
Version: 1.2
Author: Sajid Ashraf
Author URI: https://pk.linkedin.com/in/sajidashrafdev
Requires Plugins: woocommerce
*/

if ( ! defined( 'ABSPATH' ) ) exit;


// ===============================
// CHECK WOOCOMMERCE
// ===============================
add_action('admin_init', function () {
    if ( ! class_exists('WooCommerce') ) {
        add_action('admin_notices', function () {
            echo '<div class="error"><p><strong>Pickup Days Toggle:</strong> WooCommerce is required.</p></div>';
        });
    }
});


// ===============================
// ADMIN MENU
// ===============================
add_action('admin_menu', function () {
    add_menu_page(
        'Pickup Days Settings',
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
        update_option('pdt_days', $_POST['days'] ?? []);
        echo '<div class="updated"><p>Settings Saved</p></div>';
    }

    $saved_days = get_option('pdt_days', []);
    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    ?>

    <div class="wrap">
        <h1>Pickup Days Settings</h1>

        <form method="post">
            <table class="form-table">
                <?php foreach ($days as $day): ?>
                    <tr>
                        <th><?php echo ucfirst($day); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="days[]" value="<?php echo esc_attr($day); ?>"
                                    <?php checked(in_array($day, $saved_days)); ?>>
                                Active
                            </label>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <p>
                <input type="submit" name="pdt_save" class="button button-primary" value="Save Settings">
            </p>
        </form>
    </div>

    <?php
}


// ===============================
// FRONTEND LOGIC (MAIN FIX)
// ===============================
add_action('wp_footer', function () {

    if ( is_admin() ) return;

    $active_days = get_option('pdt_days', []);

?>
<script>
(function () {

    const activeDays = <?php echo json_encode(array_values($active_days)); ?>;

    const allDays = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

    const dayMap = {
        1: "monday",
        2: "tuesday",
        3: "wednesday",
        4: "thursday",
        5: "friday",
        6: "saturday",
        0: "sunday"
    };

    function getToday() {
        return dayMap[new Date().getDay()];
    }

    function getValidDay() {

        let today = getToday();

        if (activeDays.includes(today)) {
            return today;
        }

        for (let d of allDays) {
            if (activeDays.includes(d)) return d;
        }

        return null;
    }

    function hideInactiveTabs() {
        allDays.forEach(day => {
            let tab = document.getElementById("tab-" + day);
            if (!tab) return;

            if (!activeDays.includes(day)) {
                tab.style.display = "none";
            }
        });
    }

    function activateTab(day) {
        if (!day) return;

        let el = document.getElementById("tab-" + day);
        if (el) el.click();
    }

    function waitTabs(cb) {
        let i = 0;
        let t = setInterval(() => {
            if (document.getElementById("tab-monday") || i > 20) {
                clearInterval(t);
                cb();
            }
            i++;
        }, 300);
    }

    window.addEventListener("load", function () {

        // ======================
        // 1. If NO active days → hide everything
        // ======================
        if (activeDays.length === 0) {

            document.querySelectorAll('[id^="tab-"]').forEach(el => {
                el.style.display = "none";
            });

            let btn = document.getElementById("today-menu-btn");
            if (btn) btn.style.display = "none";

            return;
        }

        waitTabs(function () {

            hideInactiveTabs();

            let params = new URLSearchParams(window.location.search);
            let dayParam = params.get("day");

            if (dayParam && activeDays.includes(dayParam)) {
                activateTab(dayParam);
                return;
            }

            activateTab(getValidDay());

        });

        // ======================
        // BUTTON FIX
        // ======================
        let btnWrap = document.getElementById("today-menu-btn");

        if (btnWrap) {

            let a = btnWrap.querySelector("a");
            let valid = getValidDay();

            if (a && valid) {
                a.href = "/menu/?day=" + valid;
            }

            if (!valid) {
                btnWrap.style.display = "none";
            }
        }

    });

})();
</script>
<?php
});
