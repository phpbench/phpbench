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

namespace PhpBench\Assertion;

use PhpBench\Assertion\Exception\AssertionError;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\ToleratedTrue;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\Printer;
use PhpBench\Expression\Printer\EvaluatingPrinter;
use PhpBench\Model\Variant;

class AssertionProcessor
{
    public function __construct(private readonly ExpressionLanguage $expressionLanaugage, private readonly Evaluator $evaluator, private readonly Printer $printer, private readonly EvaluatingPrinter $evaluatingPrinter, private readonly ParameterProvider $provider)
    {
    }

    public function assert(Variant $variant, string $assertion): AssertionResult
    {
        $node = $this->expressionLanaugage->parse($assertion);
        $params = $this->provider->provideFor($variant);
        $evaluated = $this->evaluator->evaluate($node, $params);

        $message = sprintf(
            "%s\n= %s\n= %s",
            $this->printer->print($node),
            $this->evaluatingPrinter->withParams($params)->print($node),
            $this->printer->print($evaluated)
        );

        if ($evaluated instanceof BooleanNode) {
            if ($evaluated->value()) {
                return AssertionResult::ok();
            }

            return AssertionResult::fail($message);
        }

        if ($evaluated instanceof ToleratedTrue) {
            return AssertionResult::tolerated($message);
        }

        throw new AssertionError(sprintf(
            'Assertion expression must evaluate to a boolean-like value, got "%s" as "%s"',
            $evaluated::class,
            $this->printer->print($evaluated)
        ));
    }
}
