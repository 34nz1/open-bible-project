# Known issues: 
# Using the built-in webserver, you will _not_ be able to edit config.yml or .twig files,
# because the built-in server chokes on the URL rewriting. This is a bug in PHP, which they
# apparently do not intend to fix anytime soon. See: https://bugs.php.net/bug.php?id=67671

php -S localhost:8000