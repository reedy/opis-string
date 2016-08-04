<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2016 Marius Sarca
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

/**
 * Class wstring
 */
final class wstring implements ArrayAccess
{
    /** @var array  */
    protected $codes;

    /** @var array  */
    protected $chars;

    /** @var string|null */
    protected $string;

    /** @var int */
    protected $length;

    /** @var array */
    protected $cache = array();

    public function __construct(array $codes, array $chars)
    {
        $this->codes = $codes;
        $this->chars = $chars;
        $this->length = count($codes);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->codes[$offset]);
    }

    /**
     * @param mixed $offset
     * @return string
     */
    public function offsetGet($offset)
    {
        return $this->chars[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws Exception
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception("Invalid operation");
    }

    /**
     * @param mixed $offset
     * @throws Exception
     */
    public function offsetUnset($offset)
    {
        throw new Exception("Invalid operation");
    }

    /**
     * @return array
     */
    public function chars()
    {
        return $this->chars;
    }

    /**
     * @return array
     */
    public function codePoints()
    {
        return $this->codes;
    }

    /**
     * @return int
     */
    public function length()
    {
        return $this->length;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->length === 0;
    }

    /**
     * @param string|wstring $text
     * @param bool $ignoreCase
     * @return bool
     */
    public function equals($text, $ignoreCase = false)
    {
        $text = wstring($text);

        if($this->length !== $text->length){
            return false;
        }

        if($ignoreCase){
            return $this->toLower()->equals($text->toLower());
        }

        for($i = 0, $l = $this->length; $i < $l; $i++){
            if($this->codes[$i] !== $text->codes[$i]){
                return false;
            }
        }

        return true;
    }

    /**
     * @param string|wstring $text
     * @param bool $ignoreCase
     * @return int
     */
    public function compareTo($text, $ignoreCase = false)
    {
        $text = wstring($text);

        if($this->length !== $text->length){
            return $this->length > $text->length ? 1 : -1;
        }

        if($ignoreCase){
            return $this->toLower()->compareTo($text->toLower());
        }

        for($i = 0, $l = $this->length; $i < $l; $i++){
            if($this->codes[$i] !== $text->codes[$i]){
                return $this->codes[$i] > $text->codes[$i] ? 1 : -1;
            }
        }

        return 0;
    }

    /**
     * @param string|wstring $text
     * @param bool $ignoreCase
     * @return bool
     */
    public function contains($text, $ignoreCase = false)
    {
        return $this->indexOf($text, 0, $ignoreCase) !== false;
    }

    /**
     * @param string|wstring $text
     * @param bool $ignoreCase
     * @return bool
     */
    public function startsWith($text, $ignoreCase = false)
    {
        return $this->indexOf($text, 0, $ignoreCase) === 0;
    }

    /**
     * @param string|wstring $text
     * @param bool $ignoreCase
     * @return bool
     */
    public function endsWith($text, $ignoreCase = false)
    {
        $text = wstring($text);

        $offset = $this->length - $text->length;

        if($offset < 0){
            return false;
        }

        return $this->indexOf($text, $offset, $ignoreCase) === $offset;
    }

    /**
     * @param string|wstring $text
     * @param int $offset
     * @param bool $ignoreCase
     * @return int|false
     * @throws Exception
     */
    public function indexOf($text, $offset = 0, $ignoreCase = false)
    {
        $text = wstring($text);

        if($ignoreCase){
            return $this->toLower()->indexOf($text->toLower(), $offset);
        }

        if($offset < 0){
            $offset = 0;
        }

        $cp1 = $this->codes;
        $cp2 = $text->codes;

        for($i = $offset, $l = $this->length - $text->length; $i <= $l; $i++){
            $match = true;

            for($j = 0, $f = $text->length; $j < $f; $j++){
                if($cp1[$i + $j] != $cp2[$j]){
                    $match = false;
                    break;
                }
            }

            if($match){
                return $i;
            }
        }

        return false;
    }

    /**
     * @param wstring|string $text
     * @param bool $ignoreCase
     * @return false|int
     */
    public function lastIndexOf($text, $ignoreCase = false)
    {
        $text = wstring($text);

        if($ignoreCase){
            return $this->toLower()->lastIndexOf($text->toLower());
        }

        $index = false;
        $offset = 0;

        while(true){
            if(false === $offset = $this->indexOf($text, $offset)){
                break;
            }
            $index = $offset;
            $offset += $text->length;
        }

        return $index;
    }

    /**
     * @param int $start
     * @param int|null $length
     * @return wstring
     */
    public function substring($start, $length = null)
    {
        $cp = array_slice($this->codes, $start, $length);
        $ch = array_slice($this->chars, $start, $length);

        return new self($cp, $ch);
    }

    /**
     * @param wstring|string $text
     * @return wstring
     */
    public function append($text)
    {
        $text = wstring($text);
        $cp = array_merge($this->codes, $text->codes);
        $ch = array_merge($this->chars, $text->chars);

        return new self($cp, $ch);
    }

    /**
     * @param wstring|string $text
     * @return wstring
     */
    public function prepend($text)
    {
        $text = wstring($text);
        $cp = array_merge($text->codes, $this->codes);
        $ch = array_merge($text->chars, $this->chars);

        return new self($cp, $ch);
    }

    /**
     * @param string|wstring $character_mask
     * @return wstring
     */
    public function trim($character_mask = " \t\n\r\0\x0B")
    {
        return $this->doTrim($character_mask);
    }

    /**
     * @param string|wstring $character_mask
     * @return wstring
     */
    public function ltrim($character_mask = " \t\n\r\0\x0B")
    {
        return $this->doTrim($character_mask, true, false);
    }

    /**
     * @param string|wstring $character_mask
     * @return wstring
     */
    public function rtrim($character_mask = " \t\n\r\0\x0B")
    {
        return $this->doTrim($character_mask, false, true);
    }

    /**
     * @param string|wstring $subject
     * @param string|wstring $replace
     * @param int $offset
     * @return wstring
     * @throws Exception
     */
    public function replace($subject, $replace, $offset = 0)
    {
        $subject = wstring($subject);
        $replace = wstring($replace);

        if(false === $pos = $this->indexOf($subject, $offset)){
            return clone $this;
        }

        $cp1 = array_slice($this->codes, 0, $pos);
        $cp2 = array_slice($this->codes, $pos + $subject->length);
        $ch1 = array_slice($this->chars, 0, $pos);
        $ch2 = array_slice($this->chars, $pos + $subject->length);

        $cp = array_merge($cp1, $replace->codes, $cp2);
        $ch = array_merge($ch1, $replace->chars, $ch2);

        return new self($cp, $ch);
    }

    /**
     * @param string|wstring $subject
     * @param string|wstring $replace
     * @return wstring
     */
    public function replaceAll($subject, $replace)
    {
        $subject = wstring($subject);
        $replace = wstring($replace);

        if(false === $offset = $this->indexOf($subject) || $subject->isEmpty()){
            return clone $this;
        }

        $text = $this;

        do{
            $text = $text->replace($subject, $replace, $offset);
            $offset = $text->indexOf($subject, $offset + $replace->length);
        } while($offset !== false);

        return $text;
    }

    /**
     * @return wstring
     */
    public function reverse()
    {
        $cp = array_reverse($this->codes);
        $ch = array_reverse($this->chars);

        return new self($cp, $ch);
    }

    /**
     * @param string|wstring $char
     * @return array
     */
    public function split($char = ' ')
    {
        $char = wstring($char);
        $results = array();

        if($char->isEmpty()){
            for($i = 0, $l = $this->length; $i < $l; $i++){
                $results[] = new self(array($this->codes[$i]), array($this->chars[$i]));
            }
            return $results;
        }

        if(false === $offset = $this->indexOf($char)){
            return array(clone $this);
        }

        $start = 0;
        do{
            $cp = array_slice($this->codes, $start, $offset - $start);
            $ch = array_slice($this->chars, $start, $offset - $start);
            $results[] = new self($cp, $ch);
            $start = $offset + $char->length;
            $offset = $this->indexOf($char, $start);
        } while ($offset !== false);

        $cp = array_slice($this->codes, $start);
        $ch = array_slice($this->chars, $start);
        $results[] = new self($cp, $ch);
        return $results;
    }

    /**
     * @return bool
     */
    public function isLowerCase()
    {
        return $this->isCase($this->getUpperMap());
    }

    /**
     * @return bool
     */
    public function isUpperCase()
    {
        return $this->isCase($this->getLowerMap());
    }

    /**
     * @return wstring
     */
    public function toLower()
    {
        return $this->toCase($this->getLowerMap());
    }

    /**
     * @return wstring
     */
    public function toUpper()
    {
        return $this->toCase($this->getUpperMap());
    }

    /**
     * @param int $offset
     * @return int
     */
    public function __invoke($offset)
    {
        return $this->codes[$offset];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if($this->string === null){
            $this->string = implode('', $this->chars);
        }
        return $this->string;
    }

    /**
     * @param $character_mask
     * @param bool $left
     * @param bool $right
     * @return wstring
     * @throws Exception
     */
    protected function doTrim($character_mask, $left = true, $right = true)
    {
        $character_mask = wstring($character_mask);

        $cm = $character_mask->codes;
        $cp = $this->codes;
        $l = count($cm);
        $start = 0;
        $end = $this->length;

        if($left){
            for ($i = 0; $i < $this->length; $i++) {
                if (!in_array($cp[$i], $cm)) {
                    break;
                }
            }
            $start = $i;
        }

        if($right){
            for ($i = $this->length - 1; $i > $start; $i--) {
                if (!in_array($cp[$i], $cm)) {
                    break;
                }
            }
            $end = $i + 1;
        }

        $cp = array_slice($cp, $start, $end - $start);
        $ch = array_slice($this->chars, $start, $end - $start);

        return new self($cp, $ch);
    }

    /**
     * @param array $map
     * @return wstring
     */
    protected function toCase(array $map)
    {
        $cp = $this->codes;
        $ch = $this->chars;
        $ocp = $och = array();

        for($i = 0, $l = $this->length; $i < $l; $i++){
            $p = $cp[$i];
            if(isset($map[$p])){
                $v = $map[$p];
                $ocp[] = $v[0];
                $och[] = $v[1];
            } else {
                $ocp[] = $p;
                $och[] = $ch[$i];
            }
        }

        return new self($ocp, $och);
    }

    /**
     * @param array $map
     * @return bool
     */
    protected function isCase(array $map)
    {
        foreach ($this->codes as $cp){
            if(isset($map[$cp])){
                return false;
            }
        }
        return true;
    }

    /**
     * @return array
     */
    protected function getUpperMap()
    {
        static $upper;

        if($upper === null){
            $upper = require __DIR__ . '/../res/upper.php';
        }

        return $upper;
    }

    /**
     * @return array
     */
    protected function getLowerMap()
    {
        static $lower;

        if($lower === null){
            $lower = require __DIR__ . '/../res/lower.php';
        }

        return $lower;
    }


}

