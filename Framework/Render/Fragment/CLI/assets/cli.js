/**
 * Created with JetBrains PhpStorm.
 * User: Ari
 * Date: 8/1/13
 * Time: 8:40 PM
 * To change this template use File | Settings | File Templates.
 */
(function(){
    var THIS = {};

    var getRowHTML = function(row, offset) {
        var html = '<tr data-offset="' + offset + '">';
        jQuery.each(row, function(key, value) {
            if(value)
                value = value.toString().replace( /<.*?>/g, ' ' );
            html += '<td>' + value + '</td>'
        });
        html += '</tr>';
        return html;
    };

    jQuery(document).ready(function() {
        var basePath = jQuery('base').attr('href');
        jQuery('.search-form-util').each(function(i, frag) {
            frag = jQuery(frag);
            var form = frag.find('form.apiview-form');
            var Form = new CPath.Form(form);
            var API = Form.API;
            var table = frag.find('table.search-content');
            var tbody = frag.find('table.search-content tbody');
            var pages = frag.find('.search-form-pages');
            var Stats = jQuery.parseJSON(table.attr('data-stats'));

            var RowCache = {};

            var hasPageCache = function(page, limit) {
                var has = true;
                var offset = (page - 1) * limit;
                for(var i=0; i<limit; i++)
                    if(typeof RowCache[i + offset] == "undefined")
                        has = false;
                return has;
            };

            var pageSize = 1;
            var getRowCache = function(offset, limit, callback) {
                if(typeof RowCache[offset] != "undefined") {
                    callback(RowCache[offset]);
                } else {
                    var page = Math.floor(offset / limit) + 1;
                    if(!hasPageCache(page, limit)) {
                        var spanPage = Math.ceil(page / pageSize);
                        var spanLimit = limit * pageSize;
                        API.execute('page=' + spanPage + "&limit=" + spanLimit, function(response) {
                            var Stats2 = response.getStats();
                            var offset2 = Stats2.offset;
                            response.getSearchResults(function(row) {
                                RowCache[offset2++] = row;
                            });
                            callback(RowCache[offset]);
                            pageSize *= 2;
                            if(pageSize > 4)
                                pageSize = 4;
                        });
                    }
                }
            };

            var getURL = function(id) {
                return API.getPath() + '?limit=' + Stats.limit + '&page=' + id;
            };

            var getPageLinkHtml = function(id) {
                return " <a href='" + getURL(id) + "' class='search-form-page'>" + id + '</a>';
            };

            var onPageClick = function() {
                var url = jQuery(this).attr('href');
                if(url.indexOf(basePath) == -1)
                    url = basePath + url;
                //var params = CPath.parseQueryString(url.split('?')[1]);
                API.execute(url, function(response, content) {
                    console.debug(arguments, form, table);
                    tbody
                        .children()
                        .remove();

                    Stats = response.getStats();

                    var curRow = Stats.offset;
                    response.getSearchResults(function(row) {
                        var html = getRowHTML(row, curRow);
                        tbody.append(jQuery(html).fadeIn());
                        RowCache[curRow++] = row;
                    });


                    var label = {};
                    label.prev = frag.find('.search-form-page-previous');
                    label.next = frag.find('.search-form-page-next');
                    //label.last = container.find('.search-form-page-last');
                    //label.next = container.find('.search-form-page-next');


                    pages.children().remove();
                    var ids = response.getPageIDs();
                    jQuery.each(ids, function(i, id) {
                        pages.append(getPageLinkHtml(id));
                    });

                    label.prev.attr('href', getURL(Stats.previousPage));
                    label.next.attr('href', getURL(Stats.nextPage));

                    frag
                        .find('.search-form-pages a')
                        .click(onPageClick);
                });
                return false;
            };

            var seek = function(pos) {
                pos = parseInt(pos);
                if(pos == 0)
                    throw new Error("Invalid Pos: " + pos);
                pos = pos / Math.abs(pos);

                Stats.offset += pos;

                var i, id, remove;
                if(pos > 0) {
                    if(Stats.offset > Stats.total)
                        Stats.offset -= Stats.total;

                    var last = Stats.offset + Stats.limit - 1;
                    if(last > Stats.total)
                        return;

                    id = last;
                    remove = tbody.children().first();
                } else {

                    id = Stats.offset;
                    if(id <= 0)
                        return;
                    remove = tbody.children().last();
                }

                getRowCache(id, Stats.limit, function(row) {
                    var html = getRowHTML(row, Stats.offset);
                    if(pos > 0) {
                        tbody.append(jQuery(html));
                    } else {
                        tbody.prepend(jQuery(html));
                    }
                    remove.remove();
                });

//                if(!found) {
//                    var curRow;
//                    if(pos > 0) {
//                        curRow = id;
//                    } else {
//                        curRow = id - Stats.limit;
//                        if(curRow < 0)
//                            curRow += Stats.total;
//                    }
//                    var page = Math.floor((curRow / Stats.limit)) + 1;
//                    API.execute('page=' + page, function(response) {
//                        var i=0;
//                        response.getSearchResults(function(row) {
//                            var html = getRowHTML(row, curRow + i);
//                            RowCache[curRow + i] = row;
//                            tbody.find('tr[data-offset='+(curRow + i)+']')
//                                .replaceWith(html);
//                            i++;
//                        });
//                    });
//                }
            };

            frag
                .find('a.search-form-page')
                .click(onPageClick);

            table.on('DOMMouseScroll mousewheel', function(evt) {
                evt.preventDefault();
                pos = evt.originalEvent.wheelDelta >=0 ? -1 : 1;
                seek(pos);
                return false;
            });

        });
    });
})();
