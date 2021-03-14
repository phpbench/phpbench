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
use PhpBench\Model\Variant;

class AssertionProcessor
{
    /**
     * @var Evaluator
     */
    private $evaluator;

    /**
     * @var Printer
     */
    private $printer;

    /**
     * @var ParameterProvider
     */
    private $provider;

    /**
     * @var Printer
     */
    private $evaluatingPrinter;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanaugage;

    public function __construct(
        ExpressionLanguage $expressionLanaugage,
        Evaluator $evaluator,
        Printer $printer,
        Printer $evaluatingPrinter,
        ParameterProvider $provider
    ) {
        $this->evaluator = $evaluator;
        $this->printer = $printer;
        $this->provider = $provider;
        $this->evaluatingPrinter = $evaluatingPrinter;
        $this->expressionLanaugage = $expressionLanaugage;
    }

    public function assert(Variant $variant, string $assertion): AssertionResult
    {
        $node = $this->expressionLanaugage->parse($assertion);
        $params = $this->provider->provideFor($variant);
        $evaluated = $this->evaluator->evaluate($node, $params);

        $message = sprintf(
            "%s\n= %s\n= %s",
            $this->printer->print($node, $params),
            $this->evaluatingPrinter->print($node, $params),
            $this->printer->print($evaluated, $params)
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
            get_class($evaluated),
            $this->printer->print($evaluated, $params)
        ));
    }
}
