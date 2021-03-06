<?php

namespace PhpBench\Progress;

use Closure;
use PhpBench\Assertion\ParameterProvider;
use PhpBench\Assertion\VariantAssertionResults;
use PhpBench\Expression\Ast\ConcatNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\Printer;
use PhpBench\Expression\SyntaxHighlighter;
use PhpBench\Math\Statistics;
use PhpBench\Model\Variant;
use PhpBench\Util\TimeUnit;

final class VariantSummaryFormatter
{
    const DEFAULT_FORMAT = 'mode(variant.time.avg) as ms ~ " (±" ~ rstdev(variant.time.avg) ~ "%)"';
    const BASELINE_FORMAT = 'mode(variant.time.avg) as ms ~" <fg=magenta;bg=black>vs</> " ~ mode(baseline.time.avg) as ms ~ " (±" ~ rstdev(variant.time.avg) ~ "%) " ~ percent_diff(mode(baseline.time.avg), mode(variant.time.avg))';
    const NOT_APPLICABLE = 'n/a';
    const FORMAT_NEUTRAL = 'result-neutral';
    const FORMAT_FAILURE = 'result-failure';
    const FORMAT_GOOD_CHANGE = 'result-good';
    const FORMAT_NONE = 'result-none';

    /**
     * @var string
     */
    private $format;

    /**
     * @var string
     */
    private $baselineFormat;

    /**
     * @var ExpressionLanguage
     */
    private $parser;

    /**
     * @var Printer
     */
    private $printer;

    /**
     * @var ParameterProvider
     */
    private $paramProvider;

    /**
     * @var SyntaxHighlighter
     */
    private $highlighter;

    public function __construct(
        ExpressionLanguage $parser,
        Printer $printer,
        ParameterProvider $paramProvider,
        SyntaxHighlighter $highlighter,
        string $format = self::DEFAULT_FORMAT,
        string $baselineFormat = self::BASELINE_FORMAT
    ) {
        $this->format = $format;
        $this->baselineFormat = $baselineFormat;
        $this->parser = $parser;
        $this->printer = $printer;
        $this->paramProvider = $paramProvider;
        $this->highlighter = $highlighter;
    }

    public function formatVariant(Variant $variant): string
    {
        $data = $this->paramProvider->provideFor($variant);
        $node = $this->parser->parse($this->baselineFormat);

        if ($node instanceof ConcatNode) {
            return implode('', array_map(function (Node $node) use ($data) {
                return trim($this->highlighter->highlight($this->printer->print($node, $data)), '"');
            }, $node->nodes()));
        }

    }
}
