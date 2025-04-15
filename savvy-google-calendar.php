<?php
/**
 * Plugin Name: Savvy Google Calendar Viewer
 * Description: Display events from Google Calendar using FullCalendar and a custom modal.
 * Version: 1.0
 * Author: Savvy Post Marketing
 */

if (!defined('ABSPATH')) exit;

class Savvy_Google_Calendar_Viewer {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_shortcode('savvy_google_calendar', array($this, 'render_calendar'));
        add_action('wp_footer', array($this, 'print_inline_assets'), 99);
    }

    public function add_settings_page() {
        add_options_page('Savvy Google Calendar', 'Savvy Google Calendar', 'manage_options', 'savvy-google-calendar', array($this, 'settings_page'));
    }

    public function register_settings() {
        register_setting('savvy_google_calendar_options', 'savvy_google_api_key');
        register_setting('savvy_google_calendar_options', 'savvy_google_calendar_id');
        register_setting('savvy_google_calendar_options', 'savvy_google_default_view');
        register_setting('savvy_google_calendar_options', 'savvy_google_max_events');
        register_setting('savvy_google_calendar_options', 'savvy_google_calendar_color');
        register_setting('savvy_google_calendar_options', 'savvy_google_text_color');
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Savvy Google Calendar Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('savvy_google_calendar_options'); ?>
                <table class="form-table">
                    <tr>
                        <th>Google API Key</th>
                        <td><input type="text" name="savvy_google_api_key" value="<?php echo esc_attr(get_option('savvy_google_api_key')); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th>Google Calendar ID</th>
                        <td><input type="text" name="savvy_google_calendar_id" value="<?php echo esc_attr(get_option('savvy_google_calendar_id')); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th>Default View</th>
                        <td>
                            <select name="savvy_google_default_view">
                                <option value="dayGridMonth" <?php selected(get_option('savvy_google_default_view'), 'dayGridMonth'); ?>>Month</option>
                                <option value="listWeek" <?php selected(get_option('savvy_google_default_view'), 'listWeek'); ?>>List Week</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Maximum Events</th>
                        <td><input type="number" name="savvy_google_max_events" value="<?php echo esc_attr(get_option('savvy_google_max_events', 100)); ?>" min="1"></td>
                    </tr>
                    <tr>
                        <th>Event Color</th>
                        <td><input type="color" name="savvy_google_calendar_color" value="<?php echo esc_attr(get_option('savvy_google_calendar_color', '#3b76bf')); ?>"></td>
                    </tr>
                    <tr>
                        <th>Event Text Color</th>
                        <td><input type="color" name="savvy_google_text_color" value="<?php echo esc_attr(get_option('savvy_google_text_color', '#FFFFFF')); ?>"></td>
                    </tr>
                </table>
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php
    }

    public function render_calendar() {
        return '<div id="savvy-google-calendar"></div>
        <div id="savvy-event-modal" class="savvy-modal">
            <div class="savvy-modal-content">
                <span class="savvy-modal-close">&times;</span>
                <div id="savvy-event-details"></div>
            </div>
        </div>';
    }

    public function print_inline_assets() {
        global $post;
        if (!$post || !has_shortcode($post->post_content ?? '', 'savvy_google_calendar')) return;

        $api_key = esc_js(get_option('savvy_google_api_key'));
        $calendar_id = esc_js(get_option('savvy_google_calendar_id'));
        $default_view = esc_js(get_option('savvy_google_default_view', 'dayGridMonth'));
        $max_events = intval(get_option('savvy_google_max_events', 100));
        $calendar_color = esc_js(get_option('savvy_google_calendar_color', '#3b76bf'));
        $text_color = esc_js(get_option('savvy_google_text_color', '#FFFFFF'));

        // Load FullCalendar
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/google-calendar@5.11.3/main.min.js"></script>';

        // Custom styles
        echo '<style>
/* Body style when modal is open */
body.savvy-modal-open {
    overflow: hidden !important;
    position: fixed;
    width: 100%;
    height: 100%;
}

/* Modal styles */
.savvy-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    overflow-y: hidden;
}
.savvy-modal-content {
    background: #fff;
    margin: 15% auto;
    padding: 20px;
    border-radius: 10px;
    width: 90%;
    max-width: 700px;
    max-height: 80vh;
    position: relative;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    overflow-y: auto;
}
.savvy-modal-close {
    position: absolute;
    top: 10px;
    right: 15px;
    cursor: pointer;
    font-size: 28px;
    color: #555;
    transition: color 0.3s;
    z-index: 1;
}
.savvy-modal-close:hover {
    color: #000;
}

/* Calendar styles */
#savvy-google-calendar {
    width: 100%;
    height: auto;
    min-height: 600px;
    margin: 20px 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
.fc-toolbar-title {
    font-size: 1.5em !important;
    font-weight: 500 !important;
}
.fc-button-primary {
    background-color: ' . $calendar_color . ' !important;
    border-color: ' . $this->adjust_brightness($calendar_color, -20) . ' !important;
}
.fc-button-primary:hover {
    background-color: ' . $this->adjust_brightness($calendar_color, -30) . ' !important;
    border-color: ' . $this->adjust_brightness($calendar_color, -40) . ' !important;
}
.fc-event {
    cursor: pointer;
    border-radius: 4px !important;
    border: none !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}
.fc-daygrid-event {
    padding: 2px 4px !important;
    margin-bottom: 3px !important;
    font-size: 0.85em !important;
    white-space: normal !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    display: block !important;
    max-height: 40px !important;
}
.fc-list-event-title {
    max-height: none !important;
}
.fc-list-event-dot {
    border-color: ' . $calendar_color . ' !important;
}
.fc-list-event:hover td {
    background-color: ' . $this->adjust_brightness($calendar_color, 90) . ' !important;
}

