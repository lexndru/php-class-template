#!/usr/bin/env php 
<?php
// Copyright (c) 2020 Alexandru Catrina <alex@codeissues.net>
// 
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
// 
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.

if ($argc === 1) {
    echo "Usage: $args[0] filepath";
    exit(0);
}

// Retrieve the first filename only from argv
// and check if it's a valid file path. Avoid
// providing multiple file to this script and
// use the shell script instead.
list(, $filepath) = $argv;
if (!file_exists($filepath)) {
    echo "File $filepath does not exist";
    exit(1);
}

// Decode the JSON file and use the resulting
// data to build the template. If the JSON is
// invalid, stop.
$content = @file_get_contents($filepath);
$data = json_decode($content);
if ($data === null) {
    echo "Invalid data for file $filepath";
    exit(1);
}

// Start building the template. Invoke decoded
// data from the file's content instead of PHP
// placeholders. There is no need to use extra 
// templating engines when PHP is already one.
//
// HEREDOC is used for multi-line support, but
// feel free to use whatever suits you best.
$template = <<<PHP
<?php

namespace $data->namespace;

class $data->className
{
PHP;

// Loop through all fields and generate class
// properties, getters and setters.
if (is_array($data->fields)) {
    foreach ($data->fields as $field) {
        $pascal = ucfirst($field->name);
        $template .= <<<PHP

    /**
     * $field->docs
     *
     * @var $field->type
     */
    protected \$$field->name;

    /**
     * Property getter.
     *
     * @return $field->type
     */
    public function get$pascal(): $field->type
    {
        return \$this->$field->name;
    }

    /**
     * Property setter.
     *
     * @param \$$field->name $field->type
     * @return void
     */
    public function set$pascal($field->type \$$field->name): void
    {
        \$this->$field->name = \$$field->name;
    }

PHP;
    }
} 

// If there's no fields to populate, add four
// spaces intentation and an empty comment.
else {
    $template .= PHP_EOL . str_repeat(" ", 4) . "//" . PHP_EOL;
}

// Close the last curly bracket and one last
// newline for pretty print.
$template .= <<<PHP
}

PHP;

// Output the template.
exit($template);
