<?php
namespace HTMLy;

class HeaderTemplate
{
    public static function printHeader($version = null)
    {
        if (true) : ?>
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                    }

                    *, *:before, *:after {
                        -moz-box-sizing: border-box;
                        -webkit-box-sizing: border-box;
                        box-sizing: border-box;
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

                    #branding h1 {
                        font-size: 36px;
                        font-family: Georgia, sans-serif;
                        margin: 0;
                    }

                    #branding h1 a {
                        color: inherit;
                        text-decoration: inherit;
                    }

                    #branding h1 a:hover {
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

                    label {
                        width: 100%;
                        max-width: 180px;
                        float: left;
                    }

                    input:not([type=submit]), select {
                        float: left;
                        width: 100%;
                        max-width: 520px;
                        font-size: 80%;
                    }

                    input {
                        padding: 2px;
                    }

                    input[type=submit] {
                        margin-top: 10px;
                        padding: 5px;
                        width: 100%;
                    }

                    span.required {
                        color: red;
                    }
                </style>
                <link rel="icon"
                      href="data:image/vnd.microsoft.icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAABMLAAATCwAAAAAAAAAAAACVWQBclVkAsJVZAJGVWQAUlVkAAAAAAAAAAAAAu3EAALtxABa7cQCPu3EArrtxAKy7cQCsu3EArbtxAKW7cQBFlVkAyZVZAP+VWQD2lVkAQ5VZAAAAAAAAAAAAALtxAAC7cQBGu3EA97txAP+7cQD/u3EA/7txAP+7cQD/u3EAp5VZAMWVWQD/lVkA/5VZAHaVWQAAlVkAAAAAAAC7cQAAu3EATLtxAPq7cQD/u3EA/7txAP+7cQD/u3EA/7txALCVWQBBlVkAg5VZAKCVWQDLlVkAdJVZAECVWQAYtW0AALtxAEy7cQD6u3EA/7txAP+7cQD/u3EA/7txAP+7cQCwAAAAAJVZAACWWgAAlVkAbJVZAPyVWQD5lVkAoAAAAAC7cQBMu3EA+rtxAP+7cQD/u3EA/7txAP+7cQD/u3EAsAAAAAAAAAAAlVkAAJVZADuVWQD0lVkA/5VZAMB7SAAGu3EAYbtxAP67cQD/u3EA/7txAP+7cQD/u3EA/7txALAAAAAAAAAAAJVZAACVWQAblVkAt5VZAN2UWAB/uG8AHLtxAL67cQD/u3EA/7txAP+7cQD/u3EA/7txAP+7cQCqunAAALtwAAG8cQABt24AAZdaABCZWwActm4AMbtxALO7cQD/u3EA87txAMG7cQCru3EAq7txAKu7cQClu3EASLtxADu7cQCcu3EApbtxAKW7cQCku3EArLtxAN+7cQD/u3EA47xxAFrRcgAI/3gAAf92AAH5dwABv3EAAbpxAAC7cQCWu3EA/7txAP+7cQD/u3EA/7txAP+7cQD/u3EA5LxxAEltbnEOb25udXBubZRwbm2UcG5tgXBubRpwbm0Au3EAnLtxAP+7cQD/u3EA/7txAP+7cQD/u3EA/7txAId+b1kAcG5tPHBubfNwbm3/cG5t/3Bubf1wbm1acG5tALtxAJy7cQD/u3EA/7txAP+7cQD/u3EA/7txAP67cQBdmHAzAHBubUJwbm33cG5t/3Bubf9wbm3/cG5tYXBubQC7cQCcu3EA/7txAP+7cQD/u3EA/7txAP+7cQD+u3EAW5hwMwBwbm1CcG5t93Bubf9wbm3/cG5t/3BubWBwbm0Au3EAnLtxAP+7cQD/u3EA/7txAP+7cQD/u3EA/rtxAFudcCwAcG5tMXBubeVwbm38cG5t+3BubfJwbm1LcG5tALtxAJW7cQD/u3EA/7txAP+7cQD/u3EA/7txAPy7cQBVtnEHAHBubQRwbm04cG5tTnBubU5wbm1AcG5tCXBubQC7cQA+u3EAqrtxALW7cQC1u3EAtbtxALa7cQCdu3EAHrtxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADwAAAA8AAAAPAAAAAQAAAOAAAADgAAAA4AAAAAAAAAAAAAAAAAEAAACBAAAAgQAAAIEAAACBAAAAgQAAAP8AAA=="
                      type="image/ico"/>
                <title>HTMLy Installer</title>
            </head>
        <body>
            <div id="cover">
                <div id="header-wrapper">
                    <header id="header" class="responsive">
                        <div id="branding">
                            <h1>
<?php if ($version !== null) : ?>
    <a href="<?php echo $version['html_url']; ?>" target="_blank"> HTMLy <small>/<?php echo $version['tag_name']; ?>/</a></small>
    <?php else : ?>
    HTMLy
<?php endif; ?>
                            </h1>
                            <div id="blog-tagline">
                                <p>the HTMLy Installer Tool
                                    <small> /v1.9.0/</small>
                                </p>
                            </div>
                        </div>
                    </header>
                </div>
            </div>
            <div id="main-wrapper">
                <div id="main" class="responsive">
                    <p id="rewriteRule" style="display:none;" class="warning">Your rewriteRule is not ready to use. <a
                            href="https://github.com/danpros/htmly#lighttpd" target="_blank">Help!</a></p><br/>
                    <script>
                        function testRewriteRule() {
                            var xmlhttp;
                            if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
                                xmlhttp = new XMLHttpRequest();
                            }
                            else {// code for IE6, IE5
                                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                            }
                            xmlhttp.onreadystatechange = function () {
                                if (xmlhttp.readyState == 4 && xmlhttp.status != 200) {
                                    document.getElementById("rewriteRule").style.display = "block";
                                }
                            };
                            xmlhttp.open("GET", "../rewriteTest.html", true);
                            xmlhttp.send();
                        }
                        testRewriteRule();
                    </script><?php
        endif;
    }
}