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

class BreakoutTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        // Reset the delimiters to default values
        Breakout::setDelimiters(['{{', '}}']);
    }
    /**
     * Test the setting of the delimiters as an array
     */
    public function testGetSetDelimitersArray()
    {
        $delimiters = ['%%', '||'];
        Breakout::setDelimiters($delimiters);

        $this->assertEquals($delimiters, Breakout::getDelimiters());
    }

    /**
     * Test the setting of delimiters as a single string, not an array
     */
    public function testGetSetDelimitersString()
    {
        $delimiter = '%%';
        Breakout::setDelimiters($delimiter);

        $this->assertEquals(
            [$delimiter, $delimiter],
            Breakout::getDelimiters()
        );
    }

    /**
     * Test a basic string replace
     */
    public function testRenderDocumentStringReplace()
    {
        $data = ['foo' => 'bar'];
        $document = 'this is a {{foo}} test.';
        $result = Breakout::render($document, $data);

        $this->assertEquals('this is a bar test.', $result);
    }

    /**
     * Test the replacement of document data when the delimiters are
     *     specificed on the render call
     */
    public function testRenderDocumentReplaceDelimiters()
    {
        $data = ['foo' => 'bar'];
        $document = 'this is a %%foo%% test.';
        $result = Breakout::render($document, $data, '%%');

        $this->assertEquals('this is a bar test.', $result);
    }

    /**
     * Test the replacement of strings in a document using the escaping
     */
    public function testRenderDocumentStringReplaceEscape()
    {
        $data = array(
            'html1' => '<b>testing</b>',
            'js1' => 'te"this"sting',
            'css1' => 'font-size:100px;color:red;font-weight:bold'
        );
        $document = "this is a test of the document rendering.\n{{ js1|js }} and\n {{ css1|css }} finally\n {{ html1 }}";
        $result = Breakout::render($document, $data);

        $this->assertEquals(
            'this is a test of the document rendering.
te\\x22this\\x22sting and
 font&#45;size&#58;100px&#59;color&#58;red&#59;font&#45;weight&#58;bold finally
 &lt;b&gt;testing&lt;/b&gt;',
            $result->getContents()
        );
    }

    /**
     * Test that the same document is returned when there's no tags to match
     */
    public function testRenderDocumentNoTags()
    {
        $document = 'this text contains no tags';
        $this->assertEquals(
            $document,
            Breakout::render($document, [])
        );
    }

    /**
     * Test that the tag is completely removed when a matching value cannot be found
     */
    public function testRenderDocumentMissingInfo()
    {
        $document = "this is a {{test}} but {{this}} won't be replaced";
        $data = ['test' => 'foobar'];

        $result = Breakout::render($document, $data);
        $this->assertEquals(
            "this is a foobar but  won't be replaced",
            $result
        );
    }

    /**
     * Non-recursive rendering.
     */
    public function testNonRecursive()
    {
        $document = '{{x}} o {{x}}';
        $data = ['x' => str_repeat('{{x}}',5000) ];

        $result = Breakout::render($document, $data);
        $this->assertEquals(
            $data['x'] . ' o ' . $data['x'],
            $result
        );
    }

    /**
     * Test the document rendering with an object included
     */
    public function testRenderDocumentWithObject()
    {
        $object = new \stdClass();
        $object->test = 'foobar';
        $document = 'this will replace this value from an object: {{obj1.test}} and string "{{string1}}"';
        $data = [
            'obj1' => $object,
            'string1' => 'this is a string'
        ];

        $result = Breakout::render($document, $data);
        $this->assertEquals(
            'this will replace this value from an object: foobar and string "this is a string"',
            $result
        );
    }

    public function testReplaceDataComplex()
    {
        $data = [
            'this' => "for is 'a test' here",
            'users' => [
                [
                    'username' => 'test1fdsafdsafdsafsdafdsafdsa',
                    'name' => 'Test User #1',
                    'perms' => [ ['name' => 'test2', 'value' => 'here2'] ]
                ],
                [
                    'username' => 'foobarbaz',
                    'name' => 'Test User #2',
                    'perms' => [ ['name' => 'test', 'value' => 'here1'], ['name' => 'name1', 'value' => 'thisvalue'] ]
                ]
            ]
        ];
        $document = <<<TEMPLATE
{% if users %}we have users!{% endif %}
{{ this }}

{% for user in users %}
    <tr>
        <td>{{ user.username }}| -> {{ user.name }}
        {% if user.username %} foobar {% endif %}</td>
        <td>
        <ul>{% for perm in user.perms %}
        <li>{{ perm.name }} -> {{ perm.value }}</li>{% endfor %}
        </td>
        <td>{% if user.username %} baz! {% endif %}</td>
    </tr>
{% endfor %}
and then {{ this }}

{% if this %}testing123{% endif %}
TEMPLATE;

        $matchOutput = <<<MATCH
we have users!
for is &#39;a test&#39; here


    <tr>
        <td>test1fdsafdsafdsafsdafdsafdsa| -> Test User #1
         foobar </td>
        <td>
        <ul>
        <li>test2 -> here2</li>
        </td>
        <td> baz! </td>
    </tr>

    <tr>
        <td>foobarbaz| -> Test User #2
         foobar </td>
        <td>
        <ul>
        <li>test -> here1</li>
        <li>name1 -> thisvalue</li>
        </td>
        <td> baz! </td>
    </tr>

and then for is &#39;a test&#39; here

testing123
MATCH;

        $result = Breakout::render($document, $data);
        $contents = $result->getContents();
        $this->assertEquals($matchOutput, $contents);
    }

    /**
     * Escape a string with the default method (HTML)
     */
    public function testEscapeStringDataDefaultContext()
    {
        $data = "this is some <b>sample</b> data";

        $b = new Breakout();
        $this->assertEquals(
            'this is some &lt;b&gt;sample&lt;/b&gt; data',
            $b->escape($data)
        );
    }

    /**
     * Test the escaping (and direct return) of empty strings and nulls
     */
    public function testEscapeNullEmptyDataDefaultContext()
    {
        $b = new Breakout();

        $this->assertEquals(null, $b->escape(null));
        $this->assertEquals('', $b->escape(''));
    }

    /**
     * Test the escaping when given an array or object (direct return)
     */
    public function testEscapeArrayObjectDefaultContext()
    {
        $b = new Breakout();

        $arr = ['test' => 'foo'];
        $this->assertEquals($arr, $b->escape($arr));

        $obj = new \stdClass();
        $obj->test = 'foo';
        $this->assertEquals($obj, $b->escape($obj));
    }

    /**
     * Test that an exception is thrown with a bod context is provided
     *
     * @expectedException \InvalidArgumentException
     */
    public function testEscapeInvalidContext()
    {
        $b = new Breakout();
        $b->escape('string', 'badcontext');
    }
}

// For testing the object handling
class SampleObject
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
    public function test()
    {
        return $this->value;
    }
    public function __toString()
    {
        return 'to string: '.$this->value;
    }
}