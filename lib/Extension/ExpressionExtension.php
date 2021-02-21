<?php

namespace PhpBench\Extension;

use PhpBench\Console\Command\EvaluateCommand;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Expression\Ast\ArithmeticOperatorNode;
use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Evaluator\ArgumentListEvaluator;
use PhpBench\Expression\Evaluator\ArithmeticOperatorEvaluator;
use PhpBench\Expression\Evaluator\BooleanEvaluator;
use PhpBench\Expression\Evaluator\ComparisonEvaluator;
use PhpBench\Expression\Evaluator\DisplayAsEvaluator;
use PhpBench\Expression\Evaluator\FloatEvaluator;
use PhpBench\Expression\Evaluator\FunctionEvaluator;
use PhpBench\Expression\Evaluator\IntegerEvaluator;
use PhpBench\Expression\Evaluator\ListEvaluator;
use PhpBench\Expression\Evaluator\LogicalOperatorEvaluator;
use PhpBench\Expression\Evaluator\ParameterEvaluator;
use PhpBench\Expression\Evaluator\ParenthesisEvaluator;
use PhpBench\Expression\Evaluator\TolerableEvaluator;
use PhpBench\Expression\Evaluator\UnitEvaluator;
use PhpBench\Expression\ExpressionFunctions;
use PhpBench\Expression\Func\MaxFunction;
use PhpBench\Expression\Func\MeanFunction;
use PhpBench\Expression\Func\MinFunction;
use PhpBench\Expression\Func\ModeFunction;
use PhpBench\Expression\Lexer;
use PhpBench\Expression\NodePrinter\ArgumentListPrinter;
use PhpBench\Expression\NodePrinter\BinaryOperatorPrinter;
use PhpBench\Expression\NodePrinter\BooleanPrinter;
use PhpBench\Expression\NodePrinter\ComparisonPrinter;
use PhpBench\Expression\NodePrinter\DisplayAsPrinter;
use PhpBench\Expression\NodePrinter\FunctionPrinter;
use PhpBench\Expression\NodePrinter\ListPrinter;
use PhpBench\Expression\NodePrinter\NumberPrinter;
use PhpBench\Expression\NodePrinter\ParameterPrinter;
use PhpBench\Expression\NodePrinter\ParenthesisPrinter;
use PhpBench\Expression\NodePrinter\PercentagePrinter;
use PhpBench\Expression\NodePrinter\TolerablePrinter;
use PhpBench\Expression\NodePrinter\UnitPrinter;
use PhpBench\Expression\NodePrinters;
use PhpBench\Expression\Parselet\ArithmeticOperatorParselet;
use PhpBench\Expression\Parselet\BooleanParselet;
use PhpBench\Expression\Parselet\ComparisonParselet;
use PhpBench\Expression\Parselet\DisplayAsParselet;
use PhpBench\Expression\Parselet\FloatParselet;
use PhpBench\Expression\Parselet\FunctionParselet;
use PhpBench\Expression\Parselet\IntegerParselet;
use PhpBench\Expression\Parselet\ListParselet;
use PhpBench\Expression\Parselet\LogicalOperatorParselet;
use PhpBench\Expression\Parselet\ParameterParselet;
use PhpBench\Expression\Parselet\ParenthesisParselet;
use PhpBench\Expression\Parselet\PercentageParselet;
use PhpBench\Expression\Parselet\TolerableParselet;
use PhpBench\Expression\Parselet\UnitParselet;
use PhpBench\Expression\Parselets;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Precedence;
use PhpBench\Expression\Printer;
use PhpBench\Expression\Printer\EvaluatingPrinter;
use PhpBench\Expression\Printer\NormalizingPrinter;
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
                $container->get(Evaluator::class),
                $container->get(Lexer::class),
                $container->get(Parser::class),
                $container->get(Printer::class),
                $container->get(EvaluatingPrinter::class)
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
                    new ParameterParselet(),
                ]),
                Parselets::fromInfixParselets([
                    new LogicalOperatorParselet(Token::T_LOGICAL_OR, Precedence::LOGICAL_OR),
                    new LogicalOperatorParselet(Token::T_LOGICAL_AND, Precedence::LOGICAL_AND),
                    new ArithmeticOperatorParselet(Token::T_PLUS, Precedence::SUM),
                    new ArithmeticOperatorParselet(Token::T_MINUS, Precedence::SUM),
                    new ArithmeticOperatorParselet(Token::T_MULTIPLY, Precedence::PRODUCT),
                    new ArithmeticOperatorParselet(Token::T_DIVIDE, Precedence::PRODUCT),

                    new ComparisonParselet(Token::T_LT, Precedence::COMPARISON),
                    new ComparisonParselet(Token::T_LTE, Precedence::COMPARISON),
                    new ComparisonParselet(Token::T_EQUALS, Precedence::COMPARISON_EQUALITY),
                    new ComparisonParselet(Token::T_GT, Precedence::COMPARISON),
                    new ComparisonParselet(Token::T_GTE, Precedence::COMPARISON),
                    new TolerableParselet(),
                    new DisplayAsParselet(),
                ]),
                Parselets::fromSuffixParselets([
                    new UnitParselet(),
                    new PercentageParselet(),
                ])
            );
        });

        $container->register(Evaluator::class, function (Container $container) {
            /** @phpstan-ignore-next-line */
            return new Evaluator([
                new ArgumentListEvaluator(),
                new IntegerEvaluator(),
                new ArithmeticOperatorEvaluator(),
                new LogicalOperatorEvaluator(),
                new FloatEvaluator(),
                new FunctionEvaluator($container->get(ExpressionFunctions::class)),
                new ListEvaluator(),
                new UnitEvaluator(),
                new ParenthesisEvaluator(),
                new ComparisonEvaluator(),
                new TolerableEvaluator(),
                new BooleanEvaluator(),
                new DisplayAsEvaluator(),
                new ParameterEvaluator(),
            ]);
        });

        $container->register(NodePrinters::class, function (Container $container) {
            return new NodePrinters([
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
                new DisplayAsPrinter(),
                new ParameterPrinter(),
            ]);
        });

        $container->register(Printer::class, function (Container $container) {
            return new NormalizingPrinter($container->get(NodePrinters::class));
        });

        $container->register(EvaluatingPrinter::class, function (Container $container) {
            return new EvaluatingPrinter(
                $container->get(NodePrinters::class),
                $container->get(Evaluator::class),
                [
                    FunctionNode::class,
                    ArithmeticOperatorNode::class,
                    ParenthesisNode::class,
                    DisplayAsNode::class
                ]
            );
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
