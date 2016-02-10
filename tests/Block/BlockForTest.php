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
use SalesforceEng\Breakout\Block\BlockFor;

class BlockForTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the usage of the "for" block and replacing a variable inside the loop
     */
    public function testForBlockWithVarReplace()
    {
        // Use the tokenizer to build the tokens
        $t = new Tokenizer();
        $document = 'this is a {% for item in items %} name: {{ item.name }} {% endfor %} here';
        $tokens = $t->execute($document);

        $data = [
            'items' => [
                (object)['name' => 'test1'],
                (object)['name' => 'test2'],
            ]
        ];
        $block = new BlockFor($data, $tokens[0], new Document($document));

        $result = $block->execute();
        $this->assertEquals('this is a  name: test1  name: test2  here', $result);
    }

    /**
     * Test the "for" block where the source variable isn't found
     *
     * @expectedException \Exception
     */
    public function testForBlockWithBadReplace()
    {
        // Use the tokenizer to build the tokens
        $t = new Tokenizer();
        $document = 'this is a {% for item in badvar %} bad varname {% endfor %} here';
        $tokens = $t->execute($document);

        $data = [];
        $block = new BlockFor($data, $tokens[0], new Document($document));
        $result = $block->execute();
    }
}
