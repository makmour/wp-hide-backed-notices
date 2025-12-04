(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

})(jQuery);

function openSettings(evt, cityName, tab) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("hide-tabcontent-notices");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
        console.log('-2-');
    }
    tablinks = document.getElementsByClassName("hide-tablinks-notices");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
        console.log('-1-');
    }
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";

    var currentUrl = jQuery(location).attr('href');
    var url = new URL(currentUrl);
    url.searchParams.set("tab", tab);
    var newUrl = url.href;
//    console.log(newUrl);
    location.href = newUrl;

}
 