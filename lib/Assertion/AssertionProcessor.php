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

use Exception;
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Exception\ExpressionError;
use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Assertion\Exception\PropertyAccessError;
use PhpBench\Model\Variant;
use RuntimeException;

class AssertionProcessor
{
    /**
     * @var ExpressionEvaluatorFactory
     */
    private $evaluator;

    /**
     * @var ExpressionParser
     */
    private $parser;

    /**
     * @var ExpressionPrinterFactory
     */
    private $printer;

    /**
     * @var ExpressionLexer
     */
    private $lexer;

    public function __construct(
        ExpressionParser $parser,
        ExpressionLexer $lexer,
        ExpressionEvaluatorFactory $evaluator,
        ExpressionPrinterFactory $printer
    ) {
        $this->evaluator = $evaluator;
        $this->parser = $parser;
        $this->printer = $printer;
        $this->lexer = $lexer;
    }

    public function assert(Variant $variant, string $assertion): AssertionResult
    {
        $node = $this->parser->parse($this->lexer->lex($assertion));

        $args = (function (array $variantData) use ($variant) {
            return [
                'variant' => $variantData,
                'baseline' => $variant->getBaseline() ? $this->buildVariantData($variant->getBaseline()) : $variantData,
            ];
        })($this->buildVariantData($variant));

        try {
            $result = $this->evaluator->createWithParameters($args)->evaluate($node);
        } catch (PropertyAccessError $error) {
            throw ExpressionError::forExpression($assertion, $error->getMessage());
        } catch (Exception $error) {
            throw ExpressionError::forExpression($assertion, $error->getMessage());
        }
        $printer = $this->printer->create($args);

        if (!$result instanceof ComparisonResult) {
            throw ExpressionError::forExpression($assertion, sprintf(
                'Expected boolean expression, got "%s", with value "%s"',
                gettype($result),
                (function (string $value) {
                    if (strlen($value) > 255) {
                        return substr($value, 0, 255) . '...';
                    }

                    return $value;
                })(json_encode($result, JSON_PRETTY_PRINT))
            ));
        }

        if ($result->isTolerated()) {
            return AssertionResult::tolerated($printer->format($node));
        }

        if ($result->isTrue()) {
            return AssertionResult::ok();
        }

        return AssertionResult::fail($printer->format($node));
    }

    /**
     * @return parameters
     */
    private function buildVariantData(Variant $variant): array
    {
        return $variant->getAllMetricValues();
    }
}
