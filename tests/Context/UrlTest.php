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

class UrlTest extends \PHPUnit_Framework_TestCase
{
    private $context;

    public function setUp()
    {
        $this->context = new Url();
    }
    public function tearDown()
    {
        unset($this->context);
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
     * Test the valid output for the escape function on URL data
     *     This one uses just a string
     */
    public function testValidOutputString()
    {
        $url = 'this "is a string" that needs encoding';

        $this->assertEquals(
            'this+%22is+a+string%22+that+needs+encoding',
            $this->context->escape($url)
        );
    }

    /**
     * Test the valid output for the escape function on URL data
     *     This one uses an array of data
     */
    public function testValidOutputArray()
    {
        $url = array(
            'val1' => 'one here',
            'val2' => '"two" here'
        );

        $this->assertEquals(
            'val1=one+here&val2=%22two%22+here',
            $this->context->escape($url)
        );
    }
}