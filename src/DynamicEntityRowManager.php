<?php

/**
 * @file
 * Contains \Drupal\views_dynamic_entity_row\DynamicEntityRowManager.
 */

namespace Drupal\views_dynamic_entity_row;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Service class for Views Dynamic Entity Row module configs management.
 */
class DynamicEntityRowManager implements DynamicEntityRowManagerInterface {

  /**
   * The Views Dynamic Entity Row config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $settingsConfig;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Discovery and retrieval of entity type bundles manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleInfo;

  /**
   * Constructs a DynamicEntityRowManager object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   *   The bundle info manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeBundleInfoInterface $bundle_info, EntityTypeManagerInterface $entity_type_manager) {
    $this->settingsConfig = $config_factory
      ->getEditable('views_dynamic_entity_row.settings');
    $this->bundleInfo = $bundle_info;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function isSupported($entity_type_id, $bundle = NULL) {
    $settings = $this->settingsConfig->get('entity_types.' . $entity_type_id);
    if (!isset($settings)) {
      return FALSE;
    }

    if ($bundle) {
      return $this->getSupportMode($entity_type_id) == self::ALL_BUNDLES || (!empty($settings['bundles']) && in_array($bundle, $settings['bundles']));
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  function getSupportMode($entity_type_id) {
    if ($this->settingsConfig->get('entity_types.' . $entity_type_id . '.all')) {
      return self::ALL_BUNDLES;
    }
    else {
      return self::PER_BUNDLE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedBundles($entity_type_id) {
    if (!$this->isSupported($entity_type_id)) {
      return [];
    }

    // Return all bundles list if ALL selected.
    if ($this->getSupportMode($entity_type_id) == self::ALL_BUNDLES) {
      $bundles = $this->bundleInfo->getBundleInfo($entity_type_id);
      return array_keys($bundles);
    }

    return $this
      ->settingsConfig
      ->get('entity_types.' . $entity_type_id . '.bundles');
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicViewMode($entity) {
    $entity_type_id = $entity->getEntityTypeId();
    $uuid = $entity->uuid();

    return $this
      ->settingsConfig
      ->get('dynamic_view_mode.' . $entity_type_id . '.' . $uuid);
  }

  /**
   * {@inheritdoc}
   */
  public function setDynamicViewMode($entity, $view_mode) {
    $entity_type_id = $entity->getEntityTypeId();
    $uuid = $entity->uuid();

    $this
      ->settingsConfig
      ->set('dynamic_view_mode.' . $entity_type_id . '.' . $uuid, $view_mode)
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public function setDynamicViewModeByEntityId($entity_type_id, $entity_id, $view_mode) {
    $entity = $this
      ->entityTypeManager
      ->getStorage($entity_type_id)
      ->load($entity_id);

    if ($entity) {
      $this->setDynamicViewMode($entity, $view_mode);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function dropDynamicViewMode($entity) {
    if ($this->getDynamicViewMode($entity)) {
      $entity_type_id = $entity->getEntityTypeId();
      $uuid = $entity->uuid();

      $this
        ->settingsConfig
        ->delete('dynamic_view_mode.' . $entity_type_id . '.' . $uuid)
        ->save();
    }
  }

}