/**
 * @param $string
 * @return wstring
 * @throws Exception
 */
function wstring($string)
{
    if($string instanceof wstring){
        return $string;
    }

    $codes = $chars = array();

    if(false === $text = json_encode((string) $string)) {
        throw new Exception("Invalid UTF-8 string");
    }

    for($i = 1, $l = strlen($text) - 1; $i < $l; $i++) {
        $c = $text[$i];

        if($c === '\\'){
            if(isset($text[$i + 1])){

                if($text[$i + 1] === 'u'){

                    $codes[] = $cp = hexdec(substr($text, $i, 6));

                    if ($cp < 0x80) {
                        $chars[] = chr($cp);
                    } elseif ($cp <= 0x7FF) {
                        $chars[] = chr(($cp >> 6) + 0xC0) . chr(($cp & 0x3F) + 0x80);
                    } elseif ($cp <= 0xFFFF) {
                        $chars[] = chr(($cp >> 12) + 0xE0) . chr((($cp >> 6) & 0x3F) + 0x80) . chr(($cp & 0x3F) + 0x80);
                    } elseif ($cp <= 0x10FFFF) {
                        $chars[] = chr(($cp >> 18) + 0xF0) . chr((($cp >> 12) & 0x3F) + 0x80)
                            . chr((($cp >> 6) & 0x3F) + 0x80) . chr(($cp & 0x3F) + 0x80);
                    } else {
                        throw new Exception("Invalid UTF-8");
                    }

                    $i += 5;
                    continue;

                } else{

                    switch ($text[$i + 1]){
                        case '\\':
                            $c = "\\";
                            break;
                        case '\'':
                            $c = "'";
                            break;
                        case '"':
                            $c = '"';
                            break;
                        case 'n':
                            $c = "\n";
                            break;
                        case 'r':
                            $c = "\r";
                            break;
                        case 't':
                            $c = "\t";
                            break;
                        case 'b':
                            $c = "\b";
                            break;
                        case 'f':
                            $c = "\f";
                            break;
                        case 'v':
                            $c = "\v";
                            break;
                        case '0':
                            $c = "\0";
                            break;
                        default:
                            $c = $text[$i + 1];
                    }

                    $codes[] = ord($c);
                    $chars[] = $c;
                    $i++;
                    continue;
                }
            }
        }

        $codes[] = ord($c);
        $chars[] = $c;
    }

    return new wstring($codes, $chars, null);
}