<?php

namespace common\components;

/**
 * Component for falidating values
 * @createdBy Basil A Konakov
 * @createdAt 2018-08-24
 * @author Mixcart
 * @module Frontend
 * @version 1.0
 */
class SimpleChecker
{

    /** List of whole number admissible symbols packed in format of string */
    const SYMB_INTEGER = '1234567890';

    /** Checks if a string or other value contains only number symbols only
     * @var $value mixed Validated data
     * @return boolean
     */
    public static function validateWholeNumerExactly($value = NULL): bool
    {
        $value = (string)$value;
        $res = FALSE;
        if (!strlen($value)) {
            return $res;
        }
        $symbolsAdmissible = str_split(self::SYMB_INTEGER);

        foreach (str_split($value) as $v) {
            if (!in_array($v, $symbolsAdmissible)) {
                return $res;
            }
        }

        return TRUE;
    }

}