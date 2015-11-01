<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This file contains functions which are to be used in XPath expressions.
 */
namespace PhpBench\Dom\functions;

/**
 * Convert a DOMNodeList of parameter elements to a JSON encoded string.
 *
 * @param \DOMNodeList|DOMElement[]
 *
 * @return string
 */
function parameters_to_json($parameterEls)
{
    return json_encode(parameters_to_array($parameterEls));
}

/**
 * Convert a DOMNodeList of parameter elements to an array.
 *
 * @param \DOMNodeList|DOMElement[]
 *
 * @return array
 */
function parameters_to_array($parameterEls)
{
    $array = array();
    foreach ($parameterEls as $parameterEl) {
        if (!$parameterEl instanceof \DOMElement) {
            continue;
        }

        if ($parameterEl->getAttribute('type') === 'collection') {
            $value = parameters_to_array($parameterEl->childNodes);
        } else {
            $value = $parameterEl->getAttribute('value');
        }

        $array[$parameterEl->getAttribute('name')] = $value;
    }

    return $array;
}

/**
 * Return the class name of the given fully qualified name.
 *
 * @param string $classFqn
 *
 * @return string
 */
function class_name($classFqn)
{
    $parts = explode('\\', $classFqn);
    end($parts);

    return current($parts);
}

/**
 * Concatenate the values of the given nodes using the given delimiter.
 *
 * @param string $delimiter
 * @param DOMNodeList|\DOMElement[] $list
 *
 * @return string
 */
function join_node_values($delimiter, $list)
{
    $els = array();
    foreach ($list as $el) {
        if (!$el instanceof \DOMNode) {
            continue;
        }

        $els[] = $el->nodeValue;
    }

    return implode($delimiter, $els);
}
