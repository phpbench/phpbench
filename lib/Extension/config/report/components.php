<?php

return [
    'bar_chart_time' => [
        'title' => 'Average iteration times by variant',
        'component' => 'bar_chart_aggregate',
        "x_partition" => ["subject_name", "variant_name"],
        "bar_partition" => ["suite_tag"],
        "y_expr" => "mode(partition[\"result_time_avg\"])",
        "y_error_margin" => "stdev(partition[\"result_time_avg\"])",
        "y_axes_label" => "yValue as time precision 1"
    ]
];
