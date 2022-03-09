function cookie_law() {
	// The number of days
	var duration = 30;
	
	// Create a date
	var d = new Date();
    d.setTime(d.getTime() + (duration*24*60*60*1000));
	
	// Generate the expiration date
    var expires = d.toUTCString();
	
	// Set the cookie
	document.cookie="cookie_law=1; expires=" + expires;
	
	// Hide the banner
	var banner = document.getElementById("cookie-law-banner");
	fade(banner);
}
function fade(element) {
	// Default opacity (matches the CSS one)
    var op = 0.85;
    var timer = setInterval(function() {
        if(op <= 0.1) {
            clearInterval(timer);
            element.style.display = 'none';
        }
        element.style.opacity = op;
        element.style.filter = 'alpha(opacity=' + op * 100 + ")";
        op -= op * 0.1;
    }, 25);
}