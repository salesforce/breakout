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

namespace SalesforceEng\Breakout\Block;

use SalesforceEng\Breakout\Parser;
use SalesforceEng\Breakout\Tokenizer;
use SalesforceEng\Breakout\Token;

class BlockIf extends \SalesforceEng\Breakout\Block
{
    /**
     * Execute the "if" block handling
     *
     * @return \SalesforceEng\Breakout\Document instance
     */
    public function execute()
    {
        $tokens = $this->getTokens();
        $data = $this->getData();
        $document = $this->getDocument();

        // get the details for the "if"
        $ifBlock = array_shift($tokens);
        $last = array_pop($tokens);

        // Parse out the block contents
        preg_match('/{% if (.+?) ([=!]*)(.*?)%}/', $ifBlock->getFull(), $matches);
        $varname = $matches[1];
        $operator = (isset($matches[2]) && !empty($matches[2])) ? $matches[2] : 'isset';

        $elseBetween = null;
        $matchValue = null;

        // If we have a value to match against, normalize it a bit
        if (isset($matches[3])) {
            $matchValue = $this->normalizeValue($matches[3]);
        }

        // See if we have an "else" we need to get it out and update our current tokens
        if (!empty($tokens) && count($tokens) == 1 && !is_array($tokens[0]) && $tokens[0]->getToken() == 'else') {
            $else = array_pop($tokens);
            $elseBetween = $document->between($else, $last);
            $between = $document->between($ifBlock, $else);
        } else {
            $between = $document->between($ifBlock, $last);
        }

        $resolved = Parser::resolveValue($data, $varname);

        // Match the replacement template depending on evaluation result
        $template = ($this->evaluate($operator, $resolved, $matchValue) === true)
            ? $between : $elseBetween;

        // Now perform the replacement
        $token = new Tokenizer();
        $parser = new Parser($token->execute($template), $template);
        $content = $parser->execute($data);

        $contentToken = new Token([
            'content' => $content,
            'start' => $ifBlock->getStart(),
            'end' => $last->getEnd()
        ]);
        $document->replace($contentToken, $content);

        return $document;
    }

    /**
     * Normalize the value a bit
     *
     * @param string $value Value to "normalize"
     * @return string Updated value
     */
    public function normalizeValue($value)
    {
        $matchValue = trim($value);

        // Normalize to remove outer single quotes
        if (substr($matchValue, 0, 1) == "'" && substr($matchValue, -1) == "'") {
            $matchValue = substr($matchValue, 1, strlen($matchValue) - 2);
        }

        return $matchValue;
    }

    /**
     * Evaluate the data based on the operator provided
     *
     * @param string $operator Operation for comparison
     * @param string $data Data to compare
     * @param string $matchValue Data to compare against [optional]
     * @return boolean Pass/fail of evaluation
     */
    public function evaluate($operator, $data, $matchValue = null)
    {
        $valid = false;

        switch($operator) {
            case '==':
                $valid = ($data == $matchValue);
                break;
            case 'isset':
                $valid = ($data !== null);
                break;
        }
        return $valid;
    }
}
