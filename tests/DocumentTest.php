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

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    private $document;
    private $string = 'this is a test';

    public function setUp()
    {
        $this->document = new Document($this->string);
    }
    public function tearDown()
    {
        unset($this->document);
    }

    /**
     * Test that the current contents of the document match as expected
     */
    public function testContentsMatch()
    {
        $this->assertEquals(
            $this->string,
            $this->document->getContents()
        );
    }

    /**
     * Test the getting/setting of the offset
     */
    public function testGetSetOffset()
    {
        $offset = 42;

        $this->document->setOffset($offset);
        $this->assertEquals(
            $offset,
            $this->document->getOffset()
        );
    }

    /**
     * Test the resetting of the offset back to the beginning
     *     of the document
     */
    public function testResetOffset()
    {
        $this->document->setOffset(42);
        $this->document->resetOffset();

        $this->assertEquals(0, $this->document->getOffset());
    }

    /**
     * Evaluate the replacement of a tag in the current document
     */
    public function testReplaceValidTag()
    {
        $text = 'this is a {{ foo }} test';
        $content = 'user1';
        $offset = 10;

        $tag = new Token([
            'type' => 'variable',
            'start' => 10,
            'content' => ' foo ',
            'full' => '{{ foo }}',
            'end' => 19,
            'token' => 'foo'
        ]);

        $doc = new Document($text);
        $doc->replace($tag, $content, $offset);

        $this->assertEquals('this is a user1 test', $doc);
    }

    /**
     * Test that the offset is correct when a tag is replaced
     */
    public function testReplaceCheckOffset()
    {
        $content = 'user1';
        $tag = new Token([
            'type' => 'variable',
            'start' => 10,
            'content' => ' foo ',
            'full' => '{{ foo }}',
            'end' => 19,
            'token' => 'foo'
        ]);

        $doc = new Document('this is a {{ foo }} test');
        $doc->replace($tag, $content, 10);

        $this->assertEquals(-4, $doc->getOffset());
    }

    /**
     * Test the conversion of the document to a string
     */
    public function testToStringDocument()
    {
        $this->assertEquals($this->string, $this->document);
    }
}
