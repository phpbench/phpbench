window.BENCHMARK_DATA = {
  "lastUpdate": 1774174532264,
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
      }
    ]
  }
}