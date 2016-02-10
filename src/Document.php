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

class Document
{
    /**
     * Current document content
     * @var string
     */
    private $document = '';

    /**
     * Current document offset (for multple tag replacement)
     * @var integer
     */
    private $offset = 0;

    /**
     * Init the object and assign the document content
     *
     * @param string $document Document content
     */
    public function __construct($document)
    {
        $this->document = $document;
    }

    /**
     * Get the current document contents
     *
     * @return string Current document
     */
    public function getContents()
    {
        return $this->document;
    }

    /**
     * Reset the offset on the document specifically
     */
    public function resetOffset()
    {
        $this->offset = 0;
    }

    /**
     * Get the current offset value
     *
     * @return integer Offset value
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Set the current offset value
     *
     * @param integer $offset Offset value
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * Get the data between the end of the first token and the
     *     start of the second token in the current document
     *
     * @param \SalesforceEng\Breakout\Token $token1 Start token instance
     * @param \SalesforceEng\Breakout\Token $token2 End token instance
     * @return string Contents between the two tokens
     */
    public function between($token1, $token2)
    {
        $currentOffset = $this->getOffset();
        $start = $token1->getEnd() + $currentOffset;
        $end = $token2->getStart() + $currentOffset;

        return substr($this->document, $start, $end - $start);
    }

    /**
     * Replace the token with the provided content
     *     Updates the document (self) in place, no return value
     *
     * @param \SalesforceEng\Breakout\Token $token Token instance
     * @param string $content Content used for replacement
     */
    public function replace($token, $content)
    {
        // get the length of the tag to replace
        $tagLength = strlen($token->getFull());
        $contentLength = strlen($content);
        $currentOffset = $this->getOffset();

        // Get the portions before and after the tag
        $start = substr($this->document, 0, $token->getStart() + $currentOffset);
        $end = substr($this->document, $token->getEnd() + $currentOffset);

        // ...and merge
        $updatedDocument = $start.$content.$end;

        $this->setOffset(strlen($updatedDocument) - strlen($this->document) + $currentOffset);
        $this->document = $updatedDocument;
    }

    /**
     * When transformed into a string, just return the current document
     *
     * @return string Current document
     */
    public function __toString()
    {
        return $this->document;
    }
}
