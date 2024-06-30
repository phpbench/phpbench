<?php

return [
    'bar_chart_time' => [
        'title' => 'Average iteration times by variant',
        'component' => 'bar_chart_aggregate',
        "x_partition" => ["subject_name", "variant_name"],
        "bar_partition" => ["suite_tag"],
        "y_expr" => "mode(partition['result_time_avg'])",
        "y_error_margin" => "stdev(partition['result_time_avg'])",
        "y_axes_label" => "yValue as time precision 1"
    ],
    'table_summary' => [
        "component" => "table_aggregate",
        "title" => "{{ first(frame.suite_tag) }}",
        "partition" => ["benchmark_name", "subject_name", "variant_name"],
        "row" => [
            "benchmark" => "first(partition['benchmark_name'])",
            "subject" => "first(partition['subject_name']) ~ ' (' ~ first(partition['variant_name']) ~ ')'",
            "memory" => "first(partition['result_mem_peak']) as memory",
            "mode" => "mode(partition['result_time_avg']) as time",
            "rstdev" => "rstdev(partition['result_time_avg'])",
            "stdev" => "stdev(partition['result_time_avg']) as time"
        ]
    ],
];
