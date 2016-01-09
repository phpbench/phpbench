<?php

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
 * Written by: Robert Kern
 *
 * Date: 2004-08-09
 *
 * Modified: 2005-02-10 by Robert Kern.
 *             Contributed to Scipy
 *           2005-10-07 by Robert Kern.
 *             Some fixes to match the new scipy_core
 *
 * Copyright 2004-2005 by Enthought, Inc.
 */
class Kde
{
    /**
     * Computes the coefficient (`kde.factor`) that
     * multiplies the data covariance matrix to obtain the kernel covariance
     * matrix. The default is `scotts_factor`.  A subclass can overwrite this
     * method to provide a different method, or set it through a call to
     * `kde.set_bandwidth`
     *
     * @var Closure
     */
    private $covariance_factor;

    private $dataset;

    private $factor;

    private $_data_inv_cov;

    private $_data_covariance;

    private $inv_cov;

    /**
     * Representation of a kernel-density estimate using Gaussian kernels.
     * Kernel density estimation is a way to estimate the probability density
     * function (PDF) of a random variable in a non-parametric way.
     * `gaussian_kde` works for both uni-variate and multi-variate data.   It
     * includes automatic bandwidth determination.  The estimation works best for
     * a unimodal distribution; bimodal or multi-modal distributions tend to be
     * oversmoothed.
     *
     * Parameters
     * ----------
     * dataset : array_like
     *     Datapoints to estimate from. In case of univariate data this is a 1-D
     *     array, otherwise a 2-D array with shape (# of dims, # of data).
     * bw_method : str, scalar or callable, optional
     *     The method used to calculate the estimator bandwidth.  This can be
     *     'scott', 'silverman', a scalar constant or a callable.  If a scalar,
     *     this will be used directly as `kde.factor`.  If a callable, it should
     *     take a `gaussian_kde` instance as only parameter and return a scalar.
     *     If null (default), 'scott' is used.  See Notes for more details.
     *
     * Attributes
     * ----------
     * dataset : ndarray
     *     The dataset with which `gaussian_kde` was initialized.
     * d : int
     *     Number of dimensions.
     * n : int
     *     Number of datapoints.
     * factor : float
     *     The bandwidth factor, obtained from `kde.covariance_factor`, with which
     *     the covariance matrix is multiplied.
     * covariance : ndarray
     *     The covariance matrix of `dataset`, scaled by the calculated bandwidth
     *     (`kde.factor`).
     * inv_cov : ndarray
     *     The inverse of `covariance`.
     *
     * Methods
     * -------
     *
     * evaluate
     * __call__
     * integrate_gaussian
     * integrate_box_1d
     * integrate_box
     * integrate_kde
     * pdf
     * logpdf
     * resample
     * set_bandwidth
     * covariance_factor
     *
     * Notes
     * -----
     * Bandwidth selection strongly influences the estimate obtained from the KDE
     * (much more so than the actual shape of the kernel).  Bandwidth selection
     * can be done by a "rule of thumb", by cross-validation, by "plug-in
     * methods" or by other means; see [3]_, [4]_ for reviews.  `gaussian_kde`
     * uses a rule of thumb, the default is Scott's Rule.
     * Scott's Rule [1]_, implemented as `scotts_factor`, is::
     *     n**(-1./(d+4)),
     * with ``n`` the number of data points and ``d`` the number of dimensions.
     * Silverman's Rule [2]_, implemented as `silverman_factor`, is::
     *     (n * (d + 2) / 4.)**(-1. / (d + 4)).
     * Good general descriptions of kernel density estimation can be found in [1]_
     * and [2]_, the mathematics for this multi-dimensional implementation can be
     * found in [1]_.
     *
     * References
     * ----------
     * .. [1] D.W. Scott, "Multivariate Density Estimation: Theory, Practice, and
     *        Visualization", John Wiley & Sons, New York, Chicester, 1992.
     * .. [2] B.W. Silverman, "Density Estimation for Statistics and Data
     *        Analysis", Vol. 26, Monographs on Statistics and Applied Probability,
     *        Chapman and Hall, London, 1986.
     * .. [3] B.A. Turlach, "Bandwidth Selection in Kernel Density Estimation: A
     *        Review", CORE and Institut de Statistique, Vol. 19, pp. 1-33, 1993.
     * .. [4] D.M. Bashtannyk and R.J. Hyndman, "Bandwidth selection for kernel
     *        conditional density estimation", Computational Statistics & Data
     *        Analysis, Vol. 36, pp. 279-298, 2001.
     *
     * Examples
     * --------
     *
     * Generate some random two-dimensional data:
     * >>> from scipy import stats
     * >>> def measure(n):
     * ...     "Measurement model, return two coupled measurements."
     * ...     m1 = np.random.normal(size=n)
     * ...     m2 = np.random.normal(scale=0.5, size=n)
     * ...     return m1+m2, m1-m2
     * >>> m1, m2 = measure(2000)
     * >>> xmin = m1.min()
     * >>> xmax = m1.max()
     * >>> ymin = m2.min()
     * >>> ymax = m2.max()
     * Perform a kernel density estimate on the data:
     * >>> X, Y = np.mgrid[xmin:xmax:100j, ymin:ymax:100j]
     * >>> positions = np.vstack([X.ravel(), Y.ravel()])
     * >>> values = np.vstack([m1, m2])
     * >>> kernel = stats.gaussian_kde(values)
     * >>> Z = np.reshape(kernel(positions).T, X.shape)
     * Plot the results:
     * >>> import matplotlib.pyplot as plt
     * >>> fig, ax = plt.subplots()
     * >>> ax.imshow(np.rot90(Z), cmap=plt.cm.gist_earth_r,
     * ...           extent=[xmin, xmax, ymin, ymax])
     * >>> ax.plot(m1, m2, 'k.', markersize=2)
     * >>> ax.set_xlim([xmin, xmax])
     * >>> ax.set_ylim([ymin, ymax])
     * >>> plt.show()
     * """
     */
    public function __construct(array $dataset, $bw_method = null)
    {
        $this->dataset = $dataset;
        if (count($this->dataset) <= 1) {
            throw new \OutOfBoundsException('`dataset` input should have multiple elements.');
        }

        $this->set_bandwidth($bw_method);
    }

