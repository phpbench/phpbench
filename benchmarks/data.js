window.BENCHMARK_DATA = {
  "lastUpdate": 1780825528883,
  "repoUrl": "https://github.com/phpbench/phpbench",
  "entries": {
    "PHPBench Performance": [
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "9c7d0df43ea9b2470dc5ab611d873f1f08143ad8",
          "message": "Add github_action_benchmark example",
          "timestamp": "2026-03-22T09:34:36Z",
          "tree_id": "0d5772728eca9e9c9afbec02df997e614c85600c",
          "url": "https://github.com/phpbench/phpbench/commit/9c7d0df43ea9b2470dc5ab611d873f1f08143ad8"
        },
        "date": 1774172132136,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ExpressionGeneratorBench::benchGenerate",
            "value": 7158.283953033134,
            "range": "± 0.54%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,0)",
            "value": 349.44814090019827,
            "range": "± 2.60%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,0)",
            "value": 351.61056751467675,
            "range": "± 1.63%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,0)",
            "value": 344.8258317025461,
            "range": "± 2.00%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,25)",
            "value": 2306.095890410927,
            "range": "± 1.04%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,25)",
            "value": 3404.856164383513,
            "range": "± 0.54%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,25)",
            "value": 5987.96771037176,
            "range": "± 0.52%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,50)",
            "value": 3769.622309197615,
            "range": "± 1.73%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,50)",
            "value": 5487.527397260266,
            "range": "± 0.65%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,50)",
            "value": 9110.702544031297,
            "range": "± 0.66%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,75)",
            "value": 5208.548923679033,
            "range": "± 1.18%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,75)",
            "value": 7419.43150684933,
            "range": "± 0.74%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,75)",
            "value": 12284.570450097734,
            "range": "± 0.53%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "HtmlRendererBench::benchRender",
            "value": 2847.572015655607,
            "range": "± 0.99%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRowArrays",
            "value": 231.84598825831446,
            "range": "± 1.89%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRecords",
            "value": 1076.987866927585,
            "range": "± 0.41%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "RunCommandBench::benchDefault",
            "value": 125604.79452054865,
            "range": "± 0.93%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchInBand",
            "value": 127389.30724070617,
            "range": "± 1.33%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchNoEnv",
            "value": 23868.007827788413,
            "range": "± 1.15%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp. w/tol)",
            "value": 73.73953033267993,
            "range": "± 1.59%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp.)",
            "value": 66.2232876712336,
            "range": "± 1.31%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "AssertionProcessorBench::benchAssert",
            "value": 966.1318982387519,
            "range": "± 1.64%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "VariantSummaryFormatterBench::benchFormat",
            "value": 1455.03992172212,
            "range": "± 0.59%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "dan.t.leech@gmail.com",
            "name": "dantleech",
            "username": "dantleech"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "4ad451b76965635a7b76037b05d26243d5a98a8d",
          "message": "Introduce CI integration guide (#1144)\n\n- Introduce CI integration guide\n- Downgrade sphinx tabs to previous release for github actions\n- Disable yaml/yml doctor rule\n- Do not generate unused references that cause warnings\n- Disable doctor indentation",
          "timestamp": "2026-03-22T10:14:45Z",
          "tree_id": "282613905a3e1970594bf627c5caa4cb3539fd44",
          "url": "https://github.com/phpbench/phpbench/commit/4ad451b76965635a7b76037b05d26243d5a98a8d"
        },
        "date": 1774174530790,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ExpressionGeneratorBench::benchGenerate",
            "value": 7192.658904109632,
            "range": "± 1.93%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,0)",
            "value": 357.53816046966404,
            "range": "± 2.47%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,0)",
            "value": 362.0684931506786,
            "range": "± 1.92%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,0)",
            "value": 347.257338551859,
            "range": "± 2.09%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,25)",
            "value": 2310.6722113502997,
            "range": "± 0.72%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,25)",
            "value": 3462.3003913894277,
            "range": "± 1.58%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,25)",
            "value": 6103.252446183913,
            "range": "± 1.58%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,50)",
            "value": 3808.4442270057916,
            "range": "± 0.96%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,50)",
            "value": 5573.945205479395,
            "range": "± 1.50%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,50)",
            "value": 9321.35714285713,
            "range": "± 1.64%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,75)",
            "value": 5206.349315068498,
            "range": "± 2.72%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,75)",
            "value": 7627.772994129211,
            "range": "± 2.58%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,75)",
            "value": 12688.664383561658,
            "range": "± 2.04%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "HtmlRendererBench::benchRender",
            "value": 2860.0634050880753,
            "range": "± 0.98%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRowArrays",
            "value": 232.10254403131486,
            "range": "± 1.54%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRecords",
            "value": 1084.4947162426952,
            "range": "± 0.75%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "RunCommandBench::benchDefault",
            "value": 130539.19178082186,
            "range": "± 1.71%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchInBand",
            "value": 133803.92759295352,
            "range": "± 1.43%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchNoEnv",
            "value": 24439.017612524505,
            "range": "± 1.44%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp. w/tol)",
            "value": 75.44931506849416,
            "range": "± 2.28%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp.)",
            "value": 67.55205479452147,
            "range": "± 2.28%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "AssertionProcessorBench::benchAssert",
            "value": 965.8821917808274,
            "range": "± 1.15%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "VariantSummaryFormatterBench::benchFormat",
            "value": 1479.041487279855,
            "range": "± 1.22%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "661c8c6abbc7734986cf7bc6062c237fbb450461",
          "message": "Do not specify base time unit in example",
          "timestamp": "2026-03-22T10:27:20Z",
          "tree_id": "41a3835e9148847946db182411ea82a5df818734",
          "url": "https://github.com/phpbench/phpbench/commit/661c8c6abbc7734986cf7bc6062c237fbb450461"
        },
        "date": 1774175298224,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ExpressionGeneratorBench::benchGenerate",
            "value": 7086.557338551834,
            "range": "± 1.22%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,0)",
            "value": 341.990215264193,
            "range": "± 1.91%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,0)",
            "value": 345.84442270058685,
            "range": "± 2.04%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,0)",
            "value": 351.6741682974498,
            "range": "± 1.46%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,25)",
            "value": 2239.3796477495507,
            "range": "± 0.73%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,25)",
            "value": 3368.5185909980773,
            "range": "± 1.01%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,25)",
            "value": 5885.640900195718,
            "range": "± 1.53%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,50)",
            "value": 3706.8336594912166,
            "range": "± 0.61%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,50)",
            "value": 5397.406066536216,
            "range": "± 1.16%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,50)",
            "value": 9109.107632093934,
            "range": "± 0.60%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,75)",
            "value": 5133.2358121330035,
            "range": "± 0.76%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,75)",
            "value": 7282.2788649706745,
            "range": "± 0.50%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,75)",
            "value": 12137.170254403078,
            "range": "± 0.63%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "HtmlRendererBench::benchRender",
            "value": 2827.901565557676,
            "range": "± 1.41%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRowArrays",
            "value": 229.89393346379677,
            "range": "± 2.14%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRecords",
            "value": 1080.8726027397702,
            "range": "± 0.71%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "RunCommandBench::benchDefault",
            "value": 124873.2465753432,
            "range": "± 0.97%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchInBand",
            "value": 125114.44618395373,
            "range": "± 0.51%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchNoEnv",
            "value": 23625.04500978518,
            "range": "± 0.97%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp. w/tol)",
            "value": 74.81624266144706,
            "range": "± 1.93%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp.)",
            "value": 64.44579256360048,
            "range": "± 1.96%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "AssertionProcessorBench::benchAssert",
            "value": 964.3516634050874,
            "range": "± 0.79%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "VariantSummaryFormatterBench::benchFormat",
            "value": 1444.0195694716308,
            "range": "± 0.65%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "eb7bd89a56463c4b1086f33fb3e79fc8b636e0a2",
          "message": "Fix typos",
          "timestamp": "2026-03-22T10:36:26Z",
          "tree_id": "647886b2a4ff278f206d2d63b26fa863faec6c7d",
          "url": "https://github.com/phpbench/phpbench/commit/eb7bd89a56463c4b1086f33fb3e79fc8b636e0a2"
        },
        "date": 1774175832869,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ExpressionGeneratorBench::benchGenerate",
            "value": 7316.974755381556,
            "range": "± 1.33%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,0)",
            "value": 366.5753424657578,
            "range": "± 1.78%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,0)",
            "value": 364.61839530333236,
            "range": "± 2.47%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,0)",
            "value": 373.83855185910966,
            "range": "± 1.73%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,25)",
            "value": 2450.965753424697,
            "range": "± 1.17%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,25)",
            "value": 3580.6604696673503,
            "range": "± 0.89%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,25)",
            "value": 6197.206457925749,
            "range": "± 0.57%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,50)",
            "value": 3982.0166340509204,
            "range": "± 0.86%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,50)",
            "value": 5849.289628180066,
            "range": "± 0.99%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,50)",
            "value": 9632.452054794143,
            "range": "± 1.04%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,75)",
            "value": 5585.08904109585,
            "range": "± 0.81%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,75)",
            "value": 7945.510763209411,
            "range": "± 2.09%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,75)",
            "value": 12888.684931507014,
            "range": "± 0.94%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "HtmlRendererBench::benchRender",
            "value": 2898.7013698630444,
            "range": "± 0.63%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRowArrays",
            "value": 231.8643835616479,
            "range": "± 1.33%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRecords",
            "value": 1079.3101761252663,
            "range": "± 0.44%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "RunCommandBench::benchDefault",
            "value": 135847.44618395457,
            "range": "± 1.07%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchInBand",
            "value": 138728.87475538187,
            "range": "± 1.03%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchNoEnv",
            "value": 24623.037181996013,
            "range": "± 0.71%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp. w/tol)",
            "value": 76.45753424657572,
            "range": "± 2.20%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp.)",
            "value": 67.45557729941221,
            "range": "± 2.14%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "AssertionProcessorBench::benchAssert",
            "value": 968.1851272015697,
            "range": "± 1.31%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "VariantSummaryFormatterBench::benchFormat",
            "value": 1454.7714285714237,
            "range": "± 1.06%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "8888764ea1ef66db2dbb839d80a90a080bd8dec9",
          "message": "Update doc",
          "timestamp": "2026-03-22T17:50:31Z",
          "tree_id": "bda621a607fe36aaa38aa91eb8331aec8737fd05",
          "url": "https://github.com/phpbench/phpbench/commit/8888764ea1ef66db2dbb839d80a90a080bd8dec9"
        },
        "date": 1774201875598,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "ExpressionGeneratorBench::benchGenerate",
            "value": 7166.860469667357,
            "range": "± 0.49%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,0)",
            "value": 354.5577299412971,
            "range": "± 2.30%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,0)",
            "value": 357.68786692760375,
            "range": "± 2.63%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,0)",
            "value": 353.8806262230935,
            "range": "± 1.71%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,25)",
            "value": 2278.3091976516844,
            "range": "± 1.04%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,25)",
            "value": 3426.364970645808,
            "range": "± 0.74%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,25)",
            "value": 5962.691780821944,
            "range": "± 1.14%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,50)",
            "value": 3742.907045009788,
            "range": "± 1.61%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,50)",
            "value": 5462.381604696659,
            "range": "± 1.26%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,50)",
            "value": 9171.136007827707,
            "range": "± 0.86%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,75)",
            "value": 5281.357142857076,
            "range": "± 1.29%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,75)",
            "value": 7593.484344422677,
            "range": "± 1.36%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,75)",
            "value": 12291.681017612651,
            "range": "± 0.92%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "HtmlRendererBench::benchRender",
            "value": 2825.335616438337,
            "range": "± 1.16%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRowArrays",
            "value": 231.3939334637943,
            "range": "± 1.59%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRecords",
            "value": 1082.1700587084015,
            "range": "± 1.54%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "RunCommandBench::benchDefault",
            "value": 128940.20939334795,
            "range": "± 1.22%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchInBand",
            "value": 127703.86692759267,
            "range": "± 0.94%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchNoEnv",
            "value": 24089.812133072322,
            "range": "± 1.20%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp. w/tol)",
            "value": 74.62328767123341,
            "range": "± 1.61%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp.)",
            "value": 66.93502935420658,
            "range": "± 1.34%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "AssertionProcessorBench::benchAssert",
            "value": 975.1786692759104,
            "range": "± 1.26%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "VariantSummaryFormatterBench::benchFormat",
            "value": 1452.8473581213111,
            "range": "± 0.32%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "e1fcce2f8072842fa9a814490378bea3bba29c42",
          "message": "docs: Add an example on adding a diff column",
          "timestamp": "2026-04-04T15:54:05+01:00",
          "tree_id": "900c9edce7ab8970dab24d2e318f99b1dff63031",
          "url": "https://github.com/phpbench/phpbench/commit/e1fcce2f8072842fa9a814490378bea3bba29c42"
        },
        "date": 1775314483281,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "HtmlRendererBench::benchRender",
            "value": 1836.7064579256387,
            "range": "± 0.93%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,0)",
            "value": 283.7681017612474,
            "range": "± 1.23%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,0)",
            "value": 274.8356164383572,
            "range": "± 1.99%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,0)",
            "value": 280.9129158512716,
            "range": "± 2.12%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,25)",
            "value": 2287.3727984343714,
            "range": "± 2.16%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,25)",
            "value": 3191.993150685009,
            "range": "± 0.96%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,25)",
            "value": 5163.3698630136605,
            "range": "± 0.81%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,50)",
            "value": 3668.3287671232865,
            "range": "± 1.05%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,50)",
            "value": 5316.0205479451115,
            "range": "± 1.03%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,50)",
            "value": 8242.037181996115,
            "range": "± 1.93%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,75)",
            "value": 5148.286692759224,
            "range": "± 1.79%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,75)",
            "value": 7184.0763209392735,
            "range": "± 0.84%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,75)",
            "value": 11063.824853229027,
            "range": "± 0.98%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ExpressionGeneratorBench::benchGenerate",
            "value": 6176.8133072406645,
            "range": "± 0.55%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRowArrays",
            "value": 208.22465753424706,
            "range": "± 2.24%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRecords",
            "value": 901.1853228962809,
            "range": "± 0.92%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp. w/tol)",
            "value": 57.84579256360099,
            "range": "± 1.71%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp.)",
            "value": 51.41448140900244,
            "range": "± 1.34%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "VariantSummaryFormatterBench::benchFormat",
            "value": 1186.0410958904115,
            "range": "± 0.76%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "AssertionProcessorBench::benchAssert",
            "value": 802.0776908023386,
            "range": "± 0.68%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "RunCommandBench::benchDefault",
            "value": 96894.75342465745,
            "range": "± 0.63%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchInBand",
            "value": 96063.6712328768,
            "range": "± 1.42%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchNoEnv",
            "value": 19844.318982387646,
            "range": "± 0.66%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "sasezaki+github@gmail.com",
            "name": "sasezaki",
            "username": "sasezaki"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "2f2742b3bfd1416ca95d4e0338af0b6897cf4e64",
          "message": "Add export-ignore rules to .gitattributes (#1146)\n\n* Add missing export-ignore entries to .gitattributes\n\n* Remove /templates/ from export-ignore (used at runtime)",
          "timestamp": "2026-05-06T21:19:39+02:00",
          "tree_id": "90e9703a359be4f7f3a3eed18625b9d2f74141a7",
          "url": "https://github.com/phpbench/phpbench/commit/2f2742b3bfd1416ca95d4e0338af0b6897cf4e64"
        },
        "date": 1778095229677,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "RunCommandBench::benchDefault",
            "value": 123466.39726027445,
            "range": "± 2.11%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchInBand",
            "value": 129975.26223092062,
            "range": "± 0.89%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchNoEnv",
            "value": 23456.637964774956,
            "range": "± 0.54%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp. w/tol)",
            "value": 76.20528375733652,
            "range": "± 1.49%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp.)",
            "value": 67.16986301369793,
            "range": "± 2.01%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "VariantSummaryFormatterBench::benchFormat",
            "value": 1425.938943248517,
            "range": "± 0.94%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRowArrays",
            "value": 255.6381604696698,
            "range": "± 2.03%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRecords",
            "value": 1116.0493150684913,
            "range": "± 0.73%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "AssertionProcessorBench::benchAssert",
            "value": 948.267123287678,
            "range": "± 1.09%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,0)",
            "value": 328.1203522504871,
            "range": "± 2.06%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,0)",
            "value": 338.3718199608729,
            "range": "± 1.89%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,0)",
            "value": 321.2866927592946,
            "range": "± 2.31%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,25)",
            "value": 2099.145792563613,
            "range": "± 1.56%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,25)",
            "value": 3248.0802348336438,
            "range": "± 1.74%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,25)",
            "value": 5742.154598825837,
            "range": "± 1.26%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,50)",
            "value": 3635.9706457925577,
            "range": "± 1.15%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,50)",
            "value": 5377.595890410945,
            "range": "± 1.05%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,50)",
            "value": 8978.643835616502,
            "range": "± 1.15%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,75)",
            "value": 5221.660469667289,
            "range": "± 1.57%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,75)",
            "value": 7835.084148728145,
            "range": "± 1.94%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,75)",
            "value": 12972.475538160425,
            "range": "± 1.54%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ExpressionGeneratorBench::benchGenerate",
            "value": 7609.2434442269805,
            "range": "± 2.48%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "HtmlRendererBench::benchRender",
            "value": 2433.768493150627,
            "range": "± 0.62%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "dan.t.leech@gmail.com",
            "name": "dantleech",
            "username": "dantleech"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "07b4fa73a8c706cd1bd98755ddadace3dba8248e",
          "message": "Allow lazy evaluation for functions (#1149)",
          "timestamp": "2026-06-06T16:19:12+01:00",
          "tree_id": "3f25ac29991acaa8f0a82c7b8cd7591f01646b3e",
          "url": "https://github.com/phpbench/phpbench/commit/07b4fa73a8c706cd1bd98755ddadace3dba8248e"
        },
        "date": 1780759190068,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "VariantSummaryFormatterBench::benchFormat",
            "value": 1416.9023483366045,
            "range": "± 1.81%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp. w/tol)",
            "value": 72.08219178082234,
            "range": "± 1.95%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp.)",
            "value": 65.72348336595009,
            "range": "± 1.85%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "AssertionProcessorBench::benchAssert",
            "value": 941.4217221135034,
            "range": "± 0.82%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ExpressionGeneratorBench::benchGenerate",
            "value": 7158.73091976512,
            "range": "± 0.83%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,0)",
            "value": 331.8483365949212,
            "range": "± 2.33%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,0)",
            "value": 328.06164383561463,
            "range": "± 2.43%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,0)",
            "value": 335.5909980430631,
            "range": "± 2.62%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,25)",
            "value": 2057.090998043055,
            "range": "± 1.31%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,25)",
            "value": 3168.2446183952707,
            "range": "± 1.21%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,25)",
            "value": 5547.847358121311,
            "range": "± 0.74%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,50)",
            "value": 3537.7045009784724,
            "range": "± 0.77%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,50)",
            "value": 5273.9148727984575,
            "range": "± 1.58%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,50)",
            "value": 8792.689823874749,
            "range": "± 1.24%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,75)",
            "value": 5077.126223091975,
            "range": "± 0.81%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,75)",
            "value": 7398.478473581083,
            "range": "± 1.15%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,75)",
            "value": 12183.647749510621,
            "range": "± 0.90%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "HtmlRendererBench::benchRender",
            "value": 2421.8430528375557,
            "range": "± 0.82%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "RunCommandBench::benchDefault",
            "value": 121694.49902152584,
            "range": "± 0.67%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchInBand",
            "value": 122228.15068493379,
            "range": "± 0.67%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchNoEnv",
            "value": 22825.320939334848,
            "range": "± 0.43%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRowArrays",
            "value": 244.27964774951,
            "range": "± 1.56%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRecords",
            "value": 1131.1430528375774,
            "range": "± 0.74%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "dan.t.leech@gmail.com",
            "name": "dantleech",
            "username": "dantleech"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "fbab09e522838babcdca79085ff769f3bc734e77",
          "message": "Various fixes and improvements (#1150)\n\n- expr: Boolean node is a scalar value\n- expr: Support the nullsafe operator on bare variables\n- expr: Allow display as \"rstdev\"\n- tests: approval support for empty files",
          "timestamp": "2026-06-07T09:50:04+01:00",
          "tree_id": "7a7664abd6ad642bf61a1b20a3b78b3cbf767fc4",
          "url": "https://github.com/phpbench/phpbench/commit/fbab09e522838babcdca79085ff769f3bc734e77"
        },
        "date": 1780822248804,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "VariantSummaryFormatterBench::benchFormat",
            "value": 1400.4219178082242,
            "range": "± 1.10%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp. w/tol)",
            "value": 75.25577299412814,
            "range": "± 1.73%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp.)",
            "value": 66.98669275929421,
            "range": "± 1.89%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "AssertionProcessorBench::benchAssert",
            "value": 946.1559686888431,
            "range": "± 1.09%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ExpressionGeneratorBench::benchGenerate",
            "value": 7236.970254403114,
            "range": "± 0.93%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,0)",
            "value": 330.1722113502974,
            "range": "± 2.56%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,0)",
            "value": 332.7534246575341,
            "range": "± 2.13%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,0)",
            "value": 336.2084148727947,
            "range": "± 1.76%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,25)",
            "value": 2120.821917808248,
            "range": "± 1.27%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,25)",
            "value": 3215.8336594911925,
            "range": "± 1.36%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,25)",
            "value": 5640.761252446135,
            "range": "± 0.97%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,50)",
            "value": 3723.438356164459,
            "range": "± 0.83%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,50)",
            "value": 5545.917808219199,
            "range": "± 2.44%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,50)",
            "value": 8996.177103718226,
            "range": "± 2.55%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,75)",
            "value": 5213.321917808213,
            "range": "± 1.05%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,75)",
            "value": 7815.63405088059,
            "range": "± 1.73%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,75)",
            "value": 12709.438356164253,
            "range": "± 1.19%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "HtmlRendererBench::benchRender",
            "value": 2435.761839530371,
            "range": "± 1.22%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "RunCommandBench::benchDefault",
            "value": 130963.65362035147,
            "range": "± 1.14%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchInBand",
            "value": 131874.44031311368,
            "range": "± 0.38%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchNoEnv",
            "value": 23659.045009784062,
            "range": "± 1.16%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRowArrays",
            "value": 246.85714285714198,
            "range": "± 2.03%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRecords",
            "value": 1133.304500978464,
            "range": "± 1.24%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "dan.t.leech@gmail.com",
            "name": "dantleech",
            "username": "dantleech"
          },
          "committer": {
            "email": "noreply@github.com",
            "name": "GitHub",
            "username": "web-flow"
          },
          "distinct": true,
          "id": "5c72bb108eac249dd427953a90b5583e2e2c1a7f",
          "message": "Bump phpstan (#1151)\n\n* Bump PHPStan and related deps and fix some issues\n\n- PHPstan to 2.x and related packages.\n- Fix some of the newly reported issues.\n- Update baseline.\n\n* Support console 6.x",
          "timestamp": "2026-06-07T10:33:04+01:00",
          "tree_id": "4babbe93ec5a9c37a42b152f2c1db1c6d3e09735",
          "url": "https://github.com/phpbench/phpbench/commit/5c72bb108eac249dd427953a90b5583e2e2c1a7f"
        },
        "date": 1780824824564,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "VariantSummaryFormatterBench::benchFormat",
            "value": 1406.174951076309,
            "range": "± 1.34%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp. w/tol)",
            "value": 73.41878669276029,
            "range": "± 2.36%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp.)",
            "value": 66.3851272015662,
            "range": "± 1.73%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "AssertionProcessorBench::benchAssert",
            "value": 943.0669275929661,
            "range": "± 1.17%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ExpressionGeneratorBench::benchGenerate",
            "value": 7132.07318982385,
            "range": "± 0.67%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,0)",
            "value": 322.77103718199277,
            "range": "± 1.29%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,0)",
            "value": 317.4960861056768,
            "range": "± 2.72%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,0)",
            "value": 319.386497064581,
            "range": "± 2.58%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,25)",
            "value": 2215.484344422657,
            "range": "± 1.09%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,25)",
            "value": 3335.157534246556,
            "range": "± 1.31%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,25)",
            "value": 5718.952054794527,
            "range": "± 1.03%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,50)",
            "value": 3593.8718199608784,
            "range": "± 1.40%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,50)",
            "value": 5327.438356164275,
            "range": "± 0.95%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,50)",
            "value": 8777.323874755482,
            "range": "± 1.15%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,75)",
            "value": 5126.949119373784,
            "range": "± 1.34%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,75)",
            "value": 7456.952054794601,
            "range": "± 0.92%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,75)",
            "value": 12194.98043052843,
            "range": "± 1.50%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "HtmlRendererBench::benchRender",
            "value": 2416.442465753444,
            "range": "± 1.01%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "RunCommandBench::benchDefault",
            "value": 122695.4637964773,
            "range": "± 1.53%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchInBand",
            "value": 122638.30136986238,
            "range": "± 1.18%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchNoEnv",
            "value": 22917.027397260368,
            "range": "± 0.43%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRowArrays",
            "value": 246.64814090019433,
            "range": "± 1.04%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRecords",
            "value": 1134.6976516634238,
            "range": "± 1.52%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          }
        ]
      },
      {
        "commit": {
          "author": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "committer": {
            "email": "daniel@dantleech.com",
            "name": "Daniel Leech",
            "username": "dantleech"
          },
          "distinct": true,
          "id": "e8145c81d2ffffb249a2f9f6c6adec2ef1efc3f5",
          "message": "Delete dead snapshots",
          "timestamp": "2026-06-07T10:44:43+01:00",
          "tree_id": "fd326cb5ee7584559bea1407edd718400f91fefd",
          "url": "https://github.com/phpbench/phpbench/commit/e8145c81d2ffffb249a2f9f6c6adec2ef1efc3f5"
        },
        "date": 1780825528048,
        "tool": "customSmallerIsBetter",
        "benches": [
          {
            "name": "VariantSummaryFormatterBench::benchFormat",
            "value": 1435.581017612527,
            "range": "± 1.18%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp. w/tol)",
            "value": 72.16144814090075,
            "range": "± 2.41%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ParserBench::benchEvaluate (comp.)",
            "value": 64.4724070450097,
            "range": "± 2.38%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "AssertionProcessorBench::benchAssert",
            "value": 948.3616438356134,
            "range": "± 1.06%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ExpressionGeneratorBench::benchGenerate",
            "value": 7122.40567514674,
            "range": "± 0.78%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,0)",
            "value": 346.5185909980426,
            "range": "± 2.07%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,0)",
            "value": 347.25048923679014,
            "range": "± 1.87%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,0)",
            "value": 348.36888454011694,
            "range": "± 1.91%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,25)",
            "value": 2256.8590998043114,
            "range": "± 1.83%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,25)",
            "value": 3348.450097847347,
            "range": "± 0.89%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,25)",
            "value": 5898.365949119416,
            "range": "± 0.63%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,50)",
            "value": 3589.573385518604,
            "range": "± 0.92%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,50)",
            "value": 5266.256360078254,
            "range": "± 1.07%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,50)",
            "value": 9009.331702543925,
            "range": "± 1.43%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (text,75)",
            "value": 5102.496086105664,
            "range": "± 0.85%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (bar_chart_aggregate,75)",
            "value": 7340.281800391405,
            "range": "± 0.77%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "ComponentGeneratorBench::benchGenerate (table_aggregate,75)",
            "value": 12174.765166340621,
            "range": "± 0.58%",
            "unit": "μs",
            "extra": "10 iterations, 2 revs"
          },
          {
            "name": "HtmlRendererBench::benchRender",
            "value": 2720.491585127196,
            "range": "± 0.79%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "RunCommandBench::benchDefault",
            "value": 116233.97847358105,
            "range": "± 0.63%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchInBand",
            "value": 116452.16438356153,
            "range": "± 0.62%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "RunCommandBench::benchNoEnv",
            "value": 23353.090019569034,
            "range": "± 0.68%",
            "unit": "μs",
            "extra": "10 iterations, 1 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRowArrays",
            "value": 235.57045009784497,
            "range": "± 1.32%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          },
          {
            "name": "DataFrameBench::benchCreateFromRecords",
            "value": 1167.7178082191683,
            "range": "± 0.42%",
            "unit": "μs",
            "extra": "10 iterations, 10 revs"
          }
        ]
      }
    ]
  }
}