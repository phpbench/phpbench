<?php

namespace PhpBench\Extension;

use PhpBench\Console\Command\EvaluateCommand;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Expression\Ast\ArithmeticOperatorNode;
use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\Ast\TolerableNode;
use PhpBench\Expression\ColorMap\Standard8ColorMap;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Evaluator\MainEvaluator;
use PhpBench\Expression\Evaluator\PrettyErrorEvaluator;
use PhpBench\Expression\ExpressionFunctions;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\ExpressionLanguage\MemoisedExpressionLanguage;
use PhpBench\Expression\ExpressionLanguage\RealExpressionLanguage;
use PhpBench\Expression\Func\FormatFunction;
use PhpBench\Expression\Func\MaxFunction;
use PhpBench\Expression\Func\MeanFunction;
use PhpBench\Expression\Func\MinFunction;
use PhpBench\Expression\Func\ModeFunction;
use PhpBench\Expression\Func\PercentDifferenceFunction;
use PhpBench\Expression\Func\RStDevFunction;
use PhpBench\Expression\Func\StDevFunction;
use PhpBench\Expression\Func\VarianceFunction;
use PhpBench\Expression\Lexer;
use PhpBench\Expression\NodeEvaluator;
use PhpBench\Expression\NodeEvaluator\ArgumentListEvaluator;
use PhpBench\Expression\NodeEvaluator\ArithmeticOperatorEvaluator;
use PhpBench\Expression\NodeEvaluator\BooleanEvaluator;
use PhpBench\Expression\NodeEvaluator\ComparisonEvaluator;
use PhpBench\Expression\NodeEvaluator\ConcatEvaluator;
use PhpBench\Expression\NodeEvaluator\DisplayAsEvaluator;
use PhpBench\Expression\NodeEvaluator\FloatEvaluator;
use PhpBench\Expression\NodeEvaluator\FunctionEvaluator;
use PhpBench\Expression\NodeEvaluator\IntegerEvaluator;
use PhpBench\Expression\NodeEvaluator\ListEvaluator;
use PhpBench\Expression\NodeEvaluator\LogicalOperatorEvaluator;
use PhpBench\Expression\NodeEvaluator\MemoisedNodeEvaluator;
use PhpBench\Expression\NodeEvaluator\ParameterEvaluator;
use PhpBench\Expression\NodeEvaluator\ParenthesisEvaluator;
use PhpBench\Expression\NodeEvaluator\StringEvaluator;
use PhpBench\Expression\NodeEvaluator\TolerableEvaluator;
use PhpBench\Expression\NodeEvaluator\UnitEvaluator;
use PhpBench\Expression\NodeEvaluators;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\NodePrinter\ArgumentListPrinter;
use PhpBench\Expression\NodePrinter\BinaryOperatorPrinter;
use PhpBench\Expression\NodePrinter\BooleanPrinter;
use PhpBench\Expression\NodePrinter\ComparisonPrinter;
use PhpBench\Expression\NodePrinter\ConcatPrinter;
use PhpBench\Expression\NodePrinter\DisplayAsPrinter;
use PhpBench\Expression\NodePrinter\FunctionPrinter;
use PhpBench\Expression\NodePrinter\HighlightingNodePrinter;
use PhpBench\Expression\NodePrinter\ListPrinter;
use PhpBench\Expression\NodePrinter\NumberPrinter;
use PhpBench\Expression\NodePrinter\ParameterPrinter;
use PhpBench\Expression\NodePrinter\ParenthesisPrinter;
use PhpBench\Expression\NodePrinter\PercentageDifferencePrinter;
use PhpBench\Expression\NodePrinter\PercentagePrinter;
use PhpBench\Expression\NodePrinter\StringPrinter;
use PhpBench\Expression\NodePrinter\TolerablePrinter;
use PhpBench\Expression\NodePrinter\UnitPrinter;
use PhpBench\Expression\NodePrinters;
use PhpBench\Expression\Parselet\ArithmeticOperatorParselet;
use PhpBench\Expression\Parselet\BooleanParselet;
use PhpBench\Expression\Parselet\ComparisonParselet;
use PhpBench\Expression\Parselet\ConcatParselet;
use PhpBench\Expression\Parselet\DisplayAsParselet;
use PhpBench\Expression\Parselet\FloatParselet;
use PhpBench\Expression\Parselet\FunctionParselet;
use PhpBench\Expression\Parselet\IntegerParselet;
use PhpBench\Expression\Parselet\ListParselet;
use PhpBench\Expression\Parselet\LogicalOperatorParselet;
use PhpBench\Expression\Parselet\ParameterParselet;
use PhpBench\Expression\Parselet\ParenthesisParselet;
use PhpBench\Expression\Parselet\PercentageParselet;
use PhpBench\Expression\Parselet\StringParselet;
use PhpBench\Expression\Parselet\TolerableParselet;
use PhpBench\Expression\Parselet\UnitParselet;
use PhpBench\Expression\Parselets;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Precedence;
use PhpBench\Expression\Printer;
use PhpBench\Expression\Printer\EvaluatingPrinter;
use PhpBench\Expression\Printer\NormalizingPrinter;
use PhpBench\Expression\Printer\UnderlinePrinterFactory;
use PhpBench\Expression\Token;
use PhpBench\Util\TimeUnit;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpressionExtension implements ExtensionInterface
{
    const PARAM_SYNTAX_HIGHLIGHTING = 'expression.syntax_highlighting';

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
                    new StringParselet(),
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
                    new ConcatParselet(),
                ]),
                Parselets::fromSuffixParselets([
                    new UnitParselet(),
                    new PercentageParselet(),
                ])
            );
        });

        $container->register(NodeEvaluator::class, function (Container $container) {
            /** @phpstan-ignore-next-line */
            $evaluators = new NodeEvaluators([
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
                new StringEvaluator(),
                new ConcatEvaluator(),
            ]);

            return $evaluators;
        });

        $container->register(Evaluator::class, function (Container $container) {
            return new PrettyErrorEvaluator(
                new MainEvaluator(new MemoisedNodeEvaluator($container->get(NodeEvaluator::class))),
                $container->get(Printer::class),
                new UnderlinePrinterFactory($container->get(NodePrinter::class))
            );
        });

        $container->register(NodePrinter::class, function (Container $container) {
            if ($container->getParameter(self::PARAM_SYNTAX_HIGHLIGHTING)) {
                return $container->get(HighlightingNodePrinter::class);
            }

            return $container->get(NodePrinters::class);
        });

        $container->register(HighlightingNodePrinter::class, function (Container $container) {
            return new HighlightingNodePrinter(
                $container->get(NodePrinters::class),
                new Standard8ColorMap()
            );
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
                new DisplayAsPrinter($container->get(TimeUnit::class)),
                new ParameterPrinter(),
                new StringPrinter(),
                new ConcatPrinter(),
                new PercentageDifferencePrinter(),
            ]);
        });

        $container->register(Printer::class, function (Container $container) {
            return new NormalizingPrinter($container->get(NodePrinter::class));
        });

        $container->register(EvaluatingPrinter::class, function (Container $container) {
            return new EvaluatingPrinter(
                $container->get(NodePrinter::class),
                $container->get(Evaluator::class),
                [
                    TolerableNode::class,
                    FunctionNode::class,
                    ArithmeticOperatorNode::class,
                    ParenthesisNode::class,
                    DisplayAsNode::class,
                    ParameterNode::class,
                ]
            );
        });

        $container->register(ExpressionFunctions::class, function () {
            return new ExpressionFunctions([
                'mode' => new ModeFunction(),
                'mean' => new MeanFunction(),
                'min' => new MinFunction(),
                'max' => new MaxFunction(),
                'stdev' => new StDevFunction(),
                'rstdev' => new RStDevFunction(),
                'variance' => new VarianceFunction(),
                'percent_diff' => new PercentDifferenceFunction(),
                'format' => new FormatFunction()
            ]);
        });

        $container->register(Lexer::class, function (Container $container) {
            return new Lexer(DisplayAsPrinter::supportedUnitNames());
        });

        $container->register(ExpressionLanguage::class, function (Container $container) {
            return new MemoisedExpressionLanguage(
                new RealExpressionLanguage($container->get(Lexer::class), $container->get(Parser::class))
            );
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefault(self::PARAM_SYNTAX_HIGHLIGHTING, true);
        $resolver->setAllowedTypes(self::PARAM_SYNTAX_HIGHLIGHTING, 'bool');
    }
}
