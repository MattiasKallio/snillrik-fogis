<?php
defined('ABSPATH') or die('This script cannot be accessed directly.');

new SNFogis_API();
class SNFogis_API
{
    public function __construct()
    {
        add_action('wp_ajax_snfogis_get_teams', [$this, 'list_of_teamsteers']);
        add_action('wp_ajax_nopriv_snfogis_get_teams', [$this, 'list_of_teamsteers']);
    }

    /**
     * Display list of clubs with IDs as options.
     */
    public static function list_of_clubs()
    {
        wp_enqueue_script('snillrik-fogis-script');

        if (SNILLRIK_FOGIS_USE_TRANSIENTS && $html = get_transient('snfo_clubs')) {
            return $html;
        }
        $clubs = SNFogis_API::call('club/getclubs/');
        $html_out = "<option value=''>Inget valt</option>";
        if ($clubs) {
            foreach ($clubs as $comp) {
                $clubId = $comp->clubId;
                $catname = $comp->name;
                $html_out .= "<option value='$clubId'>$catname</option>";
            }
        }
        set_transient('snfo_clubs', $html_out, 60 * 60 * 24);

        return $html_out;
    }

    /**
     * get a list of teams from a specific club via ajax
     */
    public static function list_of_teamsteers()
    {
        $clubid = isset($_POST["clubid"]) ? sanitize_post($_POST["clubid"]) : false;
        $transient_name = 'snfo_teams_' . $clubid;
        if (SNILLRIK_FOGIS_USE_TRANSIENTS && $html = get_transient($transient_name)) {
            wp_send_json_success($html);
            wp_die();
        }

        $club = SNFogis_API::call('club/getteams/', ['clubId' => $clubid]);
        $html_out = "<option value=''>Inget valt</option>";
        if ($club) {
            foreach ($club->teams as $team) {
                $teamId = intval($team->teamId);
                $teamName = esc_attr($team->name) . " " . esc_attr($team->gender) . " " . esc_attr($team->ageCategoryName);
                $html_out .= "<option value='$teamId'>$teamName</option>";
            }
        }
        set_transient('snfo_teams_' . $clubid, $html_out, 60 * 60 * 24);
        wp_send_json_success($html_out);

        wp_die();
    }

    public static function list_of_teams($clubId)
    {
        $transient_name = 'snfo_teams_' . $clubId;
        if (SNILLRIK_FOGIS_USE_TRANSIENTS && $teams = get_transient($transient_name)) {
            return $teams;
        }
        $teams = SNFogis_API::call('club/getteams/', ['clubId' => $clubId]);
        if(!$teams || !isset($teams->teams)) {
            return [];
        }
        foreach ($teams->teams as $team) {
            $teams_arr[] = wp_kses_post($team->name);
        }
        //unique teams
        $teams_arr = array_unique($teams_arr);
        set_transient($transient_name, $teams_arr, 60 * 60 * 24);
        return $teams_arr;
    }

    /**
     * Get list of competitions.
     */
    public static function list_of_comps($seasonId = 115, $categories = true)
    {
        $html_out = "";
        if (SNILLRIK_FOGIS_USE_TRANSIENTS && $html_out = get_transient("snfo_comps" . $seasonId)) {
            return $html_out;
        }

        $comps = SNFogis_API::call("comp-find/filter/", ["seasonId" => $seasonId]);
        $html_out = "<option value=''>Inget valt</option>";
        foreach ($comps->competitions as $comp) {
            foreach ($comp->comps as $compp) {
                $html_out .= "<option value='".intval($compp->id)."'>".esc_attr($compp->name)."</option>";
            }
        }
        set_transient("snfo_comps" . $seasonId, $html_out, 60 * 60 * 24);
        return $html_out;
    }

    /**
     * Get list of filters.
     * associationsFilter is districts
     * ageCategoryFilter is ages 
     * genderGroupFilter is gender
     * footballTypesFilter is football types
     * competitionTypesFilter
     * municipalityFilter säsong
     */
    public static function list_of_filter($type = "associationsFilter", $text_instead_of_id = false, $endpoint = "comp-find/getfiltercriteria/")
    {
        $html_out = "";
        $cookie_name = "snfo_filter_" . $type . ($text_instead_of_id ? "_text" : "_id") . $endpoint;
        if (SNILLRIK_FOGIS_USE_TRANSIENTS && $html_out = get_transient($cookie_name)) {
            return $html_out;
        }
        $html_out = "<option value=''>Inget valt</option>";
        $catoptions = SNFogis_API::call($endpoint);
        $options = $catoptions->$type->options;
        foreach ($options as $option) {
            $value = $text_instead_of_id ? $option->text : $option->value;
            $value = $value == "Alla" ? "" : $value;
            $html_out .= "<option value='".esc_attr($value)."'>".esc_attr($option->text)."</option>";
        }
        set_transient($cookie_name, $html_out, 60 * 60 * 24);
        return $html_out;
    }

    /**
     * Get competitionids for a club
     */
    public static function get_club_comps($clubId)
    {
        $api = new SNFogis_API();
        $comps = [];

        $comps = get_transient('snfo_club_comps_' . $clubId);
        if (!$comps) {
            $club_comps = $api->call("comp-find/getclubcompetitions/", ["clubId" => $clubId]);
            foreach ($club_comps->competitions as $comp) {
                foreach ($comp->comps as $compp) {
                    $comps[] = intval($compp->id);
                }
            }
            set_transient('snfo_club_comps_' . $clubId, $comps, 60 * 60 * 24);
        }

        return $comps;
    }

    public static function call($endpoint, $args = [], $method = "GET")
    {
        $urlen = SNILLRIK_FOGIS_API_URL . $endpoint;

        switch ($method) {
            case "GET":
                $urlen .= is_array($args) && count($args) > 0 ? "?" . http_build_query($args) : "";
                break;
        }

        $response = wp_remote_request(
            $urlen,
            array(
                'method' => $method,
                'headers' => array(
                    "Content-type" => "application/json",
                ),
                'body' => $method != "GET" && is_array($args) && count($args) > 0 ? json_encode($args) : "",
            )
        );
        if (is_wp_error($response)) {
            error_log(print_r($response, true));
            return;
        }
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code != 200) {
            error_log("Fogis API returned error code: " . $response_code);
            return;
        }

        return json_decode($response['body']);
    }
}
