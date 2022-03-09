function geoLocation(cookie_path) {
    if(!navigator.geolocation){
        return;
    }

    function success(position) {
        var latitude  = position.coords.latitude;
        var longitude = position.coords.longitude;

        old_lat = getCookie('lat');
        old_lon = getCookie('lon');

        setCookie('lat', latitude, cookie_path);
        setCookie('lon', longitude, cookie_path);

        var distance = distanceAB(old_lat, old_lon, latitude, longitude, 'K');

        if(old_lat == 0 || old_lon == 0) {
            location.reload();
        }

        // If the distance between the last remembered location is bigger than 5 KM, reload
        if(distance > 5) {
            location.reload();
        }
    }

    function error() {
        old_lat = getCookie('lat');
        old_lon = getCookie('lon');

        setCookie('lat', 0, cookie_path);
        setCookie('lon', 0, cookie_path);

        if(old_lat != 0 || old_lon != 0) {
            location.reload();
        }
    }

    navigator.geolocation.getCurrentPosition(success, error);
}

function distanceAB(lat1, lon1, lat2, lon2, unit) {
    var radlat1 = Math.PI * lat1/180;
    var radlat2 = Math.PI * lat2/180;
    var theta = lon1-lon2;
    var radtheta = Math.PI * theta/180;
    var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
    if(dist > 1) {
        dist = 1;
    }
    dist = Math.acos(dist);
    dist = dist * 180/Math.PI;
    dist = dist * 60 * 1.1515;
    if (unit=="K") { dist = dist * 1.609344 }
    if (unit=="N") { dist = dist * 0.8684 }
    return dist
}

/**
 * Set a cookie
 *
 * @param   name
 * @param   value
 * @param   expire
 * @param   path
 */
function setCookie(name, value, path) {
    var d = new Date();
    d.setTime(d.getTime() + (10 * 365 * 24 * 60 * 60 * 1000));
    document.cookie = name + "=" + value + ";expires=" + d.toUTCString() + ";path=" + path;
}

/**
 * Get the value of a given cookie
 *
 * @param   name
 * @returns {*}
 */
function getCookie(name) {
    var name = name + '=';
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while(c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if(c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return '';
}