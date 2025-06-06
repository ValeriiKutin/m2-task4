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
 */

namespace PDepend\Source\AST;

use Stringable;

/**
 * This code class represents a class property.
 *
 * @copyright 2008-2017 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ASTProperty extends AbstractASTArtifact implements Stringable
{
    /** The parent type object. */
    private ASTClass $declaringClass;

    /**
     * Constructs a new item for the given field declaration and variable
     * declarator.
     *
     * @param ASTFieldDeclaration $fieldDeclaration The context field declaration where this property was declared in
     *                                              the source.
     * @param ASTVariableDeclarator $variableDeclarator The context variable declarator for this property instance.
     */
    public function __construct(
        private readonly ASTFieldDeclaration $fieldDeclaration,
        private readonly ASTVariableDeclarator $variableDeclarator,
    ) {
        $this->id = spl_object_hash($this);
    }

    /**
     * This method returns a string representation of this parameter.
     *
     * @since  0.9.6
     */
    public function __toString(): string
    {
        $static = '';

        if ($this->isStatic()) {
            $static = ' static';
        }

        $visibility = ' public';
        if ($this->isProtected()) {
            $visibility = ' protected';
        } elseif ($this->isPrivate()) {
            $visibility = ' private';
        }

        return sprintf(
            'Property [%s%s %s ]%s',
            $visibility,
            $static,
            $this->getImage(),
            PHP_EOL,
        );
    }

    /**
     * Returns the item name.
     */
    public function getImage(): string
    {
        return $this->variableDeclarator->getImage();
    }

    /**
     * This method returns a OR combined integer of the declared modifiers for
     * this property.
     *
     * @since  0.9.6
     */
    public function getModifiers(): int
    {
        return $this->fieldDeclaration->getModifiers();
    }

    /**
     * Returns <b>true</b> if this node is marked as public, otherwise the
     * returned value will be <b>false</b>.
     */
    public function isPublic(): bool
    {
        return $this->fieldDeclaration->isPublic();
    }

    /**
     * Returns <b>true</b> if this node is marked as protected, otherwise the
     * returned value will be <b>false</b>.
     */
    public function isProtected(): bool
    {
        return $this->fieldDeclaration->isProtected();
    }

    /**
     * Returns <b>true</b> if this node is marked as private, otherwise the
     * returned value will be <b>false</b>.
     */
    public function isPrivate(): bool
    {
        return $this->fieldDeclaration->isPrivate();
    }

    /**
     * Returns <b>true</b> when this node is declared as static, otherwise
     * the returned value will be <b>false</b>.
     */
    public function isStatic(): bool
    {
        return $this->fieldDeclaration->isStatic();
    }

    /**
     * This method will return <b>true</b> when this property doc comment
     * contains an array type hint, otherwise the it will return <b>false</b>.
     *
     * @since  0.9.6
     */
    public function isArray(): bool
    {
        $typeNode = $this->fieldDeclaration->getFirstChildOfType(
            ASTType::class,
        );
        if ($typeNode === null) {
            return false;
        }

        return $typeNode->isArray();
    }

    /**
     * This method will return <b>true</b> when this property doc comment
     * contains a primitive type hint, otherwise the it will return <b>false</b>.
     *
     * @since  0.9.6
     */
    public function isScalar(): bool
    {
        $typeNode = $this->fieldDeclaration->getFirstChildOfType(
            ASTType::class,
        );
        if ($typeNode === null) {
            return false;
        }

        return $typeNode->isScalar();
    }

    /**
     * Returns the type of this property. This method will return <b>null</b>
     * for all scalar type, only class properties will have a type.
     *
     * @since  0.9.5
     */
    public function getClass(): ?AbstractASTClassOrInterface
    {
        $reference = $this->fieldDeclaration->getFirstChildOfType(
            ASTClassOrInterfaceReference::class,
        );
        if ($reference === null) {
            return null;
        }

        return $reference->getType();
    }

    /**
     * Returns the doc comment for this item or <b>null</b>.
     */
    public function getComment(): ?string
    {
        return $this->fieldDeclaration->getComment();
    }

    /**
     * Returns the line number where the property declaration can be found.
     *
     * @since  0.9.6
     */
    public function getStartLine(): int
    {
        return $this->variableDeclarator->getStartLine();
    }

    /**
     * Returns the column number where the property declaration starts.
     *
     * @since  0.9.8
     */
    public function getStartColumn(): int
    {
        return $this->variableDeclarator->getStartColumn();
    }

    /**
     * Returns the line number where the property declaration ends.
     *
     * @since  0.9.6
     */
    public function getEndLine(): int
    {
        return $this->variableDeclarator->getEndLine();
    }

    /**
     * Returns the column number where the property declaration ends.
     *
     * @since  0.9.8
     */
    public function getEndColumn(): int
    {
        return $this->variableDeclarator->getEndColumn();
    }

    /**
     * This method will return the class where this property was declared.
     *
     * @since  0.9.6
     */
    public function getDeclaringClass(): AbstractASTClassOrInterface
    {
        return $this->declaringClass;
    }

    /**
     * Sets the declaring class object.
     *
     * @since  0.9.6
     */
    public function setDeclaringClass(ASTClass $declaringClass): void
    {
        $this->declaringClass = $declaringClass;
    }

    /**
     * This method will return <b>true</b> when the parameter declaration
     * contains a default value.
     *
     * @since  0.9.6
     */
    public function isDefaultValueAvailable(): bool
    {
        $value = $this->variableDeclarator->getValue();
        if ($value === null) {
            return false;
        }

        return $value->isValueAvailable();
    }

    /**
     * This method will return the default value for this property instance or
     * <b>null</b> when this property was only declared and not initialized.
     *
     * @since  0.9.6
     */
    public function getDefaultValue(): mixed
    {
        $value = $this->variableDeclarator->getValue();
        if ($value === null) {
            return null;
        }

        return $value->getValue();
    }
}
