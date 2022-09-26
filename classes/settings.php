<?php
defined('ABSPATH') or die('This script cannot be accessed directly.');
/**
 * The settings page for the plugin.
 */
new SNFogis_settings();
class SNFogis_settings
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'snillrik_settings_create_menu']);
    }

    public function snillrik_settings_create_menu()
    {
        add_menu_page(
            'Fogis',
            'Fogis',
            'administrator',
            'fogis-settings',
            [$this, 'settings_page'],
            SNILLRIK_FOGIS_PLUGIN_URL . '/images/snillrik_icon.svg'
        );
        add_action('admin_init', [$this, 'settings_regs']);
    }

    /**
     * Register the settings
     */
    function settings_regs()
    {
        $sanitize_args_str = array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        );
        register_setting(SNILLRIK_FOGIS_NAME . '-settings-group', SNILLRIK_FOGIS_NAME . '_dont_save_data_uninstall', $sanitize_args_str);
    }
    /**
     * The settings page content.
     */
    public function settings_page()
    {
        $club_option_str = SNFogis_API::list_of_clubs();

        $html_out = "<div class='snfogis-admin-wrapper'><div class='snfogis-admin-wrapper-left'><div class='snfogis-admin-box'><div class='snfogis-admin-box-inner'>
            <h4>Klubbar</h4><p>För att hitta ID till en speciell klubb</p>";
        $html_out .= "<select class='snfo-clubs-select' data-name='club'>";
        $html_out .= SNFogis_API::list_of_clubs();
        $html_out .= "</select><br /><select class='snfo-teams-select' data-name='team'></select></div></div>";

        $html_out .= "<div class='snfogis-admin-box'><div class='snfogis-admin-box-inner'>
            <h4>Distrikt</h4>";
        $html_out .= "<select class='snfo-districts-select' data-name='district'>
            " . SNFogis_API::list_of_filter() . "
        </select></div></div>";

        $html_out .= "<div class='snfogis-admin-box'><div class='snfogis-admin-box-inner'>
            <h4>Tävlingar</h4>";
        $html_out .= "<select class='snfo-competition-select' data-name='competition'>
            " . SNFogis_API::list_of_comps() . "
        </select></div></div>";

        $html_out .= "<div class='snfogis-admin-box'><div class='snfogis-admin-box-inner'>
        <h4>Ålder</h4>";
        $html_out .= "<select class='snfo-age-select' data-name='age'>
        " . SNFogis_API::list_of_filter("ageCategoryFilter", true) . "
        </select></div></div>";
        $html_out .= "<div class='snfogis-admin-box'><div class='snfogis-admin-box-inner'>
        <h4>Kön</h4>";
        $html_out .= "<select class='snfo-gender-select' data-name='gender'>
        <option value=''>Alla</option>
        <option value='man'>Män</option>
        <option value='kvinna'>Kvinnor</option>
        </select></div></div></div>";        

        $html_out .= "<div class='snfogis-admin-wrapper-right'>
        <h4>Info och val</h4>
            <div class='snfogis-admin-box'>
            <div class='snfogis-admin-box-inner'>
            
            <div class='snfogis-admin-box-inner-content'>
            <div class='snfogis-admin-box-inner-content-part'>Klubb id: <span class='snfo-club-id'></span><br>
                Distrikt id: <span class='snfo-district-id'></span><br>
                Tävling id: <span class='snfo-competition-id'></span><br>
                Ålder name: <span class='snfo-age-id'></span><br>
                Gender name: <span class='snfo-gender-id'></span><br>
                Team id: <span class='snfo-team-id'></span><br>
                </div>
                
                <div class='snfogis-admin-box-inner-content-part'>
                <h4>För kommande matcher i klubben</h4>
                <p>shownum=-1 kan sättas till 5 om man vill visa 5 stycken.</p>
                <span id='shortcode-box1'>[snfo_clubmatches]</span></div>
                
                <div class='snfogis-admin-box-inner-content-part'>
                <h4>För kommande (eller tidigare) matcher i distriktet</h4>
                <p>defaultar till dagens datum, för igår sätt daynum=-1 eller daynum=2 för i övermorgon. Sätter man clubid så visas de tävlingar där något lag från klubben är med. Om matchen är avslutad visas resultatet.</p>
                <span id='shortcode-box2'>(behöver ett districtsid)</span> 
                </div> 
                
                <div class='snfogis-admin-box-inner-content-part'>
                <h4>För kommande matcher i en speciell tävling (html formaterat från fogis)</h4>
                <p>played kan vara played-games eller upcoming-games</p>
                <span id='shortcode-box3'>(behöver ett tavlingsid)</span></div>
                
                <div class='snfogis-admin-box-inner-content-part'>
                <h4>För matcher för ett speciellt lag (html formaterat från fogis)</h4>
                <p>played kan vara latest-games eller upcoming-games</p>
                <span id='shortcode-box4'>[snfo_teammatches]</span></div>
                
                        
            </div>";

        $html_out .= "</div></div></div></div>";


        echo $html_out;
    }
}
