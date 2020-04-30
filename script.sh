#!/bin/bash
#
# Copyright (c) 2020 Alexandru Catrina <alex@codeissues.net>
# 
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
# 
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
# 
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.

for each in $@; do
    
    # Avoid non-directory arguments
    if [ ! -d $each ]; then
        echo "$each is not a directory, doing nothing..."
        continue
    fi
    
    # Send each file to php script
    for file in $(ls "$each"); do
        tmp=$(mktemp)

        # Run the PHP script to generate the template
        if php script.php $(realpath "$each/$file") > $tmp; then
            
            # Extract required data to build path to class file
            dir=$(grep namespace $tmp | cut -f2 -d ' ' | tr '\\' '/' | tr -d ';')
            fname=$(grep class $tmp | cut -f2 -d ' ')
        
            # Create full path to class file 
            if mkdir -p $dir; then
                mv $tmp $dir/$fname.php
            fi

        # ... or show a message if it fails.
        else
            echo "Failed to generate template for $file ..."
        fi
    done

done
