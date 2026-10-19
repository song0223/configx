<?php

namespace Ichynul\Configx\Field;

use Encore\Admin\Form\Field;

class Outer extends Field
{
    /**
     * Raw html to render. Declared explicitly to avoid dynamic property
     * deprecations on PHP >= 8.2.
     *
     * @var string
     */
    protected $html = '';

    /**
     * Create a new Outer instance.
     *
     * @param mixed $text
     * @param array $arguments
     */
    public function __construct($html, $arguments = [])
    {
        $this->html = $html;
    }

    public function render()
    {
        return $this->html;
    }
}
