(function ($) {
    'use strict';
    // Document ready logic if needed in future
})(jQuery);

function openSettings(evt, cityName, tab) {
    var i, tabcontent, tablinks;

    // 1. Hide all tab content
    tabcontent = document.getElementsByClassName("hide-tabcontent-notices");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // 2. Remove 'active' class from all buttons
    tablinks = document.getElementsByClassName("hide-tablinks-notices");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // 3. Show the current tab and add 'active' class to the clicked button
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";

    // 4. Update the URL without reloading the page (Modern UX)
    if (window.history.replaceState) {
        var currentUrl = new URL(window.location);
        currentUrl.searchParams.set("tab", tab);
        window.history.pushState({}, '', currentUrl);
        
        // Update the form action URL dynamically so saving preserves the tab
        var form = document.querySelector('.setting-top-wrap form');
        if(form) {
            form.action = currentUrl.href;
        }
    }
}
