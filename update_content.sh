#!/bin/sh

# This script is intended to be used for running Saasta directly from
# the SVN working directory.  As the SVN repository doesn't contain
# Saasta content, the content needs to be copied into the respective
# directories in order to use the SVN working copy directory directly
# with the web server.
#
# The reason for doing this is that it's convenient to be able to
# point Apache to read PHP source code directly from the SVN working
# copy, as opposed to having a separate "copy pass" where source code
# is moved under Apache's /htdocs directory.

if [ -z $1 ]; then
    echo "Usage: $0 <source_directory>"
    echo ""
    echo "Need to specify source content directory."
    echo "The source content directory needs to contain"
    echo "the full wp-content, wp-photoz etc. directories, as copied"
    echo "from the live server."
    exit 1
fi

SRC_DIR="$1"

# Test that we're in the root of a Saasta WordPress installation
if [ ! -e "wp-login.php" ]; then
    echo "Couldn't find wp-login.php from the current directory."
    echo "In order to run the content updater, you need to be"
    echo "in the root directory of the Saasta WordPress installation/SVN"
    echo "directory."
    exit 1
fi

# Check that the source directory is also a proper WordPress/saasta
# installation
if [ ! -e "$SRC_DIR/wp-login.php" ]; then
    echo "Couldn't find wp-login.php from the source directory."
    echo "Source directory needs to point to the root directory of a Saasta WordPress installation"
    exit 1
fi

mkdir -p wp-content/uploads
mkdir -p wp-filez
mkdir -p wp-photos

echo Copy wp-content/uploads
cp -fR $SRC_DIR/wp-content/uploads/* wp-content/uploads
echo Copy wp-filez
cp -fR $SRC_DIR/wp-filez/* wp-filez
echo Copy wp-photos
cp -fR $SRC_DIR/wp-photos/* wp-photos