    /**
     * Evaluate the estimated pdf on a set of points.
     * Parameters
     * ----------
     * points : (# of dimensions, # of points)-array
     *     Alternatively, a (# of dimensions,) vector can be passed in and
     *     treated as a single point.
     * Returns
     * -------
     * values : (# of points,)-array
     *     The values at each point.
     * Raises
     * ------
     * ValueError : if the dimensionality of the input points is different than
     *              the dimensionality of the KDE.
     */
    public function evaluate(array $points)
    {
        $count = count($this->dataset);

        # loop over points
        foreach(range(0, $count - 1) as $i) {
            //$diff = $this->dataset - array_slice(points[:, i, newaxis]
            $diff = array_map(function ($v) use ($points, $i) {
                return $v - $points[$i];
            }, $this->dataset);

            // dot product (consider dedicated function)
            $inv_cov = $this->inv_cov;
            $tDiff = array_map(function ($v) use ($inv_cov) {
                return $inv_cov * $v;
            }, $diff);

            // multiply the two arrays
            $multiplied = array();

            foreach ($diff as $index => $value) {
               $multiplied[$index] = $diff[$index] * $tDiff[$index];
            }

            // numpy sum does nothing with our 2d array in PHP
            // $energy = array_sum(diff * tdiff, axis=0) / 2.0
            $energy = array_map(function ($v) { return exp(-($v / 2)); }, $multiplied);
            $result[$i] = array_sum($energy);
        }


        $result = array_map(function ($v) { return $v / $this->_norm_factor; }, $result);

        return $result;
    }

    public function __invoke(array $points)
    {
        $this->evaluate($points);
    }

