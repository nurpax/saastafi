# How to upgrade WP #

Upgrading WP is not quite as simple as it is on a normal WP installation.  The reason is that we've modified WP's internals from a couple of places.  When an upgrade is done, we need to apply our custom changes on top of the upgraded WP, in order to not loose our WP customizations.

## Create an upgrade development branch ##

  1. Create an "upgrade branch"
    * Make sure main branch up to date and committed (=`svn st` shouldn't print anything)
    * `svn cp https://saastafi.googlecode.com/svn/trunk/ https://saastafi.googlecode.com/svn/branches/upgrade-wp-x.y.z` where x.y.z is the version number of WP you're upgrading to.  Commit the branch with "svn ci"
  1. Test that your upgrade branch works by pointing creating a link from your htdocs to the newly created branch.  The site should function as it was working from trunk.

## Upgrade WP ##
  1. Take a backup of your DB!  WP upgrade will modify your DB once you run it the first time, so it's good to have the old one available in case something goes wrong.
  1. Working in your newly created upgrade branch, upgrade WP using the standard WP upgrade procedure
  1. Add all new files (run `svn st` and add all `?` files)
  1. Submit everything before going any further

## Apply custom changes ##
Now this is the tricky part.  We need to apply our custom changes to the newly upgraded WP.

Here are the custom changes that we've done to WP.  The table lists both SVN change numbers and issue numbers in the issue DB.  You should apply each change and verify that each change works by testing against the descriptions in the issue thread.

As always, submit each change as its own changeset into SVN.  Keep working in the upgrade branch.

| **SVN change** | **Issue** |
|:---------------|:----------|
| [r97](https://code.google.com/p/saastafi/source/detail?r=97) | [Issue 29: Jesse "Raksa" Heap's search-tags plugin doesn't search for multiple tag](http://code.google.com/p/saastafi/issues/detail?id=29) |
| [r113](https://code.google.com/p/saastafi/source/detail?r=113),[r118](https://code.google.com/p/saastafi/source/detail?r=118) | [Issue 27: Include category tags in the RSS feed entries](http://code.google.com/p/saastafi/issues/detail?id=27).  No need to apply two patches separately on upgrade, you can just combine both changes into one, say by doing `svn diff -r 112:119 saasta/wp-includes/feed-rss2.php` |

## Merge upgrade branch into mainline ##
As the final step, merge your upgrade branch into mainline (trunk).

Follow the merging best practices that are detailed here: http://svnbook.red-bean.com/en/1.0/ch04s04.html.