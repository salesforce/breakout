<?php
/**
 * Copyright (c) 2016, Salesforce.com, Inc.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of Salesforce.com nor the names of its contributors may
 * be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace SalesforceEng\Breakout;

use SalesforceEng\Breakout\Tag;

class Breakout
{
    private static $defaultContext = 'html';
    private static $delimiterLeft = '{{';
    private static $delimiterRight = '}}';
    private static $contextCache = [];

    /**
     * Set both delimiters at the same time
     *
     * @param string|array $delimiters Either an array of 2 delimiters or a single string
     */
    public static function setDelimiters($delimiters)
    {
        if (!is_array($delimiters)) {
            $delimiters = [$delimiters, $delimiters];
        }
        self::setDelimiter(array_shift($delimiters), 'left');
        self::setDelimiter(array_shift($delimiters), 'right');
    }

    /**
     * Get the current delimiter information
     *
     * @return array Delimiter set
     */
    public static function getDelimiters()
    {
        return [ self::getDelimiter('left'), self::getDelimiter('right')];
    }

    /**
     * Set the delimiter(s) for the current replacement
     *
     * @param string $value Delimiter value
     * @param string $side Side to place the delimiter on ("left" or "right")
     */
    public static function setDelimiter($value, $side = 'left')
    {
        $type = 'delimiter'.ucwords(strtolower($side));
        self::$$type = $value;
    }

    /**
     * Get the delimiter for the specified side
     *
     * @param string $side Side ro fetch (left or right)
     * @return string Delimiter value
     */
    public static function getDelimiter($side = 'left')
    {
        return (strtolower($side) == 'left') ? self::$delimiterLeft : self::$delimiterRight;
    }

    /**
     * Render the document, replacing data with provided escaping
     *
     * @param string $document Document to replace content inside of
     * @param array $data Data to replace
     * @return string Document with content replaced and escaped
     */
    public static function render($document, array $data, $delimiters = null)
    {
        if ($delimiters === null) {
            $delimiters = self::getDelimiters();
        }

        $token = new Tokenizer($delimiters);
        $result = $token->execute($document);

        $parser = new Parser($result, $document);
        return $parser->execute($data);
    }

    /**
     * Escape the provided data in the given context
     *
     * @param string $data Data to escape
     * @param string $context Context to use for escaping (defaults to HTML)
     * @param array $config Configuration options [optional]
     * @return string Escaped content
     */
    public function escape($data, $context = 'html', array $config = array())
    {
        $contextNs = '\\SalesforceEng\\Breakout\\Context\\'.ucwords(strtolower($context));
        if (!class_exists($contextNs)) {
            throw new \InvalidArgumentException('Context "'.$context.'" does not exist!');
        }
        if ($data === null || empty($data)) {
            return $data;
        }
        // If it's not a string or int, just return it
        if (is_array($data) || is_object($data)) {
            return $data;
        }
        $contextInstance = new $contextNs($config);
        return $contextInstance->escape((string)$data);
    }
}