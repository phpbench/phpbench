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
use PhpBench\Expression\Lexer;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Printer;
use PhpBench\Math\Statistics;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Variant;

class AssertionProcessor
{
    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var Parser
     */
    private $parser;

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

    public function __construct(
        Lexer $lexer,
        Parser $parser,
        Evaluator $evaluator,
        Printer $printer,
        ParameterProvider $provider
    )
    {
        $this->lexer = $lexer;
        $this->parser = $parser;
        $this->evaluator = $evaluator;
        $this->printer = $printer;
        $this->provider = $provider;
    }

    public function assert(Variant $variant, string $assertion): AssertionResult
    {
        $tokens = $this->lexer->lex($assertion);
        $node = $this->parser->parse($tokens);
        $params = $this->provider->provideFor($variant);
        $evaluated = $this->evaluator->evaluate($node, $params);
        $expression = $this->printer->print($node, $params);
        $result = $this->printer->print($evaluated, $params);
        $message = sprintf(
            '%s = %s',
            $expression,
            $result
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
            'Assertion expression must evaluate to a boolean-like value, got "%s"',
            get_class($evaluated)
        ));

        return $result;
    }
}
