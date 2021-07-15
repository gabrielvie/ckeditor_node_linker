/**
 * @file
 * CMKEditor Entity Link plugin. Based on core link plugin.
 */

(function ($, Drupal, drupalSettings, CKEDITOR) {
  'use strict';

  CKEDITOR.plugins.add('nodelink', {
    init: function (editor) {
      console.log("chegou aqui");
      editor.addCommand('nodelink', {
        allowedContent: new CKEDITOR.style({
          element: 'a',
          styles: {},
          attributes: {
            '!href': ''
          }
        }),
        requiredContent: new CKEDITOR.style({
          element: 'a',
          styles: {},
          attributes: {
            href: ''
          }
        }),
        modes: { wysiwyg: 1 },
        canUndo: true,
        exec: function (editor) {
          console.log(editor);

          var dialogUrl = Drupal.url(`ckeditor/node-link/dialog/${editor.config.drupal.format}`)
          var dialogValues = {};

          var dialogCallback = function (values) {
            console.log(values);
          }

          var dialogSettings = {
            title: editor.config.NodeLink_dialogTitleAdd,
            dialogClass: 'editor-link-dialog',
          };

          Drupal.ckeditor.openDialog(
            editor,
            dialogUrl,
            dialogValues,
            dialogCallback,
            dialogSettings
          );
        }
      });

      if (editor.ui.addButton) {
        editor.ui.addButton('NodeLinker', {
          label: Drupal.t('Node Linker'),
          command: 'nodelinker',
          icon: `${this.path}../images/link.png`
        });
      }
    }

  });
})(jQuery, Drupal, drupalSettings, CKEDITOR);
