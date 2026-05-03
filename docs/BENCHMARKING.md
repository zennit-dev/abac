# Benchmarking

This repository includes phpbench profiles for common ABAC policy shapes and for degraded latency conditions.

## Available benchmark scenarios

- Flat AND chain with multiple resource checks.
- Nested OR chain with two child branches.
- Mixed resource + actor checks.

Bench sources:

- `benchmarks/AbacEvaluationBench.php` for normal in-process evaluation.
- `benchmarks/AbacLatencyEvaluationBench.php` for latency-simulated evaluation.

## Run benchmarks

From repository root:

```bash
composer bench
```

Quick local feedback:

```bash
composer bench:quick
```

Latency-simulated runs:

```bash
composer bench-latency
```

Quick latency feedback:

```bash
composer bench-latency:quick
```

## Notes

- Benchmarks use in-memory SQLite for repeatable local runs.
- The normal suite measures package overhead and stays in the low-ms range.
- The latency suite adds 100 ms of synthetic delay per evaluation to model bad conditions.
- Results are best used for relative comparisons between changes.
- For release comparisons, run on a quiet machine with consistent CPU settings.
