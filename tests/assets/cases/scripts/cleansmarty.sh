#!/bin/bash
#
echo "Cleaning Smarty compiled templates:";
rm -fv cache/smarty/compile/* | awk '{print "\t" $0}';
