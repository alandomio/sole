<!DOCTYPE html>
<html lang="en">
<head>
	<title>Tooltip Visual Test: Default</title>

<link href="js/jquery/css/ui-lightness/jquery-ui-1.8.10.custom.css" rel="stylesheet" type="text/css" />
<link href="js/jquery/css/jqueryslidemenu.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="js/jquery/jquery-ui-1.8.10.custom.min.js"></script>



	<script type="text/javascript">
	$(function() {
		$.fn.themeswitcher && $('<div/>').css({
			position: "absolute",
			right: 10,
			top: 10
		}).appendTo(document.body).themeswitcher();
		
		function enable() {
			// default
			$("#context1, form, #childish").tooltip();
			
			// custom class, replaces ui-widget-content
			$("#context2").tooltip().each(function() {
				$(this).tooltip("widget").addClass("ui-widget-header");
			})
			$("#right1").tooltip().tooltip("widget").addClass("ui-state-error");
			
			// synchronous content
			$("#footnotes").tooltip({
				items: "[href^=#]",
				content: function() {
					return $($(this).attr("href")).html();
				}
			});
			// asynchronous content
			$("#ajax").tooltip({
				content: function(response) {
					$.get("ajaxcontent.php", response);
					return "Loading...";
				}
			});
			// asynchronous content with caching
			var content;
			$("#ajax2").tooltip({
				content: function(response) {
					if (content) {
						return content;
					}
					$.ajax({
						url: "ajaxcontent.php",
						cache: false,
						success: function(result) {
							content = result;
							response(result);
						}
					});
					return "Loading...";
				}
			});
			
			// custom position
			$("#right2").tooltip({
				position: {
					my: "center top",
					at: "center bottom",
					offset: "0 10"
				}
			}).tooltip("widget").addClass("ui-state-highlight");
			
			$("#button1").button();
			$("#button2").button({
				icons: {
					primary: "ui-icon-wrench"
				}
			});
			$("#button3, #button4").button({
				icons: {
					primary: "ui-icon-wrench"
				},
				text: false
			});
			$("#buttons").tooltip({
				position: {
					my: "center bottom",
					at: "center top",
					offset: "0 -5"
				}
			});
		}
		enable();
		
		$("#disable").toggle(function() {
			$("*").tooltip("disable");
		}, function() {
			$("*").tooltip("enable");
		});
		$("#toggle").toggle(function() {
			$("*").tooltip("destroy");
		}, function() {
			enable();
		});
	});
	</script>
</head>
<body>

<div style="width:300px">
	<ul id="context1" class="ui-widget ui-widget-header">
		<li><a href="#" title="Tooltip text 1">Anchor 1</a></li>
		<li><a href="#" title="Tooltip text 2">Anchor 2</a></li>
		<li><a href="#" title="Tooltip text 3">Anchor 3</a></li>
		<li><a href="#" title="Tooltip text 4 more Tooltip text Tooltip text ">Anchor 4</a></li>
		<li><a href="#" title="Tooltip text 5 more Tooltip text Tooltip text ">Anchor 5</a></li>

		<li><a href="#" title="Tooltip text 6 more Tooltip text Tooltip text ">Anchor 6</a></li>
	</ul>

	<div id="right1" style="position: absolute; right: 1em" title="right aligned element">
		collision detection should kick in around here
	</div>
	
	<div id="footnotes" style="margin: 2em 0">
		<a href="#footnote1">I'm a link to a footnote.</a>
		<a href="#footnote2">I'm another link to a footnote.</a>

	</div>
	
	<div id="right2" style="position: absolute; right: 1em" title="right aligned element with custom position">
		right aligned with custom position
	</div>
	
	<div id="ajax" style="width: 100px;" class="ui-widget-content" title="never be seen">
		gets its content via ajax
	</div>
	<div id="ajax2" style="width: 100px;" class="ui-widget-content" title="never be seen">
		gets its content via ajax, caches the response
	</div>

	
	<div id="context2" class="ui-widget ui-widget-content">
		<span title="something" style="border:1px solid blue">span</span>
		<div title="something else" style="border:1px solid red;">
			div
			<span title="something more" style="border:1px solid green;">nested span</span>
		</div>
	</div>
	
	<div id="childish" class="ui-widget ui-widget-content" style="margin: 2em 0; border: 1px solid black;" title="element with child elements">

		Text in <strong>bold</strong>.
	</div>
	
	<form style="margin: 2em 0;">
		<div>
			<label for="first">First Name:</label>
			<input id="first" title="Your first name is optional" />
		</div>
		<div>

			<label for="last">Last Name:</label>
			<input id="last" title="Your last name is optional" />
		</div>
	</form>
	
	<div id="buttons">
		<button id="button1" title="Button Tooltip">Button Label</button>
		<button id="button2" title="Icon Button">Button with Icon</button>

		<button id="button3">Icon Only Button 1</button>
		<button id="button4">Icon Only Button 2</button>
	</div>
	
	<div id="footnote1">This is <strong>the</strong> footnote, including other elements</div>
	<div id="footnote2">This is <strong>the other</strong> footnote, including other elements</div>

	
	<button id="disable">Toggle disabled</button>
	<button id="toggle">Toggle widget</button>
</div>

<div style="height: 2000px"></div>

</body>
</html>
