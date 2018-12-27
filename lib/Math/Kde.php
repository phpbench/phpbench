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

namespace PhpBench\Math;

/**
 * This class was ported from the Python scipy package.
 *
 * https://github.com/scipy/scipy/blob/master/scipy/stats/kde.py
 */

/**
 * Define classes for (uni/multi)-variate kernel density estimation.
 *
 * Currently, only Gaussian kernels are implemented.
 *
 * Original Python version by: Robert Kern
 *
 * Date: 2004-08-09
 *
 * Modified: 2005-02-10 by Robert Kern.
 *             Contributed to Scipy
 *           2005-10-07 by Robert Kern.
 *             Some fixes to match the new scipy_core
 *
 * Copyright 2004-2005 by Enthought, Inc.
 * Copyright 2003-2013 SciPy Developers.
 *   All rights reserved.
 */
class Kde
{
    /**
     * @var \Closure
     */
    private $coVarianceFactor;

    /**
     * @var array
     */
    private $dataset;

    /**
     * @var float
     */
    private $factor;

    /**
     * @var float
     */
    private $_dataInvCov;

    /**
     * @var float
     */
    private $_dataCovariance;

    /**
     * @var float
     */
    private $invCov;

    private $covariance;

    private $normFactor;

    /**
     * Representation of a kernel-density estimate using Gaussian kernels.
     *
     * Kernel density estimation is a way to estimate the probability density
     * function (PDF) of a random variable in a non-parametric way.
     *
     * `gaussian_kde` works for both uni-variate and multi-variate data.   It
     * includes automatic bandwidth determination.  The estimation works best for
     * a unimodal distribution; bimodal or multi-modal distributions tend to be
     * oversmoothed.
     *
     * Note:
     *
     *   Bandwidth selection strongly influences the estimate obtained from the KDE
     *   (much more so than the actual shape of the kernel).  Bandwidth selection
     *   can be done by a "rule of thumb", by cross-validation, by "plug-in
     *   methods" or by other means; see [3]_, [4]_ for reviews.  `gaussian_kde`
     *   uses a rule of thumb, the default is Scott's Rule.
     *   Scott's Rule [1]_, implemented as `scotts_factor`, is::
     *       n**(-1./(d+4)),
     *   with ``n`` the number of data points and ``d`` the number of dimensions.
     *   Silverman's Rule [2]_, implemented as `silverman_factor`, is::
     *       (n * (d + 2) / 4.)**(-1. / (d + 4)).
     *   Good general descriptions of kernel density estimation can be found in [1]_
     *   and [2]_, the mathematics for this multi-dimensional implementation can be
     *   found in [1]_.
     *
     * References
     *
     *   .. [1] D.W. Scott, "Multivariate Density Estimation: Theory, Practice, and
     *          Visualization", John Wiley & Sons, New York, Chicester, 1992.
     *   .. [2] B.W. Silverman, "Density Estimation for Statistics and Data
     *          Analysis", Vol. 26, Monographs on Statistics and Applied Probability,
     *          Chapman and Hall, London, 1986.
     *
     * @param array $dataset Array of univariate data points.
     * @param string $bwMethod : Either "scott", "silverman" or an explicit (float) value.
     */
    public function __construct(array $dataset, $bwMethod = null)
    {
        $this->dataset = $dataset;

        if (count($this->dataset) <= 1) {
            throw new \OutOfBoundsException('`dataset` input should have multiple elements.');
        }

        $this->setBandwidth($bwMethod);
    }

    /**
     * Evaluate the estimated pdf on a set of points.
     *
     * @param array $points 1-D array of points on to which we will map the kde
     *
     * @return array
     */
    public function evaluate(array $points)
    {
        $count = count($this->dataset);

        $bigger = count($points) > $count;

        if ($bigger) {
            $range = $count - 1;
        } else {
            $range = count($points) - 1;
        }

        $result = array_fill(0, count($points), 0);

        // loop over points
        foreach (range(0, $range) as $i) {
            if ($bigger) {
                $dataValue = $this->dataset[$i];
                $diff = array_map(function ($point) use ($dataValue) {
                    return $dataValue - $point;
                }, $points);
            } else {
                $diff = array_map(function ($v) use ($points, $i) {
                    return $v - $points[$i];
                }, $this->dataset);
            }

            // dot product (consider dedicated function)
            $invCov = $this->invCov;
            $tDiff = array_map(function ($v) use ($invCov) {
                return $invCov * $v;
            }, $diff);

            // multiply the two arrays
            $multiplied = [];

            foreach ($diff as $index => $value) {
                $multiplied[$index] = $diff[$index] * $tDiff[$index];
            }

            // numpy sum does nothing with our 2d array in PHP
            // $energy = array_sum(diff * tdiff, axis=0) / 2.0
            $energy = array_map(function ($v) {
                return exp(-($v / 2));
            }, $multiplied);

            if ($bigger) {
                $sum = $result;

                foreach ($sum as $index => $value) {
                    $sum[$index] = $sum[$index] + $energy[$index];
                }
                $result = $sum;
            } else {
                $result[$i] = array_sum($energy);
            }
        }

        $result = array_map(function ($v) {
            return $v / $this->normFactor;
        }, $result);

        return $result;
    }

    /**
     * Compute the estimator bandwidth with given method.
     *
     * The new bandwidth calculated after a call to `setBandwidth` is used
     * for subsequent evaluations of the estimated density.
     *
     * @param string $bwMethod Either "scott" or "silverman"
     */
    public function setBandwidth($bwMethod = null)
    {
        if ($bwMethod == 'scott' || null === $bwMethod) {
            $this->coVarianceFactor = function () {
                return pow(count($this->dataset), -1. / (5));
            };
        } elseif ($bwMethod == 'silverman') {
            $this->coVarianceFactor = function () {
                return pow(count($this->dataset) * (3.0) / 4.0, -1. / (5));
            };
        } elseif (is_numeric($bwMethod)) {
            $this->coVarianceFactor = function () use ($bwMethod) {
                return $bwMethod;
            };
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Unknown bandwidth method "%s"',
                $bwMethod
            ));
        }

        $this->computeCovariance();
    }

    /**
     * Computes the covariance matrix for each Gaussian kernel using
     * coVarianceFactor().
     */
    private function computeCovariance()
    {
        $factorCallable = $this->coVarianceFactor;
        $this->factor = $factorCallable();

        // Cache covariance and inverse covariance of the data
        if (null === $this->_dataInvCov) {
            // original used the numpy.cov function.
            $this->_dataCovariance = pow(Statistics::stdev($this->dataset, true), 2);

            //$this->_dataInvCov = 1/ linalg.inv($this->_dataCovariance)
            $this->_dataInvCov = 1 / $this->_dataCovariance;
        }

        $this->covariance = $this->_dataCovariance * pow($this->factor, 2);
        $this->invCov = $this->_dataInvCov / pow($this->factor, 2);
        $this->normFactor = sqrt(2 * M_PI * $this->covariance) * count($this->dataset);
    }
}
