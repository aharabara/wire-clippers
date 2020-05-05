<?php

namespace WireClippers;


class CaseConverter
{


    public function pascalize(string $string):string
    {
        /*
         * This will take any dash or underscore turn it into a space, run ucwords against
         * it so it capitalizes the first letter in all words separated by a space then it
         * turns and deletes all spaces.
         */
        return ucfirst(str_replace(['_', '-'], '', ucwords($string, '_-')));
    }

    public function camelize(string $string): string
    {
        return lcfirst($this->pascalize($string));
    }
}