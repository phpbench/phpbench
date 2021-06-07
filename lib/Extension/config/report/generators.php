<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

return [
    'bare' => [
        'generator' => 'bare',
    ],
    'aggregate' => [
        'generator' => 'expression',
        'cols' => [
            'benchmark',
            'subject',
            'set',
            'revs',
            'its',
            'mem_peak',
            'mode',
            'rstdev',
        ]
    ],
    'default' => [
        'generator' => 'expression',
        'cols' => [
            'iter' => 'first(iteration_index)',
            'benchmark' => 'first(benchmark_name)',
            'subject' => 'first(subject_name)',
            'set' => 'first(variant_name)',
            'revs' => 'first(variant_revs)',
            'mem_peak' => 'first(result_mem_peak) as bytes',
            'time_avg' => 'display_as_time(first(result_time_avg), first(subject_time_unit))',
            'comp_z_value' => 'format("%+.2fσ", first(result_comp_z_value))',
            'comp_deviation' => 'format("%+.2f%%", first(result_comp_deviation))',
        ],
        'aggregate' => ['benchmark_class', 'subject_name', 'variant_name', 'iteration_index'],
    ],
    'memory' => [
        'generator' => 'expression',
        'aggregate' => ['benchmark_class', 'subject_name', 'variant_name', 'iteration_index'],
        'cols' => [
            'iter' => 'first(iteration_index)',
            'benchmark' => 'first(subject_name)',
            'subject' => 'first(subject_name)',
            'set' => 'first(variant_name)',
            'revs' => 'first(variant_revs)',
            'mem_final' => 'first(result_mem_final) as bytes',
            'mem_real' => 'first(result_mem_real) as bytes',
            'mem_peak' => 'first(result_mem_peak) as bytes',
        ],
    ],
    "overview" => [
            "generator" => "component",
            "components" => [
                [
                    "_type" => "report",
                    "title" => "Overview",
                    "components" => [
                        [
                            "title" => "Suites",
                            "_type" => "table_aggregate",
                            "partition" => ["suite_tag"],
                            "row" => [
                                "suite" => "first(partition[\"suite_tag\"])",
                                "date" => "first(partition[\"suite_date\"]) ~ \" \" ~ first(partition[\"suite_time\"])",
                                "php" => "first(partition[\"env_php_version\"])",
                                "vcs branch" => "first(partition[\"env_vcs_branch\"])",
                                "xdebug" => "first(partition[\"env_php_xdebug\"])",
                                "iterations" => "count(partition)",
                                "revs" => "sum(partition[\"result_time_revs\"])",
                                "mode" => "mode(partition[\"result_time_avg\"]) as time",
                                "net_time" => "sum(partition[\"result_time_net\"]) as time"
                            ]
                        ],
                        [
                            "_type" => "report",
                            "tabbed" => true,
                            "components" => [
                                [
                                    "title" => "Time",
                                    "_type" => "bar_chart_aggregate",
                                    "x_partition" => ["benchmark_name", "subject_name", "variant_name"],
                                    "set_partition" => ["suite_tag"],
                                    "y_expr" => "mode(partition[\"result_time_avg\"])",
                                    "y_error_margin" => "stdev(partition[\"result_time_avg\"])",
                                    "y_axes_label" => "yValue as time precision 1"
                                ],
                                [
                                    "title" => "Memory",
                                    "_type" => "bar_chart_aggregate",
                                    "x_partition" => ["benchmark_name", "subject_name", "variant_name"],
                                    "set_partition" => ["suite_tag"],
                                    "y_expr" => "mode(partition[\"result_mem_peak\"])",
                                    "y_error_margin" => "stdev(partition[\"result_mem_peak\"])",
                                    "y_axes_label" => "yValue as memory precision 1"
                                ],
                                [
                                    "title" => "By Subject",
                                    "_type" => "bar_chart_aggregate",
                                    "x_partition" => ["suite_tag"],
                                    "set_partition" => ["benchmark_name", "subject_name"],
                                    "y_expr" => "mode(partition[\"result_time_avg\"])",
                                    "y_error_margin" => "stdev(partition[\"result_time_avg\"])",
                                    "y_axes_label" => "yValue as time precision 1"
                                ],
                                [
                                    "_type" => "report",
                                    "title" => "Table",
                                    "partition" => ["suite_tag"],
                                    "components" => [
                                        [
                                            "_type" => "table_aggregate",
                                            "title" => "{{ first(frame.suite_tag) }}",
                                            "partition" => ["benchmark_name", "subject_name", "variant_name"],
                                            "row" => [
                                                "benchmark" => "first(partition[\"benchmark_name\"])",
                                                "subject" => "first(partition[\"subject_name\"]) ~ \" (\" ~ first(partition[\"variant_name\"]) ~ \")\"",
                                                "memory" => "first(partition[\"result_mem_peak\"]) as memory",
                                                "min" => "min(partition[\"result_time_avg\"]) as time",
                                                "max" => "max(partition[\"result_time_avg\"]) as time",
                                                "mode" => "mode(partition[\"result_time_avg\"]) as time",
                                                "rstdev" => "rstdev(partition[\"result_time_avg\"])",
                                                "stdev" => "stdev(partition[\"result_time_avg\"]) as time"
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    "_type" => "report",
                    "partition" => ["benchmark_name"],
                    "components" => [
                        [
                            "_type" => "report",
                            "title" => "{{ first(frame[\"benchmark_name\"]) }}",
                            "components" => [
                                [
                                    "_type" => "report",
                                    "tabbed" => true,
                                    "components" => [
                                        [
                                            "title" => "Time",
                                            "_type" => "bar_chart_aggregate",
                                            "x_partition" => ["subject_name", "variant_name"],
                                            "set_partition" => ["suite_tag"],
                                            "y_expr" => "mode(partition[\"result_time_avg\"])",
                                            "y_error_margin" => "stdev(partition[\"result_time_avg\"])"
                                        ],
                                        [
                                            "title" => "Memory",
                                            "_type" => "bar_chart_aggregate",
                                            "x_partition" => ["subject_name", "variant_name"],
                                            "set_partition" => ["suite_tag"],
                                            "y_expr" => "mode(partition[\"result_mem_peak\"])",
                                            "y_error_margin" => "stdev(partition[\"result_mem_peak\"])",
                                            "y_axes_label" => "yValue as memory precision 1"
                                        ]
                                    ]
                                ],
                                [
                                    "_type" => "report",
                                    "partition" => ["suite_tag"],
                                    "tabbed" => true,
                                    "components" => [
                                        [
                                            "_type" => "table_aggregate",
                                            "title" => "{{ first(frame.suite_tag) }}",
                                            "partition" => ["subject_name", "variant_name"],
                                            "row" => [
                                                "subject" => "first(partition[\"subject_name\"]) ~ \" (\" ~ first(partition[\"variant_name\"]) ~ \")\"",
                                                "memory" => "first(partition[\"result_mem_peak\"]) as memory",
                                                "mode" => "mode(partition[\"result_time_avg\"]) as time",
                                                "rstdev" => "rstdev(partition[\"result_time_avg\"])",
                                                "stdev" => "stdev(partition[\"result_time_avg\"]) as time"
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
];
