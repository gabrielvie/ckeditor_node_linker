<?php

/**
 * @file
 * Hooks for Node Linker Token module.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\node\Entity\Node;

/**
 * Implements hook_token_info().
 */
function ckeditor_node_linker_token_info() {
  // Adding to existng token type 'node'. It's provided by core so always there.
  $tokens['node']['link'] = [
    'name' => 'Link to a node',
    'description' => 'Use with arguments: [node:link:nid]',
  ];
  return ['tokens' => $tokens];
}

/**
 * Implements hook_tokens().
 */
function ckeditor_node_linker_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  if ($type !== 'node') {
    return [];
  }

  $replacements = [];
  foreach ($tokens as $token) {
    $query = "[node:link:";
    if (substr($token, 0, strlen($query)) !== $query) {
      continue;
    }

    $token_keys = str_replace(['[', ']'], '', $token);
    list($token_type, $node_field, $nid) = explode(':', $token_keys);

    if ($node_field == 'link') {
      /** @var \Drupal\node\NodeInterface $node */
      if ($node = Node::load($nid)) {
        $language_manager = \Drupal::languageManager();

        /** @var \Drupal\path_alias\AliasManager $path_alias_manager */
        $path_alias_manager = \Drupal::service('path_alias.manager');

        $node_path = "/node/$nid";
        $langcode = $language_manager->getCurrentLanguage()->getId();
        $path_alias = $path_alias_manager->getAliasByPath($node_path, $langcode);

        $replacements[$token] = $path_alias;
      }
    }
  }
  return $replacements;
}
