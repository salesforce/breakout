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

class Token
{
    /**
     * Type of token (block or variable)
     * @var string
     */
    private $type;

    /**
     * Starting character position
     * @var integer
     */
    private $start;

    /**
     * Contents of the tag (inside the open/close tags)
     * @var string
     */
    private $content;

    /**
     * Full contents of tag (includes open/close tags)
     * @var string
     */
    private $full;

    /**
     * End character position
     * @var integer
     */
    private $end;

    /**
     * Token type (ex: endif, endfor) or variable path
     * @var string
     */
    private $token;

    /**
     * Init the token and set up the property values
     *
     * @param array $data Data to populate into the token
     */
    public function __construct(array $data)
    {
        foreach ($data as $index => $value) {
            $this->$index = $value;
        }
    }

    /**
     * Get the current type
     *
     * @return string Type value
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the current start location
     *
     * @return integer Start character position
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Get the current contents of the tag
     *
     * @return string Tag contents
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get the full content of the tag (with open/close tags)
     *
     * @return string Full tag content
     */
    public function getFull()
    {
        return $this->full;
    }

    /**
     * Get the current end character position
     *
     * @return integer End character position
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Get the current token type
     *
     * @return string Token type value
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the current token type
     *
     * @param string $token Token type value
     */
    public function setToken($token)
    {
        $this->token = $token;
    }
}