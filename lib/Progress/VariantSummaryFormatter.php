<?php

namespace PhpBench\Progress;

use Closure;
use PhpBench\Math\Statistics;
use PhpBench\Model\Variant;
use PhpBench\Util\TimeUnit;

final class VariantSummaryFormatter
{
    const DEFAULT_FORMAT = '%variant.mode% %time_unit% (±%variant.rstdev%%)';
    const BASELINE_FORMAT = '%variant.mode% vs. <baseline>%baseline.mode%</> (%time_unit%) (±%variant.rstdev%%) <%diff_format%>%percent_difference%%</>';

    const NOT_APPLICABLE = 'n/a';
    const FORMAT_NO_CHANGE = 'fg=cyan';
    const FORMAT_BAD_CHANGE = 'fg=red';
    const FORMAT_GOOD_CHANGE = 'fg=green';

    /**
     * @var string
     */
    private $format;

    /**
     * @var TimeUnit
     */
    private $timeUnit;

    /**
     * @var string
     */
    private $baselineFormat;

    public function __construct(TimeUnit $timeUnit, string $format = self::DEFAULT_FORMAT, string $baselineFormat = self::BASELINE_FORMAT)
    {
        $this->format = $format;
        $this->timeUnit = $timeUnit;
        $this->baselineFormat = $baselineFormat;
    }

    public function formatVariant(Variant $variant): string
    {
        $subject = $variant->getSubject();

        $timeUnit = $this->timeUnit->resolveDestUnit($variant->getSubject()->getOutputTimeUnit());
        $mode = $this->timeUnit->resolveMode($subject->getOutputMode());
        $precision = $this->timeUnit->resolvePrecision($subject->getOutputTimePrecision());

        $timeFormatter = function (float $time) use ($timeUnit, $mode, $precision): string {
            return $this->timeUnit->format($time, $timeUnit, $mode, $precision, false);
        };

        $tokens = [
            'time_unit' => $this->timeUnit->getDestSuffix($timeUnit),
            'variant.min' => self::NOT_APPLICABLE,
            'variant.max' => self::NOT_APPLICABLE,
            'variant.mean' => self::NOT_APPLICABLE,
            'variant.mode' => self::NOT_APPLICABLE,
            'variant.stdev' => self::NOT_APPLICABLE,
            'variant.rstdev' => self::NOT_APPLICABLE,
            'variant.variance' => self::NOT_APPLICABLE,
            'baseline.min' => self::NOT_APPLICABLE,
            'baseline.max' => self::NOT_APPLICABLE,
            'baseline.mean' => self::NOT_APPLICABLE,
            'baseline.mode' => self::NOT_APPLICABLE,
            'baseline.stdev' => self::NOT_APPLICABLE,
            'baseline.rstdev' => self::NOT_APPLICABLE,
            'baseline.variance' => self::NOT_APPLICABLE,
            'percent_difference' => self::NOT_APPLICABLE,
            'diff_format' => self::FORMAT_NO_CHANGE,
        ];

        $tokens = array_merge($tokens, $this->populateFromVariant($timeFormatter, 'variant', $variant));

        if ($variant->getBaseline()) {
            $tokens = array_merge($tokens, $this->getBaselineTokens($timeFormatter, $variant, $variant->getBaseline()));
        }

        return strtr($this->resolveFormat($variant), (array)array_combine(
            array_map(function (string $token) {
                return '%' . $token . '%';
            }, array_keys($tokens)),
            array_values($tokens)
        ));
    }

    /**
     * @return array<string,string>
     */
    private function populateFromVariant(Closure $f, string $prefix, Variant $variant): array
    {
        $stats = $variant->getStats();
        return [
            $prefix.'.min' => $f($stats->getMin()),
            $prefix.'.max' => $f($stats->getMax()),
            $prefix.'.mean' => $f($stats->getMean()),
            $prefix.'.mode' => $f($stats->getMode()),
            $prefix.'.stdev' => $f($stats->getStdev()),
            $prefix.'.rstdev' => number_format($stats->getRstdev(), 2),
            $prefix.'.variance' => $f($stats->getVariance()),
        ];
    }

    /**
     * @return array<string,string>
     */
    private function getBaselineTokens(Closure $timeFormatter, Variant $variant, Variant $baseline): array
    {
        $stats = $baseline->getStats();
        $tokens = $this->populateFromVariant($timeFormatter, 'baseline', $baseline);
        $diff = Statistics::percentageDifference($baseline->getStats()->getMode(), $variant->getStats()->getMode());

        $tokens['percent_difference'] = (function (float $diff) {
            $prefix = $diff > 0 ? '+' : '';
            return $prefix .number_format($diff, 2);
        })($diff);

        $tokens['diff_format'] = (function (float $diff, float $rstdev) {
            // difference falls within margin of error
            if (abs($diff) <= abs($rstdev)) {
                return self::FORMAT_NO_CHANGE;
            }

            if ($diff > 0) {
                return self::FORMAT_BAD_CHANGE;
            }
            return self::FORMAT_GOOD_CHANGE;
        })($diff, $stats->getRstdev());

        return $tokens;
    }

    private function resolveFormat(Variant $variant): string
    {
        if ($variant->getBaseline()) {
            return $this->baselineFormat;
        }

        return $this->format;
    }
}
