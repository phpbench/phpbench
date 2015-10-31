PHPBench Benchmark Results
==========================

benchmark | subject | group | params | revs | iter | time | memory | deviation
 --- | --- | --- | --- | --- | --- | --- | --- | --- 
BenchmarkBench | benchRandom |  | [] | 1 | 0 | 19,253.0000μs | 288b | +2,145.65%
BenchmarkBench | benchDoNothing |  | [] | 1 | 0 | 12.0000μs | 192b | -98.6
BenchmarkBench | benchDoNothing |  | [] | 1 | 1 | 8.0000μs | 384b | -99.07
BenchmarkBench | benchDoNothing |  | [] | 1 | 2 | 8.0000μs | 576b | -99.07
BenchmarkBench | benchParameterized |  | [] | 1 | 2 | 9.0000μs | 192b | -98.95
BenchmarkBench | benchParameterized |  | [] | 1 | 2 | 8.0000μs | 384b | -99.07
BenchmarkBench | benchParameterized |  | [] | 1 | 2 | 8.0000μs | 576b | -99.07
BenchmarkBench | benchParameterized |  | [] | 1 | 2 | 8.0000μs | 768b | -99.07
IsolatedBench | benchIterationIsolation |  | [] | 1 | 0 | 91.0000μs | 248b | -89.39
IsolatedBench | benchIterationIsolation |  | [] | 1 | 1 | 37.0000μs | 440b | -95.68
IsolatedBench | benchIterationIsolation |  | [] | 1 | 2 | 31.0000μs | 632b | -96.38
IsolatedBench | benchIterationIsolation |  | [] | 1 | 3 | 29.0000μs | 824b | -96.62
IsolatedBench | benchIterationIsolation |  | [] | 1 | 4 | 29.0000μs | 1,016b | -96.62
IsolatedBench | benchIterationsIsolation |  | [] | 1 | 0 | 34.0000μs | 248b | -96.03
IsolatedBench | benchIterationsIsolation |  | [] | 1 | 1 | 30.0000μs | 440b | -96.5
IsolatedBench | benchIterationsIsolation |  | [] | 1 | 2 | 29.0000μs | 632b | -96.62
IsolatedBench | benchIterationsIsolation |  | [] | 1 | 3 | 28.0000μs | 824b | -96.73
IsolatedBench | benchIterationsIsolation |  | [] | 1 | 4 | 29.0000μs | 1,016b | -96.62
IsolatedParameterBench | benchIterationIsolation |  | [] | 1 | 2 | 10.0000μs | 256b | -98.83
IsolatedParameterBench | benchIterationIsolation |  | [] | 1 | 3 | 8.0000μs | 256b | -99.07
IsolatedParameterBench | benchIterationIsolation |  | [] | 1 | 4 | 7.0000μs | 256b | -99.18
IsolatedParameterBench | benchIterationIsolation |  | [] | 1 | 5 | 7.0000μs | 256b | -99.18
IsolatedParameterBench | benchIterationIsolation |  | [] | 1 | 6 | 6.0000μs | 256b | -99.3
 |  |  |  |  |  |  |  | 
 |  |  |  |  | stability | -320683.33% |  | 
 |  |  |  |  | average | 857.3478μs | 477b | 


