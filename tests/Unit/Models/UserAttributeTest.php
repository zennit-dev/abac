<?php

namespace zennit\ABAC\Tests\Unit\Models;

use zennit\ABAC\Models\UserAttribute;
use zennit\ABAC\Tests\TestCase;

class UserAttributeTest extends TestCase
{
    public function test_subject_relation()
    {
        $attr = new UserAttribute;
        $subject = $attr->subject();
        $this->assertNotNull($subject);
    }

    public function test_get_morph_methods()
    {
        $attr = new UserAttribute;
        $this->assertNotNull($attr->getMorphClass());
        $this->assertNotNull($attr->getKeyName());
    }
}
