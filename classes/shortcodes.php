<?php

new SNFogis_Shortcodes();

/**
 * Shortcodes for displayin things in a cool way.
 */
class SNFogis_Shortcodes
{
    public function __construct()
    {
        add_shortcode("snfo_games", array($this, "snfo_competition_games"));
        add_shortcode("snfo_teammatches", array($this, "team_matches"));
        add_shortcode("snfo_clubmatches", array($this, "club_matches"));
        add_shortcode("snfo_districtmatches", array($this, "district_matches"));
    }

    /**
     * List of games from a specific competition.
     */
    public function snfo_competition_games($attr)
    {
        wp_enqueue_script('snillrik-fogis-script');

        $attributes = shortcode_atts([
            'played' => 'played-games', //played-games or upcoming-games
            'competitionId' => 88521,
        ], $attr);

        $transient_name = "snfo_games" . $attributes["competitionId"] . $attributes["played"];
        if (SNILLRIK_FOGIS_USE_TRANSIENTS && $html = get_transient($transient_name)) {
            return wp_kses_post($html);
        }

        $api = new SNFogis_API();
        $games = $api->call(
            $attributes["played"],
            ["competitionId" => $attributes["competitionId"], "from" => 0]
        );



        $html_out = str_replace("data-src=\"", "src=\"", $games->data);
        $html_out = "<ul class='snillrik-fogis-list-wrapper'>" . $html_out . "</ul>";
        set_transient("snfo_games" . $attributes["competitionId"] . $attributes["played"], $html_out, SNILLRIK_FOGIS_TRANSIENT_TIME);
        return wp_kses_post($html_out);
    }
    /**
     * List of teams upcoming games.
     */
    public function team_matches($attr)
    {
        wp_enqueue_script('snillrik-fogis-script');
        $attributes = shortcode_atts([
            'played' => 'latest-games', //latest-games or upcoming-games
            'teamid' => 34976,
        ], $attr);
        $api = new SNFogis_API();

        $transient_name = "snfo_team" . $attributes["teamid"] . $attributes["played"];
        if (SNILLRIK_FOGIS_USE_TRANSIENTS && $html = get_transient($transient_name)) {
            return wp_kses_post($html);
        }

        $games = $api->call(
            "team/" . $attributes["played"] . "/",
            ["teamId" => $attributes["teamid"], "from" => 0]
        );

        $html_out = str_replace("data-src=\"", "src=\"", $games->data);
        $html_out = "<ul class='snillrik-fogis-list-wrapper'>" . $html_out . "</ul>";

        set_transient($transient_name, $html_out, SNILLRIK_FOGIS_TRANSIENT_TIME);

        return wp_kses_post($html_out);
    }

