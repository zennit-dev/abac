<?php

namespace zennit\ABAC\Tests\Unit\Models;

use zennit\ABAC\Models\UserAttribute;
use zennit\ABAC\Tests\TestCase;

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
