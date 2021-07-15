<?php

namespace Drupal\ckeditor_node_linker\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "node_linker" CKEditor plugin.
 *
 * @CKEditorPlugin(
 *   id = "node_linker",
 *   label = @Translation("Node Linker"),
 * )
 */
class NodeLinker extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'ckeditor_node_linker') . '/js/node_linker.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return [
      'core/drupal.ajax',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'NodeLinker_dialogTitleAdd' => $this->t('Add Node Linker'),
      'NodeLinker_dialogTitleEdit' => $this->t('Edit Node Linker'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    $image_path = drupal_get_path('module', 'ckeditor_node_linker') . '/images/link.png';
    return [
      'NodeLinker' => [
        'label' => $this->t('Node Linker'),
        'image' => $image_path,
      ],
    ];
  }
}
