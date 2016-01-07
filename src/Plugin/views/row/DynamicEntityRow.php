<?php

/**
 * @file
 * Contains \Drupal\views\Plugin\views\row\DynamicEntityRow.
 */

namespace Drupal\views_dynamic_entity_row\Plugin\views\row;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views_dynamic_entity_row\Entity\Render\DynamicViewModeEntityTranslationRenderTrait;
use Drupal\views\Plugin\views\row\EntityRow;

/**
 * Dynamic entity row plugin.
 *
 * @ViewsRow(
 *   id = "dynamic_entity",
 *   deriver = "Drupal\views_dynamic_entity_row\Plugin\Derivative\ViewsDynamicEntityRow"
 * )
 */
class DynamicEntityRow extends EntityRow {
  use DynamicViewModeEntityTranslationRenderTrait;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['view_mode']['#title'] = new TranslatableMarkup('Default view mode');
  }

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    $options = \Drupal::entityManager()->getViewModeOptions($this->entityTypeId);

    if (isset($options[$this->options['view_mode']])) {
      return $this->t('Autodetect (default: @default)', [
        '@default' => $options[$this->options['view_mode']],
      ]);
    }
    else {
      return $this->t('Autodetect (default not set)');
    }
  }

}
