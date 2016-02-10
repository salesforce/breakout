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
use \SalesforceEng\Breakout\Document;

class Parser
{
    public $container = [];
    public $document = '';
    public $data = [];
    public $contextCache = [];

    /**
     * Init the object and set up the token container and document to parse
     *
     * @param array $container Array of tokens to walk through
     * @param mixed $document Either a Document instance or just a string
     */
    public function __construct(array $container, $document)
    {
        $this->container = new \ArrayIterator($container);
        $this->document = (is_string($document)) ? new Document($document) : $document;
    }

    /**
     * Process the provided chain with the data given
     *
     * @param \SalesforceEng\Breakout\Tag $tagInstance Tag
     * @param mixed $data Data to process
     * @return mixed Data result from processing
     */
    public static function resolveValue($data, $path)
    {
        $parts = explode('.', $path);
        $chain = [];

        foreach ($parts as $part) {
            $type = (strpos($part, ')') !== false && strpos($part, '(') !== false) ? 'function' : 'other';
            $chain[] = [
                'name' => $part,
                'type' => $type
            ];
        }

        $current = null;

        if (isset($data[$path])) {
            return $data[$path];
        }

        foreach ($chain as $item) {
            if ($current === null) {
                $current = $data;
            }
            $name = $item['name'];
            $type = $item['type'];

            $currentType = gettype($current);
            if ($currentType === 'array') {
                if (isset($current[$name])) {
                    $current = $current[$name];
                } else {
                    // If we can't find the data we need, null it out
                    return null;
                }
            } elseif ($currentType === 'object') {
                $current = self::handleObject($current, $item);
            }
        }
        return $current;
    }

    /**
     * Handle an object with possible property or method calls
     *
     * @param mixed $obejct Object to operate on
     * @param array $settings Array of settings for the provided object/tag combo
     * @return string Resulting string with replaced data
     */
    public static function handleObject($object, array $settings)
    {
        $replaceWith = null;

        if ($settings['type'] === 'function') {
            $function = str_replace(['(',
             ')'], '', $settings['name']);
            if (!method_exists($object, $function)) {
                throw new \InvalidArgumentException('Function "'.$function.'" not defined on object.');
            }
            $replaceWith = $object->$function();
        } else {
            $replaceWith = $object->$settings['name'];
        }

        return $replaceWith;
    }

    /**
     * Execute the token/data parsing
     *
     * @param array $data Data to populate into the structure
     * @return \SalesforceEng\Breakout\Document instance
     */
    public function execute(array $data)
    {
        $document = $this->document;

        while($this->container->valid()) {
            $current = $this->container->current();
            $this->container->next();

            if (is_array($current)) {
                $tokenType = $current[0]->getToken();

                switch($tokenType) {
                    case 'if':
                        $ifBlock = new Block\BlockIf($data, $current, $document);
                        $document = $ifBlock->execute();
                        break;
                    case 'for':
                        $forBlock = new Block\BlockFor($data, $current, $document);
                        $document = $forBlock->execute();
                        break;
                    case 'raw':
                        $rawBlock = new Block\BlockRaw($data, $current, $document);
                        $document = $rawBlock->execute();
                }
            } else {
                $document = $this->handleVariable($data, $current, $document);
            }
        }

        return $document;
    }

    /**
     * Normalize the string to UTF-8
     *
     * @param string $data Data to normalize
     * @return string Normalized version of the data
     */
    private static function normalizeUtf8($data)
    {
        if (is_string($data)) {
            $encoding = mb_detect_encoding($data, 'auto');
            $data = iconv($encoding, 'UTF-8', $data);
        }
        return $data;
    }

    /**
     * Escape the Unicode characters (translate to entities)
     *
     * @param string $data Data to escape
     * @return string Escaped string result
     */
    private function unicodeEscape($data)
    {
        return stripslashes(trim(json_encode($data), '"'));
    }

    /**
     * Handle the replacement and escaping for a variable value
     *
     * @param array $data Data to use for the variable
     * @param \SalesforceEng\Breakout\Token $token Token instance
     * @param \SalesforceEng\Breakout\Document $document Document instance
     * @return SalesforceEng\Breakout\Document updated instance
     */
    public function handleVariable(array $data, $token, Document $document)
    {
        $key = trim($token->getContent());
        if (strpos($key, '|') !== false) {
            list($key, $context) = explode('|', $key);
        } else {
            $context = 'html';
        }
        $resolved = $this->resolveValue($data, $key);
        $content = ($resolved !== null) ? $resolved : '';

        if (!array_key_exists($context, $this->contextCache)) {
            // Escape the content according to the context
            $contextNs = '\\SalesforceEng\\Breakout\\Context\\'.ucwords(strtolower($context));
            if (!class_exists($contextNs)) {
                throw new \InvalidArgumentException('Context "'.$context.'" does not exist!');
            }
            $instance = new $contextNs();
            $this->contextCache[$context] = $instance;
        } else {
            $instance = $this->contextCache[$context];
        }

        $content = $this->normalizeUtf8($content);
        $content = $this->unicodeEscape($content);

        $document->replace($token, $instance->escape($content));
        return $document;
    }
}
