<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use ArrayObject;
use Laminas\View\Exception;

use function stristr;
use function strpos;

/**
 * Helper for setting and retrieving the doctype
 *
 * @final
 */
class Doctype extends AbstractHelper
{
    /**#@+
     * DocType constants
     */
    public const XHTML11             = 'XHTML11';
    public const XHTML1_STRICT       = 'XHTML1_STRICT';
    public const XHTML1_TRANSITIONAL = 'XHTML1_TRANSITIONAL';
    public const XHTML1_FRAMESET     = 'XHTML1_FRAMESET';
    public const XHTML1_RDFA         = 'XHTML1_RDFA';
    public const XHTML1_RDFA11       = 'XHTML1_RDFA11';
    public const XHTML_BASIC1        = 'XHTML_BASIC1';
    public const XHTML5              = 'XHTML5';
    public const HTML4_STRICT        = 'HTML4_STRICT';
    public const HTML4_LOOSE         = 'HTML4_LOOSE';
    public const HTML4_FRAMESET      = 'HTML4_FRAMESET';
    public const HTML5               = 'HTML5';
    public const CUSTOM_XHTML        = 'CUSTOM_XHTML';
    public const CUSTOM              = 'CUSTOM';
    /**#@-*/

    /**
     * Default DocType
     *
     * @var string
     */
    protected $defaultDoctype = self::HTML4_LOOSE;

    /**
     * Registry containing current doctype and mappings
     *
     * @var ArrayObject
     */
    protected $registry;

    /** @var ArrayObject Shared doctypes to use throughout all instances */
    protected static $registeredDoctypes;

    /**
     * Constructor
     *
     * Map constants to doctype strings, and set default doctype
     */
    public function __construct()
    {
        if (null === static::$registeredDoctypes) {
            static::registerDefaultDoctypes();
            $this->setDoctype($this->defaultDoctype);
        }
        $this->registry = static::$registeredDoctypes;
    }

    /**
     * Set or retrieve doctype
     *
     * @param  string $doctype
     * @throws Exception\DomainException
     * @return Doctype
     */
    public function __invoke($doctype = null)
    {
        if (null !== $doctype) {
            switch ($doctype) {
                case self::XHTML11:
                case self::XHTML1_STRICT:
                case self::XHTML1_TRANSITIONAL:
                case self::XHTML1_FRAMESET:
                case self::XHTML_BASIC1:
                case self::XHTML1_RDFA:
                case self::XHTML1_RDFA11:
                case self::XHTML5:
                case self::HTML4_STRICT:
                case self::HTML4_LOOSE:
                case self::HTML4_FRAMESET:
                case self::HTML5:
                    $this->setDoctype($doctype);
                    break;
                default:
                    if (0 !== strpos($doctype, '<!DOCTYPE')) {
                        throw new Exception\DomainException('The specified doctype is malformed');
                    }
                    if (stristr($doctype, 'xhtml') !== false) {
                        $type = self::CUSTOM_XHTML;
                    } else {
                        $type = self::CUSTOM;
                    }
                    $this->setDoctype($type);
                    $this->registry['doctypes'][$type] = $doctype;
                    break;
            }
        }

        return $this;
    }

    /**
     * String representation of doctype
     *
     * @return string
     */
    public function __toString()
    {
        $doctypes = $this->getDoctypes();

        return $doctypes[$this->getDoctype()];
    }

    /**
     * Register the default doctypes we understand
     *
     * @return void
     */
    protected static function registerDefaultDoctypes()
    {
        // @codingStandardsIgnoreStart
        static::$registeredDoctypes = new ArrayObject([
            'doctypes' => [
                self::XHTML11             => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
                self::XHTML1_STRICT       => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
                self::XHTML1_TRANSITIONAL => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
                self::XHTML1_FRAMESET     => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
                self::XHTML1_RDFA         => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">',
                self::XHTML1_RDFA11       => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.1//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-2.dtd">',
                self::XHTML_BASIC1        => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">',
                self::XHTML5              => '<!DOCTYPE html>',
                self::HTML4_STRICT        => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
                self::HTML4_LOOSE         => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
                self::HTML4_FRAMESET      => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
                self::HTML5               => '<!DOCTYPE html>',
            ],
        ]);
        // @codingStandardsIgnoreEnd
    }

    /**
     * Unset the static doctype registry
     *
     * Mainly useful for testing purposes. Sets {@link $registeredDoctypes} to
     * a null value.
     *
     * @return void
     */
    public static function unsetDoctypeRegistry()
    {
        static::$registeredDoctypes = null;
    }

    /**
     * Set doctype
     *
     * @param  string $doctype
     * @return Doctype
     */
    public function setDoctype($doctype)
    {
        $this->registry['doctype'] = $doctype;
        return $this;
    }

    /**
     * Retrieve doctype
     *
     * @return string
     */
    public function getDoctype()
    {
        if (! isset($this->registry['doctype'])) {
            $this->setDoctype($this->defaultDoctype);
        }

        return $this->registry['doctype'];
    }

    /**
     * Get doctype => string mappings
     *
     * @return array
     */
    public function getDoctypes()
    {
        return $this->registry['doctypes'];
    }

    /**
     * Is doctype XHTML?
     *
     * @return bool
     */
    public function isXhtml()
    {
        return (bool) stristr($this->getDoctype(), 'xhtml');
    }

    /**
     * Is doctype HTML5? (HeadMeta uses this for validation)
     *
     * @return bool
     */
    public function isHtml5()
    {
        return (bool) stristr((string) $this->__invoke(), '<!DOCTYPE html>');
    }

    /**
     * Is doctype RDFa?
     *
     * @return bool
     */
    public function isRdfa()
    {
        return $this->isHtml5() || stristr($this->getDoctype(), 'rdfa') !== false;
    }
}
