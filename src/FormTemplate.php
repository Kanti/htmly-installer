<?php
namespace HTMLy;

class FormTemplate
{
    public static function printForm()
    {
        if (true): ?>
            <form method="POST">
                <label for="user_name">Username:<span class="required">*</span></label>
                <input name="user_name" value="" placeholder="Your User Name" required>
                <br/>
                <label for="user_password">Password:<span class="required">*</span></label>
                <input name="user_password" value="" type="password" placeholder="Password" required>
                <br/>
                <br/>
                <label for="blog_title">Blog Title:</label>
                <input name="blog_title" value="" placeholder="HTMLy">
                <br/>
                <label for="blog_tagline">Blog Tagline:</label>
                <input name="blog_tagline" value="" placeholder="Just another HTMLy blog">
                <br/>
                <label for="blog_description">Blog Description:</label>
                <input name="blog_description" value=""
                       placeholder="Proudly powered by HTMLy, a databaseless blogging platform.">
                <br/>
                <label for="blog_copyright">Blog Copyright:</label>
                <input name="blog_copyright" value="" placeholder="(c) Your name.">
                <br/>
                <br/>
                <label for="social_twitter">Twitter Link:</label>
                <input name="social_twitter" type="url" value="" placeholder="https://twitter.com">
                <br/>
                <label for="social_facebook">Facebook Link:</label>
                <input name="social_facebook" type="url" value="" placeholder="https://www.facebook.com">
                <br/>
                <label for="social_google">Google+ Link:</label>
                <input name="social_google" type="url" value="" placeholder="https://plus.google.com">
                <br/>
                <label for="social_tumblr">Tumblr Link:</label>
                <input name="social_tumblr" type="url" value="" placeholder="https://www.tumblr.com">
                <br/>
                <br/>
                <label for="comment_system">Comment System:</label>
                <select name="comment_system" onchange="checkCommentSystemSelection();" id="comment.system">
                    <option value="disable">disable</option>
                    <option value="facebook">facebook</option>
                    <option value="disqus">disqus</option>
                </select>

                <div id="facebook" style="display:none">
                    <br/>
                    <label for="fb_appid">Facebook AppId:</label>
                    <input name="fb_appid" value="" placeholder="facebook AppId">
                </div>
                <div id="disqus" style="display:none">
                    <br/>
                    <label for="disqus_shortname">Disqus Shortname:</label>
                    <input name="disqus_shortname" value="" placeholder="disqus shortname">
                </div>
                <br/><input type="submit" value="Install via Tool">
            </form>
            <script>
                function checkCommentSystemSelection() {
                    a = document.getElementById("comment.system");
                    if (a.value == "facebook")
                        document.getElementById("facebook").setAttribute("style", "display:inline");
                    else
                        document.getElementById("facebook").setAttribute("style", "display:none");
                    if (a.value == "disqus")
                        document.getElementById("disqus").setAttribute("style", "display:inline");
                    else
                        document.getElementById("disqus").setAttribute("style", "display:none");
                    return a.value;
                }
            </script>
        <?php endif;
    }
}