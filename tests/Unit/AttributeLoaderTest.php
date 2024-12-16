<?php

namespace zennit\ABAC\Tests\Unit;

use stdClass;
use zennit\ABAC\Models\UserAttribute;
use zennit\ABAC\Services\AttributeLoader;
use zennit\ABAC\Tests\TestCase;

class AttributeLoaderTest extends TestCase
{
    private AttributeLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loader = $this->app->make(AttributeLoader::class);
    }

    public function test_loads_subject_attributes(): void
    {
        $subject = new stdClass();
        $subject->id = 1;

        UserAttribute::create([
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'attribute_name' => 'role',
            'attribute_value' => 'admin',
        ]);

        $attributes = $this->loader->loadAttributes($subject, 'posts');

        $this->assertEquals('admin', $attributes->get('role'));
    }
}
