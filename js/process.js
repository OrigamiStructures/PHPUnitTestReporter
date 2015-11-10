$(document).ready(function(){
	mainloop();
});

function mainloop() {
	$(report).each(function(){
		$('body').append(makeHeaders(this));
		if (typeof(this.status) != 'undefined') {
			$('body').append(reportBlock(this));
		}		
	});
}

function makeHeaders(obj) {
	var output = "<h1>" + obj.event + "</h1>";
	output += "<h2>" + obj.suite + "</h2>";
	if (typeof(obj.test) == 'undefined') {
		output += "<h3>" + obj.tests + "</h3>";
	} else {
		var start = obj.suite.length + 6;
		output += "<h3>" + obj.test.substr(start, obj.test.length - start) + "</h3>";
	}
	
	return output;
}

function reportBlock(obj) {
	var nodes = ['status', 'time', 'trace', 'message', 'output'];
	var output = "<table><tbody>";
	var headers = '';
	var data = '';
	for (var i = 0; i < nodes.length; i++) {
		headers += "<th>" + nodes[i] + "</th>";
		data += "<td>" + obj[nodes[i]] + "</td>";		
	}
	output += "<tr>" + headers + "</tr><tr>" + data + "</tr></tbody></table>";
	return output;
}