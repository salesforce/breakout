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

class Tokenizer
{
    /**
     * Default delimiter set
     * @var array
     */
    private $delimiters = [
        'variable' => ['{{', '}}'],
        'block' => ['{%', '%}']
    ];

    /**
     * Init the object and optionally set custom delimiters
     *
     * @param mixed $delimiters Custom delimiter [optional]
     */
    public function __construct($delimiters = null)
    {
        if ($delimiters !== null) {
            $this->setDelimiters($delimiters);
        }
    }

    /**
     * Set the custom delimiters for the parsing
     *
     * @param mixed $delimiters Either a single string or an array set of start/end
     */
    public function setDelimiters($delimiters)
    {
        if (is_array($delimiters)) {
            $this->delimiters['variable'] = $delimiters;
        } else {
            $this->delimiters['variable'] = [
                $delimiters,
                $delimiters
            ];
        }
    }

    /**
     * Execute the tokenization of the document
     *
     * @param string $document Document to parse
     * @return array Container of nested token data
     */
    public function execute($document)
    {
        return $this->buildContainer($document);
    }

    /**
     * Build the token container, parsed from the provided document
     *
     * @param string $document Document to parse
     * @return array Container of nested tokens
     */
    public function buildContainer($document)
    {
        // Find the start and end tokens
        $startRegex = '/('.$this->delimiters['variable'][0].'|'.$this->delimiters['block'][0].')+/';
        $endRegex = '/('.$this->delimiters['variable'][1].'|'.$this->delimiters['block'][1].')+/';

        preg_match_all($startRegex, $document, $start, PREG_OFFSET_CAPTURE+PREG_SET_ORDER);
        preg_match_all($endRegex, $document, $end, PREG_OFFSET_CAPTURE+PREG_SET_ORDER);

        // Walk through our matches
        $match = new Matches($start, $end, $this->delimiters);
        $result = $match->walk($document);

        $container = $this->nest($result);
        return $container;
    }

    /**
     * Given the set of matches, nest them according to their types
     *
     * @param \ArrayIterator $result Iterator of match results
     * @param  \SalesforceEng\Breakout\Token $latest Token instance
     * @return array Set of nested token instances (arrays)
     */
    public function nest(&$result, $latest = null)
    {
        $container = [];
        if (!($result instanceof \ArrayIterator)) {
            $result = new \ArrayIterator($result);
        }
        while ($result->valid()) {
            $current = new Token($result->current());
            $result->next();

            if ($current->getType() == 'block') {
                // see if we're closing it out
                $content = trim($current->getContent());

                if ($content !== 'endif' && $content !== 'endfor' && $content !== 'else' && $content !== 'endraw') {
                    $group = [$current];

                    $found = $this->nest($result, $current);
                    $current = array_merge($group, $found);
                } else {
                    if ($latest !== null) {
                        $latestType = explode(' ', trim($latest->getContent()))[0];

                        if ($latestType == 'if' && trim($current->getContent()) == 'endif') {
                            $container[] = $current;
                            return $container;

                        } elseif ($latestType == 'for' && trim($current->getContent()) == 'endfor') {
                            $container[] = $current;
                            return $container;

                        } elseif ($latestType == 'raw' && trim($current->getContent()) == 'endraw') {
                            $container[] = $current;
                            return $container;
                        }
                    }
                }
            }
            $container[] = $current;
        }

        return $container;
    }

}