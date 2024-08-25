<?php

declare(strict_types=1);

namespace Drupal\plausible_pageviews;

use GuzzleHttp\ClientInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * @todo Add class description.
 */
final class ApiService {

  /**
   * The HTTP client to fetch the API data.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ApiService constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The Guzzle HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(ClientInterface $http_client, ConfigFactoryInterface $config_factory) {
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
  }

  /**
   * Helper method to resolve a node from a path.
   * First check for the provided path, then check for path with guessed langcode.
   */
  private function resolveNodeFromPath(string $path): ?\Drupal\node\Entity\Node {
    $node = NULL;

    // https://drupal.stackexchange.com/questions/197157/how-can-i-get-the-node-id-from-a-path-alias/197163#197163
    $path = \Drupal::service('path_alias.manager')->getPathByAlias($path);
    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      $node = \Drupal\node\Entity\Node::load($matches[1]);
    }

    if (!$node) {
      $url_parts = explode('/', $path);

      if (count($url_parts) > 1) {
        $langcode = $url_parts[1];

        $language_manager = \Drupal::service('language_manager');
        $languages = $language_manager->getLanguages();

        if (isset($languages[$langcode])) {
          $url = '/' . implode('/', array_slice($url_parts, 2));

          $path = \Drupal::service('path_alias.manager')->getPathByAlias($url, $langcode);
          if (preg_match('/node\/(\d+)/', $path, $matches)) {
            $node = \Drupal\node\Entity\Node::load($matches[1]);
          }
        }
      }
    }

    return $node;
  }

  /**
   * @todo Add method description.
   */
  public function setPageviews(): void {
    $config = $this->configFactory->get('plausible_pageviews.settings');

    $bearer_token = $config->get('bearer_token');
    $date_range = $config->get('date_range');
    $site_id = $config->get('site_id');

    if (empty($bearer_token) || empty($date_range || $site_id)) {
      \Drupal::logger('plausible_pageviews')->warning('Missing Bearer token, site ID or date range.');
      return;
    }

    $response = $this->httpClient->request('GET', 'https://plausible.io/api/v1/stats/breakdown', [
      'headers' => [
        'Authorization' => 'Bearer ' . $bearer_token,
      ],
      'query' => [
        'site_id' => $site_id,
        'period' => $date_range,
        'property' => 'event:page',
        'metrics' => 'pageviews',
      ],
    ]);

    $data = json_decode($response->getBody()->getContents(), TRUE);

    if (empty($data)) {
      \Drupal::logger('plausible_pageviews')->warning('No data returned from the API.');
      return;
    }

    if (!array_key_exists('results', $data)) {
      \Drupal::logger('plausible_pageviews')->warning('No results in the API response.');
      return;
    }

    $results = $data['results'];

    $nodes_pageviews = [];

    foreach ($results as $result) {
      $node = $this->resolveNodeFromPath($result['page']);

      if (!$node) {
        continue;
      }

      if (!array_key_exists($node->id(), $nodes_pageviews)) {
        $nodes_pageviews[$node->id()] = [
          'pageviews' => $result['pageviews'],
          'node' => $node,
        ];
      } else {
        $nodes_pageviews[$node->id()]['pageviews'] += $result['pageviews'];
      }
    }

    foreach ($nodes_pageviews as $node_id => $result) {
      $node = $result['node'];
      $pageviews = $result['pageviews'];

      $field_manager = \Drupal::service('entity_field.manager');
      $bundle = $node->bundle();
      $field_definitions = $field_manager->getFieldDefinitions('node', $bundle);

      foreach ($field_definitions as $field_name => $field_definition) {
        if ($field_definition->getType() === 'plausible_pageviews') {
          $node->set($field_name, $pageviews);
          $node->save();
        }
      }
    }

    $message = 'Pageviews updated for ' . count($nodes_pageviews) . ' nodes.';
    \Drupal::logger('plausible_pageviews')->info($message);
  }
}
