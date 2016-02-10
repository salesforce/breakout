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

class Matches
{
    /**
     * Start token match details
     * @var array
     */
    private $start = [];

    /**
     * End token match details
     * @var array
     */
    private $end = [];

    private $delimiters = [];

    /**
     * Init the object and provide the token start/token end position matches
     *
     * @param array $start Set of token start position matches
     * @param array $end Set of token end position matches
     */
    public function __construct($start, $end, array $delimiters = array())
    {
        $this->start = $this->parseMatches($start);
        $this->end = $this->parseMatches($end);

        $this->delimiters = $delimiters;
    }

    /**
     * Cycle through the matches and parse them into the needed format
     *
     * @param array $matches Set of matches (from a preg_match)
     * @return array Set of formatted match details
     */
    public function parseMatches($matches)
    {
        $result = [];
        foreach ($matches as $match) {
            $result[$match[0][1]] = $match[0][0];
        }
        return $result;
    }

    /**
     * Walk the document provided along with the keys and build the results
     *
     * @param string $document Document to parse
     * @return array Set of tokens found as arrays
     */
    public function walk($document)
    {
        $result = [];
        $endKeys = array_keys($this->end);
        $end = array_pop($endKeys);
        $endToken = '';
        $endPosition = 0;

        $openVariable = $this->delimiters['variable'][0];
        $closeVariable = $this->delimiters['variable'][1];
        $closeBlock = $this->delimiters['block'][1];

        foreach ($this->start as $position => $startToken) {
            $find = ($startToken == $openVariable) ? $closeVariable : $closeBlock;
            $type = ($startToken == $openVariable) ? 'variable' : 'block';

            // find the matching end tag
            $content = '';
            for ($i = ($position + 2); $i <= $end; $i++) {
                // go through the end values and find the end for the token
                if (isset($this->end[$i]) && $this->end[$i] == $find) {
                    $contentTrim = trim($content);
                    $result[] = [
                        'start' => $position,
                        'content' => $content,
                        'full' => $startToken.$content.$this->end[$i],
                        'end' => $i + 2,
                        'type' => $type,
                        'token' => explode(' ', $contentTrim)[0]
                    ];
                    continue 2;
                } else {
                    $content .= $document[$i];
                }
            }
        }

        return $result;
    }
}