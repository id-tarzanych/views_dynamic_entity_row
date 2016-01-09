<?php

/**
 * @file
 * Contains \Drupal\views_dynamic_entity_row\Entity\Render\DynamicViewModeEntityTranslationRenderTrait.
 */

namespace Drupal\views_dynamic_entity_row\Entity\Render;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\views\Plugin\views\PluginBase;
use Drupal\views\ResultRow;

/**
 * Trait used to instantiate the view's entity translation dynamic renderer.
 */
trait DynamicViewModeEntityTranslationRenderTrait {

  /**
   * Returns the current renderer.
   *
   * @return \Drupal\views\Entity\Render\EntityTranslationRendererBase
   *   The configured renderer.
   */
  protected function getEntityTranslationRenderer() {
    if (!isset($this->entityTranslationRenderer)) {
      $view = $this->getView();
      $rendering_language = $view->display_handler->getOption('rendering_language');
      $langcode = NULL;
      $dynamic_renderers = array(
        '***LANGUAGE_entity_translation***' => 'DynamicViewModeTranslationLanguageRenderer',
        '***LANGUAGE_entity_default***' => 'DefaultLanguageRenderer',
      );
      if (isset($dynamic_renderers[$rendering_language])) {
        // Dynamic language set based on result rows or instance defaults.
        $renderer = $dynamic_renderers[$rendering_language];
      }
      else {
        if (strpos($rendering_language, '***LANGUAGE_') !== FALSE) {
          $langcode = PluginBase::queryLanguageSubstitutions()[$rendering_language];
        }
        else {
          // Specific langcode set.
          $langcode = $rendering_language;
        }
        $renderer = 'ConfigurableLanguageRenderer';
      }

      if ($renderer == 'DynamicViewModeTranslationLanguageRenderer') {
        $class = '\Drupal\views_dynamic_entity_row\Entity\Render\\' . $renderer;
      }
      else {
        $class = '\Drupal\views\Entity\Render\\' . $renderer;
      }
      $entity_type = $this->getEntityManager()->getDefinition($this->getEntityTypeId());
      $this->entityTranslationRenderer = new $class($view, $this->getLanguageManager(), $entity_type, $langcode);
    }

    return $this->entityTranslationRenderer;
  }

}
