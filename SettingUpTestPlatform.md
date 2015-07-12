# Setting up saasta.fi test server #

### Download and install MAMP ###

#### Install ####
http://www.mamp.info/en/download.html

Mount disk image and drag MAMP to Applications.

- Start Applications/MAMP/MAMP.app -- this will start MySQL and Apache automatically

#### Configure MAMP ####
  * Change PHP's max memory limit to 30M (default is 8M):
    * Edit file `/Applications/MAMP/conf/php5/php.ini` and edit line `memory_limit = xxx` to `memory_limit = 30M`

### Import DB ###
- Download the latest mysql dump from saasta: ~/saasta/backup/

&lt;n&gt;

/saastafidb.dump.  It's better to use a backup rather than copying from the site, as this will test that backups are working

- Import the DB from the command line (phpMyAdmin doesn't support big DBs):

Create DB:
sudo /Applications/MAMP/Library/bin/mysqladmin -p create saastafi

(passwd=root)

Import DB:

sudo /Applications/MAMP/Library/bin/mysql -u root -p saastafi < saastafidb.dump

Access DB:

sudo /Applications/MAMP/Library/bin/mysql -u root -p saastafi

### Install saasta.fi wordpress from backups ###

**Note:** If you want to update the live saasta.fi server from your SVN repository, see UpdatingLiveServerFromSvn.  You should do this only after you have a working test installation.

Go to the root of your www.saasta.fi backup, and run:

cp -R saasta /Applications/MAMP/htdocs

Edit /Applications/MAMP/htdocs/saasta/wp-config.php and change DB\_USER and DB\_PASSWORD to the following:

define('DB\_USER', 'root');     // Your MySQL username

define('DB\_PASSWORD', 'root'); // ...and password

### Change WordPress URL to localhost ###

Follow these instructions and set the host URL to http://localhost:8888

http://codex.wordpress.org/Changing_The_Site_URL

Note that changing the siteurl is not enough.


### Check that it all works ###

Browse to url http://localhost:8888/saasta/ and it all just works!

### Working ###

If it worked, you should now read UpdatingLiveServerFromSvn to familiarize yourself with edit/update workflow.