    /**
     * Compute the estimator bandwidth with given method.
     * The new bandwidth calculated after a call to `set_bandwidth` is used
     * for subsequent evaluations of the estimated density.
     * Parameters
     * ----------
     * bw_method : str, scalar or callable, optional
     *     The method used to calculate the estimator bandwidth.  This can be
     *     'scott', 'silverman', a scalar constant or a callable.  If a
     *     scalar, this will be used directly as `kde.factor`.  If a callable,
     *     it should take a `gaussian_kde` instance as only parameter and
     *     return a scalar.  If null (default), nothing happens; the current
     *     `kde.covariance_factor` method is kept.
     * Notes
     * -----
     * .. versionadded:: 0.11
     * Examples
     * --------
     * >>> import scipy.stats as stats
     * >>> x1 = np.array([-7, -5, 1, 4, 5.])
     * >>> kde = stats.gaussian_kde(x1)
     * >>> xs = np.linspace(-10, 10, num=50)
     * >>> y1 = kde(xs)
     * >>> kde.set_bandwidth(bw_method='silverman')
     * >>> y2 = kde(xs)
     * >>> kde.set_bandwidth(bw_method=kde.factor / 3.)
     * >>> y3 = kde(xs)
     * >>> import matplotlib.pyplot as plt
     * >>> fig, ax = plt.subplots()
     * >>> ax.plot(x1, np.ones(x1.shape) / (4. * x1.size), 'bo',
     * ...         label='Data points (rescaled)')
     * >>> ax.plot(xs, y1, label='Scott (default)')
     * >>> ax.plot(xs, y2, label='Silverman')
     * >>> ax.plot(xs, y3, label='Const (1/3 * Silverman)')
     * >>> ax.legend()
     * >>> plt.show()
     */
    public function set_bandwidth($bw_method = null)
    {
        if ($bw_method == 'scott' || null === $bw_method) {
            $this->covariance_factor = function () {
                return pow(count($this->dataset), -1. / (5));
            };
        } elseif ($bw_method == 'silverman') {
            $this->covariance_factor = function () {
                return pow(count($this->dataset) * (3.0) /4.0, -1. / (5));
            };
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Unknown bw_method "%s"',
                $bw_method
            ));
        }

        $this->_compute_covariance();
    }

    /**
     * Computes the covariance matrix for each Gaussian kernel using
     * covariance_factor().
     */
    private function _compute_covariance()
    {
        $factorCallable = $this->covariance_factor;
        $this->factor = $factorCallable();

        # Cache covariance and inverse covariance of the data
        if (null === $this->_data_inv_cov) {
            // original used the numpy.cov function.
            $this->_data_covariance = pow(Statistics::stdev($this->dataset, true), 2);

            //$this->_data_inv_cov = 1/ linalg.inv($this->_data_covariance)
            $this->_data_inv_cov = 1 / $this->_data_covariance;
        }


        $this->covariance = $this->_data_covariance * $this->factor ** 2;
        $this->inv_cov = $this->_data_inv_cov / $this->factor ** 2;
        //$this->_norm_factor = sqrt(linalg.det(2*pi*$this->covariance)) * $this->n
        $this->_norm_factor = sqrt(2* M_PI * $this->covariance) * count($this->dataset);
    }


    /**
    public function integrate_gaussian(mean, cov):
        """
        Multiply estimated density by a multivariate Gaussian and integrate
        over the whole space.
        Parameters
        ----------
        mean : aray_like
            A 1-D array, specifying the mean of the Gaussian.
        cov : array_like
            A 2-D array, specifying the covariance matrix of the Gaussian.
        Returns
        -------
        result : scalar
            The value of the integral.
        Raises
        ------
        ValueError :
            If the mean or covariance of the input Gaussian differs from
            the KDE's dimensionality.
        """
        mean = atleast_1d(squeeze(mean))
        cov = atleast_2d(cov)

        if mean.shape != ($this->d,):
            raise ValueError("mean does not have dimension %s" % $this->d)
        if cov.shape != ($this->d, $this->d):
            raise ValueError("covariance does not have dimension %s" % $this->d)

        # make mean a column vector
        mean = mean[:, newaxis]

        sum_cov = $this->covariance + cov

        # This will raise LinAlgError if the new cov matrix is not s.p.d
        # cho_factor returns (ndarray, bool) where bool is a flag for whether
        # or not ndarray is upper or lower triangular
        sum_cov_chol = linalg.cho_factor(sum_cov)

        diff = $this->dataset - mean
        tdiff = linalg.cho_solve(sum_cov_chol, diff)

        sqrt_det = np.prod(np.diagonal(sum_cov_chol[0]))
        norm_const = power(2 * pi, sum_cov.shape[0] / 2.0) * sqrt_det

        energies = sum(diff * tdiff, axis=0) / 2.0
        result = sum(exp(-energies), axis=0) / norm_const / $this->n

        return result

    public function integrate_box_1d(low, high):
        """
        Computes the integral of a 1D pdf between two bounds.
        Parameters
        ----------
        low : scalar
            Lower bound of integration.
        high : scalar
            Upper bound of integration.
        Returns
        -------
        value : scalar
            The result of the integral.
        Raises
        ------
        ValueError
            If the KDE is over more than one dimension.
        """
        if $this->d != 1:
            raise ValueError("integrate_box_1d() only handles 1D pdfs")

        stdev = ravel(sqrt($this->covariance))[0]

        normalized_low = ravel((low - $this->dataset) / stdev)
        normalized_high = ravel((high - $this->dataset) / stdev)

        value = np.mean(special.ndtr(normalized_high) -
                        special.ndtr(normalized_low))
        return value

    public function integrate_box(low_bounds, high_bounds, maxpts=null):
        """Computes the integral of a pdf over a rectangular interval.
        Parameters
        ----------
        low_bounds : array_like
            A 1-D array containing the lower bounds of integration.
        high_bounds : array_like
            A 1-D array containing the upper bounds of integration.
        maxpts : int, optional
            The maximum number of points to use for integration.
        Returns
        -------
        value : scalar
            The result of the integral.
        """
        if maxpts is not null:
            extra_kwds = {'maxpts': maxpts}
        else:
            extra_kwds = {}

        value, inform = mvn.mvnun(low_bounds, high_bounds, $this->dataset,
                                  $this->covariance, **extra_kwds)
        if inform:
            msg = ('An integral in mvn.mvnun requires more points than %s' %
                   ($this->d * 1000))
            warnings.warn(msg)

        return value

    public function integrate_kde(other):
        """
        Computes the integral of the product of this  kernel density estimate
        with another.
        Parameters
        ----------
        other : gaussian_kde instance
            The other kde.
        Returns
        -------
        value : scalar
            The result of the integral.
        Raises
        ------
        ValueError
            If the KDEs have different dimensionality.
        """
        if other.d != $this->d:
            raise ValueError("KDEs are not the same dimensionality")

        # we want to iterate over the smallest number of points
        if other.n < $this->n:
            small = other
            large = self
        else:
            small = self
            large = other

        sum_cov = small.covariance + large.covariance
        sum_cov_chol = linalg.cho_factor(sum_cov)
        result = 0.0
        for i in range(small.n):
            mean = small.dataset[:, i, newaxis]
            diff = large.dataset - mean
            tdiff = linalg.cho_solve(sum_cov_chol, diff)

            energies = sum(diff * tdiff, axis=0) / 2.0
            result += sum(exp(-energies), axis=0)

        sqrt_det = np.prod(np.diagonal(sum_cov_chol[0]))
        norm_const = power(2 * pi, sum_cov.shape[0] / 2.0) * sqrt_det

        result /= norm_const * large.n * small.n

        return result

    public function resample(size=null):
        """
        Randomly sample a dataset from the estimated pdf.
        Parameters
        ----------
        size : int, optional
            The number of samples to draw.  If not provided, then the size is
            the same as the underlying dataset.
        Returns
        -------
        resample : ($this->d, `size`) ndarray
            The sampled dataset.
        """
        if size is null:
            size = $this->n

        norm = transpose(multivariate_normal(zeros(($this->d,), float),
                         $this->covariance, size=size))
        indices = randint(0, $this->n, size=size)
        means = $this->dataset[:, indices]

        return means + norm



    public function pdf(x):
        """
        Evaluate the estimated pdf on a provided set of points.
        Notes
        -----
        This is an alias for `gaussian_kde.evaluate`.  See the ``evaluate``
        docstring for more details.
        """
        return $this->evaluate(x)

    public function logpdf(x):
        """
        Evaluate the log of the estimated pdf on a provided set of points.
        Notes
        -----
        See `gaussian_kde.evaluate` for more details; this method simply
        returns ``np.log(gaussian_kde.evaluate(x))``.
        """
        return np.log($this->evaluate(x))
     */
}
