<?php

declare(strict_types=1);

/**
 * Ensure that a single callable(K): bool parameter (non-union) still validates
 * K's constraint. This guards against over-suppression introduced by the
 * callable-union fix (see array_filter_mixed_input.php).
 *
 * Passing fn(array $x) where K is constrained to array-key is a genuine error
 * and must not be silently swallowed.
 */

/**
 * @template K as array-key
 * @template V
 * @param array<K, V>       $array
 * @param callable(K): bool $callback
 * @return array<K, V>
 */
function filter_by_key(array $array, callable $callback): array
{
    return array_filter($array, $callback, ARRAY_FILTER_USE_KEY);
}

/** @var array<string, int> $data */
$data = ['a' => 1, 'b' => 2];

// Correct: string satisfies array-key — no error expected.
$result = filter_by_key($data, fn(string $k) => $k !== 'a');

// Wrong: array is not array-key — must still produce an argument error
// (reported as invalid-argument via verify_argument_type, not template-constraint-violation,
// because the callable argument itself is validated after template expansion).
// @mago-expect analysis:invalid-argument
$result2 = filter_by_key($data, fn(array $k) => true);
