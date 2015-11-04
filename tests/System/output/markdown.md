PHPBench Benchmark Results
==========================

benchmark | subject | group | params | revs | iter | rej | time | memory | deviation
 --- | --- | --- | --- | --- | --- | --- | --- | --- | --- 
BenchmarkBench | benchRandom |  | [] | 1 | 0 | 0 | 41,634.0000μs | 576b | 0.00%
BenchmarkBench | benchDoNothing | do_nothing | [] | 10000 | 0 | 0 | 1.7757μs | 560b | 70.67%
BenchmarkBench | benchDoNothing | do_nothing | [] | 10000 | 1 | 0 | 1.3282μs | 560b | 27.66%
BenchmarkBench | benchDoNothing | do_nothing | [] | 10000 | 2 | 0 | 1.2570μs | 560b | 20.82%
BenchmarkBench | benchDoNothing | do_nothing | [] | 10000 | 3 | 0 | 0.9412μs | 560b | 9.54%
BenchmarkBench | benchDoNothing | do_nothing | [] | 10000 | 4 | 0 | 0.8391μs | 560b | 19.35%
BenchmarkBench | benchDoNothing | do_nothing | [] | 10000 | 5 | 0 | 0.8895μs | 560b | 14.51%
BenchmarkBench | benchDoNothing | do_nothing | [] | 10000 | 6 | 0 | 0.8493μs | 560b | 18.37%
BenchmarkBench | benchDoNothing | do_nothing | [] | 10000 | 7 | 0 | 0.8270μs | 560b | 20.51%
BenchmarkBench | benchDoNothing | do_nothing | [] | 10000 | 8 | 0 | 0.8271μs | 560b | 20.50%
BenchmarkBench | benchDoNothing | do_nothing | [] | 10000 | 9 | 0 | 0.8701μs | 560b | 16.37%
BenchmarkBench | benchParameterized | parameterized | {"length":"1","strategy":"left"} | 1 | 2 | 0 | 6.0000μs | 552b | 0.00%
BenchmarkBench | benchParameterized | parameterized | {"length":"2","strategy":"left"} | 1 | 2 | 0 | 6.0000μs | 552b | 0.00%
BenchmarkBench | benchParameterized | parameterized | {"length":"1","strategy":"right"} | 1 | 2 | 0 | 6.0000μs | 552b | 0.00%
BenchmarkBench | benchParameterized | parameterized | {"length":"2","strategy":"right"} | 1 | 2 | 0 | 7.0000μs | 552b | 0.00%
IsolatedBench | benchIterationIsolation |  | [] | 1 | 0 | 0 | 45.0000μs | 896b | 2.74%
IsolatedBench | benchIterationIsolation |  | [] | 1 | 1 | 0 | 45.0000μs | 896b | 2.74%
IsolatedBench | benchIterationIsolation |  | [] | 1 | 2 | 0 | 42.0000μs | 896b | 4.11%
IsolatedBench | benchIterationIsolation |  | [] | 1 | 3 | 0 | 45.0000μs | 896b | 2.74%
IsolatedBench | benchIterationIsolation |  | [] | 1 | 4 | 0 | 42.0000μs | 896b | 4.11%
IsolatedBench | benchIterationsIsolation |  | [] | 1 | 0 | 0 | 43.0000μs | 896b | 0.46%
IsolatedBench | benchIterationsIsolation |  | [] | 1 | 1 | 0 | 45.0000μs | 896b | 4.17%
IsolatedBench | benchIterationsIsolation |  | [] | 1 | 2 | 0 | 43.0000μs | 896b | 0.46%
IsolatedBench | benchIterationsIsolation |  | [] | 1 | 3 | 0 | 43.0000μs | 896b | 0.46%
IsolatedBench | benchIterationsIsolation |  | [] | 1 | 4 | 0 | 42.0000μs | 896b | 2.78%
IsolatedParameterBench | benchIterationIsolation | process | {"hello":"Look \"I am using double quotes\"","goodbye":"Look 'I am use $dollars\""} | 1 | 2 | 0 | 7.0000μs | 544b | 9.38%
IsolatedParameterBench | benchIterationIsolation | process | {"hello":"Look \"I am using double quotes\"","goodbye":"Look 'I am use $dollars\""} | 1 | 3 | 0 | 6.0000μs | 544b | 6.25%
IsolatedParameterBench | benchIterationIsolation | process | {"hello":"Look \"I am using double quotes\"","goodbye":"Look 'I am use $dollars\""} | 1 | 4 | 0 | 6.0000μs | 544b | 6.25%
IsolatedParameterBench | benchIterationIsolation | process | {"hello":"Look \"I am using double quotes\"","goodbye":"Look 'I am use $dollars\""} | 1 | 5 | 0 | 6.0000μs | 544b | 6.25%
IsolatedParameterBench | benchIterationIsolation | process | {"hello":"Look \"I am using double quotes\"","goodbye":"Look 'I am use $dollars\""} | 1 | 6 | 0 | 7.0000μs | 544b | 9.38%
IsolatedRevsBench | benchIterationIsolation |  | [] | 100 | 0 | 0 | 10.8500μs | 896b | 0.00%
 |  |  |  |  |  |  |  |  | 
 |  |  |  |  | stability |  | -693700.00% |  | 
 |  |  |  |  | average | 0.00 | 1,359.5888μs | 676b | 
 |  |  |  |  | sum | 0.00 | 42,147.2542μs | 20,960b | 


