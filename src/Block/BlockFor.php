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

class BlockFor extends \SalesforceEng\Breakout\Block
{
    /**
     * Execute the block and replace the results
     *
     * @return \SalesforceEng\Breakout\Document instance
     */
    public function execute()
    {
        $tokens = $this->getTokens();
        $data = $this->getData();
        $document = $this->getDocument();

        $forBlock = array_shift($tokens);
        $last = array_pop($tokens);

        preg_match_all('/{% for (.+?) in (.+?) %}/', $forBlock->getFull(), $matches);
        $varname = $matches[2][0];
        $itemName = $matches[1][0];

        $loopData = Parser::resolveValue($data, $varname);
        if ($loopData == null) {
            throw new \Exception('Variable "'.$varname.'" not found for use in for loop!');
        }

        $between = $document->between($forBlock, $last);
        $token = new Tokenizer();
        $container = $token->execute($between);

        $content = '';
        foreach ($loopData as $var) {
            $parser = new Parser($container, $between);
            $content .= $parser->execute([$itemName => $var]);
        }

        $contentToken = new Token([
            'content' => $content,
            'start' => $forBlock->getStart(),
            'end' => $last->getEnd()
        ]);
        $document->replace($contentToken, $content);

        return $document;
    }
}