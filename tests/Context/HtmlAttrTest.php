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

namespace SalesforceEng\Breakout\Context;

class HtmlAttrTest extends \PHPUnit_Framework_TestCase
{
    private $context;

    public function setUp()
    {
        $this->context = new HtmlAttr();
    }
    public function tearDown()
    {
        unset($this->context);
    }

    /**
     * Test the valid output for the escape funtion on HTML data
     *     (encode the tags rather then removing them)
     */
    public function testValidOutput()
    {
        $htmlAttr = 'name="f\'test\'oo" onclick="error"';

        $this->assertEquals(
            'name="f&quot;test&quot;oo" onclick="error"',
            $this->context->escape($htmlAttr)
        );
    }

    /**
     * Test that an exception is thrown when a non-string value is
     *     provided for escaping
     *
     * @expectedException \InvalidArgumentException
     */
    public function testNonStringInput()
    {
        $object = new \stdClass();
        $this->context->escape($object);
    }

    /**
     * Test for escaping when the string has no attributes (=) in it,
     *     just a normal string
     */
    public function testStringWithNoAttributes()
    {
        $htmlAttr = 'this is "a test" for escaping';

        $this->assertEquals(
            'this is &quot;a test&quot; for escaping',
            $this->context->escape($htmlAttr)
        );
    }
}
