#!/bin/sh
rm -rf tmp
mkdir -p tmp
svn export saasta tmp/saasta
cd tmp
tar cf saasta.tar saasta
gzip saasta.tar
