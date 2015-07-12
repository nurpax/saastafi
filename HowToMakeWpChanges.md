# How to make changes to WP internals #

  * First rule is: DON'T DO IT
  * Second rule: if you have a compelling reason to break the first rule, follow the instructions on this page!

## Why can I not make changes? ##
We shouldn't customize WP internals because this makes upgrading WP much more difficult as we need to apply our own changes again and again as we upgrade WP.

We have made a couple of changes though.  Read below how to keep track of them in order to make upgrading possible.

## Keeping track of changes WP ##
  * Each WP change must have its separate SVN changeset to make it easier to read the patch and apply it after an upgrade
  * Document the issue no. in your submit log message!
  * No WP change should go into SVN without an associated issue.  The idea of having an issue per change is to document what the change is, why it's done and how to test it.  The issue should document all of this.
  * When you've completed a WP change, you must update our "patches to apply" list on HowToUpdateWordPress