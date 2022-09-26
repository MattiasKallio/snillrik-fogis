jQuery(document).ready(function ($) {
    "use strict";

    $(".snfogis-admin-wrapper").on('change', 'select', function () {
        let valet = $(this).val();
        let myname = $(this).data('name');
        $(".snfo-" + myname + "-id").html(valet);

        let clubid = $(".snfo-club-id").html() != "" ? "clubid=\"" + $(".snfo-club-id").html() + "\"" : "";
        let age = $(".snfo-age-id").html() != "" ? "age='" + $(".snfo-age-id").html() + "'" : "";
        let gender = $(".snfo-gender-id").html() != "" ? "gender='" + $(".snfo-gender-id").html() + "'" : "";
        let districtid = $(".snfo-district-id").html() != "" ? "districtid='" + $(".snfo-district-id").html() + "'" : "";
        let competitionid = $(".snfo-competition-id").html() != "" ? "competitionId='" + $(".snfo-competition-id").html() + "'" : "";
        let teamid = $(".snfo-team-id").html() != "" ? "teamid='" + $(".snfo-team-id").html() + "'" : "";

        let out_html = '[snfo_clubmatches ' + clubid + ' ' + age + ' ' + gender + ']';
        $("#shortcode-box1").html(out_html);

        if (districtid != "") {
            let out_html2 = '[snfo_districtmatches ' + districtid + ' ' + clubid + ' ' + age + ' ' + gender + ']';
            $("#shortcode-box2").html(out_html2);
        } else {
            $("#shortcode-box2").html("(behöver ett districtsid)");
        }

        if (competitionid != "") {
            let out_html3 = '[snfo_games ' + competitionid + ' played="played-games"]';
            $("#shortcode-box3").html(out_html3);
        } else {
            $("#shortcode-box3").html("(behöver ett competitionid)");
        }

        let out_html4 = '[snfo_teammatches ' + teamid + ' played="latest-games"]';
        $("#shortcode-box4").html(out_html4);

    });

    //to get the teams from the selected club
    $(".snfogis-admin-wrapper").on('change', '.snfo-clubs-select', function () {
        let clubid = $(this).val();
        var data = {
            "action": "snfogis_get_teams",
            "clubid": clubid
        };

        console.log(ajaxurl);

        $.post(
            ajaxurl,
            data,
            function (response) {
                $(".snfo-teams-select").html(response.data);
                console.log(response);
            }
        );

    });

});
