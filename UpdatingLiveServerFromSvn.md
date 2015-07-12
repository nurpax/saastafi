# Edit/update cycle #

There are a few separate edit/update scenarios.  The most common one is when you edit source code in the SVN working copy and you want Apache to automatically pick up your changes.  The second scenario is testing your release on your test server.  The last scenario is when you want to upload your latest release to the production server.

## Editing and testing the SVN working copy ##

This is the method you'll be using most of the time.  The idea is to have a checked out copy of the site's source code and use that working copy directly with Apache running on your test server.

As the SVN copy of the site's source code doesn't contain any of the content directories, we need to first check out the source code and then copy the content directories over to the SVN copy.

Content dir (`$CONTENT_DIR`) means a directory containing a backed up copy of the production server's WordPress installation.

### Setting it up ###

  1. Check out a working copy of saastafi from SVN (see project page for instructions).  The trunk root contains a directory called 'saasta'.  This location is denoted by `$SITE/saasta`.
  1. Go to your Apache's htdocs directory and create a symlink `saasta` that points to `$SITE/saasta`.  E.g., `cd /Applications/MAMP/htdocs/` followed by `ln -s /Users/janne/dev/saastafi/trunk/saasta saasta`.
  1. Go to `$SITE/saasta` and run `../update_content.sh $CONTENT_DIR`. Update wp-config.php as instructed by the update script.

You should be good to go.

## Testing your release package on test server ##
This is only an intermediate step in order to do a production server update.  This will test whether or not a release package works on the test server.

The release package contains only files that are in SVN.  Any local files in your working copy will not be included in the release package.

  1. Make sure that you've committed all your changes to SVN by running `svn st`.  This command shouldn't print anything.  Also make sure you've ran `svn up` to pick up other people's changes.
  1. Create a release tarball by cd'ing to your trunk root, and running `./dist_package.sh`.  This will create a directory `tmp` that contains the release package `saasta.tar.gz`.
  1. Go to /Applications/MAMP/htdocs and backup your `saasta` symlink with `mv saasta bak.saasta`
  1. Untar your release tarball by running `tar zxf $PATH_TO_TRUNK/tmp/saasta.tar.gz`.  This will create a directory called `saasta` which contains the source code of your site.
  1. Go to the newly created saasta directory and copy the content files over by running: `cd saasta && $PATH_TO_TRUNK/update_content.sh $CONTENT_DIR`
  1. Update wp-config.php the same way as above.  You can also copy it from `bak.saasta/wp-config.php`)
  1. Test the release on test server (running whatever sanity checks you might have)
  1. If it worked, restore your `saasta` symlink by running `rm -rf saasta && mv bak.saasta saasta` in `htdocs`

## Update production server to use your new release ##

  1. Test that your release works on the test server by following the instructions above
  1. **BACKUP** `/saasta` directory on the production server.  Don't skip this step.
  1. **BACKUP** the database as well.
  1. Didn't backup yet?  Go to (2)
  1. Copy your `saasta.tar.gz` to the production server.  Go to the site's root directory and run `tar zxf saasta.tar.gz`
  1. Run sanity checks
  1. If it failed, restore the old site from backups.  NOTE: only restore DB from backups if you think it was screwed by your testing.  It's less risky not to attempt restoring it if you know it wasn't changed during your testing.