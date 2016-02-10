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

class Htmlattr extends \SalesforceEng\Breakout\Context
{
    /**
     * Escape the data for use in HTML attributes
     *
     * @param string $data Data to escape
     * @throws \InvalidArgumentException If data is not a string
     * @return string Escaped data
     */
    public function escape($data)
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException("Data must be a string in the HTML attribute context");
        }

        // Break it up if it's a string with attributes
        if (stripos($data, '=') !== false) {
            // Split up the values
            preg_match_all('/(.+?)[ ]*=[ ]*"(.+?)"/', $data, $matches);

            $attributes = array();
            foreach ($matches[1] as $index => $name) {
                $name = trim($name);
                $attributes[$name] = trim($matches[2][$index]);
            }

            array_walk($attributes, function(&$value) {
                $value = $this->escapeQuotes($value);
            });

            // Stick them back together
            $return = array();
            foreach ($attributes as $name => $value) {
                $return[] = $name.'="'.$value.'"';
            }
            $return = implode(' ', $return);
        } else {
            // Escaping for use inside an attribute
            $return = $this->escapeQuotes($data);
        }

        return $return;
    }

    /**
     * Escape quote characters with their entities
     *
     * @param string $data String to escape
     * @return string Escaped data string
     */
    private function escapeQuotes($data)
    {
        return str_replace(array("'", '"'), '&quot;', $data);
    }
}