    /**
     * Upcoming games for a specific club.
     */
    public function club_matches($attr)
    {
        wp_enqueue_script('snillrik-fogis-script');

        $attributes = shortcode_atts([
            'shownum' => -1,
            'clubid' => 10927,
            'age' => false,
            'gender' => false,
        ], $attr);

        $api = new SNFogis_API();
        $transient_name = "snfo_club" . $attributes["clubid"] . $attributes["shownum"] . $attributes["age"] . $attributes["gender"];
        if (SNILLRIK_FOGIS_USE_TRANSIENTS && $html = get_transient($transient_name)) {
            return wp_kses_post($html);
        }
        /*
        stdClass Object ( 
            [id] => 5083635 
            [ageCategoryName] => Senior 
            [ageCategoryId] => 4 
            [genderName] => Man 
            [genderId] => 2 
            [courtId] => 2 
            [url] => https://www.svenskfotboll.se/matchfakta/froso-if-bk-alsens-if-division-5-herr/5083635/ 
            [date] => stdClass Object ( 
                [iso8601] => 2022-09-16 19:30 
                [formatted] => 19:30, 16 sep 2022 ) 
            [location] => Länsförsäkringar AC 1 
            [category] => Division 5 Herr Jämtland-Härjedalen 
            [status] => 
            [note] => 
            [home] => stdClass Object ( 
                [team] => Frösö IF BK 
                [logo] => stdClass Object ( 
                    [src] => https://staticcdn.svenskfotboll.se/img/teamssm/13367.png 
                    [alt] => Frösö IF BK ) ) 
            [away] => stdClass Object ( 
                [team] => Alsens IF 
                [logo] => stdClass Object ( 
                [src] => https://staticcdn.svenskfotboll.se/img/teamssm/11645.png
                [alt] => Alsens IF 
            )
            */

        //error_log("Fetching games from API");
        $games = $api->call("club/getmatches/", ["clubId" => $attributes["clubid"]]);

        $html_out = "";
        $html_out .= "<ul class='snillrik-fogis-list-wrapper'>";
        $counter = 0;
        if (isset($games->matches)) {
            foreach ($games->matches as $game) {
                if ($counter < $attributes["shownum"] || $attributes["shownum"] == -1) {
                    $show_age = !$attributes["age"] || strtolower($game->ageCategoryName) == strtolower($attributes["age"]);
                    $show_gender = !$attributes["gender"] || strtolower($game->genderName) == strtolower($attributes["gender"]);
                    if ($show_age && $show_gender) {
                        $game_info = [
                            "date" => $game->date->formatted,
                            "location" => $game->location,
                            "category" => $game->category,
                            "home" => $game->home->team,
                            "away" => $game->away->team,
                            "home_logo" => $game->home->logo->src,
                            "home_logo_alt" => $game->home->logo->alt,
                            "away_logo" => $game->away->logo->src,
                            "away_logo_alt" => $game->away->logo->alt,
                            "url" => $game->url,
                            "date_formatted" => $game->date->formatted,
                            "age" => $game->ageCategoryName,
                            "name" => $game->category,
                            "gender" => $game->genderName,
                            "show_gender" => $show_gender,
                            "show_age" => $show_age,
                            "status" => $game->status,
                        ];

                        $html_out .= SNFogis_Shortcodes::format_box($game_info);
                        $counter++;
                    }
                }
            }
        }
        $html_out .= "</ul>";
        set_transient($transient_name, $html_out, SNILLRIK_FOGIS_TRANSIENT_TIME);
        return wp_kses_post($html_out);
    }
    /**
     * Matches for a district per day.
     */
    public function district_matches($attr)
    {
        wp_enqueue_script('snillrik-fogis-script');

        $attributes = shortcode_atts([
            'shownum' => 5,
            'districtid' => 15,
            'clubid' => false, //for showing only matches for a specific club
            'daynum' => 0, //0 = today, -1 = tomorrow 1 = yesterday, etc
            'age' => false,
            'gender' => false,
            ''
        ], $attr);
        $date_calced = date("Y-m-d", strtotime($attributes["daynum"] . " days"));

        //$distrtransientname = "snfo_district_matches" . $attributes["districtid"] . $date_calced;
        $transient_name = "snfo_district_matches" . $attributes["districtid"] . $date_calced;
        if (SNILLRIK_FOGIS_USE_TRANSIENTS && $html = get_transient($transient_name)) {
            return wp_kses_post($html);
        }

        $api = new SNFogis_API();
        $districts = $api->call(
            "matches-today/games/",
            ["associationId" => $attributes["districtid"], "date" => $date_calced]
        );

         /*        $html_out = "<h4>Dagens matcher i distriktet</h4>";
        if ($attributes["clubid"]) {
             $html_out .= "<p>i tävlingar som klubben är med i, inte bara matcher de spelat.</p>";
        }*/
       $html_out = "";
        foreach ($districts->competitions as $comp) {
            $compid = $comp->competitionId;
            $name = $comp->name;
            $games = $comp->games;
            $show_only_club = true;
            if ($attributes["clubid"]) {
                $club_teams = SNFogis_API::list_of_teams($attributes["clubid"]);
            }

            $html_out .= "<ul class='snillrik-fogis-list-wrapper'>";
            if ($show_only_club) {
                foreach ($games as $game) {
                    if($attributes["clubid"] && !in_array($game->homeTeam->name, $club_teams) && !in_array($game->awayTeam->name, $club_teams)){
                        continue;
                    }
                    $show_age = !$attributes["age"] || strtolower($comp->ageCategoryName) == strtolower($attributes["age"]);
                    $show_gender = !$attributes["gender"] || strtolower($comp->genderName) == strtolower($attributes["gender"]);
                    if ($show_age && $show_gender) {
                        $game_info = [
                            "home" => $game->homeTeam->name,
                            "away" => $game->awayTeam->name,
                            "home_logo" => $game->homeTeam->teamImageUrl,
                            "away_logo" => $game->awayTeam->teamImageUrl,
                            "home_logo_alt" => $game->homeTeam->teamImageAlt,
                            "away_logo_alt" => $game->awayTeam->teamImageAlt,
                            "location" => $game->location,
                            "url" => $game->url,
                            "date_formatted" => $game->dateFormatted,
                            "age" => $comp->ageCategoryName,
                            "name" => $name,
                            "gender" => $comp->genderName,
                            "show_gender" => $show_gender,
                            "show_age" => $show_age,
                            "status" => $game->status,
                            "score_home" => $game->score->home,
                            "score_away" => $game->score->away,
                        ];

                        $html_out .= SNFogis_Shortcodes::format_box($game_info);
                    }
                }
            }
            $html_out .= "</ul>";
        }
        set_transient($transient_name, $html_out, SNILLRIK_FOGIS_TRANSIENT_TIME);
        return wp_kses_post($html_out);
    }

    public static function format_box($game_info = [])
    {
        $html_out = "
        <li class='snillrik-fogis-list-item' data-gameid='" . $game_info["url"] . "'>
            <div class='snillrik-fogis-list-item-inner'>
                <div class='snillrik-fogis-list-item-inner-left'>
                    <div class='snillrik-fogis-list-item-inner-left-logo'>
                        <img src='" . $game_info["home_logo"] . "' alt='" . $game_info["home_logo_alt"] . "'>
                    </div>
                    <strong>" . $game_info["home"] . "</strong>
                </div>
                <div class='snillrik-fogis-list-item-inner-content'>
                    <h4>" . $game_info["name"] . "</h4>
                    <span class='snillrik-fogis-list-age-category'>" . $game_info["age"] . "</span><br />
                    " . $game_info["location"] . "<br />
                    " . $game_info["date_formatted"] . "
                    <h4>" . ($game_info["status"] == 4 ? $game_info["score_home"] . " - " . $game_info["score_away"] : "") . "</h4>
                </div>
                <div class='snillrik-fogis-list-item-inner-right'>
                    <div class='snillrik-fogis-list-item-inner-right-logo'>
                        <img src='" . $game_info["away_logo"] . "' alt='" . $game_info["away_logo_alt"] . "'>
                    </div>
                    <strong>" . $game_info["away"] . "</strong>     
                </div>               
            </div>
        </li>";

        return $html_out;
    }
}
