jQuery(document).ready(function($) {
    "use strict";
    $(".snillrik-fogis-list-item").on('click', function() {
        let gameid = $(this).data('gameid');
        let url_to_svg = "https://www.svenskfotboll.se" + gameid;
        window.location.href = url_to_svg;
    });
});