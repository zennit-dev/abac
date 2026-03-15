# Benchmarking

This repository includes a phpbench profile for common ABAC policy shapes.

## Available benchmark scenarios

- Flat AND chain with multiple resource checks.
- Nested OR chain with two child branches.
- Mixed resource + actor checks.

Bench source: `benchmarks/AbacEvaluationBench.php`.

## Run benchmarks

From repository root:

```bash
composer bench
```

Quick local feedback:

```bash
composer bench:quick
```

## Notes

- Benchmarks use in-memory SQLite for repeatable local runs.
- Results are best used for relative comparisons between changes.
- For release comparisons, run on a quiet machine with consistent CPU settings.
