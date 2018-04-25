<?php

namespace ZxcvbnPhp\Matchers;

class RepeatMatch extends Match
{

    /**
     * @var
     */
    public $repeatedChar;

    /**
     * Match 3 or more repeated characters.
     *
     * @copydoc Match::match()
     */
    public static function match($password, array $userInputs = array())
    {
        $groups = static::group($password);
        $matches = array();

        $k = 0;
        foreach ($groups as $group) {
            $length = strlen($group);

            if ($length > 2) {
                $char = $group[0];
                $end = $k + $length - 1;
                $token = substr($password, $k, $length);
                $matches[] = new static($password, $k, $end, $token, $char);
            }
            $k += $length;
        }
        return $matches;
    }

    public function getFeedback($isSoleMatch)
    {
        $warning = strlen($this->repeatedChar) == 1 
            ? 'Repeats like "aaa" are easy to guess'
            : 'Repeats like "abcabcabc" are only slightly harder to guess than "abc"';

        return array(
            'warning' => $warning,
            'suggestions' => array(
                'Avoid repeated words and characters'
            )
        );
    }

    /**
     * @param $password
     * @param $begin
     * @param $end
     * @param $token
     */
    public function __construct($password, $begin, $end, $token, $char)
    {
        parent::__construct($password, $begin, $end, $token);
        $this->pattern = 'repeat';
        $this->repeatedChar = $char;
    }

    /**
     * @return float
     */
    public function getEntropy()
    {
        if (is_null($this->entropy)) {
           $this->entropy = $this->log($this->getCardinality() * strlen($this->token));
        }
        return $this->entropy;
    }

    /**
     * Group input by repeated characters.
     *
     * @param string $string
     * @return array
     */
    protected static function group($string)
    {
        $grouped = array();
        $chars = str_split($string);

        $prevChar = null;
        $i = 0;
        foreach ($chars as $char) {
            if ($prevChar === $char) {
                $grouped[$i - 1] .= $char;
            }
            else {
                $grouped[$i] = $char;
                $i++;
                $prevChar = $char;
            }
        }
        return $grouped;
    }
}