<?php

declare(strict_types=1);

/**
 * Regression test for https://github.com/carthage-software/mago/issues/1722
 *
 * When array_filter is called with a mixed-typed array (e.g. from json_decode),
 * the callback's parameter type must NOT be validated against template K (array-key)
 * — it should only be matched against V (the value type). Passing mixed to the first
 * argument is a separate, expected warning; there must be no template-constraint-violation.
 */

// @mago-expect analysis:mixed-argument  — $items is mixed, not array<K, V>
// @mago-expect analysis:mixed-assignment — result of array_filter on mixed is mixed
function test_array_filter_with_mixed_input(string $json): void
{
    $items = json_decode($json, associative: true);

    // Before the fix, this produced a spurious `template-constraint-violation`:
    // "Argument type mismatch for template `K`: type `array` is not compatible
    //  with required template constraint `array-key`". That happens because the
    // callable(K): bool overload in the callback union infers K=array, violating
    // K's `as array-key` constraint. After the fix, that inference is suppressed
    // inside callable-parameter-union contexts.
    $filtered = array_filter($items, fn(array $item) => isset($item['key']));
}
