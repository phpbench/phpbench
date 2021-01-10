<?php

namespace PhpBench\Progress;

use Closure;
use PhpBench\Assertion\VariantAssertionResults;
use PhpBench\Math\Statistics;
use PhpBench\Model\Variant;
use PhpBench\Util\TimeUnit;

final class VariantSummaryFormatter
{
    const DEFAULT_FORMAT = '%variant.mode% %time_unit% (±%variant.rstdev%%)';
    const BASELINE_FORMAT = '= %variant.mode%%time_unit% vs %baseline.mode%%time_unit% (±%variant.rstdev%%) <%result_style%>%percent_difference%%</>';

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
     * @var TimeUnit
     */
    private $timeUnit;

    /**
     * @var string
     */
    private $baselineFormat;

    private static $defaultTokens = [
        'time_unit' => self::NOT_APPLICABLE,
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
        'result_style' => self::FORMAT_NEUTRAL,
    ];

    public function __construct(
        TimeUnit $timeUnit,
        string $format = self::DEFAULT_FORMAT,
        string $baselineFormat = self::BASELINE_FORMAT
    ) {
        $this->format = $format;
        $this->timeUnit = $timeUnit;
        $this->baselineFormat = $baselineFormat;
    }

    public function formatVariant(Variant $variant): string
    {
        $subject = $variant->getSubject();

        [ $timeUnit , $mode, $precision ] = [
           $this->timeUnit->resolveDestUnit($variant->getSubject()->getOutputTimeUnit()),
           $this->timeUnit->resolveMode($subject->getOutputMode()),
           $this->timeUnit->resolvePrecision($subject->getOutputTimePrecision()),
        ];

        $timeFormatter = function (float $time) use ($timeUnit, $mode, $precision): string {
            return $this->timeUnit->format($time, $timeUnit, $mode, $precision, false);
        };

        $tokens = array_merge(
            self::$defaultTokens,
            $this->populateFromVariant($timeFormatter, 'variant', $variant)
        );
        $tokens['time_unit'] = $this->timeUnit->getDestSuffix($timeUnit, $mode);

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

        $tokens['result_style'] = (function (VariantAssertionResults $results, float $diff) {
            if (!$results->count()) {
                return self::FORMAT_NONE;
            }

            if ($results->failures()->count()) {
                return self::FORMAT_FAILURE;
            }

            if ($results->tolerations()->count()) {
                return self::FORMAT_NEUTRAL;
            }

            return self::FORMAT_GOOD_CHANGE;
        })($variant->getAssertionResults(), $diff);

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
