<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

final class ExpressionEvaluator
{
    /**
     * @var ExpressionLanguage
     */
    private $language;

    /**
     * @var Evaluator
     */
    private $evaluator;

    public function __construct(ExpressionLanguage $language, Evaluator $evaluator)
    {
        $this->language = $language;
        $this->evaluator = $evaluator;
    }

    /**
     * @param parameters $params
     */
    public function evaluate(string $expression, array $params): Node
    {
        return $this->evaluator->evaluate($this->language->parse($expression), $params);
    }
}
