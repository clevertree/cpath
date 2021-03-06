/**
 * Created with JetBrains PhpStorm.
 * User: Ari
 * Date: 8/1/13
 * Time: 8:40 PM
 * To change this template use File | Settings | File Templates.
 */
(function(){
    var THIS = {};
    var TRIGGERS;

    jQuery(document).ready(function() {
        //THIS.test();
    });

    function APIException(message, API) {
        this.message = message;
        this.getAPI = function() { return API; };
        this.toString = function() { return this.message; };
    }

    window.CPath.API = THIS = function(method, path, dataType) {
        path = path.split('?')[0]; // TODO: parse params
        path = path.replace(THIS.getBaseURL(), ''); // TODO: whats the plan here?
        TRIGGERS = jQuery([this, THIS]);
        var onResponse = [], onException = [], pending=false;
        if(typeof dataType == "undefined")
            dataType = 'json';
        this.getMethod = function() { return method; };
        this.getPath = function(absolute) { return absolute ? THIS.getBaseURL() + path : path; };
        this.addOnResponse = function(callback) { onResponse.push(callback); };
        this.addOnException = function(callback) { onException.push(callback); };

        /**
         * @param args String|Object|Function vararg allowing multiple entries for
         * query string (string) ajax settings (object) or success callback (function)
         */
        this.execute = function(args) {
            var data = {}, ajax = {}, onResponse2 = onResponse.slice(0);
            for(var i=0; i<arguments.length; i++){
                var arg = arguments[i];
                switch(typeof arg) {
                    default:
                    case 'string': data = arg; break;
                    case 'object': ajax = arg; break;
                    case 'function': onResponse2.push(arg);
                }
            }
            var url = this.getPath();
            if(typeof data == "string") {
                var s = data.split('?', 2);
                if(s[1]) {
                    data = s[1];
                    url = s[0];
                }
            }
            ajax = jQuery.extend({
                url: url,
                type: method,
                dataType: dataType,
                data: data,
                complete: function(jqXHR, textStatus) {
                    pending = false;
                    var content = jqXHR.responseText;
                    var response;

                    switch(ajax.dataType) {
                        case 'json':
                            var jsonContent = jQuery.parseJSON(content);
                            response = new CPath.API.JSONSearchResponse(jsonContent);
                            TRIGGERS.trigger( "response-json", [response, jqXHR]);
                            break;
                        case 'xml':
                            var xmlContent = jQuery.parseXML(content);
                            response = new CPath.API.XMLSearchResponse(xmlContent);
                            TRIGGERS.trigger( "response-xml", [response, jqXHR]);
                            break;
                        default :
                            response = new CPath.API.Response(content);
                            break;
                    }

                    TRIGGERS.trigger( "response", [response, jqXHR]);
                    for(var iii=0; iii<onResponse2.length; iii++)
                        try {
                            onResponse2[iii](response, jqXHR);
                        } catch (e) {
                            ajax.error(jqXHR, e.message, e);
                        }

                    console.log([response, content]);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var content = jqXHR.responseText;

                    TRIGGERS.trigger( "error", [errorThrown, jqXHR]);

                    for(var i=0; i<onException.length; i++)
                        onException[i](errorThrown, content);
                }
            }, ajax || {});

            switch(ajax.dataType) {
                default:
                case 'json':
                    ajax.accepts = 'application/json';
                    break;
                case 'xml':
                    ajax.accepts = 'text/xml';
                    break;
            }

            jQuery.extend(ajax, {
                //contentType: asObject ? 'application/json' : null,
                headers: {
                    Accept : ajax.accepts + "; charset=utf-8"
                    //"MainContent-Type": asObject ? 'application/json' : null
                }
            });

            if(pending)
                throw new APIException("Waiting for last execution to complete");
            pending = true;
            jQuery.ajax(ajax);
        };
    };

    THIS.getBaseURL = function() {
        return jQuery('base').attr('href');
    };

    THIS.APIException = APIException;

    THIS.Response = function(data) {
        var context = this;
        this.getData = function() { return data; };
        this.getStatus = function() { throw new Error("Unimplemented: getStatus"); };
        this.getMessage = function() { throw new Error("Unimplemented: getMessage"); };
        this.getResponse = function() { throw new Error("Unimplemented: getResponse"); };
        //this.getSearchResults = function() { throw new Error("Unimplemented: getSearchResults"); };
    };

    THIS.JSONResponse = function(data) {
        this.base = THIS.Response;
        this.base(data);
        var context = this;
        this.getCode = function() { return data.code; };
        this.getStatus = function() { return data.code == 200; };
        this.getMessage = function() { return data.message; };
        this.getResponse = function() { return data.response; };
    };

    THIS.XMLResponse = function(data) {
        var context = this;
        this.base = THIS.Response;
        this.base(data);
        var dom = jQuery(data).children('root');
        this.getDOM = function() { return dom; };
        this.getCode = function() { return dom.children('code').val(); };
        this.getStatus = function() { return dom.children('code').val() == 200; };
        this.getMessage = function() { return dom.children('message').val(); };
        this.getResponse = function() { return dom.children('response'); };
    };

    THIS.JSONSearchResponse = function(data) {
        var context = this;
        this.base = THIS.JSONResponse;
        this.base(data);
        this.getSearchResults = function(callback) {
            var rows = context.getResponse();
            for(var i=0; i < rows.length; i++) {
                callback(rows[i], rows[i]);
            }
        };
        this.getStats = function() { return data.stats; };
        this.getPageIDs = function() { return context.getStats().pages; };
    };

    THIS.XMLSearchResponse = function(data) {
        var context = this;
        this.base = THIS.XMLResponse;
        this.base(data);
        this.getSearchResults = function(callback) {
            var rows = this.getResponse().children();
            for(var i=0; i < rows.length; i++) {
                var attr = rows[i].attributes;
                var attrObj = {};
                Array.prototype.slice.call(attr).forEach(function(item) {
                    attrObj[item.name] = item.value;
                });
                callback(attrObj, rows[i]);
            }
        };
        this.getStats = function() {
            var stats = this.getDOM().children('stats');
            var attr = stats[0].attributes;
            var attrObj = {};
            Array.prototype.slice.call(attr).forEach(function(item) {
                attrObj[item.name] = item.value;
            });
            attrObj['pages'] = {};
            stats.children('page').each(function() {
                var elm = jQuery(this);
                attrObj['pages'][elm.attr('id')] = elm.text();
            });
            return attrObj;
        };
        this.getPageIDs = function() { return context.getStats().pages; };
    };

    THIS.test = function() {
        var API = new THIS("GET", document.location.href);
        API.execute({id:'1'});
        API.execute("id=2");
    };

})();

