<?php

namespace PhpBench\Progress;

use PhpBench\Assertion\ParameterProvider;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\Printer;
use PhpBench\Model\Variant;

final class VariantSummaryFormatter implements VariantFormatter
{
    public const DEFAULT_FORMAT = <<<'EOT'
"Mo" ~ mode(variant.time.avg) as time ~ 
" (±" ~ rstdev(variant.time.avg) ~ "%)"
EOT
    ;
    public const BASELINE_FORMAT = <<<'EOT'
"[" ~ 
"Mo" ~ mode(variant.time.avg) as time ~
" <fg=magenta;bg=black>vs</> " ~ 
"Mo" ~ mode(baseline.time.avg) as time ~ "] " ~ 
percent_diff(mode(baseline.time.avg), mode(variant.time.avg), (rstdev(variant.time.avg) * 2)) ~
" (±" ~ rstdev(variant.time.avg) ~ "%)"
EOT
    ;

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

    public function __construct(
        ExpressionLanguage $parser,
        Printer $printer,
        ParameterProvider $paramProvider,
        string $format = self::DEFAULT_FORMAT,
        string $baselineFormat = self::BASELINE_FORMAT
    ) {
        $this->format = $format;
        $this->baselineFormat = $baselineFormat;
        $this->parser = $parser;
        $this->printer = $printer;
        $this->paramProvider = $paramProvider;
    }

    public function formatVariant(Variant $variant): string
    {
        $data = $this->paramProvider->provideFor($variant);
        $subjectFormat = $variant->getSubject()->getFormat();

        $node = $variant->getBaseline() ?
            $this->parser->parse($subjectFormat ?? $this->baselineFormat) :
            $this->parser->parse($subjectFormat ?? $this->format);

        return $this->printer->print($node, $data);
    }
}
