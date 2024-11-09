<?php declare (strict_types = 1);
/**
 * Project Utilities
 *
 * A set of common utilities that will be resused throughout this project.
 *
 * PHP Version 8
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */

/**
 * Utilities for the project
 */
final class Utils
{
    /**
     * Function to print with a new line.
     *
     * Meant to be equivalent to `println` or similar in other languages.
     *
     * Note that the default linebreak is meant for the web.
     * To change it (CLI, etc), change `$newline` to `PHP_EOL` or something similar.
     *
     * @param string $str     The string to echo
     * @param string $newline The newline seperator. Default is `<br>`.
     */
    public static function echoln(string $str, string $newline = "<br>"): void
    {
        echo $str . $newline;
    }

    /**
     * Check variable existence and equality.
     *
     * Returns true only if variable is defined and has the specified value.
     *
     * @param object $var The variable to be compared
     * @param object $val The value the variable is to be compared to
     *
     * @return bool
     */
    public static function checkVar(&$var, $val): bool
    {
        if (isset($var) && $var == $val) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Parses the range headers into a list of numbers.
     *
     * The first number is the start value, the last number is the end value.
     *
     * Missing values are interpreted as `null`.
     *
     * Note that this does not validate the values.
     *
     * Syntax taken from
     * [Range - HTTP | MDN](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Range#syntax)
     *
     * @param string $header The range header. Expects `$_SERVER['HTTP_Range']`.
     *
     * @return array<array<int,int>> The list of range values as [[start, end],...]
     */
    public static function parseRangeHeader(string $header): array
    {
        // Throughout the next section, `trim` is used generosly to remove whitespace

        // Value of range header
        // We're throwing away the `Range: ` bit.
        // We assume that the corrct header is being passed in.
        // TODO: Check that `Range` is the header being passed.
        $head = trim(explode(":", $header)[1]);

        // Type of range and the values
        $tmp       = explode("=", $head);
        $type      = trim($tmp[0]);
        $range_raw = trim($tmp[1]);

        $range_list = explode(",", $range_raw);

        $ranges = [];

        foreach ($range_list as $val) {
            $tmp1 = explode("-", $val);

            $start = trim($tmp1[0]) === "" ? null : (int) $tmp1[0];
            $end   = trim($tmp1[1]) === "" ? null : (int) $tmp1[1];

            $ranges[] = [$start, $end];
        }

        return $ranges;
    }
}
