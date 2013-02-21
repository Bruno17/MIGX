<?php
/**
 * @package blox
 * @subpackage build
 *
 * snippets for bloX package
 */
$snippets = array();

$snippets[1] = $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
	'id' => 1,
	'name' => 'bloX',
	'description' => 'Adds CSS or JS in a document (at the end of the head or the end of the body).',
	'snippet' => getSnippetContent($sources['snippets'] . 'snippet.bloX.php'),
		), '', true, true);
$properties = include $sources['properties'] . 'properties.blox.php';
$snippets[1]->setProperties($properties);
unset($properties);

return $snippets;