/* Event content styles */
#savvy-event-details h3 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #333;
    font-weight: 600;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}
#savvy-event-details p {
    margin-bottom: 10px;
    font-size: 16px;
    color: #555;
    line-height: 1.6;
}
#savvy-event-details p strong {
    color: #333;
    font-weight: 600;
}
/* Scrollable container for long descriptions */
#savvy-event-details .event-description {
    max-height: 300px;
    overflow-y: auto;
    padding-right: 10px;
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
}
/* Scrollbar styles */
#savvy-event-details .event-description::-webkit-scrollbar {
    width: 8px;
}
#savvy-event-details .event-description::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
#savvy-event-details .event-description::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}
#savvy-event-details .event-description::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Responsive styles */
@media (max-width: 768px) {
    .fc .fc-toolbar {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .fc .fc-toolbar-title {
        font-size: 1.3em !important;
    }
    .fc-header-toolbar .fc-toolbar-chunk {
        display: flex;
        justify-content: center;
        width: 100%;
        margin-bottom: 5px;
    }
    .fc-button {
        padding: 0.3em 0.6em !important;
        font-size: 0.9em !important;
    }
    .savvy-modal-content {
        margin: 5% auto;
        width: 95%;
        padding: 15px;
        max-height: 85vh;
    }
    #savvy-event-details h3 {
        font-size: 20px;
    }
}
</style>';

        // Calendar handling script
        echo "<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('savvy-google-calendar');
    if (!calendarEl) {
        console.error('Calendar element not found');
        return;
    }
    
    // Variables to control scroll
    var scrollPosition;
    
    // Function to disable body scroll
    function disableBodyScroll() {
        scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
        document.body.classList.add('savvy-modal-open');
        document.body.style.top = -scrollPosition + 'px';
    }
    
    // Function to enable body scroll
    function enableBodyScroll() {
        document.body.classList.remove('savvy-modal-open');
        document.body.style.removeProperty('top');
        window.scrollTo(0, scrollPosition);
    }
    
    // Function to show modal and disable scroll
    function showModal() {
        disableBodyScroll();
        document.getElementById('savvy-event-modal').style.display = 'block';
    }
    
    // Function to close modal and enable scroll
    function closeModal() {
        enableBodyScroll();
        document.getElementById('savvy-event-modal').style.display = 'none';
    }
    
    try {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: '$default_view',
            locale: 'en',
            timeZone: 'local',
            headerToolbar: { 
                left: 'prev,next today', 
                center: 'title', 
                right: 'dayGridMonth,listWeek' 
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                var startDate = info.event.start ? info.event.start.toLocaleString() : '';
                var endDate = info.event.end ? info.event.end.toLocaleString() : '';
                
                var dateInfo = startDate;
                if (endDate && startDate !== endDate) {
                    dateInfo += ' to ' + endDate;
                }
                
                document.getElementById('savvy-event-details').innerHTML = 
                    '<h3>' + info.event.title + '</h3>' +
                    '<div class=\"event-meta\">' +
                    '<p><strong>Date:</strong> ' + dateInfo + '</p>' +
                    (info.event.extendedProps.location ? '<p><strong>Location:</strong> ' + info.event.extendedProps.location + '</p>' : '') +
                    '</div>' +
                    (info.event.extendedProps.description ? '<div class=\"event-description\">' + info.event.extendedProps.description + '</div>' : '');
                
                showModal();
            },
            googleCalendarApiKey: '$api_key',
            eventSources: [
                {
                    googleCalendarId: '$calendar_id',
                    className: 'savvy-google-event',
                    color: '$calendar_color',
                    textColor: '$text_color'
                }
            ],
            eventDidMount: function(info) {
                if (info.event.extendedProps.description) {
                    info.el.title = info.event.extendedProps.description.replace(/<[^>]*>/g, '');
                }
            },
            dayMaxEvents: true,
            eventMaxStack: 3,
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: 'short'
            },
            height: 'auto',
            contentHeight: 'auto'
        });
        
        calendar.render();
        console.log('Calendar initialized successfully');
        
        // Modal close handlers
        document.querySelector('.savvy-modal-close').onclick = function() { 
            closeModal();
        };
        
        window.onclick = function(event) { 
            if (event.target == document.getElementById('savvy-event-modal')) { 
                closeModal();
            } 
        };
        
        // Close modal when ESC key is pressed
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && document.getElementById('savvy-event-modal').style.display === 'block') {
                closeModal();
            }
        });
        
        // Make the calendar responsive
        function adjustCalendarHeight() {
            var width = window.innerWidth;
            if (width < 768) {
                calendar.setOption('headerToolbar', {
                    left: 'prev,next',
                    center: 'title',
                    right: 'dayGridMonth,listWeek'
                });
            } else {
                calendar.setOption('headerToolbar', {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listWeek'
                });
            }
        }
        
        // Initial adjustment
        adjustCalendarHeight();
        
        // Adjust on resize
        window.addEventListener('resize', adjustCalendarHeight);
    } catch (error) {
        console.error('Calendar initialization error:', error);
    }
});
</script>";
    }
    
    /**
     * Function to adjust brightness of a hexadecimal color
     */
    public function adjust_brightness($hex, $steps) {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));

        return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
    }
}

// Initialize the plugin
new Savvy_Google_Calendar_Viewer();