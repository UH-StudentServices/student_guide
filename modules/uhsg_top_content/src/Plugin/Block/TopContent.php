<?php

namespace Drupal\uhsg_top_content\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;







/**
 * Provides a 'top_content' block.
 *
 * @Block(
 *  id = "top_content",
 *  admin_label = @Translation("Top content"),
 * )
 */
class TopContent extends BlockBase {

  public function getReport($analytics) {

    // Replace with your view ID. E.g., XXXX.
    $VIEW_ID = "<REPLACE_WITH_VIEW_ID>";

    // Create the DateRange object.
    $dateRange = new Google_Service_AnalyticsReporting_DateRange();
    $dateRange->setStartDate("7daysAgo");
    $dateRange->setEndDate("today");

    // Create the Metrics object.
    $sessions = new Google_Service_AnalyticsReporting_Metric();
    $sessions->setExpression("ga:sessions");
    $sessions->setAlias("sessions");

    // Create the ReportRequest object.
    $request = new Google_Service_AnalyticsReporting_ReportRequest();
    $request->setViewId($VIEW_ID);
    $request->setDateRanges($dateRange);
    $request->setMetrics(array($sessions));

    $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
    $body->setReportRequests( array( $request) );
    return $analytics->reports->batchGet( $body );
  }

  public function printResults($reports) {
    for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
      $report = $reports[ $reportIndex ];
      $header = $report->getColumnHeader();
      $dimensionHeaders = $header->getDimensions();
      $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
      $rows = $report->getData()->getRows();

      for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
        $row = $rows[ $rowIndex ];
        $dimensions = $row->getDimensions();
        $metrics = $row->getMetrics();
        for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
          print($dimensionHeaders[$i] . ": " . $dimensions[$i] . "\n");
        }

        for ($j = 0; $j < count( $metricHeaders ) && $j < count( $metrics ); $j++) {
          $entry = $metricHeaders[$j];
          $values = $metrics[$j];
          print("Metric type: " . $entry->getType() . "\n" );
          for ( $valueIndex = 0; $valueIndex < count( $values->getValues() ); $valueIndex++ ) {
            $value = $values->getValues()[ $valueIndex ];
            print($entry->getName() . ": " . $value . "\n");
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    session_start();

    $client = new Google_Client();
    $client->setAuthConfig(__DIR__ . '/client_secrets.json');
    $client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);


    // If the user has already authorized this app then get an access token
    // else redirect to ask the user to authorize access to Google Analytics.
    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
      // Set the access token on the client.
      $client->setAccessToken($_SESSION['access_token']);

      // Create an authorized analytics service object.
      $analytics = new Google_Service_AnalyticsReporting($client);

      // Call the Analytics Reporting API V4.
      $response = getReport($analytics);

    } else {
      $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php';
      header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    }

    return [
      '#markup' => printResults($response)
    ];
  }
}
