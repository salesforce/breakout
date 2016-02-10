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

class TokenizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Parsing a document with a single token (variable)
     */
    public function testValidParsingSingleVariableToken()
    {
        $t = new Tokenizer();
        $document = 'this is a {{ test }} here';
        $tokens = $t->execute($document);

        $this->assertCount(1, $tokens);
        $this->assertInstanceOf('\SalesforceEng\Breakout\Token', $tokens[0]);
        $this->assertEquals('variable', $tokens[0]->getType());
    }

    /**
     * Test the parsing of the valid "if" block in the string
     */
    public function testValidParsingIfBlock()
    {
        $t = new Tokenizer();
        $document = 'this is a {% if foo = bar %} inside {% endif %} here';
        $tokens = $t->execute($document);

        // The result should be an array with a nested array for the "if" start/end
        $this->assertTrue(is_array($tokens) && isset($tokens[0][0]));
        $this->assertCount(2, $tokens[0]);

        // Check the types of the start/end tokens
        $this->assertEquals('block', $tokens[0][0]->getType());
        $this->assertEquals('block', $tokens[0][1]->getType());

        // Check the token values for the start/end tokens
        $this->assertEquals('if', $tokens[0][0]->getToken());
        $this->assertEquals('endif', $tokens[0][1]->getToken());
    }

    /**
     * Test the parsing of the valid "if" block in the string
     */
    public function testValidParsingRawBlock()
    {
        $t = new Tokenizer();
        $document = 'this is a {% raw %} inside {% endraw %} here';
        $tokens = $t->execute($document);

        // The result should be an array with a nested array for the "if" start/end
        $this->assertTrue(is_array($tokens) && isset($tokens[0][0]));
        $this->assertCount(2, $tokens[0]);

        // Check the types of the start/end tokens
        $this->assertEquals('block', $tokens[0][0]->getType());
        $this->assertEquals('block', $tokens[0][1]->getType());

        // Check the token values for the start/end tokens
        $this->assertEquals('raw', $tokens[0][0]->getToken());
        $this->assertEquals('endraw', $tokens[0][1]->getToken());
    }

}
