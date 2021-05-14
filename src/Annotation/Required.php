<?php

namespace Laminas\Form\Annotation;

use Laminas\Filter\Boolean as BooleanFilter;

use function is_bool;

/**
 * Required annotation
 *
 * Use this annotation to specify the value of the "required" flag for a given
 * input. Since the flag defaults to "true", this will typically be used to
 * "unset" the flag (e.g., "@Annotation\Required(false)"). Any boolean value
 * understood by \Laminas\Filter\Boolean is allowed as the content.
 *
 * @Annotation
 * @NamedArgumentConstructor
 */
class Required
{
    /**
     * @var bool
     */
    protected $required;

    /**
     * Receive and process the contents of an annotation
     *
     * @param bool|string $required
     */
    public function __construct($required = true)
    {
        if (! is_bool($required)) {
            $filter   = new BooleanFilter();
            $required = $filter->filter($required);
        }

        $this->required = $required;
    }

    /**
     * Get value of required flag
     *
     * @return bool
     */
    public function getRequired(): bool
    {
        return $this->required;
    }
}
