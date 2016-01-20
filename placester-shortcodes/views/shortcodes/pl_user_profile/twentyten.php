<?php
$template = array(

'css' => '
.pl-tpl-up-twentyten {
	clear: both;
	overflow: hidden;
	font-size: 1.2em;
	padding-bottom: 2em;
}
.pl-tpl-up-twentyten h3 span {
	float: right;
}
.pl-tpl-up-twentyten ul {
	margin: 0;
	padding: 0;
}
.pl-tpl-up-twentyten li {
	list-style-type: none;
	margin-right: 1em;
}
span.pl-tpl-up-label {
	display: inline-block;
	padding-right: .5em;
	width: 5em;
	color: #7f7f7f;
}
',

'not_logged_in' => '<p>Please login to view your profile.</p>',

'snippet_body' => '
<div class="pl-tpl-up-twentyten">
	<h3>About You:<span>[edit]</span></h3>
	<ul>
		<li><span class="pl-tpl-up-label">Name:</span>[name]</li>
		[if attribute="company"]
		<li><span class="pl-tpl-up-label">Company:</span>[company]</li>
		[/if]
		<li><span class="pl-tpl-up-label">Email:</span>[email]</li>
		[if attribute="phone"]
		<li><span class="pl-tpl-up-label">Phone:</span>[phone]</li>
		[/if]
		[if attribute="address"]
		<li><span class="pl-tpl-up-label">Address:</span>[address] [locality] [region] [postal] [country]</li>
		[/if]
	</ul>
</div>
',

);
