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
use SalesforceEng\Breakout\Block\BlockIf;

class BlockIfTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the evaluation of a simple "if" when the data is a match
     */
    public function testBlockIfEquals()
    {
        // Use the tokenizer to build the tokens
        $t = new Tokenizer();
        $document = 'this is a {% if foo == bar %}inside{% endif %} here';
        $tokens = $t->execute($document);

        $data = ['foo' => 'bar'];
        $block = new BlockIf($data, $tokens[0], new Document($document));

        $result = $block->execute();
        $this->assertEquals('this is a inside here', $result);
    }

    /**
     * Test the evaluation of a simple "if" when the data is NOT a match
     */
    public function testBlockIfNotEquals()
    {
        // Use the tokenizer to build the tokens
        $t = new Tokenizer();
        $document = 'this is a {% if foo == bar %}inside{% endif %} here';
        $tokens = $t->execute($document);

        $data = ['foo' => 'not-a-match'];
        $block = new BlockIf($data, $tokens[0], new Document($document));

        $result = $block->execute();
        $this->assertEquals('this is a  here', $result);
    }

    /**
     * Test the "if" block when using "isset" and the value is found
     */
    public function testBlockIfIsset()
    {
        // Use the tokenizer to build the tokens
        $t = new Tokenizer();
        $document = 'this is a {% if bar %}inside{% endif %} here';
        $tokens = $t->execute($document);

        $data = ['bar' => 'this is set!'];
        $block = new BlockIf($data, $tokens[0], new Document($document));

        $result = $block->execute();
        $this->assertEquals('this is a inside here', $result);
    }

    /**
     * Test the "if" block with an "else" included
     */
    public function testBlockIfWithElse()
    {
        // Use the tokenizer to build the tokens
        $t = new Tokenizer();
        $document = 'this is a {% if bar %}inside{% else %}in else{% endif %} here';
        $tokens = $t->execute($document);

        // Data will be found, goes into if
        $data = ['bar' => 'this is set!'];
        $block = new BlockIf($data, $tokens[0], new Document($document));
        $this->assertEquals('this is a inside here', $block->execute());

        // Data not found, goes into else
        $data = [];
        $block = new BlockIf($data, $tokens[0], new Document($document));
        $this->assertEquals('this is a in else here', $block->execute());
    }
}
