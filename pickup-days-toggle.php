<?php
/*
Plugin Name: Pickup Days Toggle (Nested Tabs Support)
Plugin URI: https://github.com/sajidashrafdev/pickup-days-toggle
Description: Toggle pickup days and control Elementor Nested Tabs visibility on Homepage and Shop.
Version: 2.0
Author: Sajid Ashraf
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// ===============================
// ADMIN MENU & SETTINGS (Keep your existing settings code here)
// ===============================
add_action('admin_menu', function () {
    add_menu_page('Pickup Days', 'Pickup Days', 'manage_options', 'pickup-days-settings', 'pdt_settings_page', 'dashicons-calendar', 25);
});

function pdt_settings_page() {
    if (isset($_POST['pdt_save'])) {
        update_option('pdt_config', $_POST['pdt_config'] ?? []);
        echo '<div class="updated"><p>Settings Saved!</p></div>';
    }
    $config = get_option('pdt_config', []);
    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    ?>
    <div class="wrap">
        <h1>Pickup Days & Locations</h1>
        <form method="post">
            <table class="form-table">
                <?php foreach ($days as $day): 
                    $is_active = isset($config[$day]['active']) ? 'checked' : '';
                    $loc = $config[$day]['location'] ?? '';
                ?>
                <tr>
                    <th><?php echo ucfirst($day); ?></th>
                    <td><input type="checkbox" name="pdt_config[<?php echo $day; ?>][active]" value="1" <?php echo $is_active; ?>> Active</td>
                    <td><input type="text" name="pdt_config[<?php echo $day; ?>][location]" value="<?php echo esc_attr($loc); ?>" class="regular-text" placeholder="Enter location for <?php echo ucfirst($day); ?>..."></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <input type="submit" name="pdt_save" class="button button-primary" value="Save Settings">
        </form>
    </div>
    <?php
}

// ===============================
// SHORTCODES FOR EACH DAY
// ===============================
add_action('init', function() {
    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    foreach ($days as $day) {
        add_shortcode('location_' . $day, function() use ($day) {
            $config = get_option('pdt_config', []);
            return (!empty($config[$day]['active']) && !empty($config[$day]['location'])) ? esc_html($config[$day]['location']) : '';
        });
    }
});

// ===============================
// FRONTEND JAVASCRIPT (The Main Fix)
// ===============================
add_action('wp_footer', function () {
    if ( is_admin() ) return;
    $config = get_option('pdt_config', []);
    $active_days = [];
    foreach ($config as $day => $data) {
        if (!empty($data['active'])) { $active_days[] = $day; }
    }
?>
<script>
(function () {
    const activeDays = <?php echo json_encode($active_days); ?>;
    const allDays = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    const dayMap = { 1: "monday", 2: "tuesday", 3: "wednesday", 4: "thursday", 5: "friday", 6: "saturday", 0: "sunday" };
    const dayToIndex = { "monday": 1, "tuesday": 2, "wednesday": 3, "thursday": 4, "friday": 5, "saturday": 6, "sunday": 7 };

    function getToday() { return dayMap[new Date().getDay()]; }

    function getValidDay() {
        let today = getToday();
        if (activeDays.includes(today)) return today;
        for (let d of allDays) { if (activeDays.includes(d)) return d; }
        return null;
    }

    function syncTabs() {
        allDays.forEach(day => {
            const index = dayToIndex[day];
            // Dono ko target karega: Aapki ID (#tab-tuesday) aur Elementor ka index [data-tab-index="2"]
            const selectors = [
                `#tab-${day}`,
                `.e-n-tab-title[data-tab-index="${index}"]`
            ];

            selectors.forEach(selector => {
                document.querySelectorAll(selector).forEach(el => {
                    if (!activeDays.includes(day)) {
                        el.style.display = "none";
                    } else {
                        el.style.display = ""; // Default behavior
                    }
                });
            });
        });
    }

    function activateTab(day) {
        if (!day) return;
        const index = dayToIndex[day];
        // Priority check: Manually added ID pehle, phir Elementor default index
        const btn = document.getElementById("tab-" + day) || 
                    document.querySelector(`.e-n-tab-title[data-tab-index="${index}"]`);
        
        if (btn) {
            btn.click();
        }
    }

    window.addEventListener("load", function () {
        if (activeDays.length === 0) {
            document.querySelectorAll('.e-n-tab-title').forEach(el => el.style.display = "none");
            return;
        }

        // Elementor Nested Tabs thora late render hotay hain, isliye interval zaroori hai
        let checkExist = setInterval(function() {
            if (document.querySelectorAll('.e-n-tab-title').length > 0) {
                clearInterval(checkExist);
                
                syncTabs();

                let params = new URLSearchParams(window.location.search);
                let dayParam = params.get("day");
                let target = (dayParam && activeDays.includes(dayParam)) ? dayParam : getValidDay();
                
                activateTab(target);
            }
        }, 300);
        
        // Link update logic
        let btnWrap = document.getElementById("today-menu-btn");
        if (btnWrap) {
            let a = btnWrap.querySelector("a");
            let valid = getValidDay();
            if (a && valid) { a.href = "/menu/?day=" + valid; }
        }
    });
})();
</script>
<?php
});
