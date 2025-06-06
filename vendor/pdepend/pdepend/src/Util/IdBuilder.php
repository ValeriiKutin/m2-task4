<?php

/**
 * This file is part of PDepend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2017 Manuel Pichler <mapi@pdepend.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright 2008-2017 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.9.12
 */

namespace PDepend\Util;

use PDepend\Source\AST\AbstractASTArtifact;
use PDepend\Source\AST\AbstractASTType;
use PDepend\Source\AST\ASTCompilationUnit;
use PDepend\Source\AST\ASTFunction;
use PDepend\Source\AST\ASTMethod;

/**
 * This class provides methods to generate unique, but reproducable identifiers
 * for nodes generated during the parsing process.
 *
 * @copyright 2008-2017 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.9.12
 */
class IdBuilder
{
    /** @var array<string, array<string, int>> */
    private array $offsetInFile = [];

    /**
     * Generates an identifier for the given file instance.
     */
    public function forFile(ASTCompilationUnit $compilationUnit): string
    {
        return $this->hash($compilationUnit->getFileName() ?? 'default');
    }

    /**
     * Generates an identifier for the given function instance.
     */
    public function forFunction(ASTFunction $function): string
    {
        return $this->forOffsetItem($function, 'function');
    }

    /**
     * Generates an identifier for the given class, interface or trait instance.
     */
    public function forClassOrInterface(AbstractASTType $type): string
    {
        return $this->forOffsetItem(
            $type,
            ltrim(strrchr(strtolower($type::class), '_') ?: '', '_'),
        );
    }

    /**
     * Generates an identifier for the given source item.
     *
     * @param string $prefix The item type identifier.
     */
    protected function forOffsetItem(AbstractASTArtifact $artifact, string $prefix): string
    {
        $fileHash = $artifact->getCompilationUnit()?->getId() ?? 'default';
        $itemHash = $this->hash($prefix . ':' . strtolower($artifact->getImage()));

        $offset = $this->getOffsetInFile($fileHash, $itemHash);

        return sprintf('%s-%s-%s', $fileHash, $itemHash, $offset);
    }

    /**
     * Generates an identifier for the given method instance.
     */
    public function forMethod(ASTMethod $method): string
    {
        $hash = $this->hash(strtolower($method->getImage()));

        $parent = $method->getParent();
        if (!$parent) {
            return $hash;
        }

        return $parent->getId() . '-' . $hash;
    }

    /**
     * Creates a base 36 hash for the given string.
     *
     * @param string $string The raw input identifier/string.
     */
    protected function hash(string $string): string
    {
        return substr(base_convert(md5($string), 16, 36), 0, 11);
    }

    /**
     * Returns the node offset/occurence of the given <b>$string</b> within a
     * file.
     *
     * @param string $file The file identifier.
     * @param string $string The node identifier.
     */
    protected function getOffsetInFile(string $file, string $string): string
    {
        if (isset($this->offsetInFile[$file][$string])) {
            $this->offsetInFile[$file][$string]++;
        } else {
            $this->offsetInFile[$file][$string] = 0;
        }

        return sprintf(
            '%02s',
            base_convert((string) $this->offsetInFile[$file][$string], 10, 36),
        );
    }
}
