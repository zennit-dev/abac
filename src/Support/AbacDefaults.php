<?php

namespace zennit\ABAC\Support;

final class AbacDefaults
{
    public const bool CACHE_ENABLED = true;

    public const string CACHE_STORE = 'database';

    public const int CACHE_TTL = 3600;

    public const string CACHE_PREFIX = 'abac_';

    public const true CACHE_INCLUDE_CONTEXT = true;

    public const true CACHE_FLUSH_ON_WRITE = true;

    public const true LOGGING_ENABLED = true;

    public const string LOG_CHANNEL = 'stack';

    public const false DETAILED_LOGGING = false;

    public const true PERFORMANCE_LOGGING_ENABLED = true;

    public const int SLOW_EVALUATION_THRESHOLD = 100;

    public const string ACTOR_ADDITIONAL_ATTRIBUTES = 'App\\Models\\User';

    public const string PRIMARY_KEY = 'id';

    public const string FALLBACK_PRIMARY_KEY = '_id';

    public const string DEFAULT_POLICY_BEHAVIOR = 'deny';

    public const false ALLOW_IF_UNMATCHED_ROUTE = false;

    public const string ACTOR_METHOD = 'user';

    /**
     * @return array<string, bool|int|string>
     */
    public static function envVariables(): array
    {
        return [
            'ABAC_CACHE_ENABLED' => self::CACHE_ENABLED,
            'ABAC_CACHE_STORE' => self::CACHE_STORE,
            'ABAC_CACHE_TTL' => self::CACHE_TTL,
            'ABAC_CACHE_PREFIX' => self::CACHE_PREFIX,
            'ABAC_CACHE_INCLUDE_CONTEXT' => self::CACHE_INCLUDE_CONTEXT,
            'ABAC_CACHE_FLUSH_ON_WRITE' => self::CACHE_FLUSH_ON_WRITE,
            'ABAC_LOGGING_ENABLED' => self::LOGGING_ENABLED,
            'ABAC_LOG_CHANNEL' => self::LOG_CHANNEL,
            'ABAC_DETAILED_LOGGING' => self::DETAILED_LOGGING,
            'ABAC_PERFORMANCE_LOGGING_ENABLED' => self::PERFORMANCE_LOGGING_ENABLED,
            'ABAC_SLOW_EVALUATION_THRESHOLD' => self::SLOW_EVALUATION_THRESHOLD,
            'ABAC_ACTOR_ADDITIONAL_ATTRIBUTES' => self::ACTOR_ADDITIONAL_ATTRIBUTES,
            'ABAC_PRIMARY_KEY' => self::PRIMARY_KEY,
            'ABAC_FALLBACK_PRIMARY_KEY' => self::FALLBACK_PRIMARY_KEY,
            'ABAC_DEFAULT_POLICY_BEHAVIOR' => self::DEFAULT_POLICY_BEHAVIOR,
            'ABAC_ALLOW_IF_UNMATCHED_ROUTE' => self::ALLOW_IF_UNMATCHED_ROUTE,
            'ABAC_MIDDLEWARE_ACTOR_METHOD' => self::ACTOR_METHOD,
        ];
    }
}
