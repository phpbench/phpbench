<?php

namespace PhpBench\Extension;

use PhpBench\Console\Command\EvaluateCommand;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\MainEvaluator;
use PhpBench\Expression\Evaluator\ArgumentListEvaluator;
use PhpBench\Expression\Evaluator\BinaryOperatorEvaluator;
use PhpBench\Expression\Evaluator\BooleanEvaluator;
use PhpBench\Expression\Evaluator\ComparisonEvaluator;
use PhpBench\Expression\Evaluator\FloatEvaluator;
use PhpBench\Expression\Evaluator\FunctionEvaluator;
use PhpBench\Expression\Evaluator\IntegerEvaluator;
use PhpBench\Expression\Evaluator\ListEvaluator;
use PhpBench\Expression\Evaluator\ParenthesisEvaluator;
use PhpBench\Expression\Evaluator\TolerableEvaluator;
use PhpBench\Expression\Evaluator\UnitEvaluator;
use PhpBench\Expression\ExpressionFunctions;
use PhpBench\Expression\Func\MaxFunction;
use PhpBench\Expression\Func\MeanFunction;
use PhpBench\Expression\Func\MinFunction;
use PhpBench\Expression\Func\ModeFunction;
use PhpBench\Expression\Lexer;
use PhpBench\Expression\MainPrinter;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Parselet\BinaryOperatorParselet;
use PhpBench\Expression\Parselet\BooleanParselet;
use PhpBench\Expression\Parselet\ComparisonParselet;
use PhpBench\Expression\Parselet\FloatParselet;
use PhpBench\Expression\Parselet\FunctionParselet;
use PhpBench\Expression\Parselet\IntegerParselet;
use PhpBench\Expression\Parselet\ListParselet;
use PhpBench\Expression\Parselet\ParenthesisParselet;
use PhpBench\Expression\Parselet\PercentageParselet;
use PhpBench\Expression\Parselet\TolerableParselet;
use PhpBench\Expression\Parselet\UnitParselet;
use PhpBench\Expression\Parselets;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Precedence;
use PhpBench\Expression\Printer;
use PhpBench\Expression\Printer\ArgumentListPrinter;
use PhpBench\Expression\Printer\BinaryOperatorPrinter;
use PhpBench\Expression\Printer\BooleanPrinter;
use PhpBench\Expression\Printer\ComparisonPrinter;
use PhpBench\Expression\Printer\FunctionPrinter;
use PhpBench\Expression\Printer\ListPrinter;
use PhpBench\Expression\Printer\NumberPrinter;
use PhpBench\Expression\Printer\ParenthesisPrinter;
use PhpBench\Expression\Printer\PercentagePrinter;
use PhpBench\Expression\Printer\TolerablePrinter;
use PhpBench\Expression\Printer\UnitPrinter;
use PhpBench\Expression\Token;
use PhpBench\Util\MemoryUnit;
use PhpBench\Util\TimeUnit;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpressionExtension implements ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(Container $container): void
    {
        $container->register(EvaluateCommand::class, function (Container $container) {
            return new EvaluateCommand(
                $container->get(MainEvaluator::class),
                $container->get(Lexer::class),
                $container->get(Parser::class)
            );
        }, [
            CoreExtension::TAG_CONSOLE_COMMAND => []
        ]);
        $container->register(Parser::class, function (Container $container) {
            return new Parser(
                Parselets::fromPrefixParselets([
                    new ListParselet(),
                    new FunctionParselet(),
                    new IntegerParselet(),
                    new FloatParselet(),
                    new ParenthesisParselet(),
                    new BooleanParselet(),
                ]),
                Parselets::fromInfixParselets([
                    new BinaryOperatorParselet(Token::T_LOGICAL_OR, Precedence::LOGICAL_OR),
                    new BinaryOperatorParselet(Token::T_LOGICAL_AND, Precedence::LOGICAL_AND),
                    new BinaryOperatorParselet(Token::T_PLUS, Precedence::SUM),
                    new BinaryOperatorParselet(Token::T_MINUS, Precedence::SUM),
                    new BinaryOperatorParselet(Token::T_MULTIPLY, Precedence::PRODUCT),
                    new BinaryOperatorParselet(Token::T_DIVIDE, Precedence::PRODUCT),

                    new ComparisonParselet(Token::T_LT, Precedence::COMPARISON),
                    new ComparisonParselet(Token::T_LTE, Precedence::COMPARISON),
                    new ComparisonParselet(Token::T_EQUALS, Precedence::COMPARISON_EQUALITY),
                    new ComparisonParselet(Token::T_GT, Precedence::COMPARISON),
                    new ComparisonParselet(Token::T_GTE, Precedence::COMPARISON),
                    new TolerableParselet(),
                ]),
                Parselets::fromSuffixParselets([
                    new UnitParselet(),
                    new PercentageParselet(),
                ])
            );
        });

        $container->register(MainEvaluator::class, function (Container $container) {
            /** @phpstan-ignore-next-line */
            return new MainEvaluator([
                new ArgumentListEvaluator(),
                new IntegerEvaluator(),
                new BinaryOperatorEvaluator(),
                new FloatEvaluator(),
                new FunctionEvaluator($container->get(ExpressionFunctions::class)),
                new ListEvaluator(),
                new UnitEvaluator(),
                new ParenthesisEvaluator(),
                new ComparisonEvaluator(),
                new TolerableEvaluator(),
                new BooleanEvaluator(),
            ]);
        });

        $container->register(Printer::class, function (Container $container) {
            return new MainPrinter([
                new ArgumentListPrinter(),
                new NumberPrinter(),
                new BinaryOperatorPrinter(),
                new ComparisonPrinter(),
                new BooleanPrinter(),
                new FunctionPrinter(),
                new ListPrinter(),
                new ParenthesisPrinter(),
                new TolerablePrinter(),
                new PercentagePrinter(),
                new UnitPrinter(),
            ]);
        });

        $container->register(ExpressionFunctions::class, function () {
            return new ExpressionFunctions([
                'mode' => new ModeFunction(),
                'mean' => new MeanFunction(),
                'min' => new MinFunction(),
                'max' => new MaxFunction()
            ]);
        });

        $container->register(Lexer::class, function (Container $container) {
            return new Lexer(
                $container->get(ExpressionFunctions::class)->names(),
                TimeUnit::supportedUnitNames(),
                MemoryUnit::supportedUnitNames()
            );
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $resolver): void
    {
    }
}
