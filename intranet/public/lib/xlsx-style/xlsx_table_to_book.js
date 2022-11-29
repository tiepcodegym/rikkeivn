if (typeof XLSX != 'undefined') {
    var SSF = XLSX.SSF;
    var DENSE = null;
    var htmldecode = (function() {
        var entities = [
                ['nbsp', ' '], ['middot', '·'],
                ['quot', '"'], ['apos', "'"], ['gt',   '>'], ['lt',   '<'], ['amp',  '&']
        ].map(function(x) { return [new RegExp('&' + x[0] + ';', "g"), x[1]]; });
        return function htmldecode(str) {
            var o = str.replace(/^[\t\n\r ]+/, "").replace(/[\t\n\r ]+$/,"").replace(/[\t\n\r ]+/g, " ").replace(/<\s*[bB][rR]\s*\/?>/g,"\n").replace(/<[^>]*>/g,"");
            for(var i = 0; i < entities.length; ++i) o = o.replace(entities[i][0], entities[i][1]);
            return o;
        };
    })();
    
    function table_to_book(table, opts) {
        return sheet_to_workbook(parse_dom_table(table, opts), opts);
    }
    function sheet_to_workbook(sheet, opts) {
        var n = opts && opts.sheet ? opts.sheet : "Sheet1";
        var sheets = {}; sheets[n] = sheet;
        return { SheetNames: [n], Sheets: sheets };
    }
    function parse_dom_table(table, _opts) {
	var opts = _opts || {};
	if(DENSE != null) opts.dense = DENSE;
	var ws = opts.dense ? ([]) : ({});
	var rows = table.getElementsByTagName('tr');
	var sheetRows = opts.sheetRows || 10000000;
	var range = {s:{r:0,c:0},e:{r:0,c:0}};
	var merges = [], midx = 0;
	var rowinfo = [];
	var _R = 0, R = 0, _C, C, RS, CS;
	for(; _R < rows.length && R < sheetRows; ++_R) {
            var row = rows[_R];
            //custom row style
            var rs = row.getAttribute('s') || '';
            if (is_dom_element_hidden(row)) {
                if (opts.display) continue;
                rowinfo[R] = {hidden: true};
            }
            var elts = (row.children);
            for(_C = C = 0; _C < elts.length; ++_C) {
                var elt = elts[_C];
                if (opts.display && is_dom_element_hidden(elt)) continue;
                var v = htmldecode(elt.innerHTML);
                // custom style
                var s = elt.getAttribute('s') || rs;
                if (s) {
                    s = JSON.parse(s);
                } else {
                    s = {};
                }
                s.alignment = {
                    vertical: 'top',
                    wrapText: true,
                }
                for(midx = 0; midx < merges.length; ++midx) {
                    var m = merges[midx];
                    if(m.s.c == C && m.s.r <= R && R <= m.e.r) { C = m.e.c+1; midx = -1; }
                }
                /* TODO: figure out how to extract nonstandard mso- style */
                CS = +elt.getAttribute("colspan") || 1;
                if((RS = +elt.getAttribute("rowspan"))>0 || CS>1) merges.push({s:{r:R,c:C},e:{r:R + (RS||1) - 1, c:C + CS - 1}});
                var o = {t:'s', v:v, s:s};
                var _t = elt.getAttribute("t") || "";
                if(v != null) {
                    if(v.length == 0) o.t = _t || 'z';
                    else if(opts.raw || v.trim().length == 0 || _t == "s"){}
                    else if(v === 'TRUE') o = {t:'b', v:true};
                    else if(v === 'FALSE') o = {t:'b', v:false};
                    else if(!isNaN(fuzzynum(v))) o = {t:'n', v:fuzzynum(v)};
                    else if(!isNaN(fuzzydate(v).getDate())) {
                        o = ({t:'d', v:parseDate(v)});
                        if(!opts.cellDates) o = ({t:'n', v:datenum(o.v)});
                        o.z = opts.dateNF || SSF._table[14];
                    }
                }
                if(opts.dense) { if(!ws[R]) ws[R] = []; ws[R][C] = o; }
                else ws[encode_cell({c:C, r:R})] = o;
                if(range.e.c < C) range.e.c = C;
                C += CS;
            }
            ++R;
	}
	if(merges.length) ws['!merges'] = merges;
	if(rowinfo.length) ws['!rows'] = rowinfo;
	range.e.r = R - 1;
	ws['!ref'] = encode_range(range);
	if(R >= sheetRows) ws['!fullref'] = encode_range((range.e.r = rows.length-_R+R-1,range)); // We can count the real number of rows to parse but we don't to improve the performance
	return ws;
    }
    function is_dom_element_hidden(element) {
	var display = '';
	var get_computed_style = get_get_computed_style_function(element);
	if(get_computed_style) display = get_computed_style(element).getPropertyValue('display');
	if(!display) display = element.style.display; // Fallback for cases when getComputedStyle is not available (e.g. an old browser or some Node.js environments) or doesn't work (e.g. if the element is not inserted to a document)
	return display === 'none';
    }
    function get_get_computed_style_function(element) {
        // The proper getComputedStyle implementation is the one defined in the element window
        if(element.ownerDocument.defaultView && typeof element.ownerDocument.defaultView.getComputedStyle === 'function') return element.ownerDocument.defaultView.getComputedStyle;
        // If it is not available, try to get one from the global namespace
        if(typeof getComputedStyle === 'function') return getComputedStyle;
        return null;
    }
    function encode_cell(cell) { return encode_col(cell.c) + encode_row(cell.r); }
    function encode_col(col) { var s=""; for(++col; col; col=Math.floor((col-1)/26)) s = String.fromCharCode(((col-1)%26) + 65) + s; return s; }
    function encode_row(row) { return "" + (row + 1); }
    function fuzzynum(s) {
	var v = Number(s);
	if(!isNaN(v)) return v;
	var wt = 1;
	var ss = s.replace(/([\d]),([\d])/g,"$1$2").replace(/[$]/g,"").replace(/[%]/g, function() { wt *= 100; return "";});
	if(!isNaN(v = Number(ss))) return v / wt;
	ss = ss.replace(/[(](.*)[)]/,function($$, $1) { wt = -wt; return $1;});
	if(!isNaN(v = Number(ss))) return v / wt;
	return v;
    }
    function fuzzydate(s) {
	var o = new Date(s), n = new Date(NaN);
	var y = o.getYear(), m = o.getMonth(), d = o.getDate();
	if(isNaN(d)) return n;
	if(y < 0 || y > 8099) return n;
	if((m > 0 || d > 1) && y != 101) return o;
	if(s.toLowerCase().match(/jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec/)) return o;
	if(s.match(/[^-0-9:,\/\\]/)) return n;
	return o;
    }
    var good_pd_date = new Date('2017-02-19T19:06:09.000Z');
    if(isNaN(good_pd_date.getFullYear())) good_pd_date = new Date('2/19/17');
    var good_pd = good_pd_date.getFullYear() == 2017;
    /* parses a date as a local date */
    function parseDate(str, fixdate) {
        var d = new Date(str);
        if(good_pd) {
            if(fixdate > 0) d.setTime(d.getTime() + d.getTimezoneOffset() * 60 * 1000);
                else if(fixdate < 0) d.setTime(d.getTime() - d.getTimezoneOffset() * 60 * 1000);
                return d;
        }
        if(str instanceof Date) return str;
        if(good_pd_date.getFullYear() == 1917 && !isNaN(d.getFullYear())) {
                var s = d.getFullYear();
                if(str.indexOf("" + s) > -1) return d;
                d.setFullYear(d.getFullYear() + 100); return d;
        }
        var n = str.match(/\d+/g)||["2017","2","19","0","0","0"];
        var out = new Date(+n[0], +n[1] - 1, +n[2], (+n[3]||0), (+n[4]||0), (+n[5]||0));
        if(str.indexOf("Z") > -1) out = new Date(out.getTime() - out.getTimezoneOffset() * 60 * 1000);
        return out;
    }
    var basedate = new Date(1899, 11, 30, 0, 0, 0); // 2209161600000
    var dnthresh = basedate.getTime() + (new Date().getTimezoneOffset() - basedate.getTimezoneOffset()) * 60000;
    function datenum(v, date1904) {
            var epoch = v.getTime();
            if(date1904) epoch -= 1462*24*60*60*1000;
            return (epoch - dnthresh) / (24 * 60 * 60 * 1000);
    }
    function encode_range(cs,ce) {
                if(typeof ce === 'undefined' || typeof ce === 'number') {
        return encode_range(cs.s, cs.e);
                }
        if(typeof cs !== 'string') cs = encode_cell((cs));
                if(typeof ce !== 'string') ce = encode_cell((ce));
        return cs == ce ? cs : cs + ":" + ce;
    }

    XLSX.utils.table_to_book = table_to_book;
}