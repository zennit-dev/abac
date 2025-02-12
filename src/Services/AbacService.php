<?php

namespace zennit\ABAC\Services;

use zennit\ABAC\Contracts\AbacManager;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AccessResult;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Logging\AuditLogger;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacPolicy;
use zennit\ABAC\Services\Evaluators\AbacChainEvaluator;
use zennit\ABAC\Traits\AccessesAbacConfiguration;
use zennit\ABAC\Validators\AccessContextValidator;

readonly class AbacService implements AbacManager
{
    use AccessesAbacConfiguration;

    public function __construct(
        private AbacCacheManager $cache,
        private AbacAttributeLoader $attributeLoader,
        private AbacChainEvaluator $evaluator,
        private AuditLogger $logger,
        private AbacPerformanceMonitor $monitor
    ) {}

    /**
     * @throws ValidationException
     */
    public function can(AccessContext $context): bool
    {
        return $this->evaluate($context)->can;
    }

    /**
     * @throws ValidationException
     */
    public function evaluate(AccessContext $context): AccessResult
    {
        if ($this->getStrictValidation()) {
            $this->validateContext($context);
        }

        $subject_class_string = get_class($context->subject->getModel());

        $policy = AbacPolicy::where('method', $context->method)
            ->where('resource', $subject_class_string)
            ->first();

        if (!$policy) {
            return new AccessResult($context->subject, 'No policy provided, full access granted.', $context);
        }

        $chain = AbacChain::wherePolicyId($policy->id)->first();
        $query = $this->evaluator->evaluate($context->subject, $chain, $context);

        return new AccessResult($query, null, $context);
    }

    /**
     * Validate the access context.
     *
     * @param  AccessContext  $context  The context to validate
     *
     * @throws ValidationException If the context is invalid
     */
    private function validateContext(AccessContext $context): void
    {
        app(AccessContextValidator::class)->validate($context);
    }
}
