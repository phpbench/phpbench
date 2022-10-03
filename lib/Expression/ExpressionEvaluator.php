<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValue;

/**
 * @deprecated Will be removed in PHPBench 2.0. For use in reports use the ExpressionBridge.
 */
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

    /**
     * @var MustacheRenderer
     */
    private $mustacheRenderer;

    /**
     * @var Printer
     */
    private $printer;

    public function __construct(ExpressionLanguage $language, Evaluator $evaluator, Printer $printer)
    {
        $this->language = $language;
        $this->evaluator = $evaluator;
        $this->mustacheRenderer = new MustacheRenderer();
        $this->printer = $printer;
    }

    /**
     * @param parameters $params
     */
    public function evaluate(string $expression, array $params): Node
    {
        return $this->evaluator->evaluate($this->language->parse($expression), $params);
    }

    /**
     * @return scalar|scalar[]
     *
     * @param parameters $params
     */
    public function evaluatePhpValue(string $expression, array $params)
    {
        $node = $this->evaluator->evaluateType($this->language->parse($expression), PhpValue::class, $params);

        return $node->value();
    }

    /**
     * Render expressions in a string delimtied by `{{` and `}}`
     *
     * @param parameters $params
     */
    public function renderTemplate(?string $template, array $params): ?string
    {
        if (null === $template) {
            return null;
        }

        return $this->mustacheRenderer->render($template, function (string $expression) use ($params) {
            return $this->printer->print($this->evaluate($expression, $params));
        });
    }
}
