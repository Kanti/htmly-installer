<?php function printHeader($version = null) { if(true):?>
<!DOCTYPE html>
<html>
<head>
<style>
* {
    margin: 0;
    padding: 0;
}
*, *:before, *:after {
  -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box;
 }
.responsive {
    min-width: 512px;
    width: 100%;
    max-width: 730px;
}
section, footer, header, aside, nav {
    display: block;
}
.error:before {
    content: "Error: ";
    color: red;
    font-weight: bold;
    font-size: 120%;
}

.warning:before {
    content: "Warning: ";
    color: gold;
    font-weight: bold;
    font-size: 120%;
}
body {
    font-size: 17px;
    line-height: 1.6em;
    font-family: Georgia, sans-serif;
    background: #F7F7F7;
    color: #444444;
}
#cover {
    padding: 0 0 20px 0;
    float: left;
    width: 100%;
}
#header-wrapper {
    float: left;
    width: 100%;
    position: relative;
}
#header {
    position: relative;
    padding: 0 15px;
    margin: 0 auto;
}
#branding {
    text-align: left;
    position: relative;
    width: 100%;
    float: left;
    margin: 1em 0;
    text-shadow: 0 1px 0 #ffffff;
}
#branding h1{
    font-size: 36px;
    font-family: Georgia,sans-serif;
    margin: 0;
}
#branding h1 a{
    color: inherit;
    text-decoration: inherit;
}
#branding h1 a:hover{
    color: black;
}
#branding p {
    margin: 0;
    font-style: italic;
    margin-top: 5px;
}
#main-wrapper {
    float: left;
    width: 100%;
    background: #ffffff;
    position: relative;
    border-top: 1px solid #DFDFDF;
    border-bottom: 1px solid #DFDFDF;
}
#main {
    position: relative;
    padding: 0;
    margin: 0 auto;
    background: #ffffff;
    overflow: hidden;
    padding: 30px 15px;
}
label{
    width: 100%;
    max-width: 180px;
    float:left;
}
input:not([type=submit]), select{
    float:left;
    width: 100%;
    max-width: 520px;
    font-size: 80%;
}
input{
    padding: 2px;
}
input[type=submit]{
    margin-top: 10px;
    padding: 5px;
    width: 100%;
}
span.required {
    color: red;
}
</style>
<link rel="icon" href="data:image/png;base64,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" type="image/ico" />
<title>HTMLy Installer</title>
</head>
<body>
<div id="cover">
    <div id="header-wrapper">
        <header id="header" class="responsive">
            <div id="branding">
                <h1>
                    <?php if($version !== null):?><a href="<?php echo $version['html_url']; ?>" target="_blank"> HTMLy <small>/<?php echo $version['tag_name']; ?>/</a></small><?php else: ?>HTMLy<?php endif;?>
                </h1>
                <div id="blog-tagline">
                    <p>the HTMLy Installer Tool <small> /v1.2.7/</small></p>
                </div>
            </div>
        </header>
    </div>
</div>
<div id="main-wrapper">
    <div id="main" class="responsive">
        <p id="rewriteRule" style="display:none;" class="warning">Your rewriteRule is not ready to use. <a href="https://github.com/danpros/htmly#lighttpd" target="_blank">Help!</a></p><br/>
        <script>
function testRewriteRule()
{
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function()
    {
        if (xmlhttp.readyState == 4 && xmlhttp.status != 200)
        {
            document.getElementById("rewriteRule").style.display = "block";
        }
    }
    xmlhttp.open("GET","rewriteTest.html",true);
    xmlhttp.send();
}
testRewriteRule();
        </script>
<?php endif; }?>