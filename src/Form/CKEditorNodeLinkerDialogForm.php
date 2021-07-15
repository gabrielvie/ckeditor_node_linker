<?php

namespace Drupal\ckeditor_node_linker\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\BaseFormIdInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\Ajax\EditorDialogSave;
use Drupal\filter\Entity\FilterFormat;
use Drupal\node\Entity\NodeType;

/**
 * CKEditor dialog form.
 *
 * @package ckeditor_node_linker
 */
class CKEditorNodeLinkerDialogForm extends FormBase implements BaseFormIdInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ckeditor_node_linker_dialog';
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId() {
    return 'editor_link_dialog';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, FilterFormat $filter_format = NULL) {
    $form['#tree'] = TRUE;
    $form['#attached']['library'][] = 'editor/drupal.editor.dialog';
    $form['#prefix'] = '<div id="ckeditor-node-link-dialog-form">';
    $form['#suffix'] = '</div>';

    /** @var \Drupal\node\NodeTypeInterface[] $node_types */
    $node_types = \Drupal::entityTypeManager()
      ->getStorage('node_type')
      ->loadMultiple();

    $node_types = array_map(function (NodeType $node_type) {
      return $node_type->get('name');
    }, $node_types);

    $form['node_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Node Type'),
      '#options' => $node_types,
      '#default_value' => $node_types[0],
      '#required' => TRUE,
      '#size' => 1,
      '#ajax' => [
        'callback' => '::updateNodeTypeSettings',
        'effect' => 'fade',
      ],
    ];

    $node_type = $form_state->getValue('node_type') ?? array_key_first($node_types);

    $form['node'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Nodes'),
      '#required' => TRUE,
      '#prefix' => '<div id="node-id-wrapper">',
      '#suffix' => '</div>',
    ];

    if (!empty($node_type)) {
      $form['node']['#selection_settings']['target_bundles'] = [$node_type];
    }

    $form['actions'] = [
      '#type' => 'actions',
      'save_modal' => [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
        // No regular submit-handler. This form only works via JavaScript.
        '#submit' => [],
        '#ajax' => [
          'callback' => '::submitForm',
          'event' => 'click',
        ],
      ],
    ];

    return $form;
  }

  /**
   * Ajax callback to update the form fields which depend on node type.
   *
   * @param array $form
   *   The build form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response with updated options for the node type.
   */
  public function updateNodeTypeSettings(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // Update options for node type bundles.
    $response->addCommand(new ReplaceCommand(
      '#node-id-wrapper',
      $form['node']
    ));

    return $response;
  }

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Returns an AjaxResponse object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    if ($form_state->getErrors()) {
      unset($form['#prefix'], $form['#suffix']);
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $response->addCommand(new HtmlCommand('#ckeditor-node-link-dialog-form', $form));
    }
    else {
      /** @var \Drupal\node\NodeInterface $node */
      $node = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->load($form_state->getValue('node'));

      $values = [
        'attributes' => [
          'href' => "[node:link:{$node->id()}]",
        ] + $form_state->getValue('attributes', []),
      ];

      $response->addCommand(new EditorDialogSave($values));
      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }

}
