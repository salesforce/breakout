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

class Js extends \SalesforceEng\Breakout\Context
{
    /**
     * Escape data for use in Javascript strings
     *
     * @param string $data Data to escape
     * @throws \InvalidArgumentException If data is not a string
     * @return string Escaped data
     */
    public function escape($data)
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException("Data must be a string in the Javascript context");
        }

        $data = $this->convertEncoding($data);
        $data = preg_replace_callback('#[^\p{L}\p{N} ]#u', function($matches) {
            $char = $matches[0];

            // \xHH
            if (!isset($char[1])) {
                return '\\x'.substr('00'.bin2hex($char), -2);
            }

            // \uHHHH
            $char = $this->convertEncoding($char, 'UTF-16BE', 'UTF-8');

            return '\\u'.substr('0000'.bin2hex($char), -4);
        }, $data);

        return $data;
    }
}