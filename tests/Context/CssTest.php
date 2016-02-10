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

class CssTest extends \PHPUnit_Framework_TestCase
{
    private $context;

    public function setUp()
    {
        $this->context = new Css();
    }
    public function tearDown()
    {
        unset($this->context);
    }

    /**
     * Test the valid output for the escape funtion on CSS data
     */
    public function testValidOutput()
    {
        $css = 'font-size:100px;color:red;font-weight:bold';

        $this->assertEquals(
            'font&#45;size&#58;100px&#59;color&#58;red&#59;font&#45;weight&#58;bold',
            $this->context->escape($css)
        );
    }

    /**
     * Test that newlines and tabs are left intact in the resulting output
     */
    public function testValidOutputNewlineTab()
    {
        $css = "\tfont-size:100px;color:red;\nfont-weight:bold";

        $this->assertEquals(
            "\tfont&#45;size&#58;100px&#59;color&#58;red&#59;\nfont&#45;weight&#58;bold",
            $this->context->escape($css)
        );
    }

    /**
     * Test that values outside the normal ASCII character set are
     *     escaped correctly too
     */
    public function testValidOutputOtherChars()
    {
        $other = '☃';
        $css = 'font-size:100px;'.$other;

        $this->assertEquals(
            'font&#45;size&#58;100px&#59;&#226;&#152;&#131;',
            $this->context->escape($css)
        );
    }

    /**
     * Test the removal of comments in the CSS string
     */
    public function testValidOutputWithComments()
    {
        $css = 'font-size:10px/* this has comments */color:#000000';
        $this->assertEquals(
            'font&#45;size&#58;10pxcolor&#58;&#35;000000',
            $this->context->escape($css)
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

    public function testGreater265()
    {
        $css = 'this has a char '.chr(914).' in it';

        $this->assertEquals(
            'this&#32;has&#32;a&#32;char&#32;&#146;&#32;in&#32;it',
            $this->context->escape($css)
        );
    }
}