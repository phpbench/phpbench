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

use PhpBench\Tabular\Tabular\Dom\values;

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

/**
 * Return a list of nodes which correspond to the given node list in the suite
 * with the given context name.
 *
 * @param \DOMNodeList $nodeList
 * @param string $suiteContext
 */
function suite($nodeList, $suiteContext)
{
    if (!$nodeList) {
        return;
    }

    $nodeList = _nodelist_to_array($nodeList);
    $ownerDocument = $nodeList[0]->ownerDocument;

    $xpath = new \DOMXPath($ownerDocument);
    $fragment = $ownerDocument->createDocumentFragment();

    foreach ($nodeList as $node) {
        $path = $node->getNodePath();

        $parts = explode('/', $path);
        $path = implode('/', array_slice($parts, 3, count($parts)));

        $path = '//suite[@context="' . $suiteContext .'"]/' . $path;
        $items = $xpath->query($path);

        if ($items->length === 0) {
            continue;
        }

        $fragment->appendChild($items->item(0)->cloneNode(true));
    }

    return $fragment;
}

/**
 * Convert a node list object into an array of nodes.
 */
function _nodelist_to_array($nodeList)
{
    if (is_array($nodeList)) {
        return $nodeList;
    }

    if (!$nodeList instanceof \DOMNodeList) {
        throw new \InvalidArgumentException(sprintf(
            'Expected array or \DOMNodeList, got "%s"',
            is_object($nodeList) ? get_class($nodeList) : gettype($nodeList)
        ));
    }

    $nodes = array();
    foreach ($nodeList as $node) {
        $nodes[] = $node;
    }

    return $nodes;
}

function kde_mode($nodeList)
{
    $values = values($nodeList);

    return Statistics::kdeMode($values);
}
