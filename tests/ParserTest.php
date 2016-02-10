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

class ParserTest extends \PHPUnit_Framework_TestCase
{
    private $parser;

    public function setUp()
    {
        $str = 'this is the document';
        $tokens = [];

        $this->parser = new Parser($tokens, $str);
    }

    public function tearDown()
    {
        unset($this->parser);
    }

    /**
     * Test that the string provided in the init is correctly
     *     converted to a Document instance
     */
    public function testInitConvertStringToDocument()
    {
        $str = 'this is the document';

        $this->assertInstanceOf('\SalesforceEng\Breakout\Document', $this->parser->document);
        $this->assertEquals($str, (string)$this->parser->document);
    }

    /**
     * Test the replacement of an object property value
     */
    public function testHandleObjectProperty()
    {
        $obj = new \stdClass();
        $obj->test = 'foobar';

        $settings = [
            'name' => 'test',
            'type' => 'other'
        ];

        $this->assertEquals(
            $obj->test,
            $this->parser->handleObject($obj, $settings)
        );
    }

    /**
     * Test the replacement of a value with a valid function from an object
     */
    public function testHandleObjectValidFunction()
    {
        $obj = new SimpleObject();
        $settings = [
            'name' => 'test1()',
            'type' => 'function'
        ];

        $this->assertEquals(
            'test1',
            $this->parser->handleObject($obj, $settings)
        );
    }

    /**
     * Test the replacement of a value with an invalid function from an object
     *
     * @expectedException \InvalidArgumentException
     */
    public function testHandleObjectInvalidFunction()
    {
        $obj = new SimpleObject();
        $settings = [
            'name' => 'testinvalid()',
            'type' => 'function'
        ];

        $this->parser->handleObject($obj, $settings);
    }

    /**
     * Test resolving the "path" with a single value
     */
    public function testResolveValuePathSingle()
    {
        $value = 'testing 1234';
        $path = 'foo';
        $data = ['foo' => $value];

        $result = $this->parser->resolveValue($data, $path);
        $this->assertEquals($value, $result);
    }

    /**
     * Test resolving the "path" with a nested value
     */
    public function testResolveValidPathNested()
    {
        $value = 'testing 1234';
        $path = 'foo.bar';
        $data = ['foo' => [
            'bar' => $value
        ]];

        $result = $this->parser->resolveValue($data, $path);
        $this->assertEquals($value, $result);
    }

    /**
     * Test the execution of the replacement with no type
     */
    public function testExecuteNoType()
    {
        $text = 'this is a {{ foo }} test';
        $content = 'user1';
        $offset = 10;

        $token = new Token([
            'type' => 'variable',
            'start' => 10,
            'content' => ' foo ',
            'full' => '{{ foo }}',
            'end' => 19,
            'token' => 'foo'
        ]);

        $doc = new Document($text);
        $parser = new Parser([$token], $doc);

        $data = ['foo' => 'barbaz'];
        $result = $parser->execute($data);

        $this->assertEquals('this is a barbaz test', $result);
    }

    /**
     * Test the execute method when an "if" block is provided
     * 	and the value is found
     */
    public function testExecuteIfBlockDataFound()
    {
        $text = "this is a {% if bar == 'this' %}here{% endif %} test";
        $tokens = [
            [
                new Token([
                    'type' => 'other',
                    'start' => 10,
                    'content' => ' bar == \'this\' ',
                    'full' => '{% if bar == \'this\' %}',
                    'end' => 32,
                    'token' => 'if'
                ]),
                new Token([
                    'type' => 'other',
                    'start' => 36,
                    'content' => ' endif ',
                    'full' => '{% endif %}',
                    'end' => 47,
                    'token' => 'endif'
                ])
            ]
        ];

        $doc = new Document($text);
        $parser = new Parser($tokens, $doc);

        $data = ['bar' => 'this'];
        $result = $parser->execute($data);

        $this->assertEquals('this is a here test', (string)$result);
    }

    /**
     * Test the execute method when an "raw" block is provided
     */
    public function testExecuteRawBlock()
    {
        $text = "this is a {% raw %} {{ test }} {% endraw %} raw block";
        $tokens = [
            [
                new Token([
                    'type' => 'other',
                    'start' => 10,
                    'content' => ' raw ',
                    'full' => '{% raw %}',
                    'end' => 19,
                    'token' => 'raw'
                ]),
                new Token([
                    'type' => 'other',
                    'start' => 31,
                    'content' => ' endraw ',
                    'full' => '{% endraw %}',
                    'end' => 43,
                    'token' => 'endraw'
                ])
            ]
        ];

        $doc = new Document($text);
        $parser = new Parser($tokens, $doc);
        $result = $parser->execute([]);

        $this->assertEquals('this is a  {{ test }}  raw block', (string)$result);
    }
}

//------------------

class SimpleObject
{
    public function test1()
    {
        return 'test1';
    }
}
