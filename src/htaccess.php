<?php function htaccess(){ return <<<EOT
# Don't show directory listings for URLs which map to a directory.
Options -Indexes

# Follow symbolic links in this directory.
Options +FollowSymLinks

# Make HTMLy handle any 404 errors.
ErrorDocument 404 /index.php

# Set the default handler.
DirectoryIndex index.php index.html index.htm

# Requires mod_expires to be enabled.

# Various rewrite rules.
<IfModule mod_rewrite.c>

  RewriteEngine on

# Uncomment the following to redirect all visitors to the www version
# RewriteCond %{HTTP_HOST} !^www\. [NC]
# RewriteRule ^ http://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Uncomment the following to redirect all visitors to non www version
# RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]
# RewriteRule ^ http://%1%{REQUEST_URI} [L,R=301]

# If your site is running in a VirtualDocumentRoot at http://example.com/,
# uncomment the following line:
# RewriteBase /

# Pass all requests not referring directly to files in the filesystem to index.php.
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d 
  RewriteRule ^ index.php [L]

</IfModule>
EOT;
}
?>