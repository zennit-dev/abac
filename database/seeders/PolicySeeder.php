<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use RuntimeException;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacCheck;
use zennit\ABAC\Models\AbacPolicy;

class PolicySeeder extends Seeder
{
    /**
     * @var array<string, int>
     */
    private array $chainKeyMap = [];

    public function run(): void
    {
        $policyPath = resource_path(config('abac.seeders.policy_file_path'));

        if (! file_exists($policyPath)) {
            $this->command->error("Permission file not found at path: $policyPath");

            return;
        }

        $payload = json_decode(file_get_contents($policyPath), true);

        if (! is_array($payload)) {
            $this->command->error('Invalid JSON structure in permission file.');

            return;
        }

        foreach ($payload['policies'] ?? [] as $policyData) {
            $this->seedPolicy($policyData);
        }

        foreach ($payload['chains'] ?? [] as $chainData) {
            $this->seedChain($chainData);
        }

        foreach ($payload['checks'] ?? [] as $checkData) {
            $this->seedCheck($checkData);
        }
    }

    private function seedPolicy(array $policyData): void
    {
        $policy = AbacPolicy::firstOrCreate([
            'resource' => $policyData['resource'],
            'method' => $policyData['method'],
        ]);

        foreach ($policyData['chains'] ?? [] as $chainData) {
            $this->seedChain([
                ...$chainData,
                'policy_id' => $policy->id,
            ]);
        }
    }

    private function seedChain(array $chainData): void
    {
        $policyId = $this->resolvePolicyId($chainData);
        $parentChainId = $this->resolveParentChainId($chainData);

        $payload = [
            'operator' => $chainData['operator'],
            'chain_id' => $parentChainId,
            'policy_id' => is_null($parentChainId) ? $policyId : null,
        ];

        $chain = AbacChain::firstOrCreate($payload);

        if (isset($chainData['chain_key'])) {
            $this->chainKeyMap[$chainData['chain_key']] = $chain->id;
        }

        foreach ($chainData['checks'] ?? [] as $checkData) {
            $this->seedCheck([
                ...$checkData,
                'chain_id' => $chain->id,
            ]);
        }

        foreach ($chainData['chains'] ?? [] as $nestedChainData) {
            $this->seedChain([
                ...$nestedChainData,
                'chain_id' => $chain->id,
                'policy_id' => null,
            ]);
        }

    }

    private function seedCheck(array $checkData): void
    {
        $chainId = $this->resolveChainId($checkData);

        AbacCheck::firstOrCreate([
            'chain_id' => $chainId,
            'operator' => $checkData['operator'],
            'key' => $checkData['key'],
            'value' => $checkData['value'],
        ]);
    }

    private function resolvePolicyId(array $data): ?int
    {
        if (! empty($data['policy_id'])) {
            return (int) $data['policy_id'];
        }

        $resource = $data['policy_resource'] ?? $data['resource'] ?? $data['policy']['resource'] ?? null;
        $method = $data['policy_method'] ?? $data['method'] ?? $data['policy']['method'] ?? null;

        if (is_null($resource) || is_null($method)) {
            return null;
        }

        $policy = AbacPolicy::firstOrCreate([
            'resource' => $resource,
            'method' => $method,
        ]);

        return $policy->id;
    }

    private function resolveParentChainId(array $data): ?int
    {
        if (! empty($data['chain_id'])) {
            return (int) $data['chain_id'];
        }

        $chainKey = $data['parent_chain_key'] ?? $data['chain_parent_key'] ?? null;
        if (is_null($chainKey)) {
            return null;
        }

        if (! isset($this->chainKeyMap[$chainKey])) {
            throw new RuntimeException("Unknown parent chain key: $chainKey");
        }

        return $this->chainKeyMap[$chainKey];
    }

    private function resolveChainId(array $data): int
    {
        if (! empty($data['chain_id'])) {
            return (int) $data['chain_id'];
        }

        $chainKey = $data['chain_key'] ?? $data['logical_chain_key'] ?? null;
        if (is_null($chainKey) || ! isset($this->chainKeyMap[$chainKey])) {
            throw new RuntimeException('Check entry is missing chain_id or known chain_key.');
        }

        return $this->chainKeyMap[$chainKey];
    }
}
