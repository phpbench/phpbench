<?php

namespace PhpBench\Extension;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Console\Command\EvaluateCommand;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Expression\Ast\ArithmeticOperatorNode;
use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\Ast\TolerableNode;
use PhpBench\Expression\ColorMap;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Evaluator\MainEvaluator;
use PhpBench\Expression\Evaluator\PrettyErrorEvaluator;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Expression\ExpressionFunctions;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\ExpressionLanguage\MemoisedExpressionLanguage;
use PhpBench\Expression\ExpressionLanguage\RealExpressionLanguage;
use PhpBench\Expression\Func\CoalesceFunction;
use PhpBench\Expression\Func\ContainsFunction;
use PhpBench\Expression\Func\CountFunction;
use PhpBench\Expression\Func\DisplayAsTimeFunction;
use PhpBench\Expression\Func\FirstFunction;
use PhpBench\Expression\Func\FormatFunction;
use PhpBench\Expression\Func\FrameFunction;
use PhpBench\Expression\Func\JoinFunction;
use PhpBench\Expression\Func\LabelFunction;
use PhpBench\Expression\Func\MaxFunction;
use PhpBench\Expression\Func\MeanFunction;
use PhpBench\Expression\Func\MinFunction;
use PhpBench\Expression\Func\ModeFunction;
use PhpBench\Expression\Func\PercentDifferenceFunction;
use PhpBench\Expression\Func\RStDevFunction;
use PhpBench\Expression\Func\StDevFunction;
use PhpBench\Expression\Func\SumFunction;
use PhpBench\Expression\Func\VarianceFunction;
use PhpBench\Expression\Lexer;
use PhpBench\Expression\NodeEvaluator;
use PhpBench\Expression\NodeEvaluator\AccessEvaluator;
use PhpBench\Expression\NodeEvaluator\ArgumentListEvaluator;
use PhpBench\Expression\NodeEvaluator\ArithmeticOperatorEvaluator;
use PhpBench\Expression\NodeEvaluator\ComparisonEvaluator;
use PhpBench\Expression\NodeEvaluator\ConcatEvaluator;
use PhpBench\Expression\NodeEvaluator\DisplayAsEvaluator;
use PhpBench\Expression\NodeEvaluator\FunctionEvaluator;
use PhpBench\Expression\NodeEvaluator\ListEvaluator;
use PhpBench\Expression\NodeEvaluator\LogicalOperatorEvaluator;
use PhpBench\Expression\NodeEvaluator\NullSafeEvaluator;
use PhpBench\Expression\NodeEvaluator\ParenthesisEvaluator;
use PhpBench\Expression\NodeEvaluator\PhpValueEvaluator;
use PhpBench\Expression\NodeEvaluator\TolerableEvaluator;
use PhpBench\Expression\NodeEvaluator\ValueWithUnitEvaluator;
use PhpBench\Expression\NodeEvaluator\VariableEvaluator;
use PhpBench\Expression\NodeEvaluators;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\NodePrinter\ArgumentListPrinter;
use PhpBench\Expression\NodePrinter\ArrayAccessPrinter;
use PhpBench\Expression\NodePrinter\BinaryOperatorPrinter;
use PhpBench\Expression\NodePrinter\BooleanPrinter;
use PhpBench\Expression\NodePrinter\ComparisonPrinter;
use PhpBench\Expression\NodePrinter\ConcatenatedNodePrinter;
use PhpBench\Expression\NodePrinter\ConcatPrinter;
use PhpBench\Expression\NodePrinter\DataFramePrinter;
use PhpBench\Expression\NodePrinter\DisplayAsPrinter;
use PhpBench\Expression\NodePrinter\FunctionPrinter;
use PhpBench\Expression\NodePrinter\HighlightingNodePrinter;
use PhpBench\Expression\NodePrinter\LabelPrinter;
use PhpBench\Expression\NodePrinter\ListPrinter;
use PhpBench\Expression\NodePrinter\NullPrinter;
use PhpBench\Expression\NodePrinter\NullSafePrinter;
use PhpBench\Expression\NodePrinter\NumberPrinter;
use PhpBench\Expression\NodePrinter\ParameterPrinter;
use PhpBench\Expression\NodePrinter\ParenthesisPrinter;
use PhpBench\Expression\NodePrinter\PercentageDifferencePrinter;
use PhpBench\Expression\NodePrinter\PercentagePrinter;
use PhpBench\Expression\NodePrinter\RelativeDeviationPrinter;
use PhpBench\Expression\NodePrinter\StringPrinter;
use PhpBench\Expression\NodePrinter\TolerablePrinter;
use PhpBench\Expression\NodePrinter\UnitPrinter;
use PhpBench\Expression\NodePrinter\UnrepresentableValuePrinter;
use PhpBench\Expression\NodePrinter\ValueWithUnitPrinter;
use PhpBench\Expression\NodePrinter\VariablePrinter;
use PhpBench\Expression\NodePrinters;
use PhpBench\Expression\Parselet\ArithmeticOperatorParselet;
use PhpBench\Expression\Parselet\ArrayAccessParselet;
use PhpBench\Expression\Parselet\BooleanParselet;
use PhpBench\Expression\Parselet\ComparisonParselet;
use PhpBench\Expression\Parselet\ConcatParselet;
use PhpBench\Expression\Parselet\DisplayAsParselet;
use PhpBench\Expression\Parselet\FloatParselet;
use PhpBench\Expression\Parselet\FunctionParselet;
use PhpBench\Expression\Parselet\IntegerParselet;
use PhpBench\Expression\Parselet\ListParselet;
use PhpBench\Expression\Parselet\LogicalOperatorParselet;
use PhpBench\Expression\Parselet\NullParselet;
use PhpBench\Expression\Parselet\NullSafeParselet;
use PhpBench\Expression\Parselet\ParenthesisParselet;
use PhpBench\Expression\Parselet\PercentageParselet;
use PhpBench\Expression\Parselet\PropertyAccessParselet;
use PhpBench\Expression\Parselet\StringParselet;
use PhpBench\Expression\Parselet\TolerableParselet;
use PhpBench\Expression\Parselet\ValueWithUnitParselet;
use PhpBench\Expression\Parselet\VariableParselet;
use PhpBench\Expression\Parselets;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Precedence;
use PhpBench\Expression\Printer;
use PhpBench\Expression\Printer\BareValuePrinter;
use PhpBench\Expression\Printer\EvaluatingPrinter;
use PhpBench\Expression\Printer\NormalizingPrinter;
use PhpBench\Expression\Printer\UnderlinePrinterFactory;
use PhpBench\Expression\Theme\EightColorTheme;
use PhpBench\Expression\Theme\SolarizedTheme;
use PhpBench\Expression\Token;
use PhpBench\Util\TimeUnit;
use RuntimeException;
use Symfony\Component\Console\Formatter\NullOutputFormatter;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpressionExtension implements ExtensionInterface
{
    private const PREFIX_BINARY = 'binary';
    private const PREFIX_DECIMAL = 'decimal';

    public const PARAM_SYNTAX_HIGHLIGHTING = 'expression.syntax_highlighting';
    public const PARAM_THEME = 'expression.theme';
    public const PARAM_MEMORY_UNIT_PREFIX = 'expression.memory_unit_prefix';
    public const PARAM_STRIP_TAILING_ZEROS = 'expression.strip_tailing_zeros';

    public const SERVICE_PLAIN_PRINTER = 'expression.printer.plain';
    public const SERVICE_BARE_PRINTER = 'expression.printer.bare';

    public const TAG_THEME = 'expression.theme';

    public const THEME_BASIC = 'basic';
    public const THEME_SOLARIZED = 'solarized';

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
                $container->get(EvaluatingPrinter::class),
                $container->get(ConsoleExtension::SERVICE_OUTPUT_STD)
            );
        }, [
            ConsoleExtension::TAG_CONSOLE_COMMAND => []
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
                    new StringParselet(),
                    new VariableParselet(),
                    new NullParselet(),
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
                    new ArrayAccessParselet(),
                    new PropertyAccessParselet(),
                    new NullSafeParselet(),
                ]),
                Parselets::fromSuffixParselets([
                    new ValueWithUnitParselet(),
                    new PercentageParselet(),
                ])
            );
        });

        $container->register(NodeEvaluator::class, function (Container $container) {
            $evaluators = new NodeEvaluators([
                new ArgumentListEvaluator(),
                new ArithmeticOperatorEvaluator(),
                new LogicalOperatorEvaluator(),
                new FunctionEvaluator($container->get(ExpressionFunctions::class)),
                new ListEvaluator(),
                new ValueWithUnitEvaluator(),
                new ParenthesisEvaluator(),
                new ComparisonEvaluator(),
                new TolerableEvaluator(),
                new DisplayAsEvaluator(),
                new VariableEvaluator(),
                new ConcatEvaluator(),
                new PhpValueEvaluator(),
                new AccessEvaluator(),
                new NullSafeEvaluator(),
            ]);

            return $evaluators;
        });

        $container->register(Evaluator::class, function (Container $container) {
            return new PrettyErrorEvaluator(
                new MainEvaluator($container->get(NodeEvaluator::class)),
                $container->get(self::SERVICE_PLAIN_PRINTER),
                new UnderlinePrinterFactory($container->get(NodePrinters::class))
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
                $container->get(ColorMap::class)
            );
        });

        $container->register(ColorMap::class, function (Container $container) {
            $themes = [];
            $selected = $container->getParameter(self::PARAM_THEME);

            foreach ($container->getServiceIdsForTag(self::TAG_THEME) as $serviceId => $attrs) {
                $name = $attrs['name'];

                if ($name === $selected) {
                    return $container->get($serviceId);
                }
                $themes[] = $name;
            }

            throw new RuntimeException(sprintf(
                'Unknown theme "%s", known themes: "%s"',
                $selected,
                implode('", "', $themes)
            ));
        });

        $container->register(SolarizedTheme::class, function (Container $container) {
            // this theme uses true colors which is only supported in SF5
            return class_exists(NullOutputFormatter::class) ? new SolarizedTheme() : new EightColorTheme();
        }, [
            self::TAG_THEME => [
                'name' => self::THEME_SOLARIZED,
            ],
        ]);

        $container->register(EightColorTheme::class, function (Container $container) {
            return new EightColorTheme();
        }, [
            self::TAG_THEME => [
                'name' => self::THEME_BASIC,
            ],
        ]);

        $container->register(NodePrinters::class, function (Container $container) {
            return new NodePrinters([
                new ConcatenatedNodePrinter(),
                new ArgumentListPrinter(),
                new RelativeDeviationPrinter(),
                new UnitPrinter(),
                new NumberPrinter(),
                new BinaryOperatorPrinter(),
                new ComparisonPrinter(),
                new BooleanPrinter(),
                new FunctionPrinter(),
                new ListPrinter(),
                new ParenthesisPrinter(),
                new TolerablePrinter(),
                new PercentagePrinter(),
                new ValueWithUnitPrinter(),
                new DisplayAsPrinter(
                    $container->get(TimeUnit::class),
                    $container->getParameter(self::PARAM_MEMORY_UNIT_PREFIX) === self::PREFIX_BINARY,
                    $container->getParameter(self::PARAM_STRIP_TAILING_ZEROS)
                ),
                new ParameterPrinter(),
                new StringPrinter(),
                new LabelPrinter(),
                new ConcatPrinter(),
                new PercentageDifferencePrinter(),
                new NullPrinter(),
                new UnrepresentableValuePrinter(),
                new VariablePrinter(),
                new ArrayAccessPrinter(),
                new NullSafePrinter(),
                new DataFramePrinter(),
            ]);
        });

        $container->register(Printer::class, function (Container $container) {
            return new NormalizingPrinter($container->get(NodePrinter::class));
        });

        $container->register(self::SERVICE_PLAIN_PRINTER, function (Container $container) {
            return new NormalizingPrinter($container->get(NodePrinters::class));
        });
        $container->register(self::SERVICE_BARE_PRINTER, function (Container $container) {
            return new BareValuePrinter();
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
                'format' => new FormatFunction(),
                'join' => new JoinFunction(),
                'first' => new FirstFunction(),
                'coalesce' => new CoalesceFunction(),
                'display_as_time' => new DisplayAsTimeFunction(),
                'label' => new LabelFunction(),
                'count' => new CountFunction(),
                'sum' => new SumFunction(),
                'frame' => new FrameFunction(),
                'contains' => new ContainsFunction(),
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

        // @deprecated
        $container->register(ExpressionEvaluator::class, function (Container $container) {
            return new ExpressionEvaluator(
                $container->get(ExpressionLanguage::class),
                $container->get(Evaluator::class),
                $container->get(Printer::class)
            );
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_SYNTAX_HIGHLIGHTING => true,
            self::PARAM_THEME => self::THEME_SOLARIZED,
            self::PARAM_MEMORY_UNIT_PREFIX => self::PREFIX_DECIMAL,
            self::PARAM_STRIP_TAILING_ZEROS => false,
        ]);
        $resolver->setAllowedTypes(self::PARAM_SYNTAX_HIGHLIGHTING, 'bool');
        $resolver->setAllowedTypes(self::PARAM_THEME, 'string');
        $resolver->setAllowedTypes(self::PARAM_MEMORY_UNIT_PREFIX, ['string']);
        $resolver->setAllowedTypes(self::PARAM_STRIP_TAILING_ZEROS, ['bool']);
        $resolver->setAllowedValues(self::PARAM_MEMORY_UNIT_PREFIX, [self::PREFIX_BINARY, self::PREFIX_DECIMAL]);

        SymfonyOptionsResolverCompat::setInfos($resolver, [
            self::PARAM_SYNTAX_HIGHLIGHTING => 'Enable syntax highlighting',
            self::PARAM_THEME => 'Select a theme to use',
            self::PARAM_STRIP_TAILING_ZEROS => 'Do not display meaningless zeros after the decimal place',
            self::PARAM_MEMORY_UNIT_PREFIX => sprintf(
                'By default use ``%s`` (1kb = 1000 bytes) or ``%s`` (1KiB = 1024 bytes) when displaying memory',
                self::PREFIX_DECIMAL,
                self::PREFIX_BINARY,
            ),
        ]);
    }
}
