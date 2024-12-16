<?php

namespace zennit\ABAC\Tests\Unit\Models;

use zennit\ABAC\Tests\TestCase;
use zennit\ABAC\Models\UserAttribute;

class UserAttributeTest extends TestCase
{
    public function testSubjectRelation()
    {
        $attr = new UserAttribute();
        $subject = $attr->subject();
        $this->assertNotNull($subject);
    }

    public function testGetMorphMethods()
    {
        $attr = new UserAttribute();
        $this->assertNotNull(actual: $attr->getMorphType());
        $this->assertNotNull(actual: $attr->getMorphID());
    }
}
