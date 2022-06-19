if(typeof handleCookie != "function")
{
    var handleCookie = function(key, value) {

        // key and at least value given, set cookie...
        if (arguments.length > 1 && (!/Object/.test(Object.prototype.toString.call(value)) || value === null || value === undefined)) {
            var options = Object();

            options.expires = 365;


            if (typeof options.expires === 'number') {
                var days = options.expires, t = options.expires = new Date();
                t.setDate(t.getDate() + days);
            }

            value = String(value);

            return (document.cookie = [
                encodeURIComponent(key), '=', options.raw ? value : encodeURIComponent(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                options.path    ? '; path=' + options.path : '',
                options.domain  ? '; domain=' + options.domain : '',
                options.secure  ? '; secure' : ''
            ].join(''));
        }

        // key and possibly options given, get cookie...
        options = value || {};
        var decode = options.raw ? function(s) { return s; } : decodeURIComponent;

        var pairs = document.cookie.split('; ');
        for (var i = 0, pair; pair = pairs[i] && pairs[i].split('='); i++) {
            if (decode(pair[0]) === key) return decode(pair[1] || ''); // IE saves cookies with empty string as "c; ", e.g. without "=" as opposed to EOMB, thus pair[1] may be undefined
        }
        return null;
    }
}

function initFilterToggle()
{
    // read cookie
    var showFilter = null;
    showFilter = handleCookie('edk:fleet_battles_mod_filter:show');

    // cookie not set
    // initialize
    if(showFilter == null)
    {
        showFilter = "true";
        handleCookie('edk:fleet_battles_mod_filter:show', showFilter);
    }

    if(showFilter == "false")
    {
        document.getElementById("battleFilters").style.display = "none";
        document.getElementById("battleFiltersHead").innerHTML = "[+] Filters";
    }
}

// toggles the filters
function toggleFilters()
{
    var showFilter;
    if(document.getElementById("battleFilters").style.display != "none")
    {
        showFilter = "false";
        document.getElementById("battleFilters").style.display = "none";
        document.getElementById("battleFiltersHead").innerHTML = "[+] Filters";
    }

    else
    {
        showFilter = "true";
        document.getElementById("battleFilters").style.display = "";
        document.getElementById("battleFiltersHead").innerHTML = "[-] Filters";
    }
    handleCookie('edk:fleet_battles_mod_filter:show', showFilter);
}